# SkillHub - Plateforme collaborative de formation en ligne

**Framework** : Symfony 8.0  
**PHP** : 8.5.1  
**Architecture** : MVC

---

## 📋 Description

Plateforme collaborative de formation en ligne avec tableau de bord utilisateur (apprenant ou formateur).

---

## 🚀 Démarrage rapide

### Prérequis
- PHP 8.4+
- Composer

### Installation
```bash
composer install
```

### Lancer le serveur
```bash
php -S localhost:8000 -t public
```

Accéder à : http://localhost:8000

---

## ✨ Fonctionnalités

### Pages publiques
- **Landing page** : Présentation de la plateforme
- **Formateurs** : Liste des formateurs avec profils

### Authentification
- **Inscription** : Nom, prénom, email, téléphone, mot de passe (hash BCrypt)
- **Connexion** : Vérification email/password avec session
- **Déconnexion** : Destruction de session

### Tableau de bord (après connexion)
- **Catalogue d'ateliers** : Grille responsive avec images et descriptions
- **Détail atelier** : Informations complètes (formateur, date, durée, places)
- **Inscription** : S'inscrire à un atelier (vérification places)
- **Désinscription** : Se désinscrire d'un atelier
- **Mes inscriptions** : Liste des inscriptions personnelles

---

## 📁 Structure du projet

```
src/
├── Controller/           # Contrôleurs MVC
│   ├── LandingController.php
│   ├── InscriptionController.php
│   ├── ConnexionController.php
│   ├── DashboardController.php
│   └── FormateurController.php
├── Model/                # Classes entités (sans BDD)
│   ├── User.php
│   ├── Atelier.php
│   └── Inscription.php
└── Service/              # Service de gestion des données
    └── DataRepository.php

templates/
├── base.html.twig        # Template de base
├── partials/              # Composants réutilisables
│   ├── _header.html.twig
│   └── _footer.html.twig
└── */                    # Templates par contrôleur

public/
├── assets/
│   ├── css/app.css       # Styles réutilisables
│   └── js/main.js       # JavaScript principal
└── index.php

var/
└── data_storage.json      # Stockage JSON (pas de BDD)
```

---

## 🔑 Comptes de test

| Email | Mot de passe |
|-------|-------------|
| test@skillhub.com | password123 |
| admin@skillhub.com | admin123 |

---

## 🏗 Architecture

**Framework** : Symfony 8.0 (étudié en cours)  
**Patron MVC** : Modèle-Vue-Contrôleur  
**Templating** : Twig 3.x  
**Stockage** : Fichier JSON (sans BDD relationnelle)  
**Données** : Simulées en dur dans le code (classes Model + DataRepository)

---

## 📦 Données simulées

**Classes Model** :
- `User` : id, nom, prenom, email, telephone, password
- `Atelier` : id, titre, description, date, duree, capaciteMax, placesRestantes, imageUrl, formateur
- `Inscription` : id, utilisateur (User), atelier (Atelier), dateInscription

**Service DataRepository** :
- Gestion des listes d'objets en mémoire
- Méthodes CRUD (findAll, findById, add, remove)
- Initialisation des données de démo en dur

---

## 🎨 Composants réutilisables

**Templates Twig** :
- `base.html.twig` : Structure de base
- `_header.html.twig` : Header dynamique (connexion/déconnexion)
- `_footer.html.twig` : Footer standardisé

**Classes CSS** :
- `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`
- `.container`, `.dashboard-container`, `.ateliers-grid`
- `.atelier-card`, `.btn-inscrire`

---

## 🔐 Sécurité

- Hash BCrypt des mots de passe
- Validation des entrées utilisateur
- Protection XSS (auto-escape Twig)
- Gestion sécurisée des sessions Symfony

---

## 🌐 Compatibilité navigateurs

- ✅ Chrome 120+
- ✅ Firefox 115+
- ✅ Safari 16+
- ✅ Edge 120+

---

## 📝 Notes

- Projet conforme à 100% aux consignes de l'épreuve EC02
- Pas de base de données relationnelle
- Données simulées en dur dans les classes Model
- Persistance par fichier JSON entre les requêtes HTTP

---

**Auteur** : [Votre Nom]  
**Date** : Janvier 2026  
**Version** : 1.0
