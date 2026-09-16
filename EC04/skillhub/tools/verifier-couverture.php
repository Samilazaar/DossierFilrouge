<?php

/**
 * Porte de qualité : vérifie le rapport de couverture Clover produit par PHPUnit.
 *
 * PHPUnit n'a pas d'option de seuil intégrée. Ce script applique la politique
 * décrite dans docs/qualite/tp2-strategie-de-tests.md :
 *
 *   - un PLANCHER global, pour empêcher l'effondrement de la couverture ;
 *   - une EXIGENCE FORTE sur le cœur métier, là où un défaut coûte cher.
 *
 * Usage : php tools/verifier-couverture.php var/coverage/clover.xml
 * Sortie : code 0 si les deux seuils sont tenus, 1 sinon (le pipeline bloque).
 */

// Plancher global : c'est un cliquet, pas un objectif. Il vaut la mesure de la
// dernière fusion arrondie vers le bas, et il ne peut que monter. Un seuil posé
// au-dessus de la mesure réelle bloque en permanence et ne prouve plus rien.
const PLANCHER_GLOBAL = 6.0;    // mesuré le 16/09/2026 — à relever à chaque fusion

// Exigence forte là où un défaut coûte cher : les règles de capacité et les
// exceptions métier. L'entité Atelier n'en fait plus partie depuis que ses
// règles sont extraites dans App\Domain — elle ne porte que de la persistance
// et des accesseurs, dont la couverture ne prouverait rien.
const EXIGENCE_COEUR  = 90.0;
const COEUR_METIER    = ['src/Domain/', 'src/Exception/'];

$chemin = $argv[1] ?? 'var/coverage/clover.xml';

if (!is_readable($chemin)) {
    fwrite(STDERR, "Rapport de couverture introuvable : {$chemin}\n");
    exit(1);
}

$clover = simplexml_load_file($chemin);
if ($clover === false) {
    fwrite(STDERR, "Rapport de couverture illisible : {$chemin}\n");
    exit(1);
}

/** Pourcentage de lignes couvertes, à partir d'un compteur Clover. */
function pourcentage(int $couvertes, int $total): float
{
    return $total === 0 ? 100.0 : round($couvertes * 100 / $total, 2);
}

// ---- couverture globale -------------------------------------------------
$metriques = $clover->project->metrics;
$globale = pourcentage(
    (int) $metriques['coveredstatements'],
    (int) $metriques['statements']
);

// ---- couverture du cœur métier -----------------------------------------
$coeurCouvertes = 0;
$coeurTotal = 0;
$detail = [];

foreach ($clover->xpath('//file') as $fichier) {
    $nom = (string) $fichier['name'];

    foreach (COEUR_METIER as $motif) {
        if (!str_contains($nom, $motif)) {
            continue;
        }

        $couvertes = (int) $fichier->metrics['coveredstatements'];
        $total = (int) $fichier->metrics['statements'];

        $coeurCouvertes += $couvertes;
        $coeurTotal += $total;
        $detail[basename($nom)] = pourcentage($couvertes, $total);
        break;
    }
}

$coeur = pourcentage($coeurCouvertes, $coeurTotal);

// ---- verdict ------------------------------------------------------------
printf("Couverture globale     : %5.2f %%  (plancher %.0f %%)\n", $globale, PLANCHER_GLOBAL);
printf("Couverture cœur métier : %5.2f %%  (exigence %.0f %%)\n", $coeur, EXIGENCE_COEUR);

foreach ($detail as $fichier => $taux) {
    printf("  - %-28s %5.2f %%\n", $fichier, $taux);
}

$echecs = [];

if ($globale < PLANCHER_GLOBAL) {
    $echecs[] = sprintf('couverture globale %.2f %% sous le plancher de %.0f %%', $globale, PLANCHER_GLOBAL);
}

if ($coeurTotal === 0) {
    $echecs[] = 'aucun fichier du cœur métier trouvé dans le rapport : le périmètre est mal configuré';
} elseif ($coeur < EXIGENCE_COEUR) {
    $echecs[] = sprintf('couverture du cœur métier %.2f %% sous l\'exigence de %.0f %%', $coeur, EXIGENCE_COEUR);
}

if ($echecs !== []) {
    fwrite(STDERR, "\nPORTE DE QUALITÉ FERMÉE :\n");
    foreach ($echecs as $echec) {
        fwrite(STDERR, "  - {$echec}\n");
    }
    exit(1);
}

echo "\nPorte de qualité franchie.\n";
exit(0);
