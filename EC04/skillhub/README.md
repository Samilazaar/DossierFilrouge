# SkillHub

Plateforme collaborative de formation en ligne.

Symfony 8.0 / PHP 8.4+ / PostgreSQL 16 / MongoDB 7

---

## Installer le projet

```bash
git clone <repo-url>
cd skillhub
composer install
```

Copier `.env` en `.env.local` et mettre les bonnes valeurs :

```
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
MONGODB_URI="mongodb://127.0.0.1:27017"
MONGODB_DB="skillhub"
```

## Lancer

```bash
docker compose up -d
php bin/console doctrine:schema:update --force
php -S localhost:8000 -t public
```

L'appli est sur http://localhost:8000

---

## Les bases de données

On utilise deux moteurs parce qu'ils répondent à des besoins différents.

**PostgreSQL** pour tout ce qui est structuré et relationnel :

| Table | C'est quoi |
|-------|-----------|
| `users` | Les utilisateurs, avec un champ `competences` en JSON |
| `users_formateurs` | Le profil des formateurs (lié 1:1 à users) |
| `ateliers` | Les ateliers, rattachés à un formateur |
| `inscriptions` | Qui est inscrit à quoi |
| `sessions` | La dernière session de chaque utilisateur (session_id, IP, date) |

**MongoDB** pour les données qu'on accumule sans jointures :

| Collection | C'est quoi |
|------------|-----------|
| `visit_logs` | Un log à chaque fois qu'un utilisateur visite un atelier |
| `Feedback` | Les avis et notes sur les ateliers |

Pour restaurer le dump PostgreSQL :

```bash
psql -U app -d app -f dump.sql
```

Les collections MongoDB se créent toutes seules quand on utilise l'appli.

---

## Ce que fait l'appli

- Connexion / déconnexion (la session est sauvegardée en SQL)
- Dashboard étudiant : voir les ateliers, s'inscrire, se désinscrire
- Dashboard formateur : créer et gérer ses ateliers
- Feedbacks sur les ateliers (stockés dans MongoDB)
- Logs de visite automatiques (MongoDB)
- Recommandation d'ateliers basée sur les compétences (Hugging Face)
- API REST avec Swagger sur `/api/doc`
- Gestion des compétences sur le profil

---

## L'API

La doc Swagger est sur http://localhost:8000/api/doc

| Méthode | URL | Quoi | Code |
|---------|-----|------|------|
| GET | `/api/workshops` | Liste des ateliers (pagination + filtre par formateur) | 200 |
| GET | `/api/workshops/{id}` | Détail d'un atelier | 200 |
| POST | `/api/workshops` | Créer un atelier | 201 |
| DELETE | `/api/workshops/{id}` | Supprimer un atelier | 204 |
| GET | `/api/students/{id}/workshops` | Les ateliers d'un étudiant | 200 |

Paramètres de `GET /api/workshops` : `page` (défaut 1), `limit` (défaut 10, max 50), `teacher` (filtrer par formateur).

```bash
php bin/console nelmio:apidoc:dump --format=json > openapi.json
```

## Tests

```bash
php bin/phpunit --testdox
```

3 tests sur l'API :
- `testGetWorkshops` — GET /api/workshops retourne 200 + JSON
- `testCreateInvalidWorkshop` — POST sans titre retourne 422
- `testCreateWorkshopWithMock` — POST valide retourne 201 (email mocké)

---

## Industrialisation — CI/CD

À chaque `git push`, GitHub Actions teste, contrôle, construit, signe et déploie
l'application. Trois pipelines séparés, enchaînés : chacun ne démarre que si le
précédent est vert.

```
                    git push
                        |
                        v
        +-----------------------------------+
        |  1. ci.yml — Tests et qualité     |
        |     lint PHP · Twig · YAML        |
        |     PHPUnit                       |
        +-----------------------------------+
                        | vert
                        v
        +-----------------------------------+
        |  2. security.yml — Sécurité       |
        |     gitleaks (secrets)            |
        |     Trivy fs (dépendances)        |  BLOQUANT
        +-----------------------------------+
                        | vert          (branche "test" uniquement)
                        v
        +-----------------------------------+
        |  3. cd.yml — Build & déploiement  |
        |     build image Docker            |
        |     smoke test de l'image         |
        |     Trivy image                   |  BLOQUANT
        |     push ghcr.io                  |
        |     signature cosign (keyless)    |
        |     - - - validation manuelle - - |
        |     deploy VPS + smoke + rollback |
        +-----------------------------------+
                        |
                        v
              VPS 72.60.132.58 : 31200
        app + postgres + mongo + keycloak
```

### Les trois fichiers

| Fichier | Rôle | Déclenchement |
|---------|------|---------------|
| `.github/workflows/ci.yml` | Tests et qualité, puis orchestre les deux autres | `push` sur `main`, `develop`, `test` · `pull_request` |
| `.github/workflows/security.yml` | gitleaks + Trivy sur le code | appelé par `ci.yml` (`workflow_call`) |
| `.github/workflows/cd.yml` | Image, signature, déploiement | appelé par `ci.yml`, branche `test` uniquement |

Le découpage utilise les **workflows réutilisables** (`uses:` + `workflow_call`)
plutôt que `workflow_run` : l'enchaînement reste explicite et fonctionne sur
n'importe quelle branche, sans dépendre de la branche par défaut.

### Ce qui arrête le pipeline

| Contrôle | Outil | Effet si KO |
|----------|-------|-------------|
| Syntaxe PHP, Twig, YAML | `php -l`, `lint:twig`, `lint:yaml` | arrêt |
| Tests unitaires | PHPUnit | arrêt |
| Secrets dans l'historique | gitleaks | arrêt |
| Failles du code (HIGH/CRITICAL corrigeables) | Trivy fs | arrêt |
| Failles de l'image (HIGH/CRITICAL corrigeables) | Trivy image | arrêt avant le push |
| L'image ne répond pas | smoke test | arrêt avant le push |
| L'app ne répond pas après déploiement | smoke test VPS | rollback automatique |

Les failles **sans correctif disponible** (`--ignore-unfixed`) sont affichées
mais ne bloquent pas : rien ne servirait d'arrêter une chaîne pour un problème
qu'on ne peut pas corriger.

### Signature de l'image (cosign)

L'image est signée **sans clé privée** (mode *keyless*) : l'identité du workflow
GitHub sert de preuve via un jeton OIDC, et le certificat est publié dans le
journal public Rekor. C'est l'**empreinte** de l'image qui est signée, pas le tag
`latest` — un tag peut être recollé sur une autre image, pas une empreinte.

Vérifier une image à la main :

```bash
cosign verify \
  --certificate-identity-regexp "^https://github.com/Samilazaar/DossierFilrouge/" \
  --certificate-oidc-issuer "https://token.actions.githubusercontent.com" \
  ghcr.io/samilazaar/skillhub-app:latest
```

### Secrets GitHub attendus

| Secret | Usage |
|--------|-------|
| `VPS_HOST` | adresse du serveur de déploiement |
| `VPS_USER` | compte SSH |
| `VPS_SSH_KEY` | clé privée de déploiement |
| `GITHUB_TOKEN` | fourni automatiquement (push ghcr, gitleaks) |

L'environnement `production` impose une **validation manuelle** avant le
déploiement (Settings → Environments → production → required reviewers).

---

## Runbook — que faire quand ça casse

### Le pipeline est rouge

Ouvrir l'onglet **Actions**, cliquer sur le run rouge, déplier l'étape en échec.

| Étape en échec | Cause probable | Quoi faire |
|----------------|----------------|------------|
| `Tests PHPUnit` | test cassé, ou service absent en CI | reproduire en local : `vendor/bin/phpunit --testdox` |
| `Vérification templates Twig` | environnement `test` mal configuré | `php bin/console lint:twig templates --env=test` en local |
| `Scan de secrets (gitleaks)` | un secret est parti dans un commit | **révoquer le secret d'abord**, puis nettoyer l'historique |
| `Scan des vulnérabilités (Trivy fs)` | dépendance Composer vulnérable | `composer update <paquet>` |
| `Scan Trivy de l'image` | paquet système vulnérable | rebuild (le Dockerfile fait `apt-get upgrade`) ; si la faille est inévitable, la tracer dans un `.trivyignore` avec une justification |
| `Smoke test — l'image démarre` | l'app plante au démarrage | `docker run --rm ghcr.io/samilazaar/skillhub-app:latest` en local et lire les logs |
| `Signature de l'image` | permission `id-token: write` absente | vérifier le bloc `permissions` du job appelant dans `ci.yml` |

### Le déploiement a échoué

Le rollback est **automatique** : si l'app ne répond pas après le déploiement,
le pipeline restaure l'image précédente (`:previous`) et se termine en rouge.

Rollback à la main, si besoin :

```bash
ssh slazaar@72.60.132.58
cd ~/skillhub
docker tag ghcr.io/samilazaar/skillhub-app:previous ghcr.io/samilazaar/skillhub-app:latest
docker compose up -d
docker compose ps
```

### Commandes utiles sur le VPS

```bash
docker compose ps                 # état des conteneurs
docker compose logs -f app        # logs de l'application
docker compose pull && docker compose up -d   # redéployer la dernière image
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:31200   # l'app répond ?
```

### Runner auto-hébergé

Un runner GitHub est installé sur le VPS (`~/skillhub-runner`, mode éphémère,
labels `self-hosted,skillhub,vps`). La construction de l'image tourne
actuellement sur les runners GitHub : dans le conteneur, le runner change
d'utilisateur et perd l'accès à `/var/run/docker.sock`. Pour rebasculer une fois
le problème d'accès résolu, remplacer dans `cd.yml` :

```yaml
runs-on: ubuntu-latest      ->    runs-on: [self-hosted, skillhub]
```

---

## Utilisation de l'IA

J'ai utilisé **Claude** (Anthropic) via Claude Code comme assistant dans mon workflow. C'est pas un générateur de code autonome : c'est moi qui décide quoi faire, l'IA m'aide à aller plus vite sur l'écriture, et je valide tout avant d'intégrer.

J'ai choisi Claude parce que les réponses sont pertinentes et qu'on voit les tokens utilisés. Ça permet de reset la session à 50% de la fenêtre de contexte pour garder des réponses de qualité, au lieu de laisser la conversation se dégrader.

### Ce que j'ai fait avec

- Création de l'entité `Session.php` et du document `VisitLog.php` en suivant les patterns du projet
- Modification des contrôleurs (`ConnexionController`, `DashboardController`) pour brancher les nouvelles features
- Création des contrôleurs API REST (`ApiController.php`, `TeacherController.php`) avec annotations OpenAPI
- Écriture des tests PHPUnit pour l'API (`ApiControllerTest.php`)
- Génération du fichier `openapi.json` pour Swagger
- Rédaction du rapport LaTeX (règles REST, screenshots, tests)
- Debug quand ça marchait pas (injection du DocumentManager, problèmes de cache, connexion BDD)
- Mise à jour du dump SQL

### Les prompts principaux

1. "Quel type SQL utiliser pour stocker un session ID PHP ?" → m'a confirmé VARCHAR(128) et expliqué pourquoi
2. "Pourquoi mon log MongoDB ne s'enregistre pas ?" → le cache Symfony n'avait pas pris mes modifs, fallait un `cache:clear`
3. "Comment structurer un rapport LaTeX avec des figures qui restent en place ?" → `\usepackage{float}` et `[H]`
4. "Rédige le rapport avec ce qu'il y a déjà" → il a analysé le code existant et généré le LaTeX avec les 4 règles REST, les tableaux de routes, le code et les emplacements pour les screenshots
5. "Installe Postman et lance Docker" → m'a aidé à mettre en place l'environnement pour tester les routes

### Pourquoi j'ai validé le code

- Le code suit les mêmes patterns que le reste du projet (même style d'entités, même injection de dépendances)
- J'ai testé chaque modif en lançant l'appli et en vérifiant dans DBeaver et MongoDB Compass que les données étaient bien là
- J'ai testé les routes API dans Postman (GET 200, POST 201, POST 422) — tout répond correctement
- Les 3 tests PHPUnit passent (3 tests, 5 assertions)
- J'ai vérifié que le dump SQL contenait bien la table `sessions` avec les bonnes contraintes
- L'appli marche même si MongoDB est éteint grâce au `if ($this->documentManager)`
