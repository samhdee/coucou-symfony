<?php

namespace App\Security;

use App\Entity\User;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DiscordAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry         $clientRegistry,
        private EntityManagerInterface $em,
        private RouterInterface        $router,
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_discord_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('discord');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var DiscordResourceOwner $discordUser */
                $discordUser = $client->fetchUserFromToken($accessToken);

                $user = $this->em->getRepository(User::class)
                    ->findOneBy(['discordId' => $discordUser->getId()]);

                if (!$user) {
                    $user = new User();
                    $user->setDiscordId($discordUser->getId());
                }

                $user->setUsername($discordUser->getUsername());
                $user->setEmail($discordUser->getEmail());
                $user->setAvatar($discordUser->getAvatarHash());

                $this->em->persist($user);
                $this->em->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($_ENV['FRONTEND_URL']);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($_ENV['FRONTEND_URL'] . '/login?error=auth_failed');
    }
}
