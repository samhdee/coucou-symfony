<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Routing\Attribute\Route;

class MercureAuthController extends AbstractController
{
    #[Route('/mercure-auth', methods: ['GET'])]
    public function __invoke(Request $request, Authorization $authorization): Response
    {
        $response = new Response();
        $cookie = $authorization->createCookie($request, ['test']);

        $response->headers->setCookie(
            new Cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                '/',
                null,
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                false,
                $cookie->getSameSite(),
            )
        );

        return $response;
    }
}
