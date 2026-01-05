<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConnexionController extends AbstractController
{
    #[Route('/connexion', name: 'app_connexion')]
    public function index(Request $request): Response
    {
        $testUsers = [
            [
                'email' => 'test@skillhub.com',
                'password' => 'password123',
                'nom' => 'Utilisateur Test',
            ],
            [
                'email' => 'admin@skillhub.com',
                'password' => 'admin123',
                'nom' => 'Admin',
            ],
        ];

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $found = false;
            $userNom = '';

            foreach ($testUsers as $user) {
                if ($user['email'] === $email && $user['password'] === $password) {
                    $found = true;
                    $userNom = $user['nom'];
                    break;
                }
            }

            if ($found) {
                return $this->render('connexion/index.html.twig', [
                    'page_title' => 'Connexion',
                    'success' => true,
                    'message' => "Bienvenue {$userNom} ! Connexion réussie.",
                ]);
            } else {
                return $this->render('connexion/index.html.twig', [
                    'page_title' => 'Connexion',
                    'error' => true,
                    'errorMessage' => 'Email ou mot de passe incorrect.',
                ]);
            }
        }
        
        return $this->render('connexion/index.html.twig', [
            'page_title' => 'Connexion',
        ]);
    }
}
