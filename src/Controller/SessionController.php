<?php

namespace App\Controller;

use App\Dto\SessionInput;
use App\Dto\TimerInput;
use App\Entity\Session;
use App\Entity\Timer;
use App\Entity\TimersPreset;
use App\Entity\User;
use App\Enum\TimerPresetAdded;
use App\Enum\TimerStatus;
use App\Repository\TimersPresetRepository;
use App\Service\Mercure;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

// #[Route('/session')]
final class SessionController extends AbstractController
{
    /**
     * Crée le timer
     * @param TimerInput $input
     * @param User|null $user
     * @param EntityManagerInterface $em
     * @param TimersPresetRepository $presetRepo
     * @return JsonResponse
     */
    #[Route('/session', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] TimerInput $input,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        TimersPresetRepository $presetRepo
    ): JsonResponse
    {
        $timer = new Timer($input->type, $input->value, $input->status);

        if (!empty($user)) {
            $timer->setUser($user);
        }

        $em->persist($timer);
        $em->flush();

        $session = new Session($timer);
        $em->persist($session);
        $em->flush();

        $timer->setSession($session);

        $existing_presets = $presetRepo->getByValue($input->value, $input->type);

        if (empty($existing_presets)) {
            $timers_preset = new TimersPreset($input->value, $input->type);
            $em->persist($timers_preset);
        } else {
            $presetRepo->incrementCount($existing_presets['id']);
        }

        $em->flush();
        return $this->json($session, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
    }

    /**
     * Récupère le timer
     * @param Session $session
     * @return JsonResponse
     */
    #[Route('/session/{id}')]
    public function get(Session $session): JsonResponse
    {
        return $this->json($session, context: ['groups' => 'session:read']);
    }

    /**
     * @param SessionInput $input
     * @param Session $session
     * @param EntityManagerInterface $em
     * @param TimersPresetRepository $presetRepo
     * @param Mercure $mercure
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route('/session/{id}/timer/next')]
    public function createNextTimer(
        #[MapRequestPayload] SessionInput $input,
        Session                $session,
        EntityManagerInterface $em,
        TimersPresetRepository $presetRepo,
        Mercure $mercure
    ): JsonResponse
    {
        // Quelqu'un a déjà avancé : on renvoie l'état réel sans rien créer
        if ($session->getCurrentTimer()->getId() !== $input->fromTimer) {
            return $this->json($session, context: ['groups' => 'session:read']);
        }

        // Crée le nouveau timer
        $timer = new Timer($input->type, $input->value, TimerStatus::Running);
        $timer->setSession($session);
        $em->persist($timer);

        try {
            // Met à jour la session
            $session->setCurrentTimer($timer);
            $em->flush();

            if (empty($existing_presets)) {
                $timers_preset = new TimersPreset($input->value, $input->type);
                $em->persist($timers_preset);
            } else {
                $presetRepo->incrementCount($existing_presets['id']);
            }

            // Envoie une notif
            $mercure->sessionUpdated('session', $session->getId(), $timer);

            return $this->json($session, Response::HTTP_CREATED, context: ['groups' => 'session:read']);
        } catch (OptimisticLockException|UniqueConstraintViolationException) {
            // Un autre client a gagné la course : on relit l'état
            $em->clear();
            $session = $em->find(Session::class, $session->getId());

            return $this->json($session, context: ['groups' => 'session:read']);
        } catch (ORMException $e) {
            return $this->json(['result' => 'Session not found'], Response::HTTP_NOT_FOUND);
        } catch (JsonException $e) {
            return $this->json(['result' => 'Session not updated'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Démarre le timer
     * @param Session $session
     * @param EntityManagerInterface $em
     * @param Mercure $mercure
     * @return JsonResponse
     * @throws DateMalformedStringException
     * @throws JsonException
     */
    #[Route('/session/{id}/start')]
    public function start(Session $session, EntityManagerInterface $em, Mercure $mercure): JsonResponse
    {
        $timer = $session->getCurrentTimer();

        if ($timer->getStatus() !== TimerStatus::Paused) {
            throw new \LogicException('Le timer ne peut être démarré que depuis l\'état pause.');
        }

        // Démarrer le timer
        $now = new DateTimeImmutable();
        $timer->setEndsAt($now->modify('+' . $timer->getRemainingAtPause() . ' seconds'));
        $timer->setStatus(TimerStatus::Running);
        $em->flush();

        // Envoie une notif
        $mercure->sessionUpdated('session', $session->getId(), $timer);

        return $this->json($session, context: ['groups' => 'session:read']);
    }

    /**
     * Met le timer sur pause
     * @param Session $session
     * @param EntityManagerInterface $em
     * @param Mercure $mercure
     * @return JsonResponse
     * @throws JsonException
     */
    #[Route('/session/{id}/pause', methods: ['PATCH'])]
    public function pause(Session $session, EntityManagerInterface $em, Mercure $mercure): JsonResponse
    {
        $timer = $session->getCurrentTimer();

        if ($timer->getStatus() !== TimerStatus::Running) {
            throw new \LogicException('Seul un timer en cours peut être mis en pause.');
        }

        // Met le timer sur pause
        $now = new DateTimeImmutable();
        $timer->setRemainingAtPause(max(0, $timer->getEndsAt()->getTimestamp() - $now->getTimestamp()));
        $timer->setEndsAt(null);
        $timer->setStatus(TimerStatus::Paused);
        $em->flush();

        // Envoi d’une notif
        $mercure->sessionUpdated('session', $session->getId(), $timer);

        return $this->json($session, context: ['groups' => 'session:read']);
    }
}
