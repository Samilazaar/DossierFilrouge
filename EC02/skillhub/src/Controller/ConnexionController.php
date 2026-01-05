<?php

namespace App\Controller;

class ConnexionController
{
    public function index(): string
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

        $success = false;
        $message = '';
        $error = false;
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

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
                $success = true;
                $message = "Bienvenue {$userNom} ! Connexion réussie.";
            } else {
                $error = true;
                $errorMessage = 'Email ou mot de passe incorrect.';
            }
        }

        ob_start();
        require __DIR__ . '/../../templates/connexion/index.html.twig';
        return ob_get_clean();
    }
}
