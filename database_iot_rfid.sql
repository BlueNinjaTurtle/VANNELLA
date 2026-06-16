-- 🆕 Tables pour le système IoT RFID avec Cisco Packet Tracer

USE gestion_salles;

-- 1️⃣ Table des lecteurs RFID (un par salle)
CREATE TABLE IF NOT EXISTS lecteurs_rfid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_lecteur VARCHAR(50) NOT NULL UNIQUE,
    id_salle INT NOT NULL,
    nom_salle VARCHAR(100) NOT NULL,
    localisation VARCHAR(100),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_salle) REFERENCES salles(id_salle)
);

-- 2️⃣ Table des cartes RFID (registre des cartes)
CREATE TABLE IF NOT EXISTS cartes_rfid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_carte VARCHAR(50) NOT NULL UNIQUE,
    proprietaire VARCHAR(100),
    type_carte ENUM('etudiant', 'professeur', 'admin', 'test') DEFAULT 'etudiant',
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

-- 3️⃣ Table des lectures RFID (historique de toutes les lectures)
CREATE TABLE IF NOT EXISTS lectures_rfid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_carte VARCHAR(50) NOT NULL,
    id_lecteur VARCHAR(50) NOT NULL,
    nom_lecteur VARCHAR(100) NOT NULL,
    id_salle INT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (id_salle, timestamp),
    INDEX (id_carte),
    INDEX (id_lecteur),
    FOREIGN KEY (id_salle) REFERENCES salles(id_salle)
);

-- 4️⃣ Insérer les lecteurs RFID par défaut (un par salle)
INSERT INTO lecteurs_rfid (id_lecteur, id_salle, nom_salle, localisation) VALUES
('LECTEUR_001', 1, 'GRANDE SALLE', 'Porte d''entrée'),
('LECTEUR_002', 2, 'CISCO', 'Porte d''entrée'),
('LECTEUR_003', 3, 'UPL', 'Porte d''entrée'),
('LECTEUR_004', 4, 'PALANGUI', 'Porte d''entrée'),
('LECTEUR_005', 5, 'ANCIENNE PREPARATOIRE', 'Porte d''entrée');

-- 5️⃣ Insérer des cartes RFID de test
INSERT INTO cartes_rfid (id_carte, proprietaire, type_carte) VALUES
('CARD_001', 'Étudiant 1 - L1 Info', 'etudiant'),
('CARD_002', 'Étudiant 2 - L1 Info', 'etudiant'),
('CARD_003', 'Étudiant 3 - L2 Info', 'etudiant'),
('CARD_004', 'Prof. MUKADI', 'professeur'),
('CARD_005', 'Prof. ILUNGA', 'professeur'),
('CARD_006', 'Admin - Test', 'admin'),
('CARD_TEST_001', 'Carte Test 1', 'test'),
('CARD_TEST_002', 'Carte Test 2', 'test');

-- 6️⃣ Afficher la structure pour vérification
SELECT '✅ Tables créées avec succès!' AS status;
