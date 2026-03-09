# Plan d'Implémentation MongoDB pour SkillHub

## C'est quoi MongoDB ? (Explication simple)

### Imagine deux types de rangement

**SQL (PostgreSQL) = Une armoire IKEA**
- Chaque tiroir a une taille fixe
- Tu dois décider à l'avance combien de cases tu veux
- Si tu veux ajouter une case, tu dois tout démonter
- Parfait pour : utilisateurs, ateliers, inscriptions (données structurées)

**NoSQL (MongoDB) = Un sac à dos**
- Tu mets ce que tu veux dedans
- Pas besoin de définir la forme à l'avance
- Tu peux ajouter des poches quand tu veux
- Parfait pour : logs, activités, notifications (données flexibles)

### Pourquoi on a besoin des deux ?

| PostgreSQL (SQL) | MongoDB (NoSQL) |
|------------------|-----------------|
| Données fixes et liées | Données variables et indépendantes |
| "Un user A inscrit à atelier B" | "User A a cliqué sur X à 14h32" |
| Relations importantes | Pas de relations, juste des événements |
| Requêtes complexes (JOIN) | Requêtes simples et rapides |

### Exemple concret

**PostgreSQL stocke :**
```
User: {id: 1, nom: "Dupont", email: "dupont@mail.com"}
```
→ Toujours la même structure, toujours ces champs.

**MongoDB stocke :**
```
{
  type: "connexion",
  user_id: 1,
  date: "2026-01-12 10:30:00",
  ip: "192.168.1.1",
  navigateur: "Chrome",
  appareil: "mobile"
}
```
→ Structure flexible. Demain on peut ajouter `pays: "France"` sans rien casser.

---

## Qu'est-ce qu'on va stocker dans MongoDB ?

### Collection 1 : `activity_logs` (Logs d'activité)

Enregistre tout ce que font les utilisateurs :

```json
{
  "_id": "ObjectId(...)",
  "type": "inscription_atelier",
  "user_id": 1,
  "atelier_id": 5,
  "timestamp": "2026-01-12T14:30:00Z",
  "details": {
    "atelier_titre": "Introduction PHP",
    "places_restantes_avant": 10,
    "places_restantes_apres": 9
  }
}
```

**Types d'activités à logger :**
- `user_register` : inscription d'un utilisateur
- `user_login` : connexion
- `user_logout` : déconnexion
- `inscription_atelier` : inscription à un atelier
- `desinscription_atelier` : désinscription d'un atelier
- `view_atelier` : consultation d'un atelier

### Collection 2 : `notifications` (Notifications)

Messages pour les utilisateurs :

```json
{
  "_id": "ObjectId(...)",
  "user_id": 1,
  "type": "rappel_atelier",
  "message": "Votre atelier 'PHP Avancé' commence demain !",
  "read": false,
  "created_at": "2026-01-12T10:00:00Z"
}
```

### Collection 3 : `stats` (Statistiques agrégées)

Statistiques calculées pour le dashboard admin :

```json
{
  "_id": "ObjectId(...)",
  "date": "2026-01-12",
  "type": "daily_stats",
  "data": {
    "connexions": 45,
    "inscriptions": 12,
    "nouveaux_users": 3,
    "ateliers_complets": 2
  }
}
```

---

## Architecture Hybride (PostgreSQL + MongoDB)

```
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION SYMFONY                      │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│   ┌─────────────────┐           ┌─────────────────┐          │
│   │   PostgreSQL    │           │    MongoDB      │          │
│   │   (Doctrine)    │           │  (MongoClient)  │          │
│   └────────┬────────┘           └────────┬────────┘          │
│            │                             │                    │
│   ┌────────▼────────┐           ┌────────▼────────┐          │
│   │ Données métier  │           │ Données flexibles│          │
│   │ - users         │           │ - activity_logs │          │
│   │ - ateliers      │           │ - notifications │          │
│   │ - inscriptions  │           │ - stats         │          │
│   │ - formateurs    │           │                 │          │
│   └─────────────────┘           └─────────────────┘          │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## Étapes d'Implémentation

### Phase 1 : Infrastructure Docker

**Objectif** : Ajouter MongoDB au docker-compose

**Fichier à modifier** : `compose.yaml`

**Ce qu'on ajoute** :
- Service `mongodb` (image mongo:7)
- Volume pour persistence des données
- Variables d'environnement pour authentification
- Réseau partagé avec PostgreSQL

**Nouveau service dans compose.yaml** :
```yaml
mongodb:
  image: mongo:7
  restart: unless-stopped
  environment:
    MONGO_INITDB_ROOT_USERNAME: ${MONGO_USER:-skillhub}
    MONGO_INITDB_ROOT_PASSWORD: ${MONGO_PASSWORD:-skillhub_secret}
    MONGO_INITDB_DATABASE: ${MONGO_DB:-skillhub_logs}
  volumes:
    - mongodb_data:/data/db
  ports:
    - "27017:27017"
  healthcheck:
    test: echo 'db.runCommand("ping").ok' | mongosh localhost:27017/test --quiet
    interval: 10s
    timeout: 5s
    retries: 5
```

---

### Phase 2 : Dépendances PHP

**Objectif** : Installer le driver MongoDB pour PHP

**Commande** :
```bash
composer require mongodb/mongodb
```

**Ce que ça fait** :
- Installe la librairie officielle MongoDB pour PHP
- Permet de se connecter et manipuler MongoDB depuis Symfony

---

### Phase 3 : Configuration Symfony

**Objectif** : Configurer la connexion MongoDB

**Fichier à créer** : `config/packages/mongodb.yaml`

```yaml
parameters:
    mongodb_url: '%env(MONGODB_URL)%'
    mongodb_database: '%env(MONGODB_DATABASE)%'
```

**Fichier à modifier** : `.env`

```env
MONGODB_URL=mongodb://skillhub:skillhub_secret@localhost:27017
MONGODB_DATABASE=skillhub_logs
```

---

### Phase 4 : Service MongoDB

**Objectif** : Créer un service pour interagir avec MongoDB

**Fichier à créer** : `src/Service/MongoDBService.php`

**Responsabilités** :
- Connexion à MongoDB
- Méthodes pour logger les activités
- Méthodes pour gérer les notifications
- Méthodes pour les statistiques

**Méthodes prévues** :

| Méthode | Description |
|---------|-------------|
| `logActivity(string $type, int $userId, array $details)` | Enregistre une activité |
| `getActivitiesByUser(int $userId, int $limit)` | Récupère les activités d'un user |
| `getRecentActivities(int $limit)` | Récupère les dernières activités |
| `addNotification(int $userId, string $type, string $message)` | Ajoute une notification |
| `getUnreadNotifications(int $userId)` | Notifications non lues |
| `markNotificationRead(string $notificationId)` | Marque comme lue |
| `saveDailyStats(array $stats)` | Sauvegarde les stats du jour |
| `getStatsByDateRange(string $from, string $to)` | Stats sur une période |

---

### Phase 5 : Intégration dans les Controllers

**Objectif** : Utiliser MongoDB dans l'application

**Fichiers à modifier** :

| Fichier | Modification |
|---------|-------------|
| `ConnexionController.php` | Logger `user_login` et `user_logout` |
| `InscriptionController.php` | Logger `user_register` |
| `DashboardController.php` | Logger `inscription_atelier`, `desinscription_atelier`, `view_atelier` |

**Exemple d'utilisation** :
```php
// Dans ConnexionController après une connexion réussie
$this->mongoService->logActivity('user_login', $user->getId(), [
    'ip' => $request->getClientIp(),
    'user_agent' => $request->headers->get('User-Agent')
]);
```

---

### Phase 6 : Interface Admin (Optionnel)

**Objectif** : Visualiser les logs et stats

**Fichiers à créer** :
- `src/Controller/AdminController.php`
- `templates/admin/logs.html.twig`
- `templates/admin/stats.html.twig`

**Routes** :
- `/admin/logs` : Voir les logs d'activité
- `/admin/stats` : Voir les statistiques

---

### Phase 7 : Scripts de Backup

**Objectif** : Sauvegarder MongoDB

**Fichier à créer** : `scripts/backup.sh`

```bash
#!/bin/bash
# Backup MongoDB
mongodump --uri="mongodb://user:pass@localhost:27017" --out=/backups/mongo_$(date +%Y%m%d)

# Backup PostgreSQL
pg_dump -h localhost -U app app > /backups/postgres_$(date +%Y%m%d).sql
```

---

## Résumé des Fichiers

### À créer

| Fichier | Description |
|---------|-------------|
| `config/packages/mongodb.yaml` | Configuration MongoDB |
| `src/Service/MongoDBService.php` | Service d'accès MongoDB |
| `src/Controller/AdminController.php` | Controller admin (optionnel) |
| `templates/admin/logs.html.twig` | Vue logs (optionnel) |
| `templates/admin/stats.html.twig` | Vue stats (optionnel) |
| `scripts/backup.sh` | Script de backup |

### À modifier

| Fichier | Modification |
|---------|-------------|
| `compose.yaml` | Ajouter service MongoDB |
| `.env` | Ajouter variables MongoDB |
| `ConnexionController.php` | Ajouter logging |
| `InscriptionController.php` | Ajouter logging |
| `DashboardController.php` | Ajouter logging |
| `composer.json` | Dépendance mongodb/mongodb |

---

## Checklist de Validation

### Infrastructure
- [ ] MongoDB tourne dans Docker (`docker compose up -d`)
- [ ] Connexion possible (`mongosh mongodb://localhost:27017`)

### Code
- [ ] `MongoDBService` injecté dans les controllers
- [ ] Logs créés à chaque action utilisateur
- [ ] Pas d'erreur si MongoDB est down (fail gracefully)

### Données
- [ ] Collection `activity_logs` contient des documents
- [ ] Collection `notifications` fonctionne
- [ ] Collection `stats` se remplit

### Backup
- [ ] Script `backup.sh` fonctionne
- [ ] Backup testable avec `mongorestore`

---

## Questions à Clarifier

1. **Notifications** : Veut-on vraiment les notifications ou juste les logs ?
2. **Stats** : Calcul automatique (cron) ou manuel ?
3. **Admin** : Interface admin nécessaire ou juste les données ?
4. **Rétention** : Combien de temps garder les logs ? (30 jours ? 90 jours ?)

---

## Estimation des Phases

| Phase | Complexité |
|-------|------------|
| 1. Docker | Simple |
| 2. Dépendances | Simple |
| 3. Configuration | Simple |
| 4. Service MongoDB | Moyenne |
| 5. Intégration Controllers | Moyenne |
| 6. Interface Admin | Optionnel |
| 7. Scripts Backup | Simple |

---

**Plan créé le** : 2026-01-12
**Statut** : En attente de validation
