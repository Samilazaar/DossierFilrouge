<?php

namespace App\Tests\Entity;

use App\Entity\Atelier;
use PHPUnit\Framework\TestCase;

/**
 * Couverture des accesseurs de l'entité.
 *
 * Ces tests ne protègent d'aucune règle métier — celles-ci sont vérifiées dans
 * CapaciteAtelierTest. Ils protègent d'une seule chose, réelle : qu'un
 * renommage de propriété ou une erreur de copier-coller entre deux accesseurs
 * (setTitre écrivant dans $description, par exemple) ne passe pas inaperçu.
 */
final class AtelierAccesseursTest extends TestCase
{
    public function testUnAtelierNeufNAPasDIdentifiant(): void
    {
        $this->assertNull((new Atelier())->getId());
    }

    public function testUnAtelierNeufNAAucuneInscription(): void
    {
        $this->assertCount(0, (new Atelier())->getInscriptions());
    }

    public function testChaqueAccesseurRelitCeQuIlAEcrit(): void
    {
        $date = new \DateTimeImmutable('2026-10-01 09:00');

        $atelier = (new Atelier())
            ->setTitre('Docker 101')
            ->setDescription('Prise en main des conteneurs')
            ->setDate($date)
            ->setDuree('3h30')
            ->setCapaciteMax(12)
            ->setPlacesRestantes(8)
            ->setImageUrl('/img/docker.png');

        $this->assertSame('Docker 101', $atelier->getTitre());
        $this->assertSame('Prise en main des conteneurs', $atelier->getDescription());
        $this->assertSame($date, $atelier->getDate());
        $this->assertSame('3h30', $atelier->getDuree());
        $this->assertSame(12, $atelier->getCapaciteMax());
        $this->assertSame(8, $atelier->getPlacesRestantes());
        $this->assertSame('/img/docker.png', $atelier->getImageUrl());
    }

    public function testLesAccesseursAcceptentLaValeurNulle(): void
    {
        $atelier = (new Atelier())
            ->setTitre(null)
            ->setDescription(null)
            ->setDate(null)
            ->setDuree(null)
            ->setCapaciteMax(null)
            ->setPlacesRestantes(null)
            ->setImageUrl(null);

        $this->assertNull($atelier->getTitre());
        $this->assertNull($atelier->getCapaciteMax());
        $this->assertNull($atelier->getPlacesRestantes());
    }

    public function testLesAccesseursSontChainables(): void
    {
        $atelier = new Atelier();

        $this->assertSame($atelier, $atelier->setTitre('Docker 101'));
        $this->assertSame($atelier, $atelier->setCapaciteMax(12));
    }

    public function testSansFormateurLeNomAfficheEstNonRenseigne(): void
    {
        $atelier = new Atelier();

        $this->assertNull($atelier->getFormateur());
        $this->assertSame('N/A', $atelier->getFormateurNom());
    }
}
