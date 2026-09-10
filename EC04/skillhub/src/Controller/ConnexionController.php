<?php

namespace App\Controller;

use App\Entity\Session;
use App\Service\DoctrineDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class ConnexionController extends AbstractController
{
    private DoctrineDataRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(DoctrineDataRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
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

                $existingSession = $this->entityManager
                    ->getRepository(Session::class)
                    ->findOneBy(['user' => $user]);

                if ($existingSession) {
                    $existingSession->setSessionId($session->getId());
                    $existingSession->setLastActivity(new \DateTime());
                    $existingSession->setIpAddress($request->getClientIp());
                } else {
                    $sessionEntity = new Session();
                    $sessionEntity->setUser($user);
                    $sessionEntity->setSessionId($session->getId());
                    $sessionEntity->setIpAddress($request->getClientIp());
                    $this->entityManager->persist($sessionEntity);
                }
                $this->entityManager->flush();

                if ($user->isFormateur()) {
                    return $this->redirectToRoute('app_dashboard_formateur');
                }
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
