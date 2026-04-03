# Suivi du Projet : VANNELLA_PROJET

## Objectif
Système de gestion et de suivi des salles en temps réel (ISPT/Likasi).
Architecture : IoT (ESP32) + API PHP/MySQL + Interface Web (HTML/CSS/JS).

## État d'avancement
- [x] Initialisation du fichier de mémoire.
- [x] Lecture du README et compréhension du prompt.
- [x] Création du script de base de données (`database.sql`).
- [x] Configuration de la connexion PDO (`config/db.php`).
- [x] Développement de l'API Backend (PHP).
    - [x] Endpoint mise à jour état salle (`updateSalle.php`).
    - [x] Endpoint récupération liste des salles (`getSalles.php`).
    - [x] **Moteur d'attribution optimale** (`optimize.php`).
    - [x] Gestion du planning et des horaires (`addHoraire.php`).
- [x] Développement de l'Interface Frontend (Dashboard & Gestion).
    - [x] Dashboard Public Live (Bootstrap/jQuery).
    - [x] **Interface Administration moderne** (`admin.php`) avec Sidebar.
    - [x] Formulaire de planification intelligente.
    - [x] **Mise à jour visuelle** : Ajout de l'image de fond (`bg.jpg`) sur la page d'accueil (`index.php`).
    - [x] **Sécurité** : Mise à jour des identifiants de l'administrateur par défaut (Nom : `ADMIN`, MDP : `ISPT2026`).
- [ ] Simulation IoT (Code ESP32).

## Notes de contexte
- **Base de données** : `gestion_salles` avec les tables `salles`, `promotions`, `cours`, `horaires`, `etat_salles`.
- **Technos** : PHP (Backend), MySQL (BD), ESP32 (IoT), Vanilla JS/AJAX (Frontend).
- **Communication** : Requêtes HTTP POST de l'ESP32 vers l'API PHP.
