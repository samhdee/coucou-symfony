<?php

namespace App\Controller;

use App\Service\VisitorIdentity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\Grant;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PresenceController extends AbstractController
{
    #[Route('/api/presence-token/{type}/{id}')]
    public function token(
        Request         $request,
        VisitorIdentity $identity,
        HubInterface    $hub,
        string          $type,
        int             $id,
    ): Response
    {
        $topic = sprintf('/%s/%d', $type, $id); // valider $type contre une liste blanche !
        $who = $identity->resolve($request);
        $token = $hub->getFactory()->create([
            new Grant(
                [Grant::ACTION_SUBSCRIBE],
                ['/.well-known/mercure/subscriptions{/topic}{/subscriber}'],
            ),
        ]);
        $response = new JsonResponse(['ok' => true]);
        $response->headers->setCookie(Cookie::create('mercureAuthorization', $token, path: '/.well-known/mercure'));

        if ($who['guestId']) {
            $response->headers->setCookie(Cookie::create('guest_id', $who['guestId']));
        }

        return $response;
    }
}
