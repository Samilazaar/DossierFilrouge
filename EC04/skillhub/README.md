# SkillHub - Plateforme collaborative de formation en ligne

**Framework** : Symfony 8.0
**PHP** : 8.5
**Architecture** : MVC + API REST

---

## Installation

```bash
git clone <repo-url>
cd skillhub
composer install
```

## Variables d'environnement

Copier le fichier `.env` et configurer :

```
DATABASE_URL=postgresql://user:password@127.0.0.1:5432/skillhub
```

## Lancer le projet

```bash
# Démarrer la base de données
docker compose up -d

# Lancer le serveur
php -S localhost:8000 -t public
```

Accéder à : http://localhost:8000

## API Docs (Swagger)

Documentation interactive accessible sur :

```
http://localhost:8000/api/doc
```

Export du fichier OpenAPI :

```bash
php bin/console nelmio:apidoc:dump --format=json > openapi.json
```

## Endpoints API

| Méthode | URL | Description | Code |
|---------|-----|-------------|------|
| GET | `/api/workshops` | Liste les ateliers (pagination + filtres) | 200 |
| POST | `/api/workshops` | Créer un atelier | 201 |
| DELETE | `/api/workshops/{id}` | Supprimer un atelier | 204 |
| GET | `/api/students/{id}/workshops` | Ateliers d'un étudiant | 200 |

### Query parameters (GET /api/workshops)

| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Numéro de page (défaut: 1) |
| `limit` | int | Résultats par page (défaut: 10, max: 50) |
| `teacher` | int | Filtrer par ID formateur |

## Tests

```bash
php bin/phpunit
```

## Prérequis

- PHP 8.4+
- Composer
- Docker (pour PostgreSQL et MongoDB)
