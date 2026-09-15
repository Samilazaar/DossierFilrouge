# PR #47 — Export CSV des inscrits à un atelier

**Auteur** : un développeur de l'équipe SkillHub
**Branche** : `feat/export-inscrits` → `main`
**Fichiers modifiés** : 1 ajouté (`src/Controller/api/ExportController.php`, 74 lignes)

## Ce que fait cette pull request

Ajoute deux routes à l'API : `GET /api/workshops/{id}/export` renvoie la liste des
inscrits à un atelier au format CSV, et `GET /api/workshops/{id}/export/count`
renvoie leur nombre. Le fichier est aussi sauvegardé sur le disque du serveur
pour permettre au formateur de le retélécharger.

## Pourquoi

Demande des formateurs : ils veulent pouvoir faire l'appel sur papier le jour de
l'atelier. Ticket SKH-212.

## Comment vérifier

    curl http://localhost:8000/api/workshops/1/export

## Points d'attention pour le relecteur

Rien de particulier, c'est un ajout isolé qui ne touche à aucun code existant.

## Vérifications

- [ ] Tests ajoutés ou mis à jour, dont un cas d'erreur
- [ ] Documentation touchée mise à jour (README, configuration, dépannage)
- [x] Aucun secret, aucune donnée personnelle dans le code ni les journaux
- [x] Les contrôles automatiques passent
- [ ] Le changement est réversible (procédure de retour arrière connue)
