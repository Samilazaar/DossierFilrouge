<?php

namespace App\Domain;

use App\Exception\AtelierCompletException;
use App\Exception\PlacesNonRenseigneesException;

/**
 * Les règles de capacité d'un atelier, isolées de l'entité Doctrine.
 *
 * L'entité Atelier porte la persistance et les accesseurs ; cette classe ne
 * porte que les règles. La séparation permet de mesurer la couverture sur le
 * code qui décide, sans la diluer dans les accesseurs générés.
 */
final class CapaciteAtelier
{
    public function __construct(
        private readonly ?int $capaciteMax,
        private readonly ?int $placesRestantes,
        private readonly ?int $idAtelier = null,
    ) {
    }

    /**
     * Un atelier dont les places ne sont pas renseignées est considéré complet :
     * on refuse d'inscrire plutôt que de risquer une surréservation.
     */
    public function estComplet(): bool
    {
        return $this->placesRestantes <= 0;
    }

    public function placesDisponibles(): int
    {
        return max(0, $this->placesRestantes ?? 0);
    }

    /**
     * @return int le nombre de places restantes après l'inscription
     *
     * @throws PlacesNonRenseigneesException si la capacité n'a jamais été renseignée
     * @throws AtelierCompletException       si l'atelier est déjà complet
     */
    public function apresUneInscription(): int
    {
        if ($this->placesRestantes === null) {
            throw PlacesNonRenseigneesException::pourAtelier($this->idAtelier);
        }

        if ($this->placesRestantes <= 0) {
            throw AtelierCompletException::pourAtelier($this->idAtelier);
        }

        return $this->placesRestantes - 1;
    }

    /**
     * @return int le nombre de places restantes après la désinscription,
     *             plafonné à la capacité maximale
     *
     * @throws PlacesNonRenseigneesException si la capacité n'a jamais été renseignée
     */
    public function apresUneDesinscription(): int
    {
        if ($this->placesRestantes === null || $this->capaciteMax === null) {
            throw PlacesNonRenseigneesException::pourAtelier($this->idAtelier);
        }

        return $this->placesRestantes < $this->capaciteMax
            ? $this->placesRestantes + 1
            : $this->placesRestantes;
    }
}
