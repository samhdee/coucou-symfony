<?php

namespace App\Controller;

use App\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthController extends AbstractController
{
    #[Route('/auth/discord', name: 'connect_discord_start')]
    public function connect(Request $request, ClientRegistry $clientRegistry): Response
    {
        $redir = $request->query->get('redir');

        if (!empty($redir) && str_starts_with($redir, '/') && !str_starts_with($redir, '//')) {
            $request->getSession()->set('oauth_redir', $redir);
        }

        return $clientRegistry->getClient('discord')->redirect(['identify', 'email'], []);
    }

    #[Route('/auth/discord/check', name: 'connect_discord_check')]
    public function check(): void
    {
        // Intercepté par l'authenticator, ce code n'est jamais exécuté
    }

    #[Route('/auth/me', name: 'api_me')]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(null, 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'discordId' => $user->getDiscordId(),
            'username' => $user->getUsername(),
            'avatar' => $user->getAvatar(),
        ]);
    }

    #[Route('/auth/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): never
    {
        // Ne sera jamais exécuté : Symfony intercepte la requête via le firewall
        throw new \LogicException('Intercepted by the logout key on the firewall.');
    }
}
