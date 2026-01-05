<?php

namespace App\Controller;

class InscriptionController
{
    public function index(): string
    {
        $success = false;
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $success = true;
            $message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        }

        ob_start();
        require __DIR__ . '/../../templates/inscription/index.html.twig';
        return ob_get_clean();
    }
}
