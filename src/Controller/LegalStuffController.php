<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalStuffController extends AbstractController
{
    #[Route('/legal/terms-of-use', name: 'app_legal_stuff_tos')]
    public function index(): Response
    {
        return $this->render('legal_stuff/index.html.twig', [
            'controller_name' => 'LegalStuffController',
        ]);
    }
    #[Route('/legal/confidentiality', name: 'app_legal_stuff_confidentiality')]
    public function confidentiality(): Response
    {
        return $this->render('legal_stuff/confidentiality.html.twig', [
            'controller_name' => 'LegalStuffController',
        ]);
    }
}
