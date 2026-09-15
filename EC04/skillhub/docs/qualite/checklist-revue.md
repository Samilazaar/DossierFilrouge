# Checklist de revue de code — SkillHub

**Module** : Qualité logicielle et amélioration continue — CDWFS, bloc BC04, FM08
**Critères servis** : C33.1 conformité aux standards · C33.2 maintenabilité
**Usage** : à dérouler sur toute pull request, dans l'ordre. Issue du TP 3.

---

## Méthode

1. Lire la **description** de la pull request, puis le **diff en entier**, sans commenter.
2. Dérouler la checklist **point par point**, dans l'ordre ci-dessous.
3. Rédiger un commentaire par défaut relevé, préfixé et motivé.
4. Rendre un **verdict** : ce qui bloque, ce qui est suggéré, ce qui est exigé avant validation.

L'ordre n'est pas arbitraire : il va du plus coûteux à corriger au moins coûteux.
Inutile de discuter d'un nom de variable si la conception doit être reprise.

---

## Les huit points

| # | Point | Ce qu'on se demande |
|:--:|---|---|
| 1 | **Le besoin** | Ça répond bien à la demande ? Le périmètre est-il tenu, sans ajout non demandé ? |
| 2 | **La conception** | Le découpage tiendra-t-il la prochaine évolution ? Y a-t-il un couplage inutile ? |
| 3 | **Les cas non traités** | Entrée vide, valeur nulle, borne, accès concurrent, dépendance indisponible |
| 4 | **Les erreurs** | Chaque échec est-il levé, journalisé, et laisse-t-il un état cohérent ? |
| 5 | **Les tests** | Existent-ils ? Échouent-ils sans le correctif ? Couvrent-ils un cas d'erreur ? |
| 6 | **La sécurité** | Entrées validées, requêtes paramétrées, aucun secret, aucune donnée personnelle en journal |
| 7 | **Le nommage** | Les noms disent-ils la vérité ? Un nouvel arrivant suivrait-il ? |
| 8 | **La documentation** | README, configuration, dépannage, changelog |

---

## Les cinq familles de défauts

À chercher systématiquement. La colonne de droite fixe la règle de blocage.

| Défaut | Comment le repérer | Bloquant ? |
|---|---|---|
| **Test manquant** | Un comportement nouveau ou modifié, et aucun test qui échoue sans le correctif | Oui |
| **Documentation absente** | Une commande, une variable ou une dépendance change — le README ne bouge pas | Oui si l'installation change |
| **Nommage trompeur** | Le nom promet autre chose que ce que fait le code | Oui si le nom ment |
| **Gestion d'erreur** | `catch` vide, erreur avalée, message perdu, état laissé à moitié écrit | Oui |
| **Dépendance non déclarée** | Un import d'une bibliothèque absente du fichier de dépendances | Oui |

**Deux familles ajoutées au TP 3**, à conserver :

| Défaut | Comment le repérer | Bloquant ? |
|---|---|---|
| **Sécurité / RGPD** | Route non protégée exposant des données personnelles, stockage sans durée de conservation | Oui |
| **Cas non traité** | Un `null`, une liste vide ou une borne qui part directement dans la suite du code | Oui si l'utilisateur voit une 500 |

---

## Rédiger un commentaire

Chaque commentaire porte un **préfixe** :

- **bloquant** — la fusion est refusée tant que ce n'est pas traité
- **suggestion** — amélioration réelle, laissée à l'appréciation de l'auteur
- **question** — je n'ai pas compris, ou le choix mérite d'être explicité
- **bravo** — ce qui est bien fait. À ne pas sauter : une revue qui ne relève que des défauts n'est pas lue de la même façon

Trois règles de formulation :

1. **Dire la conséquence, pas le jugement.** « Ce `catch` vide fera qu'une écriture ratée passera inaperçue » plutôt que « mauvais code ».
2. **Proposer une issue.** Un commentaire bloquant sans piste de correction fait perdre du temps aux deux.
3. **Viser le code, jamais la personne.** « cette méthode » et non « tu as ».

---

## Verdict

Trois blocs, toujours les mêmes :

- **Décision** — fusion acceptée, acceptée sous réserve, ou refusée en l'état.
- **Exigé avant validation** — la liste numérotée des points bloquants.
- **Laissé à l'appréciation de l'auteur** — suggestions et questions ouvertes.

Ajouter, quand c'est pertinent, une **remarque de méthode** : ce que le pipeline
aurait dû détecter et n'a pas vu. C'est ce qui transforme une revue en action
d'amélioration continue, et ça alimente la section correspondante du guide.

---

## Ce que cette checklist alimente dans le guide

Section *Processus de revue de code* — critères C33.1 et C33.2.
