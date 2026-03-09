<?php

namespace App\Controller;

use App\Service\DoctrineDataRepository;
use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\Inscription;
use App\Entity\UserFormateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\IAService;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{
    private DoctrineDataRepository $repository;
    private ?object $documentManager;
    private IAService $iaService;
    private EntityManagerInterface $entityManager;

    public function __construct(DoctrineDataRepository $repository, IAService $iaService, EntityManagerInterface $entityManager, ?object $documentManager = null)
    {
        $this->repository = $repository;
        $this->documentManager = $documentManager;
        $this->iaService = $iaService;
        $this->entityManager = $entityManager;
    }

    private function checkUser(SessionInterface $session): ?User
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

    #[Route('/dashboard/atelier/recommend', name: 'app_recommend_atelier')]
    public function recommendAtelier(SessionInterface $session): Response
    {
        $user=$this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }
        $competences=$user->getCompetences() ?? [];
        $ateliers=$this->repository->findAllAteliers();
        $recommendation=$this->iaService->recommendAtelier($competences, $ateliers);
        return $this->render('dashboard/recommend_atelier.html.twig', [
            'page_title' => 'Recommandation d\'atelier',
            'recommendation' => $recommendation,
            'user' => $user,
            'ateliers' => $ateliers,
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

        $feedbacks = [];
        if ($this->documentManager) {
            $feedbackClass = 'App\Document\Feedback';
            $feedbacks = $this->documentManager->getRepository($feedbackClass)->findBy(['atelierId' => (string) $id]);
        }

        return $this->render('dashboard/atelier_detail.html.twig', [
            'page_title' => $atelier->getTitre(),
            'atelier' => $atelier,
            'user' => $user,
            'estInscrit' => $estInscrit,
            'inscription' => $inscription,
            'feedbacks' => $feedbacks,
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

        $inscription = new Inscription();
        $inscription->setUtilisateur($user);
        $inscription->setAtelier($atelier);
        $inscription->setDateInscription(new \DateTime());

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

    #[Route('/dashboard/formateur', name: 'app_dashboard_formateur')]
    public function formateurDashboard(SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        if (!$user->isFormateur()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $formateurProfile = $this->repository->findFormateurByUser($user);
        $mesAteliers = $this->repository->findAteliersByFormateur($formateurProfile);

        return $this->render('dashboard/formateur.html.twig', [
            'page_title' => 'Dashboard Formateur',
            'mes_ateliers' => $mesAteliers,
            'formateur_profile' => $formateurProfile,
            'active_view' => 'formateur',
            'user' => $user,
        ]);
    }

    #[Route('/dashboard/formateur/atelier/creer', name: 'app_creer_atelier')]
    public function creerAtelier(Request $request, SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user || !$user->isFormateur()) {
            return $this->redirectToRoute('app_dashboard');
        }
  
        $formateurProfile = $this->repository->findFormateurByUser($user);
  
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $description = $request->request->get('description');
            $date = $request->request->get('date');
            $duree = $request->request->get('duree');
            $capaciteMax = (int) $request->request->get('capacite_max');
            $imageUrl = $request->request->get('image_url');
  
            $errors = [];
  
            if (empty($titre) || empty($description) || empty($date) || empty($duree) || $capaciteMax <= 0) {
                $errors[] = 'Tous les champs sont obligatoires.';
            }
  
            if (empty($errors)) {
                $atelier = new Atelier();
                $atelier->setTitre($titre);
                $atelier->setDescription($description);
                $atelier->setDate(new \DateTime($date));
                $atelier->setDuree($duree);
                $atelier->setCapaciteMax($capaciteMax);
                $atelier->setPlacesRestantes($capaciteMax);
                $atelier->setImageUrl($imageUrl ?: 'https://via.placeholder.com/400x200');
                $atelier->setFormateur($formateurProfile);
  
                $this->repository->addAtelier($atelier);
  
                return $this->redirectToRoute('app_dashboard_formateur');
            }
  
            return $this->render('dashboard/creer_atelier.html.twig', [
                'page_title' => 'Créer un Atelier',
                'user' => $user,
                'errors' => $errors,
            ]);
        }
  
        return $this->render('dashboard/creer_atelier.html.twig', [
            'page_title' => 'Créer un Atelier',
            'user' => $user,
        ]);
    }

    #[Route('/dashboard/profil', name: 'app_dashboard_profil', methods: ['GET', 'POST'])]
    public function profil(Request $request, SessionInterface $session): Response
    {
        $user = $this->checkUser($session);
        if (!$user) {
            return $this->redirectToRoute('app_connexion');
        }

        if ($request->isMethod('POST')) {
            $competences = $user->getCompetences() ?? [];

            $remove = $request->request->get('remove');
            if ($remove !== null) {
                $competences = array_values(array_filter($competences, fn($c) => $c !== $remove));
            }

            $competence = trim($request->request->get('competence', ''));
            if ($competence !== '' && !in_array($competence, $competences)) {
                $competences[] = $competence;
            }

            $user->setCompetences($competences);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_dashboard_profil');
        }

        return $this->render('dashboard/profil.html.twig', [
            'page_title' => 'Mon Profil',
            'user' => $user,
            'active_view' => 'profil',
        ]);
    }

}