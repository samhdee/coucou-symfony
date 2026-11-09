<?php

namespace App\Controller;

use App\Dto\SevenByNineNextInput;
use App\Entity\SevenByNine;
use App\Entity\Timer;
use App\Entity\User;
use App\Enum\TimerStatus;
use App\Enum\TimerType;
use App\Repository\SeanceAttendantRepository;
use App\Service\Mercure;
use DateMalformedStringException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Uid\Uuid;

final class SevenByNineController extends AbstractController
{
    private const int TIMER_TASK_DURATION = 540;
    private const int TIMER_PAUSE_DURATION = 300;

    /**
     * Crée le 7x9
     * @param UserInterface|null $user
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/seven-by-nine')]
    public function create(?UserInterface $user, EntityManagerInterface $em): JsonResponse
    {
        // Création du premier timer en mode pause
        $timer = new Timer(TimerType::Task, $this::TIMER_TASK_DURATION, TimerStatus::Paused, 1);
        $em->persist($timer);

        // Création du 7x9
        $seven_by_nine = new SevenByNine($timer, rand(1, SevenByNine::TIMER_TASK_MAX), $user);
        $em->persist($seven_by_nine);

        $em->flush();
        return $this->json($seven_by_nine, Response::HTTP_CREATED, context: ['groups' => 'seven_by_nine:read']);
    }

    /**
     * @param User|null $user
     * @param EntityManagerInterface $em
     * @param Mercure $mercure
     * @param SevenByNine $seven_by_nine
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    #[Route('/seven-by-nine/{id}')]
    public function get(
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        Mercure $mercure,
        Request $request,
        SeanceAttendantRepository $seanceAttendantRepository,
        SevenByNine $seven_by_nine
    ): JsonResponse
    {
        if (!empty($user)) {
            $seanceAttendantRepository->isUserAttendingSevenByNine($seven_by_nine, $user);
            $em->flush();
            $mercure->sevenByNineJoined($seven_by_nine);
        } else {
            $guest_id = $request->cookies->get('guest_id') ?? Uuid::v4()->toRfc4122();


            $request->cookies->set('guest_id', $guest_id);
        }

        return $this->json($seven_by_nine, context: ['groups' => 'seven_by_nine:read']);
    }

    /**
     * Ajoute le timer suivant
     * @param SevenByNineNextInput $input
     * @param SevenByNine $seven_by_nine
     * @param Mercure $mercure
     * @param EntityManagerInterface $em
     * @return JsonResponse
     * @throws JsonException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ExceptionInterface
     */
    #[Route('/seven-by-nine/{id}/next')]
    public function createNextTimer(
        #[MapRequestPayload] SevenByNineNextInput $input,
        SevenByNine                               $seven_by_nine,
        Mercure                                   $mercure,
        EntityManagerInterface                    $em,
    ): JsonResponse
    {
        if (
            $seven_by_nine->getCountTasks() === $seven_by_nine::TIMER_TASK_MAX
            && $seven_by_nine->getCountPauses() === $seven_by_nine::TIMER_PAUSE_MAX
        ) {
            return $this->json(['result' => 'done'], Response::HTTP_FORBIDDEN);
        }

        // Quelqu'un a déjà avancé : on renvoie l'état réel sans rien créer
        if ($seven_by_nine->getCurrentTimer()->getPosition() !== $input->fromPosition) {
            return $this->json($seven_by_nine, context: ['groups' => 'seven_by_nine:read']);
        }

        $new_timer_type = $seven_by_nine->getCurrentTimer()->getType() === TimerType::Task
            ? TimerType::Pause
            : TimerType::Task;

        // Crée le nouveau timer
        $timer = new Timer(
            $new_timer_type,
            $new_timer_type === TimerType::Task ? $this::TIMER_TASK_DURATION : $this::TIMER_PAUSE_DURATION,
            TimerStatus::Running,
            $seven_by_nine->getCurrentTimer()->getPosition() + 1,
        );
        $em->persist($timer);

        // Envoie une notif
        $mercure->sevenByNineUpdated($seven_by_nine);

        try {
            // Met à jour le 7x9
            $seven_by_nine->setPreviousTimer($seven_by_nine->getCurrentTimer());
            $seven_by_nine->setCurrentTimer($timer);

            if ($new_timer_type === TimerType::Task) {
                $seven_by_nine->incrementTaskCount();

                // Tire au sort le prochain numéro
                $seven_by_nine->drawNewValue();
            } else {
                $seven_by_nine->incrementPauseCount();
            }

            $em->flush();

            return $this->json($seven_by_nine, Response::HTTP_CREATED, context: ['groups' => 'seven_by_nine:read']);
        } catch (OptimisticLockException|UniqueConstraintViolationException) {
            // Un autre client a gagné la course : on relit l'état
            $em->clear();
            $session = $em->find(SevenByNine::class, $seven_by_nine->getId());

            return $this->json($session->getCurrentTimer(), context: ['groups' => 'seven_by_nine:read']);
        } catch (ORMException $e) {
            return $this->json(['result' => 'Timer not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Démarre le timer
     * @param SevenByNine $sevenByNine
     * @param EntityManagerInterface $em
     * @param Mercure $mercure
     * @return JsonResponse
     * @throws JsonException
     * @throws DateMalformedStringException
     * @throws ExceptionInterface
     */
    #[Route('/seven-by-nine/{id}/start')]
    public function start(SevenByNine $sevenByNine, EntityManagerInterface $em, Mercure $mercure): JsonResponse
    {
        $timer = $sevenByNine->getCurrentTimer();

        if ($timer->getStatus() !== TimerStatus::Paused) {
            throw new \LogicException('Le timer ne peut être démarré que depuis l\'état pause.');
        }

        // Démarrer le timer
        $timer->start();
        $em->flush();

        // Envoie une notif
        $mercure->sevenByNineUpdated($sevenByNine, $timer);

        return $this->json($sevenByNine, context: ['groups' => 'seven_by_nine:read']);
    }

    /**
     * Met le timer sur pause
     * @param SevenByNine $sevenByNine
     * @param EntityManagerInterface $em
     * @param Mercure $mercure
     * @return JsonResponse
     * @throws JsonException
     * @throws ExceptionInterface
     */
    #[Route('/seven-by-nine/{id}/pause', methods: ['PATCH'])]
    public function pause(SevenByNine $sevenByNine, EntityManagerInterface $em, Mercure $mercure): JsonResponse
    {
        $timer = $sevenByNine->getCurrentTimer();

        if ($timer->getStatus() !== TimerStatus::Running) {
            throw new \LogicException('Seul un timer en cours peut être mis en pause.');
        }

        // Met le timer sur pause
        $timer->pause();
        $em->flush();

        // Envoi d’une notif
        $mercure->sevenByNineUpdated($sevenByNine, $timer);

        return $this->json($sevenByNine, context: ['groups' => 'seven_by_nine:read']);
    }
}
