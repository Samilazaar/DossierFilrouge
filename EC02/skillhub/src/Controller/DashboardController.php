<?php

namespace App\Controller;

use App\Service\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private DataRepository $repository;

    public function __construct(DataRepository $repository)
    {
        $this->repository = $repository;
    }

    private function checkUser(SessionInterface $session): ?\App\Model\User
    {
        $userId = $session->get('user_id');
        if (!$userId) {
            return null;
        }
        return $this->repository->findUserById($userId);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $ateliers = $this->repository->findAllAteliers();
        $userInscriptions = $this->repository->findInscriptionsByUser($user);
        $inscritAtelierIds = array_map(function($inscription) {
            return $inscription->getAtelier()->getId();
        }, $userInscriptions);

        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Tableau de Bord',
            'ateliers' => $ateliers,
            'active_view' => 'ateliers',
            'user' => $user,
            'inscrit_atelier_ids' => $inscritAtelierIds,
        ]);
    }

    #[Route('/dashboard/inscriptions', name: 'app_dashboard_inscriptions')]
    public function inscriptions(SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $inscriptions = $this->repository->findInscriptionsByUser($user);

        return $this->render('dashboard/inscriptions.html.twig', [
            'page_title' => 'Mes Inscriptions',
            'inscriptions' => $inscriptions,
            'active_view' => 'inscriptions',
            'user' => $user,
        ]);
    }

    #[Route('/dashboard/atelier/{id}', name: 'app_atelier_detail')]
    public function detail(int $id, SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $atelier = $this->repository->findAtelierById($id);
        if (!$atelier) {
            throw $this->createNotFoundException('Atelier non trouvé');
        }

        $inscription = $this->repository->findInscription($user, $atelier);
        $estInscrit = $inscription !== null;

        return $this->render('dashboard/atelier_detail.html.twig', [
            'page_title' => $atelier->getTitre(),
            'atelier' => $atelier,
            'user' => $user,
            'estInscrit' => $estInscrit,
            'inscription' => $inscription,
        ]);
    }

    #[Route('/dashboard/atelier/{id}/inscrire', name: 'app_inscrire_atelier', methods: ['POST'])]
    public function inscrire(int $id, Request $request, SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $atelier = $this->repository->findAtelierById($id);
        if (!$atelier) {
            throw $this->createNotFoundException('Atelier non trouvé');
        }

        if ($this->repository->findInscription($user, $atelier)) {
            $request->getSession()->getFlashBag()->add('warning', 'Vous êtes déjà inscrit à cet atelier.');
            return $this->redirectToRoute('app_atelier_detail', ['id' => $id]);
        }

        if ($atelier->estComplet()) {
            $request->getSession()->getFlashBag()->add('error', 'Cet atelier est complet.');
            return $this->redirectToRoute('app_atelier_detail', ['id' => $id]);
        }

        $inscription = new \App\Model\Inscription(
            count($this->repository->findAllInscriptions()) + 1,
            $user,
            $atelier
        );

        $this->repository->addInscription($inscription);

        $request->getSession()->getFlashBag()->add('success', 'Inscription réussie !');
        return $this->redirectToRoute('app_atelier_detail', ['id' => $id]);
    }

    #[Route('/dashboard/atelier/{id}/desinscrire', name: 'app_desinscrire_atelier', methods: ['POST'])]
    public function desinscrire(int $id, Request $request, SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        $atelier = $this->repository->findAtelierById($id);
        if (!$atelier) {
            throw $this->createNotFoundException('Atelier non trouvé');
        }

        $inscription = $this->repository->findInscription($user, $atelier);
        if (!$inscription) {
            $request->getSession()->getFlashBag()->add('warning', 'Vous n\'êtes pas inscrit à cet atelier.');
            return $this->redirectToRoute('app_atelier_detail', ['id' => $id]);
        }

        $this->repository->removeInscription($inscription);

        $request->getSession()->getFlashBag()->add('success', 'Désinscription réussie !');
        return $this->redirectToRoute('app_dashboard_inscriptions');
    }
}
