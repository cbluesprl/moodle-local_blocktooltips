# Infobulles des blocs — Guide utilisateur (Français)

Ce guide s'adresse aux **enseignants/éditeurs de cours** et aux **administrateurs**.

## À quoi sert ce plugin

Le plugin Infobulles des blocs ajoute deux aides lorsque vous travaillez avec les
blocs en mode édition :

1. **Infobulles** — une petite icône d'information à côté de chaque bloc dans la
   fenêtre *Ajouter un bloc*, qui affiche une courte description du rôle du bloc.
2. **Visibilité étudiant** — une icône en forme d'œil sur chaque bloc d'un cours,
   indiquant si les étudiants voient ce bloc ou non.

Ces deux aides ne sont visibles que par les utilisateurs autorisés à modifier les
blocs, et uniquement lorsque le **mode édition** est activé. Les étudiants ne voient
jamais rien de ce qu'ajoute ce plugin.

---

## Pour les administrateurs : configurer les infobulles

1. Allez dans **Administration du site → Plugins → Plugins locaux → Infobulles des blocs**.
2. Une zone de texte apparaît pour chaque bloc installé, triée par ordre alphabétique.
3. Saisissez une courte description en texte simple pour chaque bloc à documenter.
   - Restez concis — le texte est affiché en infobulle au survol.
   - Laissez un champ vide pour n'afficher aucune infobulle sur ce bloc.
4. Cliquez sur **Enregistrer**.

![Page de configuration](screenshots/01-settings.png)

---

## Pour les éditeurs : consulter les infobulles

1. Sur n'importe quelle page, activez le **mode édition** (interrupteur en haut à droite).
2. Ouvrez la fenêtre **Ajouter un bloc**.
3. Les blocs disposant d'une description affichent une **icône d'information ℹ️**.
   Survolez-la avec la souris (ou placez le focus clavier dessus) pour lire la
   description avant d'ajouter le bloc.

![Infobulle dans la fenêtre Ajouter un bloc](screenshots/02-tooltip.png)

---

## Pour les éditeurs : indicateur de visibilité étudiant

1. Ouvrez un **cours** et activez le **mode édition**.
2. Chaque bloc affiche une icône en forme d'œil dans sa zone d'en-tête/contrôles :
   - **👁️ Œil vert** — le bloc est *visible* pour les étudiants.
   - **🚫 Œil barré rouge** — le bloc est *masqué* pour les étudiants.
3. Survolez l'icône pour afficher le libellé (« Affiché pour les étudiants » /
   « Masqué pour les étudiants »).

Cet indicateur est en lecture seule : il ne modifie rien, il vous informe seulement
de ce qu'un étudiant verrait. Il n'est disponible que dans les contextes de cours.

![Icône œil de visibilité étudiant](screenshots/03-visibility.png)

---

## Questions fréquentes

**Pourquoi est-ce que je ne vois aucune icône ?**
Vérifiez que le mode édition est activé et que vous avez le droit de modifier les
blocs (`moodle/block:edit`).

**Pourquoi un bloc précis n'a-t-il pas d'infobulle ?**
Aucune description n'a été configurée pour ce bloc. Un administrateur peut en
ajouter une dans les réglages du plugin.

**Ce plugin collecte-t-il des données personnelles ?**
Non. Il ne stocke aucune donnée personnelle.
