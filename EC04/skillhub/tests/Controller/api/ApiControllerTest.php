<?php
namespace App\Tests\Controller\api;

// On importe le service qu'on va mocker (simuler)
use App\Service\NotificationService;
// WebTestCase = classe Symfony qui permet de simuler des requêtes HTTP dans les tests
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    // Test 1 : vérifier que GET /api/workshops fonctionne
    public function testGetWorkshops(): void
    {
        // Crée un faux navigateur pour envoyer des requêtes
        $client = static::createClient();
        // Envoie une requête GET sur /api/workshops
        $client->request('GET', '/api/workshops');

        // Vérifie que la réponse est un succès (code 200)
        self::assertResponseIsSuccessful();
        // Vérifie que la réponse est bien du JSON
        self::assertResponseHeaderSame('content-type', 'application/json');
    }

    // Test 2 : vérifier qu'un POST sans données retourne 422
    public function testCreateInvalidWorkshop(): void
    {
        $client = static::createClient();
        // Envoie un POST avec un body vide {} (pas de titre)
        $client->request('POST', '/api/workshops', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        // Vérifie que le serveur rejette avec un code 422 (données invalides)
        self::assertResponseStatusCodeSame(422);
    }

    // Test 3 : vérifier que le POST fonctionne SANS envoyer de vrai email
    public function testCreateWorkshopWithMock(): void
    {
        $client = static::createClient();

        // Crée un FAUX NotificationService (il ne fait rien pour de vrai)
        $mock = $this->createMock(NotificationService::class);
        // On dit au faux : "sendEmail doit être appelé exactement 1 fois"
        $mock->expects($this->once())
             // La méthode qu'on surveille
             ->method('sendEmail')
             // Ce que le faux retourne (true = "oui c'est envoyé")
             ->willReturn(true);

        // On remplace le vrai service par le faux dans Symfony
        $client->getContainer()->set(NotificationService::class, $mock);
 
        // Envoie un POST avec un titre valide
        $client->request('POST', '/api/workshops', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"title": "Test atelier"}');

        // Vérifie que la réponse est 201 (créé avec succès)
        self::assertResponseStatusCodeSame(201);
    }
}
