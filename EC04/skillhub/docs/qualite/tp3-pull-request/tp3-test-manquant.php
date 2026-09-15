<?php
// TP 3 — LE TEST MANQUANT de la pull request #47.
// Destination une fois la PR corrigée : tests/Controller/api/ExportInscritsTest.php
//
// État actuel : ÉCHOUE. La route ne vérifie pas que l'atelier existe, donc
// findAtelierById() renvoie null, le foreach plante et Symfony répond 500.
// Le test attend 404. Il passera au vert dès que la vérification sera ajoutée.

namespace App\Tests\Controller\api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExportInscritsTest extends WebTestCase
{
    public function testLExportDUnAtelierInexistantRepond404(): void
    {
        // Arrange : un identifiant dont on sait qu'il n'existe pas
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/workshops/999999/export');

        // Assert : une demande sur une ressource absente est un 404,
        // pas une erreur serveur. C'est ce qui distingue « tu t'es trompé »
        // de « nous avons un problème ».
        self::assertResponseStatusCodeSame(404);
    }

    public function testLExportDUnAtelierExistantRenvoieUnCsvAvecEnTete(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/workshops/1/export');

        // Assert : le type de contenu ET la ligne d'en-tête, pas seulement « ça répond »
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'text/csv');
        self::assertStringStartsWith(
            'nom,prenom,email,telephone,date_inscription',
            $client->getResponse()->getContent()
        );
    }
}
