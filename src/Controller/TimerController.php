<?php

namespace App\Controller;

use App\Dto\TimerInput;
use App\Entity\Timer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

final class TimerController extends AbstractController
{
    #[Route('/timer', methods: ['POST'])]
    public function create(#[MapRequestPayload] TimerInput $input, EntityManagerInterface $em): JsonResponse
    {
        $timer = new Timer($input->label, $input->value);
        $timer->setStatus($input->status);

        $em->persist($timer);
        $em->flush();

        return $this->json($timer, Response::HTTP_CREATED);
    }

    #[Route('/timer/{id}/pause', methods: ['POST'])]
    public function pause(Timer $timer, EntityManagerInterface $em): JsonResponse
    {
        $timer->pause();
        $em->flush();

        return $this->json($timer);
    }
}
