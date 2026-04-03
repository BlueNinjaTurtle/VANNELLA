-- Script SQL mis à jour pour la base de données VANNELLA_PROJET

CREATE DATABASE IF NOT EXISTS gestion_salles;
USE gestion_salles;

-- 1️⃣ Table departements
CREATE TABLE IF NOT EXISTS departements (
    id_departement INT AUTO_INCREMENT PRIMARY KEY,
    nom_departement VARCHAR(100) NOT NULL
);

-- 2️⃣ Table salles
CREATE TABLE IF NOT EXISTS salles (
    id_salle INT AUTO_INCREMENT PRIMARY KEY,
    nom_salle VARCHAR(50) NOT NULL,
    capacite INT NOT NULL,
    batiment VARCHAR(50) NOT NULL
);

-- 3️⃣ Table promotions
CREATE TABLE IF NOT EXISTS promotions (
    id_promotion INT AUTO_INCREMENT PRIMARY KEY,
    nom_promotion VARCHAR(100) NOT NULL,
    filiere VARCHAR(100) NOT NULL,
    niveau VARCHAR(50) NOT NULL,
    effectif INT NOT NULL DEFAULT 0,
    id_departement INT,
    FOREIGN KEY (id_departement) REFERENCES departements(id_departement)
);

-- 4️⃣ Table cours
CREATE TABLE IF NOT EXISTS cours (
    id_cours INT AUTO_INCREMENT PRIMARY KEY,
    nom_cours VARCHAR(100) NOT NULL,
    enseignant VARCHAR(100) NOT NULL
);

-- 5️⃣ Table horaires
CREATE TABLE IF NOT EXISTS horaires (
    id_horaire INT AUTO_INCREMENT PRIMARY KEY,
    id_cours INT,
    id_promotion INT,
    jour VARCHAR(20) NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    id_salle INT,
    FOREIGN KEY (id_cours) REFERENCES cours(id_cours),
    FOREIGN KEY (id_promotion) REFERENCES promotions(id_promotion),
    FOREIGN KEY (id_salle) REFERENCES salles(id_salle)
);

-- 6️⃣ Table etat_salles
CREATE TABLE IF NOT EXISTS etat_salles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salle INT,
    etat ENUM('libre', 'occupée', 'réservée') DEFAULT 'libre',
    date_update DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salle) REFERENCES salles(id_salle)
);

-- 7️⃣ Table admins (pour le login)
CREATE TABLE IF NOT EXISTS admins (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom_complet VARCHAR(100)
);

-- ---------------------------------------------------------
-- Insertion de données de test
-- ---------------------------------------------------------

INSERT INTO departements (nom_departement) VALUES 
('Informatique'),
('Mines'),
('Électricité');

INSERT INTO salles (nom_salle, capacite, batiment) VALUES 
('A1', 30, 'Bâtiment Central'),
('A2', 45, 'Bâtiment Central'),
('B1', 100, 'Bâtiment Annexe');

INSERT INTO promotions (nom_promotion, filiere, niveau, effectif, id_departement) VALUES 
('L1 Informatique', 'Informatique', 'Licence 1', 25, 1),
('L2 Informatique', 'Informatique', 'Licence 2', 40, 1),
('G3 Mines', 'Mines', 'Graduat 3', 85, 2);

INSERT INTO cours (nom_cours, enseignant) VALUES 
('Architecture des Ordinateurs', 'Prof. MUKADI'),
('Base de Données', 'M. ILUNGA');

-- Initialisation de l'état des salles
INSERT INTO etat_salles (id_salle, etat) VALUES 
(1, 'libre'),
(2, 'libre'),
(3, 'libre');

-- Admin par défaut (ADMIN / ISPT2026)
INSERT INTO admins (username, password, nom_complet) VALUES 
('ADMIN', '$2y$10$CtRs7eq.fcCsafbe8.RnNuCXwhd43/6avZf6.O846f5MMGki5omWi', 'Administrateur Principal');

