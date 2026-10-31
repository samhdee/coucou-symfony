<?php

namespace App\Controller;

use App\Entity\SevenByNine;
use App\Entity\Timer;
use App\Enum\TimerStatus;
use App\Enum\TimerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $timer = new Timer(TimerType::Task, $this::TIMER_TASK_DURATION, TimerStatus::Paused, $user);
        $em->persist($timer);

        // Création du 7x9
        $seven_by_nine = new SevenByNine($timer, rand(1, SevenByNine::TIMER_TASK_MAX), $user);
        $em->persist($seven_by_nine);

        $em->flush();
        return $this->json($seven_by_nine, Response::HTTP_CREATED, context: ['groups' => 'seven_by_nine:read']);
    }

    /**
     * @param SevenByNine $seven_by_nine
     * @return JsonResponse
     */
    #[Route('/seven-by-nine/{id}')]
    public function get(SevenByNine $seven_by_nine): JsonResponse
    {
        return $this->json($seven_by_nine, context: ['groups' => 'seven_by_nine:read']);
    }

    /**
     * Ajoute le timer suivant
     * @param SevenByNine $seven_by_nine
     * @param UserInterface|null $user
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/seven-by-nine/{id}/next')]
    public function createNextTimer(
        SevenByNine            $seven_by_nine,
        ?UserInterface         $user,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        if (
            $seven_by_nine->getCountTasks() === $seven_by_nine::TIMER_TASK_MAX
            && $seven_by_nine->getCountPauses() === $seven_by_nine::TIMER_PAUSE_MAX
        ) {
            return $this->json(['result' => 'done'], Response::HTTP_FORBIDDEN);
        }

        $new_timer_type = $seven_by_nine->getLastTimerType() === TimerType::Task
            ? TimerType::Pause
            : TimerType::Task;

        // Crée le nouveau timer
        $timer = new Timer(
            $new_timer_type,
            $new_timer_type ? $this::TIMER_TASK_DURATION : $this::TIMER_PAUSE_DURATION,
            user: $user
        );
        $em->persist($timer);

        // Met à jour le 7x9
        $seven_by_nine->setLastTimerType($new_timer_type);

        if ($new_timer_type === TimerType::Task) {
            $seven_by_nine->incrementTaskCount();

            // Tire au sort le prochain numéro
            $drawn_values = $seven_by_nine->getDrawnValues();
            $new_draw = rand(1, SevenByNine::TIMER_TASK_MAX);

            while (in_array($new_draw, $drawn_values)) {
                $new_draw = rand(1, SevenByNine::TIMER_TASK_MAX);
            }

            $drawn_values[] = $new_draw;
            $seven_by_nine->setDrawnValues($drawn_values);
        } else {
            $seven_by_nine->incrementPauseCount();
        }

        $em->flush();
        return $this->json($seven_by_nine, Response::HTTP_CREATED, context: ['groups' => 'seven_by_nine:read']);
    }
}
