<?php

namespace App\Controller;

use App\Service\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    private DataRepository $repository;

    public function __construct(DataRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/inscription', name: 'app_inscription')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $telephone = $request->request->get('telephone');

            $errors = [];

            if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || empty($password)) {
                $errors[] = 'Tous les champs sont obligatoires.';
            }

            if ($this->repository->findUserByEmail($email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            }

            if (strlen($password) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            if ($password !== $passwordConfirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (!preg_match('/^[0-9]{10}$/', $telephone)) {
                $errors[] = 'Le numéro de téléphone doit contenir 10 chiffres.';
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $user = new \App\Model\User(
                    count($this->repository->findAllUsers()) + 1,
                    $nom,
                    $prenom,
                    $email,
                    $telephone,
                    $hashedPassword
                );

                $this->repository->addUser($user);

                return $this->render('inscription/index.html.twig', [
                    'page_title' => 'Inscription',
                    'success' => true,
                    'message' => 'Inscription réussie ! Vous pouvez maintenant vous connecter.',
                ]);
            }

            return $this->render('inscription/index.html.twig', [
                'page_title' => 'Inscription',
                'errors' => $errors,
            ]);
        }

        return $this->render('inscription/index.html.twig', [
            'page_title' => 'Inscription',
        ]);
    }
}
