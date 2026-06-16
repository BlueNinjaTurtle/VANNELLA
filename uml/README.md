# Dossier UML - Systeme de gestion intelligente des salles

Ce dossier contient les diagrammes UML utiles pour documenter le projet VANNELLA_PROJET.

## Fichiers fournis

- `01_cas_utilisation.puml` : diagramme de cas d'utilisation.
- `02_classes.puml` : diagramme de classes du domaine et des services principaux.
- `03_activite_attribution.puml` : activite d'attribution optimale d'une salle.
- `04_activite_iot.puml` : activite de mise a jour d'etat par IoT ou simulation.
- `05_composants.puml` : diagramme de composants.
- `06_deploiement.puml` : diagramme de deploiement.
- `07_sequence_iot_vers_systeme.puml` : sequence IoT vers mise a jour de l'etat des salles.
- `08_sequence_attribution_optimale.puml` : sequence d'attribution optimale et enregistrement horaire.
- `09_sequence_liberation_salle.puml` : sequence de liberation d'une salle avec annulation d'horaires.
- `10_sequence_consultation_temps_reel.puml` : sequence de consultation temps reel des salles et horaires.
- `explications.md` : explications pretes a adapter dans le rapport.
- `visual_paradigm_import.md` : conseils pour refaire/importer les diagrammes dans Visual Paradigm.
- `modele_visual_paradigm.xmi` : modele XMI generique importable selon la compatibilite XMI de Visual Paradigm.

## Conseil pratique

Les fichiers PlantUML servent de reference claire pour reconstruire les diagrammes dans Visual Paradigm.
Le fichier XMI peut aider a importer les elements, mais la mise en page graphique est souvent a ajuster dans Visual Paradigm apres import.

## Generer les images localement

PlantUML est installe en mode portable dans `tools/plantuml`, avec un Java portable dans `tools/java`.

Depuis la racine du projet, lancer :

```bat
tools\plantuml\plantuml.bat -tpng uml\*.puml
```

Pour verifier les fichiers sans generer d'image :

```bat
tools\plantuml\plantuml.bat -checkonly uml\*.puml
```
