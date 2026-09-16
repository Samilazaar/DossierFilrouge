<?php

namespace App\Tests\Controller\api;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Création d'un atelier via l'API.
 *
 * La notification part par un service externe : on le remplace par une doublure
 * pour que la suite reste rapide, déterministe, et n'envoie aucun vrai courriel
 * depuis l'intégration continue.
 */
final class CreationAtelierTest extends WebTestCase
{
    public function testUneNotificationEstEnvoyeeApresLaCreationDUnAtelier(): void
    {
        // Arrange : une doublure à la place du vrai service d'envoi
        $client = static::createClient();

        $messagerie = $this->createMock(NotificationService::class);
        $messagerie->expects(self::once())
            ->method('sendEmail')
            ->with(
                'admin@skillhub.com',                       // le destinataire attendu
                self::stringContains('Docker pour débutants') // le titre créé, dans le message
            )
            ->willReturn(true);

        $client->getContainer()->set(NotificationService::class, $messagerie);

        // Act
        $client->request(
            'POST',
            '/api/workshops',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['title' => 'Docker pour débutants'], JSON_THROW_ON_ERROR)
        );

        // Assert : la réponse ET l'appel prouvé par la doublure ci-dessus
        self::assertResponseStatusCodeSame(201);
        self::assertJson($client->getResponse()->getContent());
    }

    public function testAucuneNotificationNEstEnvoyeeQuandLaCreationEchoue(): void
    {
        // Arrange
        $client = static::createClient();

        $messagerie = $this->createMock(NotificationService::class);
        $messagerie->expects(self::never())->method('sendEmail');

        $client->getContainer()->set(NotificationService::class, $messagerie);

        // Act : requête sans titre
        $client->request(
            'POST',
            '/api/workshops',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}'
        );

        // Assert : le type d'échec ET le message rendu à l'appelant
        self::assertResponseStatusCodeSame(422);

        $corps = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('error', $corps);
        self::assertSame('Title is required', $corps['error']);
    }
}
