# 📋 PLAN D'IMPLÉMENTATION: TABLE USERS + TABLE USERS_FORMATEURS

## 🎯 RÉSUMÉ DE L'APPROCHE

**Structure finale:**
- **Table `users`**: Contient tous les utilisateurs (étudiants + formateurs)
- **Table `users_formateurs`**: Contient les informations spécifiques aux formateurs
- **Table `ateliers`**: Liée aux formateurs via `users_formateurs`

**Relations Doctrine:**
- `User` ↔ `UserFormateur`: OneToOne/OneToOne
- `UserFormateur` ↔ `Atelier`: OneToMany/ManyToOne
- `User` ↔ `Inscription`: OneToMany/ManyToOne
- `Atelier` ↔ `Inscription`: OneToMany/ManyToOne

---

## 📦 ÉTAPE 1: CRÉATION DE L'ENTITÉ USER_FORMATEUR

**Fichier à créer:** `src/Entity/UserFormateur.php`

**Contenu:**
```php
<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users_formateurs')]
class UserFormateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $experiences = null;

    #[ORM\OneToMany(targetEntity: Atelier::class, mappedBy: 'formateur')]
    private Collection $ateliers;

    public function __construct()
    {
        $this->ateliers = new ArrayCollection();
    }

    // Getters et setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getExperiences(): ?string
    {
        return $this->experiences;
    }

    public function setExperiences(?string $experiences): self
    {
        $this->experiences = $experiences;
        return $this;
    }

    public function getAteliers(): Collection
    {
        return $this->ateliers;
    }

    public function addAtelier(Atelier $atelier): self
    {
        if (!$this->ateliers->contains($atelier)) {
            $this->ateliers->add($atelier);
            $atelier->setFormateur($this);
        }

        return $this;
    }

    public function removeAtelier(Atelier $atelier): self
    {
        if ($this->ateliers->removeElement($atelier)) {
            if ($atelier->getFormateur() === $this) {
                $atelier->setFormateur(null);
            }
        }

        return $this;
    }
}
```

---

## 🔗 ÉTAPE 2: MODIFICATION DE L'ENTITÉ USER

**Fichier à modifier:** `src/Entity/User.php`

**Ajouter après la propriété `password`:**
```php
#[ORM\OneToOne(targetEntity: UserFormateur::class, mappedBy: 'user')]
private ?UserFormateur $formateurProfile = null;
```

**Ajouter les getters/setters (après les autres):**
```php
public function getFormateurProfile(): ?UserFormateur
{
    return $this->formateurProfile;
}

public function setFormateurProfile(?UserFormateur $formateurProfile): self
{
    $this->formateurProfile = $formateurProfile;
    return $this;
}
```

**Ajouter méthode utilitaire (après `verifyPassword`):**
```php
public function isFormateur(): bool
{
    return $this->formateurProfile !== null;
}
```

---

## 🎓 ÉTAPE 3: MODIFICATION DE L'ENTITÉ ATELIER

**Fichier à modifier:** `src/Entity/Atelier.php`

**Remplacer la propriété `formateur`:**

**Avant:**
```php
#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $formateur = null;
```

**Après:**
```php
#[ORM\ManyToOne(targetEntity: UserFormateur::class)]
#[ORM\JoinColumn(nullable: true)]
private ?UserFormateur $formateur = null;
```

**Remplacer le getter/setter:**

**Avant:**
```php
public function getFormateur(): ?string
{
    return $this->formateur;
}

public function setFormateur(?string $formateur): self
{
    $this->formateur = $formateur;
    return $this;
}
```

**Après:**
```php
public function getFormateur(): ?UserFormateur
{
    return $this->formateur;
}

public function setFormateur(?UserFormateur $formateur): self
{
    $this->formateur = $formateur;
    return $this;
}
```

**Ajouter méthode utilitaire (après `incrementerPlaces`):**
```php
public function getFormateurNom(): string
{
    if ($this->formateur) {
        $user = $this->formateur->getUser();
        return $user ? $user->getNom() . ' ' . $user->getPrenom() : 'N/A';
    }
    return 'N/A';
}
```

---

## 🗄️ ÉTAPE 4: MISE À JOUR DU SCHÉMA DE BASE DE DONNÉES

**Commandes à exécuter:**
```bash
cd /Users/samilazaar/DossierFilRouge/EC03/skillhub

# Voir les changements SQL
php bin/console doctrine:schema:update --dump-sql

# Appliquer les changements
php bin/console doctrine:schema:update --force

# Vérifier que le schéma est correct
php bin/console doctrine:schema:validate
```

---

## 💾 ÉTAPE 5: MIGRATION DES DONNÉES EXISTANTES

**Fichier à créer:** `migrate_to_formateurs_table.php`

**Contenu:**
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use App\Entity\User;
use App\Entity\UserFormateur;
use App\Entity\Atelier;

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');

$kernel = new Kernel('dev', true);
$kernel->boot();

$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

// Récupérer tous les utilisateurs
$users = $entityManager->getRepository(User::class)->findAll();

// Créer des UserFormateur basés sur des critères simples
foreach ($users as $user) {
    $email = $user->getEmail();

    // Identifier les formateurs potentiels
    $isFormateur = false;

    if (strpos($email, 'formateur') !== false ||
        strpos($email, 'admin') !== false ||
        $email === 'test@skillhub.com') {
        $isFormateur = true;
    }

    if ($isFormateur) {
        // Vérifier si un UserFormateur existe déjà
        $existingFormateur = $entityManager->getRepository(UserFormateur::class)
            ->findOneBy(['user' => $user]);

        if (!$existingFormateur) {
            $formateurProfile = new UserFormateur();
            $formateurProfile->setUser($user);
            $formateurProfile->setBio('Formateur expérimenté');
            $formateurProfile->setSpecialite('Développement Web');
            $entityManager->persist($formateurProfile);

            echo "Créé UserFormateur pour {$email}\n";
        }
    }
}

$entityManager->flush();
echo "UserFormateurs créés avec succès!\n";

// Migrer les ateliers existants
$ateliers = $entityManager->getRepository(Atelier::class)->findAll();

foreach ($ateliers as $atelier) {
    // Logique simple de matching - à adapter selon tes besoins
    $formateurs = $entityManager->getRepository(UserFormateur::class)->findAll();

    // Associer le premier formateur disponible (à adapter)
    if (!empty($formateurs)) {
        $formateurProfile = $formateurs[0];
        $atelier->setFormateur($formateurProfile);

        $user = $formateurProfile->getUser();
        echo "Atelier {$atelier->getTitre()} assigné à {$user->getNom()}\n";
    }
}

$entityManager->flush();
echo "Ateliers migrés avec succès!\n";
```

**Exécuter le script:**
```bash
php migrate_to_formateurs_table.php
```

---

## 🧩 ÉTAPE 6: MISE À JOUR DU REPOSITORY DOCTRINE

**Fichier à modifier:** `src/Service/DoctrineDataRepository.php`

**Ajouter les imports:**
```php
use App\Entity\UserFormateur;
```

**Ajouter ces méthodes à la fin de la classe:**
```php
public function findAllFormateurs(): array
{
    return $this->entityManager->getRepository(UserFormateur::class)->findAll();
}

public function findFormateurById(int $id): ?UserFormateur
{
    return $this->entityManager->getRepository(UserFormateur::class)->find($id);
}

public function findFormateurByUser(User $user): ?UserFormateur
{
    return $this->entityManager->getRepository(UserFormateur::class)
        ->findOneBy(['user' => $user]);
}

public function addFormateurProfile(UserFormateur $formateurProfile): void
{
    $this->entityManager->persist($formateurProfile);
    $this->entityManager->flush();
}

public function removeFormateurProfile(UserFormateur $formateurProfile): void
{
    $this->entityManager->remove($formateurProfile);
    $this->entityManager->flush();
}

public function findAteliersByFormateur(UserFormateur $formateur): array
{
    return $this->entityManager->getRepository(Atelier::class)
        ->findBy(['formateur' => $formateur]);
}
```

---

## 🎨 ÉTAPE 7: MODIFICATION DU FORMULAIRE D'INSCRIPTION

**Fichier à modifier:** `templates/inscription/index.html.twig`

**Ajouter les champs spécifiques aux formateurs:**

```twig
<div class="form-group">
    <label for="role">Rôle:</label>
    <select name="role" id="role" required class="form-control" onchange="toggleFormateurFields()">
        <option value="etudiant">Étudiant</option>
        <option value="formateur">Formateur</option>
    </select>
</div>

<!-- Champs spécifiques aux formateurs (cachés par défaut) -->
<div id="formateurFields" style="display: none;">
    <div class="form-group mt-3">
        <label for="bio">Bio:</label>
        <textarea name="bio" id="bio" class="form-control" rows="3"></textarea>
    </div>

    <div class="form-group">
        <label for="specialite">Spécialité:</label>
        <input type="text" name="specialite" id="specialite" class="form-control">
    </div>

    <div class="form-group">
        <label for="experiences">Expériences:</label>
        <textarea name="experiences" id="experiences" class="form-control" rows="3"></textarea>
    </div>
</div>

<script>
function toggleFormateurFields() {
    const role = document.getElementById('role').value;
    const formateurFields = document.getElementById('formateurFields');

    if (role === 'formateur') {
        formateurFields.style.display = 'block';
    } else {
        formateurFields.style.display = 'none';
    }
}
</script>
```

---

## 🧩 ÉTAPE 8: MODIFICATION DE L'INSCRIPTION CONTROLLER

**Fichier à modifier:** `src/Controller/InscriptionController.php`

**1. Ajouter l'import:**
```php
use App\Entity\UserFormateur;
```

**2. Ajouter la capture des champs formateurs (dans la méthode `index`):**
```php
$role = $request->request->get('role');
$bio = $request->request->get('bio');
$specialite = $request->request->get('specialite');
$experiences = $request->request->get('experiences');
```

**3. Ajouter une validation du rôle:**
```php
if (!in_array($role, ['etudiant', 'formateur'])) {
    $errors[] = 'Le rôle sélectionné n\'est pas valide.';
}
```

**4. Modifier la création de l'utilisateur (dans le bloc `if (empty($errors))`):**
```php
$user = new User();
$user->setNom($nom);
$user->setPrenom($prenom);
$user->setEmail($email);
$user->setTelephone($telephone);
$user->setPassword($hashedPassword);

$this->repository->addUser($user);

// Si c'est un formateur, créer son profil formateur
if ($role === 'formateur') {
    $formateurProfile = new UserFormateur();
    $formateurProfile->setUser($user);
    $formateurProfile->setBio($bio);
    $formateurProfile->setSpecialite($specialite);
    $formateurProfile->setExperiences($experiences);

    $this->repository->addFormateurProfile($formateurProfile);
}
```

---

## 🎛️ ÉTAPE 9: MODIFICATION DU DASHBOARD CONTROLLER

**Fichier à modifier:** `src/Controller/DashboardController.php`

**1. Ajouter l'import:**
```php
use App\Entity\UserFormateur;
```

**2. Ajouter la méthode pour le dashboard formateur:**
```php
#[Route('/dashboard/formateur', name: 'app_dashboard_formateur')]
public function formateurDashboard(SessionInterface $session): Response
{
    $user = $this->checkUser($session);
    if (!$user || !$user->isFormateur()) {
        return $this->redirectToRoute('app_dashboard');
    }

    $formateurProfile = $this->repository->findFormateurByUser($user);
    $mesAteliers = $this->repository->findAteliersByFormateur($formateurProfile);

    return $this->render('dashboard/formateur.html.twig', [
        'page_title' => 'Dashboard Formateur',
        'mes_ateliers' => $mesAteliers,
        'formateur_profile' => $formateurProfile,
        'active_view' => 'formateur',
        'user' => $user,
    ]);
}
```

**3. Modifier la méthode `detail` (ajouter les infos formateur):**
```php
$formateurNom = $atelier->getFormateurNom();

return $this->render('dashboard/atelier_detail.html.twig', [
    'page_title' => $atelier->getTitre(),
    'atelier' => $atelier,
    'user' => $user,
    'estInscrit' => $estInscrit,
    'inscription' => $inscription,
    'formateurNom' => $formateurNom,
]);
```

---

## 📄 ÉTAPE 10: MISE À JOUR DES TEMPLATES

### Modifier `templates/dashboard/index.html.twig`

**Remplacer l'affichage du formateur:**
```twig
<div class="card-text">
    <small class="text-muted">
        Formateur: {{ atelier.getFormateurNom() }}
    </small>
</div>
```

### Modifier `templates/dashboard/formateur.html.twig`

**Contenu:**
```twig
{% extends 'base.html.twig' %}

{% block title %}Dashboard Formateur - {{ parent() }}{% endblock %}

{% block body %}
<div class="container mt-4">
    <h1>{{ page_title }}</h1>
    <p>Bienvenue, {{ user.prenom }} {{ user.nom }}</p>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="list-group">
                <a href="{{ path('app_dashboard') }}" class="list-group-item list-group-item-action">
                    Voir tous les Ateliers
                </a>
                <a href="{{ path('app_dashboard_formateur') }}" class="list-group-item list-group-item-action active">
                    Mes Ateliers
                </a>
                <a href="{{ path('app_dashboard_inscriptions') }}" class="list-group-item list-group-item-action">
                    Inscriptions
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Mon Profil Formateur</h3>
                </div>
                <div class="card-body">
                    <p><strong>Spécialité:</strong> {{ formateur_profile.specialite }}</p>
                    <p><strong>Bio:</strong> {{ formateur_profile.bio }}</p>
                    <p><strong>Expériences:</strong> {{ formateur_profile.experiences }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Mes Ateliers</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {% for atelier in mes_ateliers %}
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <img src="{{ atelier.imageUrl }}" class="card-img-top" alt="{{ atelier.titre }}">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ atelier.titre }}</h5>
                                        <p class="card-text">{{ atelier.description|slice(0, 100) }}...</p>
                                        <div class="mt-2">
                                            <a href="{{ path('app_atelier_detail', {id: atelier.id}) }}" class="btn btn-primary">
                                                Voir détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {% else %}
                            <p>Vous n'avez pas encore créé d'ateliers.</p>
                        {% endfor %}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
```

---

## 🧪 ÉTAPE 11: TESTS ET VÉRIFICATIONS

### Tests à effectuer:

**1. Test de la structure de la BDD:**
```bash
# Vérifier que la table users_formateurs existe
sqlite3 var/data.db ".tables"

# Vérifier le schéma des tables
sqlite3 var/data.db ".schema users"
sqlite3 var/data.db ".schema users_formateurs"
sqlite3 var/data.db ".schema ateliers"
```

**2. Test d'inscription en tant qu'étudiant:**
- Se rendre sur `/inscription`
- Choisir "Étudiant" comme rôle
- Vérifier qu'un User est créé SANS UserFormateur
- Se connecter et vérifier l'accès au dashboard

**3. Test d'inscription en tant que formateur:**
- Se rendre sur `/inscription`
- Choisir "Formateur" comme rôle
- Remplir les champs spécifiques (bio, spécialité, expériences)
- Vérifier qu'un User ET un UserFormateur sont créés
- Se connecter et vérifier l'accès au dashboard formateur

**4. Vérifications Doctrine:**
```bash
# Vérifier les UserFormateurs
php bin/console doctrine:query:dql "SELECT f FROM App\Entity\UserFormateur f"

# Vérifier les relations User ↔ UserFormateur
php bin/console doctrine:query:dql "SELECT u, f FROM App\Entity\User u LEFT JOIN u.formateurProfile f"

# Vérifier les ateliers avec leurs formateurs
php bin/console doctrine:query:dql "SELECT a, f FROM App\Entity\Atelier a LEFT JOIN a.formateur f"
```

---

## 🧹 ÉTAPE 12: NETTOYAGE ET DOCUMENTATION

**Actions:**
```bash
# Supprimer le script de migration
rm migrate_to_formateurs_table.php

# Vider le cache
php bin/console cache:clear
```

**Documentation à créer:** `README_ROLES.md`
```markdown
# Structure des Utilisateurs et Formateurs

## Tables de base de données

### users
Contient tous les utilisateurs (étudiants et formateurs)
- id, nom, prenom, email, telephone, password

### users_formateurs
Contient les informations spécifiques aux formateurs
- id, user_id (FK → users), bio, specialite, experiences

### ateliers
Reliée aux formateurs via users_formateurs
- id, titre, description, ..., formateur_id (FK → users_formateurs)

## Relations Doctrine

- User ↔ UserFormateur: OneToOne/OneToOne
- UserFormateur ↔ Atelier: OneToMany/ManyToOne
- User ↔ Inscription: OneToMany/ManyToOne
- Atelier ↔ Inscription: OneToMany/ManyToOne
```

---

## ✅ CHECKLIST DE FIN D'IMPLÉMENTATION

- [ ] ÉTAPE 1: Création de l'entité UserFormateur
- [ ] ÉTAPE 2: Modification de l'entité User
- [ ] ÉTAPE 3: Modification de l'entité Atelier
- [ ] ÉTAPE 4: Mise à jour du schéma de BDD
- [ ] ÉTAPE 5: Migration des données existantes
- [ ] ÉTAPE 6: Mise à jour du repository Doctrine
- [ ] ÉTAPE 7: Modification du formulaire d'inscription
- [ ] ÉTAPE 8: Modification de l'inscription controller
- [ ] ÉTAPE 9: Modification du dashboard controller
- [ ] ÉTAPE 10: Mise à jour des templates
- [ ] ÉTAPE 11: Tests et vérifications
- [ ] ÉTAPE 12: Nettoyage et documentation

---

## 📊 RÉSUMÉ DES FICHIERS À CRÉER/MODIFIER

### Fichiers à créer:
- [ ] `src/Entity/UserFormateur.php`
- [ ] `migrate_to_formateurs_table.php` (temporaire)
- [ ] `templates/dashboard/formateur.html.twig`

### Fichiers à modifier:
- [ ] `src/Entity/User.php`
- [ ] `src/Entity/Atelier.php`
- [ ] `src/Service/DoctrineDataRepository.php`
- [ ] `src/Controller/InscriptionController.php`
- [ ] `src/Controller/DashboardController.php`
- [ ] `templates/inscription/index.html.twig`
- [ ] `templates/dashboard/index.html.twig`

---

## 🎯 VALIDATIONS FINALES

À la fin de l'implémentation, tu devrais être capable de:

- [ ] Inscrire un étudiant (création User uniquement)
- [ ] Inscrire un formateur (création User + UserFormateur)
- [ ] Vérifier les tables `users` et `users_formateurs` en BDD
- [ ] Voir les relations correctes entre User et UserFormateur
- [ ] Afficher le nom du formateur dans les ateliers
- [ ] Accéder au dashboard formateur avec les informations spécifiques
- [ ] Vérifier que `User::isFormateur()` fonctionne correctement
