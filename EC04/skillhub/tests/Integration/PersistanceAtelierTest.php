<?php

namespace App\Tests\Integration;

use App\Entity\Atelier;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests d'intégration : le vrai Doctrine, la vraie base.
 *
 * Ils attrapent ce qu'un test unitaire ne verra jamais — une colonne absente,
 * un type incompatible, une valeur qui ne revient pas telle qu'on l'a écrite.
 *
 * Base jetable : docker compose -f compose.test.yaml up -d
 * Sans base joignable, ces tests sont ignorés plutôt que rouges : un test
 * d'infrastructure absente n'est pas une régression du code.
 */
final class PersistanceAtelierTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        try {
            $em->getConnection()->executeQuery('SELECT 1');
        } catch (ConnectionException | \Doctrine\DBAL\Exception $e) {
            self::markTestSkipped(
                'Base de test injoignable. Démarrez-la avec : '
                . 'docker compose -f compose.test.yaml up -d'
            );
        }

        $this->em = $em;
        $this->em->beginTransaction();   // tout ce que le test écrit sera annulé
    }

    protected function tearDown(): void
    {
        if ($this->em !== null && $this->em->getConnection()->isTransactionActive()) {
            $this->em->rollback();       // nettoyage systématique : aucun test ne pollue le suivant
        }

        parent::tearDown();
    }

    public function testUnAtelierRelituEnBaseConserveSaCapacite(): void
    {
        // Arrange
        $atelier = new Atelier();
        $atelier->setTitre('Docker pour débutants');
        $atelier->setCapaciteMax(12);
        $atelier->setPlacesRestantes(12);

        // Act : écriture réelle, puis relecture depuis la base
        $this->em->persist($atelier);
        $this->em->flush();
        $id = $atelier->getId();

        $this->em->clear();
        $relu = $this->em->getRepository(Atelier::class)->find($id);

        // Assert
        self::assertNotNull($relu, 'L\'atelier écrit doit être relisible.');
        self::assertSame(12, $relu->getCapaciteMax());
        self::assertSame(12, $relu->getPlacesRestantes());
    }

    public function testLaDiminutionDesPlacesEstBienPersistee(): void
    {
        // Arrange
        $atelier = new Atelier();
        $atelier->setTitre('Atelier complet bientôt');
        $atelier->setCapaciteMax(2);
        $atelier->setPlacesRestantes(2);
        $this->em->persist($atelier);
        $this->em->flush();
        $id = $atelier->getId();

        // Act
        $atelier->decrementerPlaces();
        $this->em->flush();
        $this->em->clear();

        // Assert : la règle métier a bien traversé jusqu'à la base
        $relu = $this->em->getRepository(Atelier::class)->find($id);
        self::assertSame(1, $relu->getPlacesRestantes());
        self::assertFalse($relu->estComplet());
    }
}
