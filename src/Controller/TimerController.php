<?php

namespace App\Controller;

use App\Dto\TimerInput;
use App\Entity\Timer;
use App\Entity\TimersPreset;
use App\Enum\TimerPresetAdded;
use App\Enum\TimerStatus;
use App\Enum\TimerType;
use App\Repository\TimersPresetRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

final class TimerController extends AbstractController
{
    #[Route('/timers_preset')]
    public function getTimersPreset(TimersPresetRepository $presetRepository): JsonResponse
    {
        $timers_preset = $presetRepository->getAllActive();

        return $this->json($timers_preset, Response::HTTP_CREATED);
    }

    /**
     * Crée le timer
     * @param TimerInput $input
     * @param EntityManagerInterface $em
     * @param TimersPresetRepository $presetRepo
     * @return JsonResponse
     */
    #[Route('/timer', methods: ['POST'])]
    public function create(#[MapRequestPayload] TimerInput $input, EntityManagerInterface $em, TimersPresetRepository $presetRepo): JsonResponse
    {
        $timer = new Timer($input->type, $input->value, $input->status);
        $em->persist($timer);
        $em->flush();

        $existing_presets = $presetRepo->getByValue($input->value);

        if (empty($existing_presets)) {
            $timers_preset = new TimersPreset($input->value, $input->type, TimerPresetAdded::NewTimer);
            $em->persist($timers_preset);
            $em->flush();
        }

        return $this->json($timer, Response::HTTP_CREATED);
    }

    /**
     * Démarre le timer
     * @param Timer $timer
     * @param EntityManagerInterface $em
     * @param HubInterface $hub
     * @return JsonResponse
     * @throws DateMalformedStringException
     */
    #[Route('/timer/{id}/start')]
    public function start(Timer $timer, EntityManagerInterface $em, HubInterface $hub): JsonResponse
    {
        if ($timer->getStatus() !== TimerStatus::Paused) {
            throw new \LogicException('Le timer ne peut être démarré que depuis l\'état pause.');
        }

        // Démarrer le timer
        $now = new DateTimeImmutable();
        $timer->setEndsAt($now->modify('+' . $timer->getRemainingAtPause() . ' seconds'));
        $timer->setStatus(TimerStatus::Running);
        $em->flush();

        // Envoie une notif
        $hub->publish(new Update(
            "timers/{$timer->getId()}",
            json_encode([
                'status' => $timer->getStatus()->value,
                'remainingSeconds' => $timer->getRemainingSeconds(),
            ]),
        ));

        return $this->json($timer);
    }

    /**
     * Récupère le timer
     * @param Timer $timer
     * @return JsonResponse
     */
    #[Route('/timer/{id}')]
    public function get(Timer $timer): JsonResponse
    {
        return $this->json($timer);
    }

    /**
     * Met le timer sur pause
     * @param Timer $timer
     * @param EntityManagerInterface $em
     * @param HubInterface $hub
     * @return JsonResponse
     */
    #[Route('/timer/{id}/pause', methods: ['PATCH'])]
    public function pause(Timer $timer, EntityManagerInterface $em, HubInterface $hub): JsonResponse
    {
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
        $hub->publish(new Update(
            "timer/{$timer->getId()}",
            json_encode([
                'status' => $timer->getStatus()->value,
                'remainingSeconds' => $timer->getRemainingSeconds(),
                'remainingAtPause' => $timer->getRemainingAtPause(),
                'endsAt' => $timer->getEndsAt(),
            ]),
        ));

        return $this->json($timer);
    }

    /**
     * Réinitialise le timer
     * @param Timer $timer
     * @param EntityManagerInterface $em
     * @param HubInterface $hub
     * @return JsonResponse
     * @throws DateMalformedStringException
     */
    #[Route('/timer/{id}/reset', methods: ['PATCH'])]
    public function reset(Timer $timer, EntityManagerInterface $em, HubInterface $hub): JsonResponse
    {
        // Réinitialise le timer
        $now = new DateTimeImmutable();
        $timer->setEndsAt($now->modify('+' . $timer->getValue() . ' seconds'));
        $timer->setStatus(TimerStatus::Running);
        $em->flush();

        // Envoie une notif à Mercure
        $hub->publish(new Update(
            "timer/{$timer->getId()}",
            json_encode([
                'status' => $timer->getStatus()->value,
                'remainingSeconds' => $timer->getRemainingSeconds(),
                'remainingAtPause' => $timer->getRemainingAtPause(),
                'endsAt' => $timer->getEndsAt(),
            ]),
        ));

        return $this->json($timer);
    }
}
