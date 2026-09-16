## Ce que fait cette pull request

Les règles de capacité d'un atelier lèvent désormais des exceptions nommées au
lieu d'échouer en silence. La suite de tests passe de 3 à 22 tests répartis sur
trois niveaux, avec deux seuils de couverture bloquants en intégration continue.
La documentation qualité du module est consignée dans `docs/qualite/`.

## Pourquoi

Travaux pratiques 1 à 4 du module FM08. Le diagnostic initial (TP 1) a relevé
deux points : aucun test ne couvrait les règles de capacité, et un dépassement
de places renvoyait un résultat indistinguable d'un succès.

## Comment vérifier

Sans base de données — 19 tests, moins d'une seconde :

    cd EC04/skillhub
    php vendor/bin/phpunit --testdox

Les 3 tests qui exigent une base apparaissent en `S` (ignorés), pas en échec.

Avec la base :

    docker compose -f compose.test.yaml up -d
    php bin/console doctrine:schema:create --env=test
    php vendor/bin/phpunit
    docker compose -f compose.test.yaml down

Seuils de couverture :

    php tools/verifier-couverture.php var/coverage/clover.xml

Attendu : sortie 0 au-dessus des seuils, sortie non nulle en dessous.

## Points d'attention pour le relecteur

- **Le plancher global est à 20 %**, ce qui est bas. C'est délibéré : le seuil
  qui compte est celui du cœur métier, à 90 %. La justification est dans
  `docs/qualite/tp2-strategie-de-tests.md` — c'est le point sur lequel je veux
  un avis.
- **Les tests qui ont besoin de la base sont ignorés plutôt qu'en échec** quand
  elle n'est pas démarrée. Ça évite de bloquer un développeur qui lance juste
  les tests unitaires, mais ça masque aussi une base absente en CI. Choix
  discutable.
- **`docs/qualite/tp3-pull-request/ExportController.php` est du code
  volontairement défectueux**, fourni par l'énoncé du TP 3 comme support de
  revue. À ne pas relire comme du code de production, et à ne jamais déployer.

## Vérifications

- [x] Tests ajoutés ou mis à jour, dont un cas d'erreur
- [x] Documentation touchée mise à jour (README, configuration, dépannage)
- [x] Aucun secret, aucune donnée personnelle dans le code ni les journaux
- [ ] Les contrôles automatiques passent
- [x] Le changement est réversible (procédure de retour arrière connue)

La case des contrôles automatiques reste décochée tant que la CI n'a pas tourné
sur cette branche : c'est une information pour le relecteur, pas un échec.
