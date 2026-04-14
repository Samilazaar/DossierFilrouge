<?php
require __DIR__ . '/vendor/autoload.php';
use App\Kernel;
use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\Inscription;
use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$kernel = new Kernel('dev', true);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();
$jsonData = json_decode(file_get_contents(__DIR__ . '/var/data_storage.json'), true);
foreach ($jsonData['users'] as $userData) {
    $user = new User();
    $user->setNom($userData['nom']);
    $user->setPrenom($userData['prenom']);
    $user->setEmail($userData['email']);
    $user->setTelephone($userData['telephone']);
    $user->setPassword($userData['password']);
    $entityManager->persist($user);
}
foreach ($jsonData['ateliers'] as $atelierData) {
    $atelier = new Atelier();
    $atelier->setTitre($atelierData['titre']);
    $atelier->setDescription($atelierData['description']);
    $atelier->setDate(new \DateTime($atelierData['date']));
    $atelier->setDuree($atelierData['duree']);
    $atelier->setCapaciteMax($atelierData['capaciteMax']);
    $atelier->setPlacesRestantes($atelierData['placesRestantes']);
    $atelier->setImageUrl($atelierData['imageUrl']);
    // Formateur ignoré (string dans le JSON, objet attendu)
    $entityManager->persist($atelier);
}
$entityManager->flush();
foreach ($jsonData['inscriptions'] as $inscriptionData) {
    $inscription = new Inscription();
    $user = $entityManager->getRepository(User::class)->find($inscriptionData['userId']);
    $atelier = $entityManager->getRepository(Atelier::class)->find($inscriptionData['atelierId']);
    
    $inscription->setUtilisateur($user);
    $inscription->setAtelier($atelier);
    $inscription->setDateInscription(new \DateTime($inscriptionData['dateInscription']));
    
    $entityManager->persist($inscription);
}
$entityManager->flush();
echo "Migration terminée avec succès!\n";