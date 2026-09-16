<?php

namespace App\Exception;

/**
 * Levée quand on manipule les places d'un atelier dont la capacité n'a jamais
 * été renseignée (placesRestantes ou capaciteMax à null en base).
 *
 * Ce cas existe parce que les deux colonnes sont nullables côté Doctrine
 * (src/Entity/Atelier.php). Le traiter explicitement évite de le confondre
 * avec un atelier légitimement complet.
 */
class PlacesNonRenseigneesException extends \DomainException
{
    public static function pourAtelier(?int $atelierId): self
    {
        return new self(sprintf(
            'Places non renseignées pour l\'atelier %s : capacité inexploitable.',
            $atelierId ?? 'sans identifiant'
        ));
    }
}
