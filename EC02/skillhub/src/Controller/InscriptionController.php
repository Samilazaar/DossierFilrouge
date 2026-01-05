<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            
            return $this->render('inscription/index.html.twig', [
                'page_title' => 'Inscription',
                'success' => true,
                'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.',
            ]);
        }
        
        return $this->render('inscription/index.html.twig', [
            'page_title' => 'Inscription',
        ]);
    }
}
