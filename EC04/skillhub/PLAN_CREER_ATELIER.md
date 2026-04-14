# PLAN: Créer un Atelier (Dashboard Formateur)

## Résumé

Permettre à un formateur de créer un nouvel atelier depuis son dashboard.

---

## ÉTAPE 1: Ajouter la méthode dans DoctrineDataRepository

**Fichier:** `src/Service/DoctrineDataRepository.php`

**Ajouter:**
```php
public function addAtelier(Atelier $atelier): void
{
    $this->entityManager->persist($atelier);
    $this->entityManager->flush();
}
```

---

## ÉTAPE 2: Ajouter la route dans DashboardController

**Fichier:** `src/Controller/DashboardController.php`

**Ajouter après la méthode `formateurDashboard`:**
```php
#[Route('/dashboard/formateur/atelier/creer', name: 'app_creer_atelier')]
public function creerAtelier(Request $request, SessionInterface $session): Response
{
    $user = $this->checkUser($session);
    if (!$user || !$user->isFormateur()) {
        return $this->redirectToRoute('app_dashboard');
    }

    $formateurProfile = $this->repository->findFormateurByUser($user);

    if ($request->isMethod('POST')) {
        $titre = $request->request->get('titre');
        $description = $request->request->get('description');
        $date = $request->request->get('date');
        $duree = $request->request->get('duree');
        $capaciteMax = (int) $request->request->get('capacite_max');
        $imageUrl = $request->request->get('image_url');

        $errors = [];

        if (empty($titre) || empty($description) || empty($date) || empty($duree) || $capaciteMax <= 0) {
            $errors[] = 'Tous les champs sont obligatoires.';
        }

        if (empty($errors)) {
            $atelier = new Atelier();
            $atelier->setTitre($titre);
            $atelier->setDescription($description);
            $atelier->setDate(new \DateTime($date));
            $atelier->setDuree($duree);
            $atelier->setCapaciteMax($capaciteMax);
            $atelier->setPlacesRestantes($capaciteMax);
            $atelier->setImageUrl($imageUrl ?: 'https://via.placeholder.com/400x200');
            $atelier->setFormateur($formateurProfile);

            $this->repository->addAtelier($atelier);

            return $this->redirectToRoute('app_dashboard_formateur');
        }

        return $this->render('dashboard/creer_atelier.html.twig', [
            'page_title' => 'Créer un Atelier',
            'user' => $user,
            'errors' => $errors,
        ]);
    }

    return $this->render('dashboard/creer_atelier.html.twig', [
        'page_title' => 'Créer un Atelier',
        'user' => $user,
    ]);
}
```

**Note:** Ajouter `use Symfony\Component\HttpFoundation\Request;` si pas déjà présent (il l'est déjà).

---

## ÉTAPE 3: Créer le template du formulaire

**Fichier à créer:** `templates/dashboard/creer_atelier.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ page_title }} - SkillHub{% endblock %}

{% block body %}
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>SkillHub</h2>
        <p class="user-welcome">Bonjour, {{ user.prenom }} !</p>
        <nav class="sidebar-nav">
            <a href="{{ path('app_dashboard') }}">
                📚 Catalogue d'ateliers
            </a>
            <a href="{{ path('app_dashboard_formateur') }}">
                🎓 Mes Ateliers
            </a>
            <a href="{{ path('app_dashboard_inscriptions') }}">
                ✅ Mes inscriptions
            </a>
            <a href="{{ path('app_deconnexion') }}">
                🔐 Déconnexion
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <a href="{{ path('app_dashboard_formateur') }}" class="btn-back">← Retour à mes ateliers</a>

        <h1>{{ page_title }}</h1>

        {% if errors is defined and errors|length > 0 %}
            <div class="error-message">
                {% for error in errors %}
                    <p>{{ error }}</p>
                {% endfor %}
            </div>
        {% endif %}

        <form method="POST" action="{{ path('app_creer_atelier') }}" class="form-atelier">
            <div class="form-group">
                <label for="titre">Titre de l'atelier :</label>
                <input type="text" id="titre" name="titre" required>
            </div>

            <div class="form-group">
                <label for="description">Description :</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label for="date">Date et heure :</label>
                <input type="datetime-local" id="date" name="date" required>
            </div>

            <div class="form-group">
                <label for="duree">Durée :</label>
                <input type="text" id="duree" name="duree" placeholder="Ex: 2h30" required>
            </div>

            <div class="form-group">
                <label for="capacite_max">Capacité maximale :</label>
                <input type="number" id="capacite_max" name="capacite_max" min="1" required>
            </div>

            <div class="form-group">
                <label for="image_url">URL de l'image (optionnel) :</label>
                <input type="url" id="image_url" name="image_url" placeholder="https://...">
            </div>

            <button type="submit" class="btn btn-primary">Créer l'atelier</button>
        </form>
    </main>
</div>
{% endblock %}
```

---

## ÉTAPE 4: Ajouter le bouton dans le dashboard formateur

**Fichier:** `templates/dashboard/formateur.html.twig`

**Ajouter un bouton "Créer un atelier" dans la section `mes-ateliers`, avant la liste:**
```twig
<a href="{{ path('app_creer_atelier') }}" class="btn btn-primary">+ Créer un atelier</a>
```

---

## ÉTAPE 5: Tester

1. Se connecter avec un compte formateur
2. Aller sur `/dashboard/formateur`
3. Cliquer sur "Créer un atelier"
4. Remplir le formulaire
5. Vérifier que l'atelier apparaît dans "Mes Ateliers"
6. Vérifier que l'atelier apparaît dans le catalogue général

---

## Fichiers à modifier/créer

| Action | Fichier |
|--------|---------|
| Modifier | `src/Service/DoctrineDataRepository.php` |
| Modifier | `src/Controller/DashboardController.php` |
| Créer | `templates/dashboard/creer_atelier.html.twig` |
| Modifier | `templates/dashboard/formateur.html.twig` |

---

## Checklist

- [x] ÉTAPE 1: Méthode `addAtelier()` dans repository
- [x] ÉTAPE 2: Route `creerAtelier()` dans controller
- [x] ÉTAPE 3: Template `creer_atelier.html.twig`
- [x] ÉTAPE 4: Bouton dans dashboard formateur
- [x] ÉTAPE 5: Tests
