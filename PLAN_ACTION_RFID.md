# Plan d'action RFID - Adaptation du projet VANNELLA

Ce fichier sert de memoire de chantier. On avance etape par etape : une etape ne passe a `Termine` qu'apres implementation et verification.

## Statuts

- `A faire` : pas encore commence.
- `En cours` : implementation en cours.
- `Verifie` : tests faits avec succes.
- `Termine` : etape validee et prete pour la suite.

## Etapes

### 1. Memoire du chantier

- Statut : Termine
- Objectif : creer ce fichier de suivi.
- Verification attendue :
  - le fichier `PLAN_ACTION_RFID.md` existe ;
  - les etapes sont listees dans l'ordre logique ;
  - chaque etape a des criteres de verification.

### 2. Audit de l'existant

- Statut : Termine
- Objectif : verifier le schema reel de la base, les fichiers PHP/JS lies aux cours, horaires, salles et IoT.
- Verification attendue :
  - liste des tables et colonnes importantes confirmee ;
  - fichiers impactes identifies ;
  - aucun fichier modifie pendant l'audit.

### 3. Preparation base de donnees

- Statut : Termine
- Objectif : ajouter la structure necessaire a la logique RFID.
- Changements prevus :
  - creer `professeurs` ;
  - lier `cours` a `professeurs` ;
  - ajouter `date_cours` aux `horaires` ;
  - etendre `horaires.statut` avec `actif`, `en_cours`, `termine`, `annule`.
- Verification attendue :
  - migration SQL executee sans erreur ;
  - anciens cours conserves ;
  - contraintes d'unicite UID respectees.

### 4. Gestion des cours et professeurs

- Statut : Termine
- Objectif : ajouter l'UID RFID a la creation/modification des cours via le professeur associe.
- Verification attendue :
  - creation d'un cours avec professeur + UID ;
  - refus d'un UID deja attribue a un autre professeur ;
  - mise a jour d'un cours existant sans casser l'affichage.

### 5. Planning date et statuts horaires

- Statut : Termine
- Objectif : enregistrer les horaires avec une date precise et afficher les nouveaux statuts.
- Verification attendue :
  - ajout d'un horaire avec `date_cours` ;
  - affichage des mentions `En cours`, `Termine`, `Annule` ;
  - regles de conflit existantes conservees.

### 6. API RFID Cisco Packet Tracer

- Statut : Termine
- Objectif : recevoir `{ "salle": "...", "uid_badge": "..." }` et appliquer la logique metier.
- Verification attendue :
  - badge inconnu => `UNKNOWN_CARD` ;
  - salle inconnue => `UNKNOWN_ROOM` ;
  - mauvaise salle => `WRONG_ROOM` ;
  - bon badge + bonne salle + bon horaire => `AUTHORIZED` ;
  - double badgeage => succes sans doublon.

### 7. Script automatique retards et fins de cours

- Statut : Termine
- Objectif : annuler les retards de plus de 15 minutes et terminer les cours en fin de creneau.
- Verification attendue :
  - `actif` depasse `heure_debut + 15 min` => `annule` + salle libre ;
  - `en_cours` depasse `heure_fin` => `termine` + salle libre ;
  - script executable manuellement et planifiable.

### 8. Adaptation des vues et verification globale

- Statut : Termine
- Objectif : aligner les cartes salles, planning, administration et simulation avec la nouvelle logique.
- Verification attendue :
  - cartes salles coherentes avec les nouveaux statuts ;
  - planning lisible ;
  - simulation IoT encore fonctionnelle ;
  - tests PHP/JS globaux OK.

## Journal

- 2026-06-17 : creation du fichier memoire et lancement du chantier RFID.
- 2026-06-17 : etape 1 verifiee, fichier memoire cree correctement.
- 2026-06-17 : audit fichiers effectue. Points confirmes : `cours` utilise encore `enseignant` texte, `horaires` n'a pas encore `date_cours`, les statuts horaires sont encore limites a `actif`/`annule`, le formulaire planning est encore hebdomadaire. Audit DB reel bloque car MySQL ne repond pas sur localhost.
- 2026-06-17 : audit DB reel termine apres demarrage MySQL. Tables confirmees : 22 cours, 6 horaires, 4 salles. `cours(id_cours, nom_cours, enseignant, id_departement)`, `horaires(..., jour, heure_debut, heure_fin, statut enum actif/annule)`.
- 2026-06-17 : etape 3 verifiee. Migration appliquee avec succes : table `professeurs`, liaison `cours.id_professeur`, `horaires.date_cours`, statuts `actif/en_cours/termine/annule`. Verification DB : 16 professeurs, 0 cours sans professeur, 0 horaire sans date. `database.sql` aligne avec les insertions de professeurs et cours de test.
- 2026-06-17 : etape 4 terminee et verifiee. Ajout du champ UID RFID dans l'administration des cours, liaison automatique cours/professeur via `id_professeur`, controle d'unicite UID, API de lecture enrichies avec `uid_badge`. Tests OK : lint PHP, lecture API, creation/mise a jour rollbackees, conflit UID bloque.
- 2026-06-17 : etape 5 terminee et verifiee. Planning date avec `date_cours`, optimisation et conflits limites a la date concernee, affichage admin des statuts `Actif/En cours/Termine/Annule`, vues salles et simulation ajustees. Tests OK : lints PHP, checks JS, appels API dates, insertion/statuts rollbackes.
- 2026-06-17 : etape 6 terminee et verifiee. `api/iot_rfid_update.php` remplace l'ancien endpoint casse et traite `{salle, uid_badge}` : badge inconnu, salle inconnue, mauvaise salle, autorisation, double badgeage. Validation OK avec donnees temporaires nettoyees : `WRONG_ROOM`, `AUTHORIZED`, `ALREADY_VALIDATED`, `UNKNOWN_ROOM`; statut horaire passe a `en_cours`, salle passe a `occupee`.
- 2026-06-18 : etape 7 terminee et verifiee. Ajout de `check_retards.php` et `run_check_retards.bat`. Le script annule les cours `actif` depasses de plus de 15 minutes, termine les cours `en_cours` apres `heure_fin`, et libere les salles si aucun cours courant ne les occupe. Verification OK : lint PHP, dry-run sans modification, test rollbacke `actif=>annule` et `en_cours=>termine`.
- 2026-06-18 : etape 8 terminee et verifiee. Verification globale OK : lint PHP sur le projet, checks JS dans `js/`, appels API principaux (`getCours`, `getHorairesByWeek`, `getSalles`, `getSallesWithDetails`), aucune donnee temporaire restante, 0 cours sans professeur, 0 horaire sans date.
- 2026-06-18 : maintenance hebdomadaire automatisee. `check_retards.php` supprime aussi les horaires des semaines passees (`date_cours` avant le lundi de la semaine courante), puis libere les salles sans horaire actif courant/futur. Tache planifiee Windows creee : `VANNELLA Maintenance Horaires`, execution toutes les 5 minutes via `run_check_retards.bat`.
