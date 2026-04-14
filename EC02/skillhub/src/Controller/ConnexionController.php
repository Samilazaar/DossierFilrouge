<?php

namespace App\Controller;

use App\Service\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ConnexionController extends AbstractController
{
    private DataRepository $repository;

    public function __construct(DataRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Route('/connexion', name: 'app_connexion')]
    public function index(Request $request, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $user = $this->repository->findUserByEmail($email);

            if ($user && $user->verifyPassword($password)) {
                $session->set('user_id', $user->getId());
                $session->set('user_email', $user->getEmail());
                $session->set('user_nom', $user->getNom());
                $session->set('user_prenom', $user->getPrenom());

                return $this->redirectToRoute('app_dashboard');
            }

            return $this->render('connexion/index.html.twig', [
                'page_title' => 'Connexion',
                'error' => true,
                'errorMessage' => 'Email ou mot de passe incorrect.',
            ]);
        }

        return $this->render('connexion/index.html.twig', [
            'page_title' => 'Connexion',
        ]);
    }

    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function deconnexion(SessionInterface $session): Response
    {
        $session->clear();
        return $this->redirectToRoute('app_connexion');
    }
}
