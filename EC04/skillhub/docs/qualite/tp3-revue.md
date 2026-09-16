# TP 3 — Revue de la pull request #47 « Export CSV des inscrits »

**Module** : Qualité logicielle et amélioration continue — CDWFS, bloc BC04, FM08
**Critères** : C33.1 conformité aux standards · C33.2 maintenabilité
**Relecteur** : Sami Lazaar — **Date** : 14 septembre 2026
**Objet relu** : `ExportController.php` (74 lignes) et sa description, fournis par l'enonce du TP 3.
**Note** : le code d'exercice n'est pas versionne dans ce depot — il est volontairement defectueux et n'a rien a faire dans une application.

Méthode : lecture complète de la description puis du diff sans commenter, puis
application de la checklist de revue point par point, du plus coûteux au moins
coûteux à corriger.

---

## 1. Défauts relevés

| # | Famille | Emplacement | Constat |
|:--:|---|---|---|
| 1 | Test manquant | fichier entier | Deux routes publiques ajoutées, aucun test. La case « Tests ajoutés » est décochée dans la description. |
| 2 | Documentation absente | ligne 50 | `$_ENV['EXPORT_DIR']` introduit une variable d'environnement obligatoire. Absente du `README.md`, absente de `.env`. Vérifié : aucune occurrence dans le dépôt. |
| 3 | Nommage trompeur | lignes 30, 63 | `getActiveUsers()` ne renvoie pas des utilisateurs mais des `Inscription`, et ne filtre aucune notion d'« actif ». La variable `$data` ne dit rien de son contenu. |
| 4 | Gestion d'erreur | lignes 49-53 | `catch (\Exception $e) { }` : bloc vide, erreur avalée, rien de journalisé — alors que `LoggerInterface` est injecté et jamais utilisé. Aggravant : `file_put_contents` n'émet pas d'exception mais un warning, donc ce `catch` n'attrapera jamais rien. L'écriture peut échouer en silence complet. |
| 5 | Dépendance non déclarée | ligne 9 | `use League\Csv\Writer;` — vérifié : `league/csv` n'apparaît pas dans `composer.json`. Fonctionne sur le poste de l'auteur, casse en production avec une erreur fatale de classe introuvable. |

### Deux défauts hors des cinq familles, relevés en plus

| # | Famille | Emplacement | Constat |
|:--:|---|---|---|
| 6 | Sécurité / RGPD | ligne 27 | Aucun contrôle d'accès sur une route qui expose nom, prénom, **email et téléphone** des inscrits. La case « aucune donnée personnelle » est cochée dans la description : elle est fausse. Le fichier est en plus écrit sur le disque du serveur, sans durée de conservation. |
| 7 | Cas non traité | ligne 28 | `findAtelierById($id)` peut renvoyer `null` pour un identifiant inexistant. Aucune vérification : la suite plante en erreur 500 au lieu de renvoyer 404. |

---

## 2. Commentaires de revue rédigés

> **bloquant** — ligne 9 : `league/csv` est importé mais absent de `composer.json`. Le job `quality` passe parce que le lint PHP ne résout pas les classes, mais l'image de production n'embarquera pas la bibliothèque : erreur fatale au premier appel. Peux-tu ajouter `composer require league/csv` et commiter le `composer.lock` ?

> **bloquant** — lignes 49-53 : ce `catch` est vide. Si l'écriture du fichier échoue, personne ne le saura jamais — ni l'utilisateur, qui reçoit quand même un CSV, ni nous, puisque rien n'est journalisé. Le `LoggerInterface` est d'ailleurs injecté et jamais utilisé. Et `file_put_contents` émet un warning plutôt qu'une exception, donc ce bloc n'attrapera rien du tout : il faut tester la valeur de retour. Proposition : vérifier le retour, journaliser en `error` avec l'identifiant de l'atelier, et laisser l'export répondre malgré tout puisque le fichier disque n'est qu'un cache.

> **bloquant** — ligne 27 : cette route renvoie les emails et les téléphones de tous les inscrits, sans aucune authentification. N'importe qui connaissant un identifiant d'atelier peut aspirer les coordonnées. La case « aucune donnée personnelle » de la description est à décocher. Il faut au minimum restreindre l'accès au formateur propriétaire de l'atelier. Question annexe : combien de temps garde-t-on le fichier écrit dans `EXPORT_DIR` ? Sans réponse, on crée un stock de données personnelles sans durée de conservation.

> **bloquant** — ligne 28 : `findAtelierById` renvoie `null` si l'atelier n'existe pas. Ici le `null` part directement dans `getActiveUsers()` puis dans le `foreach` : l'appelant reçoit une 500 au lieu d'un 404. Un `if (!$atelier) { throw $this->createNotFoundException(...); }` suffit, comme dans `DashboardController::inscrire`.

> **bloquant** — le changement n'a aucun test. Les deux routes sont publiques et l'une d'elles écrit sur le disque. J'ai écrit le test qui manque le plus (`tp3-test-manquant.php`) : il échoue aujourd'hui sur l'atelier inexistant, et passera une fois le point précédent corrigé.

> **bloquant** — ligne 50 : `EXPORT_DIR` est une nouvelle variable obligatoire. Elle n'est ni dans le `README.md`, ni dans `.env`. Un nouvel arrivant qui suit la documentation obtiendra un chemin `/export-atelier-1.csv` à la racine du disque. Peux-tu l'ajouter aux deux endroits, avec son rôle et une valeur par défaut ?

> **suggestion** — ligne 63 : `getActiveUsers()` renvoie des `Inscription`, pas des `User`, et rien n'y est filtré sur « actif ». Le nom promet autre chose que ce que fait le code, et c'est exactement ce qui fera se tromper le prochain lecteur. `trouverInscriptionsDeLAtelier()` serait exact. Même remarque pour `$data` ligne 30, qui pourrait être `$inscriptions`.

> **question** — lignes 34 à 47 : le CSV est construit deux fois en mémoire (`$csv->toString()` est appelé à l'écriture puis à la réponse). Sur un atelier de 12 personnes c'est indolore ; y a-t-il un cas prévu avec beaucoup plus d'inscrits, ou est-ce qu'on laisse comme ça ?

> **bravo** — la séparation entre la route d'export et la route de comptage est une bonne idée : elle évite de générer tout le fichier quand l'interface veut seulement afficher un nombre.

---

## 3. Le test manquant, écrit

Fichier : `tp3-test-manquant.php`, conserve hors du depot avec le reste du support d'exercice.

Il couvre le défaut n° 7, le moins coûteux à corriger et le plus visible pour
l'utilisateur. Il échoue tant que la vérification du `null` n'est pas ajoutée :
aujourd'hui la route répond 500, le test attend 404.

---

## 4. Verdict

**Fusion refusée en l'état.** Six points bloquants, dont deux qui empêcheraient
le fonctionnement en production et un qui expose des données personnelles.

### Exigé avant validation

1. Déclarer `league/csv` dans `composer.json` et commiter le `composer.lock`.
2. Restreindre l'accès à la route d'export, et décocher la case « aucune donnée personnelle ».
3. Traiter l'atelier inexistant par un 404, avec le test fourni.
4. Remplacer le `catch` vide par une vérification du retour de `file_put_contents` et une journalisation en `error`.
5. Ajouter `EXPORT_DIR` au `README.md` et à `.env`, avec rôle et valeur par défaut.
6. Ajouter les tests des deux routes, dont un cas d'erreur.

### Laissé à l'appréciation de l'auteur

- Le renommage de `getActiveUsers()` et de `$data` (suggestion, mais fortement conseillé).
- La double génération du CSV en mémoire (question ouverte).

### Remarque de méthode

Aucun des six points bloquants n'aurait pu être détecté par le pipeline actuel :
le lint ne résout pas les classes, PHPStan n'est pas configuré au niveau qui
signale une dépendance absente, et il n'existe aucune règle sur les blocs
`catch` vides. Deux actions à porter au guide :

- ajouter `composer validate --strict` et un contrôle des dépendances non déclarées en CI ;
- monter le niveau PHPStan pour qu'il signale les `catch` vides et les retours `null` non traités.

C'est la démonstration de la règle du chapitre 1 : chaque barrière ne doit
contrôler que ce que la précédente ne pouvait pas voir — mais encore faut-il que
la barrière précédente sache voir quelque chose.

---

## Ce que ce TP alimente dans le guide

Section *Checklist de revue* et section *Processus de revue*.
