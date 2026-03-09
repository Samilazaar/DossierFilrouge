<?php

namespace App\Service;

use App\Model\User;
use App\Model\Atelier;
use App\Model\Inscription;
class DataRepository
{
    private string $storageFile;
    private array $users;
    private array $ateliers;
    private array $inscriptions;
    private int $userIdCounter;
    private int $atelierIdCounter;
    private int $inscriptionIdCounter;

    public function __construct()
    {
        $this->storageFile = __DIR__ . '/../../var/data_storage.json';
        $this->loadData();
    }

    private function loadData(): void
    {
        if (file_exists($this->storageFile)) {
            $data = json_decode(file_get_contents($this->storageFile), true);

            $userData = $data['users'] ?? [];
            $this->users = [];
            foreach ($userData as $u) {
                $this->users[] = new User($u['id'], $u['nom'], $u['prenom'], $u['email'], $u['telephone'], $u['password']);
            }

            $atelierData = $data['ateliers'] ?? [];
            $this->ateliers = [];
            foreach ($atelierData as $a) {
                $atelier = new Atelier($a['id'], $a['titre'], $a['description'], $a['date'], $a['duree'], $a['capaciteMax'], $a['imageUrl'], $a['formateur']);
                $atelier->setPlacesRestantes($a['placesRestantes']);
                $this->ateliers[] = $atelier;
            }

            $inscriptionData = $data['inscriptions'] ?? [];
            $this->inscriptions = [];
            foreach ($inscriptionData as $i) {
                $user = $this->findUserById($i['userId']);
                $atelier = $this->findAtelierById($i['atelierId']);
                if ($user && $atelier) {
                    $this->inscriptions[] = new Inscription($i['id'], $user, $atelier, $i['dateInscription']);
                }
            }

            $this->userIdCounter = $data['userIdCounter'] ?? 1;
            $this->atelierIdCounter = $data['atelierIdCounter'] ?? 1;
            $this->inscriptionIdCounter = $data['inscriptionIdCounter'] ?? 1;
        } else {
            $this->users = [];
            $this->ateliers = [];
            $this->inscriptions = [];
            $this->userIdCounter = 1;
            $this->atelierIdCounter = 1;
            $this->inscriptionIdCounter = 1;
            $this->initializeDemoData();
        }
    }

    private function saveData(): void
    {
        $data = [
            'users' => array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'telephone' => $user->getTelephone(),
                    'password' => $user->getPassword(),
                ];
            }, $this->users),
            'ateliers' => array_map(function($atelier) {
                return [
                    'id' => $atelier->getId(),
                    'titre' => $atelier->getTitre(),
                    'description' => $atelier->getDescription(),
                    'date' => $atelier->getDate(),
                    'duree' => $atelier->getDuree(),
                    'capaciteMax' => $atelier->getCapaciteMax(),
                    'placesRestantes' => $atelier->getPlacesRestantes(),
                    'imageUrl' => $atelier->getImageUrl(),
                    'formateur' => $atelier->getFormateur(),
                ];
            }, $this->ateliers),
            'inscriptions' => array_map(function($inscription) {
                return [
                    'id' => $inscription->getId(),
                    'userId' => $inscription->getUtilisateur()->getId(),
                    'atelierId' => $inscription->getAtelier()->getId(),
                    'dateInscription' => $inscription->getDateInscription(),
                ];
            }, $this->inscriptions),
            'userIdCounter' => $this->userIdCounter,
            'atelierIdCounter' => $this->atelierIdCounter,
            'inscriptionIdCounter' => $this->inscriptionIdCounter,
        ];

        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($this->storageFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function initializeDemoData(): void
    {
        $this->atelierIdCounter = 1;
        $atelier1 = new Atelier(
            $this->atelierIdCounter++,
            'Développement Web avec React',
            'Apprenez à créer des applications web modernes et réactives avec React. Ce cours couvre les fondamentaux du framework React, la gestion des composants, les hooks, le state management et les bonnes pratiques pour développer des applications web performantes et évolutives.',
            '2025-01-15',
            '3h',
            15,
            'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=800&q=80',
            'Jean Dupont'
        );

        $atelier2 = new Atelier(
            $this->atelierIdCounter++,
            'Introduction à Symfony',
            'Découvrez Symfony, le framework PHP puissant pour créer des applications web robustes et évolutives. Apprenez à maîtriser les composants clés du framework, la structure MVC, les routes, les contrôleurs et les templates Twig.',
            '2025-01-20',
            '4h',
            20,
            'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&q=80',
            'Marie Martin'
        );

        $atelier3 = new Atelier(
            $this->atelierIdCounter++,
            'Base de données avancée',
            'Approfondissez vos connaissances en bases de données relationnelles. Ce cours avancé couvre la modélisation complexe, l\'optimisation des requêtes SQL, les jointures avancées, les transactions et les bonnes pratiques de sécurisation des données.',
            '2025-01-25',
            '2h',
            10,
            'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&q=80',
            'Pierre Durand'
        );

        $atelier4 = new Atelier(
            $this->atelierIdCounter++,
            'Design UX/UI moderne',
            'Maîtrisez les principes fondamentaux du design d\'expérience utilisateur et d\'interface. Apprenez à créer des interfaces intuitives, accessibles et esthétiques en utilisant les outils de design les plus récents comme Figma et Adobe XD.',
            '2025-02-01',
            '3h',
            12,
            'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80',
            'Sophie Bernard'
        );

        $atelier5 = new Atelier(
            $this->atelierIdCounter++,
            'DevOps et CI/CD',
            'Découvrez les pratiques DevOps modernes pour automatiser le déploiement et améliorer la qualité du code. Apprenez à utiliser Docker, Kubernetes, GitHub Actions et autres outils de CI/CD pour créer des pipelines de livraison continue.',
            '2025-02-05',
            '4h',
            8,
            'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
            'Lucas Moreau'
        );

        $atelier6 = new Atelier(
            $this->atelierIdCounter++,
            'Cybersécurité Web',
            'Apprenez à sécuriser vos applications web contre les vulnérabilités courantes. Ce cours couvre les attaques XSS, CSRF, SQL injection, l\'authentification sécurisée, le chiffrement et les meilleures pratiques de sécurité OWASP.',
            '2025-02-10',
            '3h',
            15,
            'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&q=80',
            'Lucas Moreau'
        );

        $this->ateliers[] = $atelier1;
        $this->ateliers[] = $atelier2;
        $this->ateliers[] = $atelier3;
        $this->ateliers[] = $atelier4;
        $this->ateliers[] = $atelier5;
        $this->ateliers[] = $atelier6;

        $user1 = new User(
            $this->userIdCounter++,
            'Utilisateur',
            'Test',
            'test@skillhub.com',
            '0601020304',
            password_hash('password123', PASSWORD_BCRYPT)
        );

        $user2 = new User(
            $this->userIdCounter++,
            'Admin',
            'Admin',
            'admin@skillhub.com',
            '0601020305',
            password_hash('admin123', PASSWORD_BCRYPT)
        );

        $this->users[] = $user1;
        $this->users[] = $user2;

        $this->saveData();
    }

    public function findAllUsers(): array
    {
        return $this->users;
    }

    public function findUserByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function findUserById(int $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }
        return null;
    }

    public function addUser(User $user): void
    {
        $this->users[] = $user;
        $this->saveData();
    }

    public function findAllAteliers(): array
    {
        return $this->ateliers;
    }

    public function findAtelierById(int $id): ?Atelier
    {
        foreach ($this->ateliers as $atelier) {
            if ($atelier->getId() === $id) {
                return $atelier;
            }
        }
        return null;
    }

    public function findAllInscriptions(): array
    {
        return $this->inscriptions;
    }

    public function findInscriptionsByUser(User $user): array
    {
        return array_filter($this->inscriptions, function(Inscription $inscription) use ($user) {
            return $inscription->getUtilisateur()->getId() === $user->getId();
        });
    }

    public function findInscriptionsByAtelier(Atelier $atelier): array
    {
        return array_filter($this->inscriptions, function(Inscription $inscription) use ($atelier) {
            return $inscription->getAtelier()->getId() === $atelier->getId();
        });
    }

    public function findInscription(User $user, Atelier $atelier): ?Inscription
    {
        foreach ($this->inscriptions as $inscription) {
            if ($inscription->getUtilisateur()->getId() === $user->getId() &&
                $inscription->getAtelier()->getId() === $atelier->getId()) {
                return $inscription;
            }
        }
        return null;
    }

    public function addInscription(Inscription $inscription): void
    {
        $this->inscriptions[] = $inscription;
        $inscription->getAtelier()->decrementerPlaces();
        $this->saveData();
    }

    public function removeInscription(Inscription $inscription): void
    {
        $this->inscriptions = array_filter($this->inscriptions, function(Inscription $insc) use ($inscription) {
            return $insc->getId() !== $inscription->getId();
        });
        $inscription->getAtelier()->incrementerPlaces();
        $this->saveData();
    }

}
