<?php

// Routing Symfony - Simplifié (sans composer/symfony cli)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Routeur basé sur les attributs PHP 8 (simulé)
switch ($uri) {
    case '/':
        $page_title = 'Bienvenue sur SkillHub';
        $features = ['HTML5', 'CSS3', 'JavaScript', 'PHP', 'MySQL'];
        require __DIR__ . '/../templates/landing/index.html.twig';
        break;

    case '/dashboard':
        $page_title = 'Tableau de Bord';
        $active_view = 'ateliers';
        $ateliers = [
            ['id' => 1, 'titre' => 'Développement Web avec React', 'formateur' => 'Jean Dupont', 'date' => '2025-01-15', 'duree' => '3h', 'places' => 15, 'inscrit' => false],
            ['id' => 2, 'titre' => 'Introduction à Symfony', 'formateur' => 'Marie Martin', 'date' => '2025-01-20', 'duree' => '4h', 'places' => 20, 'inscrit' => true],
            ['id' => 3, 'titre' => 'Base de données avancée', 'formateur' => 'Pierre Durand', 'date' => '2025-01-25', 'duree' => '2h', 'places' => 10, 'inscrit' => false],
        ];
        require __DIR__ . '/../templates/dashboard/index.html.twig';
        break;

    case '/dashboard/inscriptions':
        $page_title = 'Mes Inscriptions';
        $active_view = 'inscriptions';
        $inscriptions = [
            ['id' => 2, 'titre' => 'Introduction à Symfony', 'formateur' => 'Marie Martin', 'date' => '2025-01-20', 'duree' => '4h', 'statut' => 'confirmé'],
        ];
        require __DIR__ . '/../templates/dashboard/inscriptions.html.twig';
        break;

    case '/inscription':
        $page_title = 'Inscription';
        $success = false;
        $message = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = true;
            $message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        }
        
        require __DIR__ . '/../templates/inscription/index.html.twig';
        break;

    case '/connexion':
        $page_title = 'Connexion';
        $success = false;
        $message = '';
        $error = false;
        $errorMessage = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $testUsers = [
                ['email' => 'test@skillhub.com', 'password' => 'password123', 'nom' => 'Utilisateur Test'],
                ['email' => 'admin@skillhub.com', 'password' => 'admin123', 'nom' => 'Admin'],
            ];
            
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
        
        require __DIR__ . '/../templates/connexion/index.html.twig';
        break;

    case '/formateurs':
        $page_title = 'Liste des Formateurs';
        $formateurs = [
            ['nom' => 'Jean Dupont', 'specialite' => 'Développement Web', 'experience' => '10 ans', 'email' => 'jean.dupont@skillhub.com', 'bio' => 'Expert en JavaScript, React et Node.js avec plus de 10 ans d\'expérience dans le développement web.'],
            ['nom' => 'Marie Martin', 'specialite' => 'Design UX/UI', 'experience' => '8 ans', 'email' => 'marie.martin@skillhub.com', 'bio' => 'Spécialiste en UX/UI design, passionnée par l\'accessibilité et les interfaces modernes.'],
            ['nom' => 'Pierre Durand', 'specialite' => 'Base de données', 'experience' => '12 ans', 'email' => 'pierre.durand@skillhub.com', 'bio' => 'Architecte de bases de données expert, maîtrisant MySQL, PostgreSQL et MongoDB.'],
            ['nom' => 'Sophie Bernard', 'specialite' => 'DevOps & Cloud', 'experience' => '6 ans', 'email' => 'sophie.bernard@skillhub.com', 'bio' => 'Ingénieure DevOps, spécialisée en Docker, Kubernetes et CI/CD.'],
            ['nom' => 'Lucas Moreau', 'specialite' => 'Cybersécurité', 'experience' => '9 ans', 'email' => 'lucas.moreau@skillhub.com', 'bio' => 'Expert en cybersécurité, spécialisé en sécurisation d\'applications web.'],
        ];
        require __DIR__ . '/../templates/formateur/liste.html.twig';
        break;

    default:
        http_response_code(404);
        echo '<h1>Page non trouvée</h1>';
        exit;
}
