# FM08 — Les huit travaux pratiques
_Extrait du support « Qualité logicielle et amélioration continue — SkillHub ». Numéros de slide indiqués._

---

## TP 1  —  Diagnostic qualité de SkillHub

*Slide 14 du support*
1 h 30   ·   en autonomie sur votre projet SkillHub  
Avant d'améliorer quoi que ce soit, il faut savoir où vous en êtes. On mesure, on ne devine pas.  

### Travail Demandé

1. Notez votre projet de 0 à 3 sur chacune des huit caractéristiques ISO/IEC 25010, avec une phrase de justification factuelle par note.  

2. Relevez ce qui est déjà automatisé sur votre dépôt : formatage, linter, tests, analyse de sécurité, construction d'image.  

3. Listez cinq éléments de dette technique que vous connaissez, avec leur raison d'origine et ce qu'ils vous coûtent aujourd'hui.  

4. Rédigez votre definition of done en six points maximum, tous vérifiables.  

5. Choisissez les trois caractéristiques que vous décidez de traiter en priorité, et dites pourquoi celles-là.  

### Livrables
Une fiche de diagnostic d'une page  
Le tableau de dette technique  
Votre definition of done  
Les trois priorités justifiées  
Critère d'évaluation :  
C33.1 — Conformité aux standards · alimente la section 1 de votre guide  

---

## TP 2  —  Suite de tests automatisés SkillHub

*Slide 40 du support*
3 h 30   ·   en autonomie sur votre projet SkillHub  
Vous travaillez sur votre propre application. Objectif : une suite qui tourne seule, en CI, et qui bloque une régression.  

### Travail Demandé

1. Écrivez d'abord, en français, la liste des cas à couvrir sur une fonctionnalité centrale — partitions d'équivalence puis valeurs aux bornes.  

2. Écrivez au moins huit tests : cinq nominaux, trois cas limites, et deux scénarios d'erreur vérifiant le type et le message.  

3. Isolez une dépendance externe avec une doublure et prouvez l'appel attendu.  

4. Ajoutez un test d'intégration sur la persistance, avec une base jetable en conteneur.  

5. Branchez la suite en intégration continue avec un seuil de couverture justifié, puis cassez volontairement le code pour prouver que le pipeline bloque.  

6. Rédigez la procédure d'exécution et renseignez une fiche de cas de test.  

### Livrables
La suite de tests versionnée  
Le fichier de pipeline  
Une capture de l'échec provoqué  
Le rapport de couverture  
La procédure d'exécution  
Une fiche de cas de test renseignée  
Critère d'évaluation :  
C34.1 Couverture · C34.2 Meilleures pratiques · C34.3 Documentation des tests  

---

## TP 3  —  Revue d'une pull request à défauts volontaires

*Slide 52 du support*
2 h   ·   en autonomie sur votre projet SkillHub  
Une pull request vous est fournie. Elle ajoute une fonctionnalité à SkillHub et contient cinq familles de défauts. À vous de les trouver.  

### Travail Demandé

1. Lisez d'abord la description de la pull request et le diff en entier, sans commenter.  

2. Appliquez votre checklist point par point et relevez chaque défaut : famille, fichier, ligne.  

3. Rédigez un commentaire pour chacun, préfixé bloquant / suggestion / question, avec la raison.  

4. Pour le test manquant, écrivez le test qui échoue sans le correctif.  

5. Rendez un verdict : ce qui bloque la fusion, ce qui relève de la suggestion, et ce que vous exigez avant de valider.  

### Livrables
La liste des défauts trouvés  
Vos commentaires de revue rédigés  
Le test manquant, écrit  
Votre verdict argumenté  
Critère d'évaluation :  
C33.1 Conformité aux standards · C33.2 Maintenabilité — alimente la checklist de votre guide  

---

## TP 4  —  Revue croisée sur votre propre code

*Slide 53 du support*
1 h 30   ·   en autonomie sur votre projet SkillHub  
Chacun ouvre une pull request sur son SkillHub, un pair la relit. Vous êtes des deux côtés.  

### Travail Demandé

1. Ouvrez une pull request réelle sur votre dépôt, en remplissant le gabarit de description.  

2. Relisez la pull request d'un camarade avec votre checklist et postez vos commentaires sur la forge.  

3. Traitez les commentaires reçus : corrigez, ou répondez en argumentant votre choix.  

4. Faites fusionner, en conservant la trace de la discussion.  

5. Notez ce que la revue a changé dans votre code — c'est la preuve de maintenabilité attendue.  

### Livrables
Le lien de la pull request  
Vos commentaires postés  
Deux exemples avant / après  
La trace de la fusion  
Critère d'évaluation :  
C33.2 Maintenabilité — les deux exemples « avant / après » sont exigés dans le guide  

---

## TP 5  —  Portail documentaire SkillHub

*Slide 63 du support*
3 h   ·   en autonomie sur votre projet SkillHub  
Vous construisez la documentation que reprendra l'équipe qui vous succède.  

### Travail Demandé

1. Réécrivez votre README selon le modèle : démarrer en cinq minutes, prérequis, variables, tests, lien vers le portail.  

2. Montez un portail mkdocs avec au minimum les pages installation, configuration, architecture, utilisation, dépannage.  

3. Produisez le schéma d'architecture et le schéma du flux de contribution, de la branche à la mise en production.  

4. Rédigez quatre fiches de dépannage sur des incidents que vous avez réellement rencontrés.  

5. Écrivez un ADR sur une décision technique que vous avez prise sur le projet.  

6. Publiez le portail automatiquement à la fusion, et définissez qui met à jour quoi.  

### Livrables
Le README réécrit  
Le portail publié et son adresse  
Les deux schémas  
Quatre fiches de dépannage  
Un ADR  
Les règles de mise à jour  
Critère d'évaluation :  
C35.1 Exhaustivité · C35.2 Clarté · C35.3 Plateformes collaboratives · C35.4 Maintenance  

---

## TP 6  —  Rétrospective réelle sur votre projet

*Slide 73 du support*
1 h 30   ·   en autonomie sur votre projet SkillHub  
Vous animez une vraie rétrospective, en groupe, sur SkillHub. Pas une simulation.  

### Travail Demandé

1. Constituez des groupes de trois à cinq et désignez un animateur, qui n'aura pas de voix prépondérante.  

2. Choisissez un format et annoncez la règle du jeu et la durée de chaque temps.  

3. Déroulez les cinq temps en respectant l'écriture silencieuse et le vote.  

4. Sur les deux sujets les plus votés, cherchez le pourquoi avec les cinq pourquoi.  

5. Sortez avec un à trois engagements : action, responsable, échéance, critère de réussite.  

6. Notez ce que cette rétrospective change concrètement dans votre façon de coder.  

### Livrables
Le compte rendu de séance  
Le tableau des actions engagées  
Le paragraphe « ce que ça change dans ma façon de coder »  
Critère d'évaluation :  
Alimente la section amélioration continue du guide — et la demande explicite de l'épreuve  

---

## TP 7  —  Analyse de cause racine sur un bug réel

*Slide 79 du support*
1 h 30   ·   en autonomie sur votre projet SkillHub  
Prenez un vrai bug de votre projet, ou un incident du TP sécurité. Pas un exemple fabriqué.  

### Travail Demandé

1. Décrivez le symptôme tel qu'il a été observé, avec la preuve : journal, capture, message d'erreur.  

2. Reconstituez la chronologie : détection, diagnostic, actions, rétablissement.  

3. Déroulez les cinq pourquoi par écrit, jusqu'à une cause sur laquelle vous pouvez agir.  

4. Si plusieurs facteurs se combinent, complétez avec les six familles du diagramme en arêtes.  

5. Écrivez le test de non-régression : prouvez qu'il échoue avant le correctif et qu'il passe après.  

6. Distinguez l'action immédiate de l'action structurelle, avec responsable et échéance.  

### Livrables
La fiche d'analyse de cause racine  
Le déroulé des cinq pourquoi  
Le test de non-régression, avec les deux exécutions  
Les actions immédiate et structurelle  
Critère d'évaluation :  
Alimente la section amélioration continue — et la demande explicite de l'épreuve  

---

## TP 8  —  Assemblage et publication du guide

*Slide 82 du support*
2 h 30   ·   en autonomie sur votre projet SkillHub  
Vous rassemblez tout ce que vous avez produit en trois jours en un guide unique, publié et exportable.  

### Travail Demandé

1. Assemblez les huit sections dans le portail : périmètre, standards, revue de code, stratégie de tests, exécution des tests, documentation technique, maintenance et partage, amélioration continue.  

2. Vérifiez que chaque règle est actionnable et vérifiable, et supprimez celles qui ne le sont pas.  

3. Intégrez les annexes : checklist de revue, gabarit de pull request, fiche de cas de test, fiche de dépannage.  

4. Vérifiez que tous les exemples de code viennent de votre propre base de code.  

5. Publiez le portail et exportez le guide complet en PDF, paginé et avec sommaire.  

6. Relisez avec les yeux d'un nouvel arrivant : est-ce qu'il pourrait travailler avec ça dès demain ?  

### Livrables
Le guide en PDF, paginé  
Les sources Markdown  
Le portail publié  
Les quatre annexes  
Les schémas  
Critère d'évaluation :  
C33 · C34 · C35 — c'est le livrable de l'épreuve certifiante EC10  
