# TP 1 — Diagnostic qualité de SkillHub

**Module** : Qualité logicielle et amélioration continue — CDWFS, bloc BC04, FM08
**Périmètre audité** : `EC04/skillhub/` (Symfony 7 / PHP 8.4, FrankenPHP), dépôt `Samilazaar/DossierFilrouge`
**Date du relevé** : 14 septembre 2026
**Méthode** : lecture du dépôt et exécution locale. Chaque note est justifiée par un fait vérifiable, pas par une impression.

---

## 1. Notation ISO/IEC 25010

Échelle : **0** absent — **1** partiel et non vérifié — **2** en place mais incomplet — **3** en place et vérifié automatiquement.

| # | Caractéristique | Note | Justification factuelle |
|---|---|:--:|---|
| 1 | Adéquation fonctionnelle | **2** | Les parcours inscription / désinscription / feedback existent et sont exercés par `DashboardController`. Aucune spécification écrite ne permet de vérifier la complétude : il n'existe aucun test des règles métier. |
| 2 | Performance | **0** | Aucune mesure d'aucune sorte. Pas de scénario de charge, pas de budget de temps de réponse, pas de mesure sur l'ouverture des inscriptions — qui est pourtant le moment critique connu. |
| 3 | Compatibilité | **2** | L'application s'intègre à PostgreSQL et MongoDB via Doctrine ; l'API est décrite dans `openapi.json` et exposée par NelmioApiDoc. Aucun test de contrat ne vérifie que l'implémentation respecte ce contrat. |
| 4 | Utilisabilité | **1** | Interface Twig fonctionnelle, messages flash en français. Aucun test de parcours, aucune vérification d'accessibilité. |
| 5 | Fiabilité | **1** | `Atelier::decrementerPlaces()` (`src/Model/Atelier.php:90`) ne fait rien quand il n'y a plus de place, sans lever d'erreur ni journaliser : une inscription en trop est acceptée silencieusement. Aucune sauvegarde de base, donc aucun test de restauration. |
| 6 | Sécurité | **1** | `POST /api/workshops` et `DELETE /api/workshops/{id}` (`src/Controller/api/ApiController.php:102` et `:120`) n'ont aucun contrôle d'accès : aucun attribut `#[IsGranted]`, et `config/packages/` ne contient pas de `security.yaml`. `gitleaks` et `Trivy` tournent en CI (`security.yml`) et ne remontent aucun secret. `VisitLog` stocke IP et user-agent sans durée de conservation déclarée. |
| 7 | Maintenabilité | **1** | La logique de places est **dupliquée à l'identique** entre `src/Model/Atelier.php` et `src/Entity/Atelier.php` (`estComplet`, `getPlacesDisponibles`, `decrementerPlaces`, `incrementerPlaces`). Deux implémentations de dépôt coexistent : `DataRepository` et `DoctrineDataRepository`. Couverture de tests inconnue et non mesurée. |
| 8 | Portabilité | **2** | `Dockerfile` multi-stage, `compose.yaml`, image publiée sur ghcr et signée par cosign : le déploiement est reproductible. Mais `compose.yaml` du VPS n'existe pas dans le dépôt, donc le serveur de production n'est pas reconstructible à partir des sources. |

**Moyenne : 1,25 / 3.** Le point faible n'est pas le code livré, c'est **l'absence de preuve** : presque rien n'est vérifié automatiquement.

---

## 2. Ce qui est déjà automatisé

| Barrière | Outil en place | Preuve | Verdict |
|---|---|---|---|
| Éditeur | — | aucune configuration partagée dans le dépôt | ❌ absent |
| Pré-commit | — | pas de `.pre-commit-config.yaml`, pas de `husky` | ❌ absent |
| Lint PHP / Twig / YAML | `ci.yml` | job `quality` | ✅ |
| Analyse statique | PHPStan | `phpstan.dist.neon`, étape bloquante du job `quality` | ✅ |
| Tests | PHPUnit | job `quality`, 3 tests | ⚠️ voir §3 |
| Couverture | — | aucune occurrence de `coverage` dans les 4 workflows | ❌ absent |
| Secrets | gitleaks | `security.yml`, 0 secret détecté | ✅ |
| Dépendances / image | Trivy (fs + image) | `security.yml`, `cd.yml` | ✅ |
| Signature d'image | cosign keyless | `cd.yml` | ✅ |
| Déploiement | SSH + rollback `:previous` | `cd.yml`, environnement `production` | ✅ |

**Lecture** : les barrières tardives (CI, déploiement) sont solides. Les barrières précoces (éditeur, pré-commit) et la mesure de couverture sont totalement absentes. C'est l'inverse du principe « shift-left ».

---

## 3. État réel de la suite de tests

Exécution locale, le 14 septembre 2026 :

```
$ php vendor/bin/phpunit
Tests: 3, Assertions: 4, Failures: 1.

Doctrine\DBAL\Exception\ConnectionException:
SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5432 failed: Connection refused
```

Trois constats :

1. **Le seul fichier de test couvre 1 classe sur 24.** `tests/Controller/api/ApiControllerTest.php` est le seul test du projet, pour 24 fichiers dans `src/`.
2. **Les trois tests sont des tests d'intégration présentés comme des tests unitaires.** Ils étendent `WebTestCase`, démarrent le noyau Symfony complet et atteignent PostgreSQL. Aucun ne peut s'exécuter sans infrastructure.
3. **La suite échoue sur un poste neuf.** Aucune documentation n'indique qu'il faut démarrer une base avant de lancer les tests. Un nouvel arrivant conclut que le projet est cassé.
4. **Les dépendances de développement ne s'installent pas en local.** `composer install` échoue : `doctrine/mongodb-odm` exige `ext-mongodb ^2.4` alors que l'extension installée est en 2.1.8. Conséquence directe : PHPStan, déclaré dans `composer.json` et rendu bloquant en CI, ne peut pas être exécuté avant de pousser. La barrière d'analyse statique n'existe donc que sur le serveur, jamais chez le développeur — l'inverse du principe « shift-left ».

La pyramide des tests du projet est donc **vide à la base et étroite au sommet** : zéro test unitaire, trois tests d'intégration, zéro bout en bout, zéro charge.

---

## 4. Dette technique

| # | Dette | Origine | Ce qu'elle coûte aujourd'hui |
|---|---|---|---|
| D1 | Logique de places dupliquée entre `Model\Atelier` et `Entity\Atelier` | Migration du stockage JSON vers Doctrine sans suppression de l'ancien modèle (`migrate_json_to_db.php` en témoigne) | Toute correction de règle métier doit être faite deux fois. Un oubli crée une incohérence invisible entre l'affichage et la persistance. |
| D2 | Échec silencieux de `decrementerPlaces()` sur atelier complet | Garde posée au niveau du contrôleur (`DashboardController:164`) plutôt que dans le modèle | En cas d'inscriptions simultanées, deux requêtes passent la garde et une place est attribuée deux fois — sans aucune trace dans les journaux. Impossible à diagnostiquer après coup. |
| D3 | API d'écriture sans authentification | API construite pour la démonstration, sécurité repoussée à plus tard | N'importe qui sur Internet peut créer ou supprimer un atelier. Bloquant pour une mise en production réelle. |
| D4 | Deux implémentations de dépôt (`DataRepository`, `DoctrineDataRepository`) | Même migration que D1 | Le lecteur ne sait pas laquelle fait foi. Risque de corriger le code mort. |
| D5 | Aucun seuil de couverture en CI | Pipeline construit pour le module CI/CD, l'axe qualité n'était pas au programme | Rien ne signale qu'une fonctionnalité arrive sans test. La dette de test grossit sans être mesurée. |

Ces cinq lignes constituent le contenu initial de `DETTE.md`, à tenir à jour et à rembourser par petits versements.

---

## 5. Definition of done

Applicable à toute pull request sur SkillHub. Six points, tous vérifiables par un tiers.

1. Le comportement nouveau ou modifié est couvert par au moins un test **unitaire** qui échoue si on annule le changement.
2. Tout chemin d'erreur introduit est couvert par un test vérifiant **le type de l'erreur et son message**, et l'absence d'écriture partielle.
3. `ci.yml` est vert : lint PHP, Twig et YAML, PHPUnit, et couverture globale ≥ 60 % sans régression par rapport à la branche `main`.
4. `security.yml` est vert : gitleaks ne remonte aucun secret, Trivy aucune vulnérabilité critique.
5. La documentation touchée est à jour dans la même pull request : `README.md` si une commande ou une variable change, `docs/qualite/` si une règle change.
6. La pull request est relue par un pair et toutes les remarques préfixées `bloquant` sont traitées ou explicitement écartées avec argument.

---

## 6. Les trois priorités retenues

**Priorité 1 — Fiabilité.** C'est la seule caractéristique dont un défaut est déjà identifié et reproductible (D2). Une place attribuée deux fois est une faute visible par l'utilisateur final, impossible à rattraper automatiquement, et invisible dans les journaux. Traitement : tests unitaires sur les règles de places, puis remontée de la garde dans le modèle.

**Priorité 2 — Maintenabilité.** Elle conditionne tout le reste : tant que la logique est dupliquée (D1, D4), toute correction de fiabilité ou de sécurité doit être appliquée deux fois, et chaque correction a une chance sur deux d'être incomplète. Traitement : mesurer la couverture, puis supprimer la duplication sous filet de tests.

**Priorité 3 — Sécurité.** L'API d'écriture ouverte (D3) est le risque de plus forte gravité du projet. Elle n'est pas en priorité 1 uniquement parce que l'exposition actuelle est limitée à un VPS de démonstration, et que la corriger sans tests de non-régression sur les parcours existants risquerait de casser l'application.

**Écartées volontairement** : performance et utilisabilité. Mesurer la charge d'une application dont les règles métier ne sont pas testées reviendrait à optimiser un comportement dont on ignore s'il est correct.

---

## Ce que ce diagnostic alimente dans le guide

Section 1 — *Périmètre et engagements qualité* : la notation ISO, la definition of done et les trois priorités.
