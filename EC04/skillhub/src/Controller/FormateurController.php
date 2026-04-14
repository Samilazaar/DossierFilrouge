<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserFormateurRepository;

class FormateurController extends AbstractController
{
    #[Route('/formateurs', name: 'app_formateurs')]
    public function liste(UserFormateurRepository $repository): Response
    {
        $formateurs = $repository->findAll();
  
        return $this->render('formateur/liste.html.twig', [
            'page_title' => 'Liste des Formateurs',
            'formateurs' => $formateurs,
        ]);
    }
}
