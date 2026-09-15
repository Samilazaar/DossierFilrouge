<?php

namespace App\Tests\Entity;

use App\Entity\Atelier;
use App\Exception\AtelierCompletException;
use App\Exception\PlacesNonRenseigneesException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires des règles de capacité d'un atelier.
 *
 * Aucune base de données, aucun noyau Symfony : ces tests s'exécutent en
 * quelques millisecondes sur un poste vierge. Ils forment la base de la
 * pyramide, absente du projet avant ce TP.
 */
final class AtelierTest extends TestCase
{
    /** Construit un atelier dans un état connu, sans passer par Doctrine. */
    private function atelier(int $capacite, ?int $placesRestantes = null): Atelier
    {
        $atelier = new Atelier();
        $atelier->setTitre('Docker pour débutants');
        $atelier->setCapaciteMax($capacite);
        $atelier->setPlacesRestantes($placesRestantes ?? $capacite);

        return $atelier;
    }

    // ---------------------------------------------------------------
    // Cas nominaux
    // ---------------------------------------------------------------

    public function testUnAtelierNeufOffreToutesSesPlaces(): void
    {
        // Arrange / Act
        $atelier = $this->atelier(capacite: 12);

        // Assert
        self::assertSame(12, $atelier->getPlacesDisponibles());
        self::assertFalse($atelier->estComplet());
    }

    public function testLesPlacesRestantesDiminuentApresUneInscription(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 12);

        // Act
        $atelier->decrementerPlaces();

        // Assert
        self::assertSame(11, $atelier->getPlacesDisponibles());
    }

    public function testLesPlacesRestantesAugmententApresUneDesinscription(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 12, placesRestantes: 10);

        // Act
        $atelier->incrementerPlaces();

        // Assert
        self::assertSame(11, $atelier->getPlacesDisponibles());
    }

    public function testUnAtelierSansPlaceRestanteEstComplet(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 12, placesRestantes: 0);

        // Act / Assert
        self::assertTrue($atelier->estComplet());
    }

    public function testLaDerniereInscriptionRendLAtelierComplet(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 1);

        // Act
        $atelier->decrementerPlaces();

        // Assert
        self::assertTrue($atelier->estComplet());
        self::assertSame(0, $atelier->getPlacesDisponibles());
    }

    // ---------------------------------------------------------------
    // Cas limites — valeurs aux bornes de la capacité
    // ---------------------------------------------------------------

    /**
     * Bornes de la règle « un atelier est complet quand il ne reste plus de place ».
     * Le cas -1 ne devrait jamais exister en base ; on vérifie quand même que
     * l'atelier est traité comme complet et n'affiche pas un nombre négatif.
     */
    #[DataProvider('fournirLesBornesDeCapacite')]
    public function testCompletudeAuxBornes(int $placesRestantes, bool $attenduComplet): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 12, placesRestantes: $placesRestantes);

        // Act / Assert
        self::assertSame($attenduComplet, $atelier->estComplet());
    }

    public static function fournirLesBornesDeCapacite(): array
    {
        return [
            'deux places avant la borne' => [2, false],
            'juste avant la borne'       => [1, false],
            'exactement a la borne'      => [0, true],
            'au-dela de la borne'        => [-1, true],
        ];
    }

    public function testLesPlacesDisponiblesNeSontJamaisNegatives(): void
    {
        // Arrange : état incohérent hérité de la base
        $atelier = $this->atelier(capacite: 12, placesRestantes: -3);

        // Act / Assert : l'utilisateur ne doit jamais lire « -3 places »
        self::assertSame(0, $atelier->getPlacesDisponibles());
    }

    public function testUneDesinscriptionNeDepasseJamaisLaCapaciteMaximale(): void
    {
        // Arrange : atelier déjà au complet de ses places disponibles
        $atelier = $this->atelier(capacite: 12, placesRestantes: 12);

        // Act
        $atelier->incrementerPlaces();

        // Assert : le plafond tient, on ne crée pas de place ex nihilo
        self::assertSame(12, $atelier->getPlacesDisponibles());
    }

    public function testUnAtelierDeCapaciteZeroEstCompletDesLOrigine(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 0);

        // Act / Assert
        self::assertTrue($atelier->estComplet());
    }

    // ---------------------------------------------------------------
    // Scénarios d'erreur — type, message, et état inchangé
    // ---------------------------------------------------------------

    public function testInscriptionRefuseeQuandLAtelierEstComplet(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 1);
        $atelier->decrementerPlaces();

        // Act / Assert : le type attendu, et pas un autre
        $this->expectException(AtelierCompletException::class);
        $this->expectExceptionMessage('capacité atteinte');

        $atelier->decrementerPlaces();
    }

    public function testUneInscriptionRefuseeNeModifiePasLesPlaces(): void
    {
        // Arrange
        $atelier = $this->atelier(capacite: 1);
        $atelier->decrementerPlaces();

        // Act
        try {
            $atelier->decrementerPlaces();
            self::fail('Une AtelierCompletException était attendue.');
        } catch (AtelierCompletException) {
            // attendu
        }

        // Assert : échouer proprement, c'est ne rien laisser derrière soi
        self::assertSame(0, $atelier->getPlacesDisponibles());
    }

    public function testPlacesNonRenseigneesLeveUneErreurDistincte(): void
    {
        // Arrange : colonne nullable jamais remplie, cas réel en base
        $atelier = new Atelier();
        $atelier->setTitre('Atelier importé sans capacité');

        // Act / Assert : surtout pas confondu avec « atelier complet »
        $this->expectException(PlacesNonRenseigneesException::class);
        $this->expectExceptionMessage('Places non renseignées');

        $atelier->decrementerPlaces();
    }
}
