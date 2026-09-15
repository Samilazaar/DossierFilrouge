<?php

namespace App\Exception;

/**
 * Levée quand on tente d'inscrire un apprenant sur un atelier dont la capacité
 * est déjà atteinte.
 *
 * Avant l'introduction de cette exception, decrementerPlaces() ne faisait rien
 * dans ce cas : l'inscription était persistée sans qu'aucune place ne soit
 * décomptée, et aucune trace n'était laissée. Voir docs/qualite/tp1-diagnostic.md, dette D2.
 */
class AtelierCompletException extends \DomainException
{
    public static function pourAtelier(?int $atelierId): self
    {
        return new self(sprintf(
            'Inscription refusée : capacité atteinte pour l\'atelier %s.',
            $atelierId ?? 'sans identifiant'
        ));
    }
}
