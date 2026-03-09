<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Kernel;
use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\UserFormateur;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

$formateurs = [
    ['nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean.dupont@skillhub.com', 'specialite' => 'Développement Web', 'bio' => 'Expert React et JavaScript', 'experiences' => '10 ans'],
    ['nom' => 'Martin', 'prenom' => 'Marie', 'email' => 'marie.martin@skillhub.com', 'specialite' => 'PHP / Symfony', 'bio' => 'Formatrice Symfony certifiée', 'experiences' => '8 ans'],
    ['nom' => 'Durand', 'prenom' => 'Pierre', 'email' => 'pierre.durand@skillhub.com', 'specialite' => 'Base de données', 'bio' => 'DBA senior', 'experiences' => '12 ans'],
    ['nom' => 'Bernard', 'prenom' => 'Sophie', 'email' => 'sophie.bernard@skillhub.com', 'specialite' => 'Design UX/UI', 'bio' => 'Designer UX senior', 'experiences' => '7 ans'],
    ['nom' => 'Moreau', 'prenom' => 'Lucas', 'email' => 'lucas.moreau@skillhub.com', 'specialite' => 'DevOps / Sécurité', 'bio' => 'Expert DevOps et cybersécurité', 'experiences' => '9 ans'],
];

$atelierFormateurs = [
    1 => 'jean.dupont@skillhub.com',
    2 => 'marie.martin@skillhub.com',
    3 => 'pierre.durand@skillhub.com',
    4 => 'sophie.bernard@skillhub.com',
    5 => 'lucas.moreau@skillhub.com',
    6 => 'lucas.moreau@skillhub.com',
];

// Créer les users formateurs
$formateurProfiles = [];
foreach ($formateurs as $f) {
    $user = new User();
    $user->setNom($f['nom']);
    $user->setPrenom($f['prenom']);
    $user->setEmail($f['email']);
    $user->setTelephone('0600000000');
    $user->setPassword(password_hash('formateur123', PASSWORD_BCRYPT));
    $entityManager->persist($user);

    $profile = new UserFormateur();
    $profile->setUser($user);
    $profile->setSpecialite($f['specialite']);
    $profile->setBio($f['bio']);
    $profile->setExperiences($f['experiences']);
    $entityManager->persist($profile);

    $formateurProfiles[$f['email']] = $profile;
}

$entityManager->flush();

// Lier les ateliers aux formateurs
foreach ($atelierFormateurs as $atelierId => $email) {
    $atelier = $entityManager->getRepository(Atelier::class)->find($atelierId);
    if ($atelier && isset($formateurProfiles[$email])) {
        $atelier->setFormateur($formateurProfiles[$email]);
    }
}

$entityManager->flush();
echo "Formateurs créés et liés aux ateliers avec succès!\n";
