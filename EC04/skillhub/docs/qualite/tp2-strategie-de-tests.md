# TP 2 — Stratégie de tests SkillHub

**Module** : Qualité logicielle et amélioration continue — CDWFS, bloc BC04, FM08
**Critères** : C34.1 couverture · C34.2 meilleures pratiques · C34.3 documentation des tests
**Périmètre** : règles de capacité d'un atelier — la fonctionnalité centrale de SkillHub.

---

## 1. Les cas à couvrir, écrits en français avant tout code

Règle métier auditée : *un atelier a une capacité maximale ; chaque inscription consomme une place ; on ne peut pas inscrire au-delà de la capacité ; une désinscription rend la place.*

### Partitions d'équivalence

On découpe les états possibles en familles qui se comportent de la même façon. Un représentant par famille suffit.

| Famille | Représentant | Comportement attendu |
|---|---|---|
| Atelier neuf | 12 places sur 12 | non complet, toutes les places offertes |
| Atelier entamé | 10 places sur 12 | non complet, inscription acceptée |
| Atelier complet | 0 place sur 12 | complet, inscription refusée |
| Atelier incohérent | -3 places sur 12 | traité comme complet, jamais affiché en négatif |
| Atelier non renseigné | places à `null` | erreur distincte : ce n'est pas « complet » |

Cinq familles, cinq cas — pas cinquante.

### Valeurs aux bornes

La règle bascule entre « il reste une place » et « il n'en reste plus ». La frontière est à zéro, on teste de part et d'autre.

| Places restantes | Attendu |
|:--:|---|
| 2 | non complet |
| 1 | non complet — dernière place disponible |
| **0** | **complet — la borne elle-même** |
| -1 | complet — ne devrait jamais arriver, vérifié quand même |

Bornes de la capacité : `capaciteMax = 0` (atelier sans aucune place), et ré-incrément quand `placesRestantes == capaciteMax` (on ne doit pas créer une place qui n'existe pas).

### Scénarios d'erreur

| Ce qui doit échouer | Erreur attendue | État après l'échec |
|---|---|---|
| Inscrire sur un atelier complet | `AtelierCompletException`, message contenant « capacité atteinte » | places inchangées à 0 |
| Manipuler un atelier dont les places sont `null` | `PlacesNonRenseigneesException`, message distinct | aucune écriture |
| Créer un atelier sans titre via l'API | HTTP 422, corps `{"error": "Title is required"}` | aucune notification envoyée |

---

## 2. La suite écrite

| Fichier | Niveau | Tests | Infrastructure requise |
|---|---|:--:|---|
| `tests/Entity/AtelierTest.php` | unitaire | 15 | aucune |
| `tests/Controller/api/CreationAtelierTest.php` | intégration légère + doublure | 2 | aucune |
| `tests/Controller/api/ApiControllerTest.php` | intégration HTTP (existant) | 3 | PostgreSQL |
| `tests/Integration/PersistanceAtelierTest.php` | intégration base | 2 | PostgreSQL jetable |

**Répartition** : 15 unitaires pour 7 tests d'intégration. La base de la pyramide, totalement absente avant ce TP, existe désormais.

### Les choix de conception, et pourquoi

**Un test, un motif d'échec.** `testInscriptionRefuseeQuandLAtelierEstComplet` vérifie le type et le message de l'erreur. `testUneInscriptionRefuseeNeModifiePasLesPlaces` vérifie l'état. Deux comportements distincts, deux tests : quand l'un casse, on sait lequel des deux est en cause.

**Les noms décrivent le comportement.** `testUneDesinscriptionNeDepasseJamaisLaCapaciteMaximale` se lit sans ouvrir le corps de la fonction. Quand la CI est rouge, le nom seul suffit à savoir ce qui est cassé.

**Un test paramétré pour les bornes.** `fournirLesBornesDeCapacite` couvre quatre valeurs autour de la frontière dans une seule fonction. Chaque cas apparaît séparément dans le rapport, avec son libellé en français.

**Une doublure pour la dépendance externe.** `NotificationService` envoie des courriels. Le test prouve l'appel, le destinataire (`admin@skillhub.com`) et le contenu (le titre créé). Sans doublure, chaque exécution de la CI enverrait de vrais messages. Un second test prouve l'inverse : **aucune** notification quand la création échoue.

**Aucun test ne dépend d'un autre.** Chaque test construit son propre atelier. Les tests d'intégration ouvrent une transaction dans `setUp()` et l'annulent dans `tearDown()` : la base revient à son état initial, quoi qu'il arrive.

**L'infrastructure absente n'est pas une régression.** Les tests d'intégration sont ignorés, pas rouges, quand la base n'est pas démarrée — avec le message qui dit comment la lancer.

---

## 3. Le seuil de couverture, et sa justification

Un seuil global unique pousse à tester le trivial pour faire monter le chiffre. La politique retenue en a donc deux, appliqués par `tools/verifier-couverture.php` :

| Seuil | Valeur | Justification |
|---|:--:|---|
| Plancher global | **20 %** | L'état de départ mesuré est proche de zéro : un test sur 24 fichiers. Un plancher à 70 % serait rouge dès le premier jour et serait désactivé dans la semaine. 20 % empêche l'effondrement et sera relevé à chaque palier atteint. |
| Cœur métier | **90 %** | `src/Entity/Atelier.php` et `src/Exception/` portent les règles dont un défaut coûte une place vendue deux fois. C'est là que l'exigence doit être forte. |
| Non-régression | la couverture ne baisse pas d'une version à l'autre | Le rapport est publié en artefact à chaque exécution, y compris en cas d'échec. |

**Hors périmètre volontairement** : les migrations Doctrine, la configuration, et le code généré. Les tester ne prouverait rien et gonflerait artificiellement le chiffre.

**Ce que la couverture ne dit pas** : elle ne prouve ni que les assertions sont pertinentes, ni que les cas limites sont traités. Elle sert ici de détecteur de zones aveugles, pas de note.

---

## 4. Procédure d'exécution

### Prérequis

- PHP 8.4 ou 8.5 avec les extensions `ctype`, `iconv`, `pdo_pgsql`
- Composer, et `composer install` exécuté une fois
- Docker, uniquement pour les tests d'intégration
- `pcov` ou `xdebug`, uniquement pour produire un rapport de couverture

### Lancer toute la suite

```bash
cd EC04/skillhub
php vendor/bin/phpunit
```

Sans base de données démarrée, les tests d'intégration sont **ignorés** et affichent `S`. Les tests unitaires, eux, passent toujours.

### Lancer un seul fichier

```bash
php vendor/bin/phpunit tests/Entity/AtelierTest.php
php vendor/bin/phpunit --filter testCompletudeAuxBornes
```

### Avec la base de données

```bash
docker compose -f compose.test.yaml up -d     # base neuve sur le port 55432
php bin/console doctrine:schema:create --env=test
php vendor/bin/phpunit
docker compose -f compose.test.yaml down      # tout disparaît
```

### Obtenir le rapport de couverture

```bash
php vendor/bin/phpunit --coverage-html var/coverage/html
open var/coverage/html/index.html
```

### Lire les résultats

| Format | Où | Conservation |
|---|---|---|
| Sortie lisible (`--testdox`) | onglet Actions, job « Tests et qualité » | 90 jours |
| Clover XML | artefact `couverture-<sha>` | 14 jours |
| HTML navigable | même artefact, `html/index.html` | 14 jours |

### Quand un test échoue

1. Le nom du test indique le comportement cassé — inutile d'ouvrir les journaux en premier.
2. L'auteur de la pull request corrige. La fusion est bloquée tant que le job est rouge.
3. Un échec sur `main` est traité le jour même : c'est la branche de déploiement.
4. Un test qui échoue par intermittence est réparé ou supprimé dans la semaine. Jamais relancé jusqu'à ce qu'il passe.

---

## 5. Preuve que la porte bloque

### Régression injectée dans le code métier

La borne haute a été retirée de `incrementerPlaces()`. Résultat (`docs/qualite/preuves/echec-provoque-tests.txt`) :

```
1) App\Tests\Entity\AtelierTest::testUneDesinscriptionNeDepasseJamaisLaCapaciteMaximale
Failed asserting that 13 is identical to 12.

Tests: 15, Assertions: 19, Failures: 1.
```

Le défaut est nommé par le test : une désinscription pouvait créer une 13ᵉ place sur un atelier de 12. Code restauré depuis.

### Seuil de couverture non tenu

Rapport de couverture volontairement insuffisant soumis à la porte (`docs/qualite/preuves/echec-provoque-couverture.txt`) :

```
Couverture globale     : 11.50 %  (plancher 20 %)
Couverture cœur métier : 45.00 %  (exigence 90 %)

PORTE DE QUALITÉ FERMÉE :
  - couverture globale 11.50 % sous le plancher de 20 %
  - couverture du cœur métier 45.00 % sous l'exigence de 90 %
code de sortie = 1
```

Code de sortie 1 : le job échoue, la fusion est bloquée. **Un contrôle qui n'échoue jamais ne sert à rien ; celui-ci sait bloquer.**

---

## 6. Fiche de cas de test

| Champ | Contenu |
|---|---|
| **Identifiant** | CT-INS-004 |
| **Objet** | Refus d'inscription sur un atelier dont la capacité est atteinte |
| **Préconditions** | Atelier « Docker pour débutants », capacité 1, 1 inscrit — donc 0 place restante |
| **Étapes** | 1. Construire l'atelier avec `capaciteMax = 1`<br>2. Appeler `decrementerPlaces()` une première fois<br>3. Appeler `decrementerPlaces()` une seconde fois |
| **Résultat attendu** | `App\Exception\AtelierCompletException` levée, message contenant « capacité atteinte », `getPlacesDisponibles()` toujours à 0 |
| **Automatisé** | Oui — `tests/Entity/AtelierTest.php::testInscriptionRefuseeQuandLAtelierEstComplet` et `::testUneInscriptionRefuseeNeModifiePasLesPlaces` |
| **Dernière exécution** | Renseigné par la CI à chaque exécution du job « Tests et qualité » |
| **Défaut d'origine** | Dette D2 du TP 1 : l'appel échouait silencieusement, sans erreur ni trace |

---

## Ce que ce TP alimente dans le guide

Section *Stratégie de tests* (§1 à §3) et section *Exécution et résultats des tests* (§4 à §6).
