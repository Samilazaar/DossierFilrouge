<?php

namespace App\Tests\Domain;

use App\Domain\CapaciteAtelier;
use App\Exception\AtelierCompletException;
use App\Exception\PlacesNonRenseigneesException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Les règles de capacité, testées directement — sans passer par l'entité
 * Doctrine ni par la base de données.
 */
final class CapaciteAtelierTest extends TestCase
{
    public function testUnAtelierAvecDesPlacesNEstPasComplet(): void
    {
        $capacite = new CapaciteAtelier(12, 3);

        $this->assertFalse($capacite->estComplet());
    }

    public function testUnAtelierSansPlaceEstComplet(): void
    {
        $capacite = new CapaciteAtelier(12, 0);

        $this->assertTrue($capacite->estComplet());
    }

    public function testUnAtelierDontLesPlacesNeSontPasRenseigneesEstComplet(): void
    {
        $capacite = new CapaciteAtelier(null, null);

        $this->assertTrue($capacite->estComplet());
    }

    #[DataProvider('placesEtDisponibilites')]
    public function testLesPlacesDisponiblesNeSontJamaisNegatives(?int $restantes, int $attendu): void
    {
        $capacite = new CapaciteAtelier(12, $restantes);

        $this->assertSame($attendu, $capacite->placesDisponibles());
    }

    public static function placesEtDisponibilites(): array
    {
        return [
            'places disponibles' => [5, 5],
            'aucune place'       => [0, 0],
            'valeur negative'    => [-3, 0],
            'non renseignees'    => [null, 0],
        ];
    }

    public function testUneInscriptionRetireUnePlace(): void
    {
        $capacite = new CapaciteAtelier(12, 4);

        $this->assertSame(3, $capacite->apresUneInscription());
    }

    public function testUneInscriptionSurUnAtelierCompletEstRefusee(): void
    {
        $capacite = new CapaciteAtelier(12, 0, 7);

        $this->expectException(AtelierCompletException::class);

        $capacite->apresUneInscription();
    }

    public function testUneInscriptionSansPlacesRenseigneesLeveUneErreurDistincte(): void
    {
        $capacite = new CapaciteAtelier(12, null, 7);

        $this->expectException(PlacesNonRenseigneesException::class);

        $capacite->apresUneInscription();
    }

    public function testUneDesinscriptionRendUnePlace(): void
    {
        $capacite = new CapaciteAtelier(12, 4);

        $this->assertSame(5, $capacite->apresUneDesinscription());
    }

    public function testUneDesinscriptionNeDepasseJamaisLaCapaciteMaximale(): void
    {
        $capacite = new CapaciteAtelier(12, 12);

        $this->assertSame(12, $capacite->apresUneDesinscription());
    }

    public function testUneDesinscriptionSansCapaciteRenseigneeLeveUneErreur(): void
    {
        $capacite = new CapaciteAtelier(null, 4, 7);

        $this->expectException(PlacesNonRenseigneesException::class);

        $capacite->apresUneDesinscription();
    }
}
