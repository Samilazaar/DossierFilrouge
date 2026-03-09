# Diagramme de Séquence - Use Case "S'inscrire à la Formation"

## Acteurs et Composants
- **Utilisateur** : Apprenant connecté
- **Navigateur** : Interface web
- **DashboardController** : Contrôleur Symfony
- **DoctrineDataRepository** : Service d'accès aux données
- **EntityManager** : Gestionnaire Doctrine ORM
- **Base de données** : PostgreSQL

---

## Diagramme PlantUML

```plantuml
@startuml
skinparam style strictuml
skinparam backgroundColor #FEFEFE
skinparam sequenceMessageAlign center

actor "Utilisateur\n(Apprenant)" as User
participant "Navigateur" as Browser
participant "DashboardController" as Controller
participant "DoctrineDataRepository" as Repository
participant "EntityManager" as EM
database "PostgreSQL" as DB

title Use Case: S'inscrire à une Formation (Atelier)

== Pré-condition: Utilisateur connecté sur /dashboard ==

User -> Browser : Clique "S'inscrire"\nsur un atelier
activate Browser

Browser -> Controller : POST /dashboard/atelier/{id}/inscrire
activate Controller

== Vérification de la session ==
Controller -> Controller : session->get('user_id')
alt Session invalide
    Controller --> Browser : Redirect /connexion
else Session valide

    == Récupération de l'utilisateur ==
    Controller -> Repository : findUserById(user_id)
    activate Repository
    Repository -> EM : find(User::class, id)
    activate EM
    EM -> DB : SELECT * FROM users WHERE id = ?
    activate DB
    DB --> EM : User data
    deactivate DB
    EM --> Repository : User entity
    deactivate EM
    Repository --> Controller : User
    deactivate Repository

    alt Utilisateur non trouvé
        Controller --> Browser : Redirect /connexion
    else Utilisateur trouvé

        == Récupération de l'atelier ==
        Controller -> Repository : findAtelierById(atelier_id)
        activate Repository
        Repository -> EM : find(Atelier::class, id)
        activate EM
        EM -> DB : SELECT * FROM ateliers WHERE id = ?
        activate DB
        DB --> EM : Atelier data
        deactivate DB
        EM --> Repository : Atelier entity
        deactivate EM
        Repository --> Controller : Atelier
        deactivate Repository

        alt Atelier non trouvé
            Controller --> Browser : Error "Atelier introuvable"
        else Atelier trouvé

            == Vérification inscription existante ==
            Controller -> Repository : findInscription(user, atelier)
            activate Repository
            Repository -> EM : findOneBy(criteria)
            activate EM
            EM -> DB : SELECT * FROM inscriptions\nWHERE utilisateur_id = ? AND atelier_id = ?
            activate DB
            DB --> EM : Result (null ou Inscription)
            deactivate DB
            EM --> Repository : Inscription | null
            deactivate EM
            Repository --> Controller : Inscription | null
            deactivate Repository

            alt Déjà inscrit
                Controller --> Browser : Error "Vous êtes déjà inscrit"
            else Pas encore inscrit

                == Vérification places disponibles ==
                Controller -> Controller : atelier->estComplet()
                note right: Vérifie placesRestantes > 0

                alt Atelier complet
                    Controller --> Browser : Error "Plus de places disponibles"
                else Places disponibles

                    == Création de l'inscription ==
                    Controller -> Controller : new Inscription()
                    Controller -> Controller : inscription->setUtilisateur(user)
                    Controller -> Controller : inscription->setAtelier(atelier)
                    Controller -> Controller : inscription->setDateInscription(now)

                    == Persistance ==
                    Controller -> Repository : addInscription(inscription)
                    activate Repository

                    Repository -> Repository : atelier->decrementerPlaces()
                    note right: placesRestantes--

                    Repository -> EM : persist(inscription)
                    activate EM
                    EM -> EM : Tracking inscription
                    deactivate EM

                    Repository -> EM : flush()
                    activate EM
                    EM -> DB : BEGIN TRANSACTION
                    activate DB
                    EM -> DB : INSERT INTO inscriptions\n(utilisateur_id, atelier_id, dateInscription)\nVALUES (?, ?, ?)
                    EM -> DB : UPDATE ateliers\nSET placesRestantes = placesRestantes - 1\nWHERE id = ?
                    EM -> DB : COMMIT
                    DB --> EM : OK
                    deactivate DB
                    deactivate EM

                    Repository --> Controller : void
                    deactivate Repository

                    == Confirmation ==
                    Controller -> Controller : addFlash('success',\n'Inscription réussie!')
                    Controller --> Browser : Redirect /dashboard/atelier/{id}

                end
            end
        end
    end
end

deactivate Controller

Browser -> User : Affiche page détail atelier\navec message "Inscription réussie!"
deactivate Browser

@enduml
```

---

## Diagramme Mermaid (Alternative)

```mermaid
sequenceDiagram
    autonumber

    actor User as Utilisateur (Apprenant)
    participant Browser as Navigateur
    participant Controller as DashboardController
    participant Repository as DoctrineDataRepository
    participant EM as EntityManager
    participant DB as PostgreSQL

    Note over User,DB: Pré-condition: Utilisateur connecté sur /dashboard

    User->>Browser: Clique "S'inscrire" sur atelier
    Browser->>Controller: POST /dashboard/atelier/{id}/inscrire

    rect rgb(255, 245, 238)
        Note right of Controller: Vérification Session
        Controller->>Controller: session->get('user_id')
    end

    rect rgb(240, 255, 240)
        Note right of Controller: Récupération Utilisateur
        Controller->>Repository: findUserById(user_id)
        Repository->>EM: find(User::class, id)
        EM->>DB: SELECT * FROM users WHERE id = ?
        DB-->>EM: User data
        EM-->>Repository: User entity
        Repository-->>Controller: User
    end

    rect rgb(240, 248, 255)
        Note right of Controller: Récupération Atelier
        Controller->>Repository: findAtelierById(atelier_id)
        Repository->>EM: find(Atelier::class, id)
        EM->>DB: SELECT * FROM ateliers WHERE id = ?
        DB-->>EM: Atelier data
        EM-->>Repository: Atelier entity
        Repository-->>Controller: Atelier
    end

    rect rgb(255, 250, 240)
        Note right of Controller: Vérification Inscription Existante
        Controller->>Repository: findInscription(user, atelier)
        Repository->>EM: findOneBy(criteria)
        EM->>DB: SELECT * FROM inscriptions WHERE utilisateur_id=? AND atelier_id=?
        DB-->>EM: null (pas d'inscription)
        EM-->>Repository: null
        Repository-->>Controller: null
    end

    rect rgb(245, 245, 255)
        Note right of Controller: Vérification Places
        Controller->>Controller: atelier->estComplet()
        Note right of Controller: placesRestantes > 0 = OK
    end

    rect rgb(240, 255, 240)
        Note right of Controller: Création Inscription
        Controller->>Controller: new Inscription()
        Controller->>Controller: setUtilisateur(user)
        Controller->>Controller: setAtelier(atelier)
        Controller->>Controller: setDateInscription(now)
    end

    rect rgb(255, 240, 245)
        Note right of Controller: Persistance
        Controller->>Repository: addInscription(inscription)
        Repository->>Repository: atelier->decrementerPlaces()
        Repository->>EM: persist(inscription)
        Repository->>EM: flush()
        EM->>DB: BEGIN TRANSACTION
        EM->>DB: INSERT INTO inscriptions (utilisateur_id, atelier_id, dateInscription)
        EM->>DB: UPDATE ateliers SET placesRestantes = placesRestantes - 1
        EM->>DB: COMMIT
        DB-->>EM: OK
        EM-->>Repository: void
        Repository-->>Controller: void
    end

    rect rgb(240, 255, 240)
        Note right of Controller: Confirmation
        Controller->>Controller: addFlash('success', 'Inscription réussie!')
        Controller-->>Browser: Redirect /dashboard/atelier/{id}
    end

    Browser-->>User: Affiche page détail + message succès
```

---

## Description Textuelle du Flux

### Scénario Principal (Succès)

| Étape | Acteur | Action |
|-------|--------|--------|
| 1 | Utilisateur | Clique sur "S'inscrire" pour un atelier depuis /dashboard |
| 2 | Navigateur | Envoie POST /dashboard/atelier/{id}/inscrire |
| 3 | Controller | Vérifie session utilisateur (user_id) |
| 4 | Controller | Récupère User via Repository |
| 5 | Controller | Récupère Atelier via Repository |
| 6 | Controller | Vérifie absence d'inscription existante |
| 7 | Controller | Vérifie places disponibles (estComplet() = false) |
| 8 | Controller | Crée nouvelle Inscription avec date courante |
| 9 | Repository | Décrémente placesRestantes de l'atelier |
| 10 | EntityManager | Persiste et flush en transaction |
| 11 | Controller | Ajoute flash message "Inscription réussie!" |
| 12 | Controller | Redirige vers /dashboard/atelier/{id} |
| 13 | Navigateur | Affiche page détail avec confirmation |

### Scénarios Alternatifs (Erreurs)

| Code | Condition | Résultat |
|------|-----------|----------|
| ALT-1 | Session invalide | Redirect vers /connexion |
| ALT-2 | Utilisateur non trouvé | Redirect vers /connexion |
| ALT-3 | Atelier non trouvé | Flash error "Atelier introuvable" |
| ALT-4 | Déjà inscrit | Flash error "Vous êtes déjà inscrit" |
| ALT-5 | Atelier complet | Flash error "Plus de places disponibles" |

---

## Fichiers Sources Impliqués

| Composant | Fichier |
|-----------|---------|
| Controller | `src/Controller/DashboardController.php` (méthode `inscrire()`) |
| Repository | `src/Service/DoctrineDataRepository.php` |
| Entity User | `src/Entity/User.php` |
| Entity Atelier | `src/Entity/Atelier.php` |
| Entity Inscription | `src/Entity/Inscription.php` |
| Template | `templates/dashboard/index.html.twig` |
| Template Détail | `templates/dashboard/atelier_detail.html.twig` |
