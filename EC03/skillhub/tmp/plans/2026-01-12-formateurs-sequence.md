# Plan Formateurs - Séquence Logique

## Vue d'ensemble

```
Entités (fait) → Activer code → BDD → Repository → Form → Controller → Dashboard → Tests
```

---

## Étape 1 : Activer les TODOs dans UserFormateur.php

**Fichier** : `src/Entity/UserFormateur.php`

**Lignes à modifier** : 76-77 et 85-86

**Avant** :
```php
// TODO: Activer après l'ÉTAPE 3 (modification de Atelier.php)
// $atelier->setFormateur($this);
```

**Après** :
```php
$atelier->setFormateur($this);
```

**Pourquoi** : L'étape 3 (Atelier.php) est déjà faite. Le code peut être activé.

---

## Étape 2 : Synchroniser la BDD

**Commandes** :
```bash
cd /Users/samilazaar/DossierFilrouge/EC03/skillhub

# Voir les changements SQL à appliquer
php bin/console doctrine:schema:update --dump-sql

# Appliquer les changements
php bin/console doctrine:schema:update --force

# Vérifier que tout est OK
php bin/console doctrine:schema:validate
```

**Résultat attendu** : Table `users_formateurs` créée avec colonnes id, user_id, bio, specialite, experiences

---

## Étape 3 : Ajouter méthodes dans DoctrineDataRepository

**Fichier** : `src/Service/DoctrineDataRepository.php`

**Import à ajouter** :
```php
use App\Entity\UserFormateur;
```

**Méthodes à ajouter** :
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

public function findAteliersByFormateur(UserFormateur $formateur): array
{
    return $this->entityManager->getRepository(Atelier::class)
        ->findBy(['formateur' => $formateur]);
}
```

---

## Étape 4 : Modifier le formulaire d'inscription

**Fichier** : `templates/inscription/index.html.twig`

**Ajouter après le champ téléphone** :

```twig
<div class="form-group">
    <label for="role">Je suis :</label>
    <select name="role" id="role" required onchange="toggleFormateurFields()">
        <option value="etudiant">Étudiant</option>
        <option value="formateur">Formateur</option>
    </select>
</div>

<div id="formateurFields" style="display: none;">
    <div class="form-group">
        <label for="specialite">Spécialité :</label>
        <input type="text" id="specialite" name="specialite" placeholder="Ex: Développement Web">
    </div>

    <div class="form-group">
        <label for="bio">Bio :</label>
        <textarea id="bio" name="bio" rows="3" placeholder="Présentez-vous en quelques lignes..."></textarea>
    </div>

    <div class="form-group">
        <label for="experiences">Expériences :</label>
        <textarea id="experiences" name="experiences" rows="3" placeholder="Vos expériences professionnelles..."></textarea>
    </div>
</div>

<script>
function toggleFormateurFields() {
    const role = document.getElementById('role').value;
    const fields = document.getElementById('formateurFields');
    fields.style.display = role === 'formateur' ? 'block' : 'none';
}
</script>
```

---

## Étape 5 : Modifier InscriptionController

**Fichier** : `src/Controller/InscriptionController.php`

**Import à ajouter** :
```php
use App\Entity\UserFormateur;
```

**Dans la méthode index(), après récupération des champs existants, ajouter** :
```php
$role = $request->request->get('role', 'etudiant');
$bio = $request->request->get('bio');
$specialite = $request->request->get('specialite');
$experiences = $request->request->get('experiences');
```

**Validation à ajouter** :
```php
if (!in_array($role, ['etudiant', 'formateur'])) {
    $errors[] = 'Rôle invalide.';
}
```

**Après `$this->repository->addUser($user);`, ajouter** :
```php
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

## Étape 6 : Créer le template dashboard formateur

**Fichier à créer** : `templates/dashboard/formateur.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}Dashboard Formateur - {{ parent() }}{% endblock %}

{% block body %}
<div class="dashboard-container">
    {% include 'partials/_header.html.twig' %}

    <div class="dashboard-content">
        <aside class="sidebar">
            <nav>
                <a href="{{ path('app_dashboard') }}">Tous les Ateliers</a>
                <a href="{{ path('app_dashboard_formateur') }}" class="active">Mes Ateliers</a>
                <a href="{{ path('app_dashboard_inscriptions') }}">Mes Inscriptions</a>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Dashboard Formateur</h1>

            <section class="profil-formateur">
                <h2>Mon Profil</h2>
                <p><strong>Spécialité :</strong> {{ formateur_profile.specialite ?? 'Non renseignée' }}</p>
                <p><strong>Bio :</strong> {{ formateur_profile.bio ?? 'Non renseignée' }}</p>
                <p><strong>Expériences :</strong> {{ formateur_profile.experiences ?? 'Non renseignées' }}</p>
            </section>

            <section class="mes-ateliers">
                <h2>Mes Ateliers</h2>
                {% if mes_ateliers|length > 0 %}
                    <div class="ateliers-grid">
                        {% for atelier in mes_ateliers %}
                            <div class="atelier-card">
                                <img src="{{ atelier.imageUrl }}" alt="{{ atelier.titre }}">
                                <h3>{{ atelier.titre }}</h3>
                                <p>{{ atelier.description|slice(0, 100) }}...</p>
                                <p><strong>Places :</strong> {{ atelier.placesRestantes }}/{{ atelier.capaciteMax }}</p>
                                <a href="{{ path('app_atelier_detail', {id: atelier.id}) }}" class="btn btn-primary">Voir détails</a>
                            </div>
                        {% endfor %}
                    </div>
                {% else %}
                    <p>Vous n'avez pas encore créé d'ateliers.</p>
                {% endif %}
            </section>
        </main>
    </div>
</div>
{% endblock %}
```

---

## Étape 7 : Modifier DashboardController

**Fichier** : `src/Controller/DashboardController.php`

**Ajouter la méthode** :
```php
#[Route('/dashboard/formateur', name: 'app_dashboard_formateur')]
public function formateurDashboard(SessionInterface $session): Response
{
    $user = $this->checkUser($session);
    if (!$user) {
        return $this->redirectToRoute('app_connexion');
    }

    if (!$user->isFormateur()) {
        return $this->redirectToRoute('app_dashboard');
    }

    $formateurProfile = $this->repository->findFormateurByUser($user);
    $mesAteliers = $this->repository->findAteliersByFormateur($formateurProfile);

    return $this->render('dashboard/formateur.html.twig', [
        'page_title' => 'Dashboard Formateur',
        'user' => $user,
        'formateur_profile' => $formateurProfile,
        'mes_ateliers' => $mesAteliers,
    ]);
}
```

---

## Étape 8-10 : Tests

### Test inscription étudiant
1. Aller sur `/inscription`
2. Choisir "Étudiant"
3. Remplir le formulaire
4. Vérifier : User créé, PAS de UserFormateur

### Test inscription formateur
1. Aller sur `/inscription`
2. Choisir "Formateur"
3. Remplir le formulaire + bio/spécialité/expériences
4. Vérifier : User créé + UserFormateur créé

### Test dashboard formateur
1. Se connecter avec un compte formateur
2. Aller sur `/dashboard/formateur`
3. Vérifier : profil affiché, liste ateliers affichée

---

## Checklist finale

- [ ] Étape 1 : TODOs activés dans UserFormateur.php
- [ ] Étape 2 : Table users_formateurs créée en BDD
- [ ] Étape 3 : Méthodes formateurs dans DoctrineDataRepository
- [ ] Étape 4 : Formulaire inscription avec choix rôle
- [ ] Étape 5 : InscriptionController gère la création formateur
- [ ] Étape 6 : Template formateur.html.twig créé
- [ ] Étape 7 : Route /dashboard/formateur fonctionnelle
- [ ] Étape 8 : Test inscription étudiant OK
- [ ] Étape 9 : Test inscription formateur OK
- [ ] Étape 10 : Test dashboard formateur OK

---

**Plan créé le** : 2026-01-12
**Statut** : Validé - Prêt pour implémentation
