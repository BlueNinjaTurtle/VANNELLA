# Utilisation avec Visual Paradigm

## Methode recommandee

Visual Paradigm permet de recreer rapidement les diagrammes a partir des fichiers PlantUML comme reference.
La methode la plus stable est la suivante :

1. Ouvrir Visual Paradigm.
2. Creer un nouveau projet.
3. Creer les diagrammes UML correspondants :
   - Use Case Diagram
   - Class Diagram
   - Activity Diagram
   - Component Diagram
   - Deployment Diagram
   - Sequence Diagram
4. Ouvrir les fichiers `.puml` de ce dossier.
5. Reproduire les elements dans Visual Paradigm en gardant les noms, relations et messages.

## Import XMI

Le fichier `modele_visual_paradigm.xmi` contient un modele UML generique avec les classes, acteurs, cas d'utilisation, composants et noeuds principaux.

Dans Visual Paradigm :

1. Aller dans `Project`.
2. Choisir `Import`.
3. Choisir `XMI...`.
4. Selectionner `uml/modele_visual_paradigm.xmi`.

Selon la version de Visual Paradigm, l'import XMI peut importer les elements du modele sans conserver une mise en page graphique parfaite.
Dans ce cas, il suffit de creer les diagrammes et de glisser les elements importes sur chaque diagramme.

## Remarque

Les fichiers `.puml` restent la version la plus lisible et la plus facile a verifier.
Le `.xmi` sert surtout de support d'import pour eviter de ressaisir tous les noms d'elements.
