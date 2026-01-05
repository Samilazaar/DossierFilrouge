<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $ateliers = [
            [
                'id' => 1,
                'titre' => 'Développement Web avec React',
                'formateur' => 'Jean Dupont',
                'date' => '2025-01-15',
                'duree' => '3h',
                'places' => 15,
                'inscrit' => false,
            ],
            [
                'id' => 2,
                'titre' => 'Introduction à Symfony',
                'formateur' => 'Marie Martin',
                'date' => '2025-01-20',
                'duree' => '4h',
                'places' => 20,
                'inscrit' => true,
            ],
            [
                'id' => 3,
                'titre' => 'Base de données avancée',
                'formateur' => 'Pierre Durand',
                'date' => '2025-01-25',
                'duree' => '2h',
                'places' => 10,
                'inscrit' => false,
            ],
        ];

        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Tableau de Bord',
            'ateliers' => $ateliers,
            'active_view' => 'ateliers',
        ]);
    }

    #[Route('/dashboard/inscriptions', name: 'app_dashboard_inscriptions')]
    public function inscriptions(): Response
    {
        $mesInscriptions = [
            [
                'id' => 2,
                'titre' => 'Introduction à Symfony',
                'formateur' => 'Marie Martin',
                'date' => '2025-01-20',
                'duree' => '4h',
                'statut' => 'confirmé',
            ],
        ];

        return $this->render('dashboard/inscriptions.html.twig', [
            'page_title' => 'Mes Inscriptions',
            'inscriptions' => $mesInscriptions,
            'active_view' => 'inscriptions',
        ]);
    }
}
