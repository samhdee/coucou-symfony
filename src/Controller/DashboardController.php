<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashoard')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class DashboardController extends AbstractController
{
    /**
     * Crée le timer
     * @param $user
     * @return JsonResponse
     */
    #[Route('/', methods: ['POST'])]
    public function index(#[CurrentUser] $user, UserRepository $repository): JsonResponse
    {
        $timer = new Timer($input->type, $input->value, $input->status);

        return $this->json($timer, Response::HTTP_CREATED, context: ['groups' => 'user:read']);
    }
}
