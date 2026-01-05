<?php

namespace App\Controller;

class DashboardController
{
    public function index(): string
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

        ob_start();
        require __DIR__ . '/../../templates/dashboard/index.html.twig';
        return ob_get_clean();
    }

    public function inscriptions(): string
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

        ob_start();
        require __DIR__ . '/../../templates/dashboard/inscriptions.html.twig';
        return ob_get_clean();
    }
}
