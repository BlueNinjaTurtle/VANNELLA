USE gestion_salles;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS professeurs (
    id_professeur INT AUTO_INCREMENT PRIMARY KEY,
    nom_professeur VARCHAR(100) NOT NULL,
    uid_badge VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_professeurs_uid_badge (uid_badge),
    UNIQUE KEY uq_professeurs_nom_professeur (nom_professeur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO professeurs (nom_professeur)
SELECT DISTINCT TRIM(enseignant)
FROM cours
WHERE TRIM(enseignant) <> '';

ALTER TABLE cours
    ADD COLUMN IF NOT EXISTS id_professeur INT NULL AFTER enseignant;

UPDATE cours c
JOIN professeurs p ON p.nom_professeur = TRIM(c.enseignant)
SET c.id_professeur = p.id_professeur
WHERE c.id_professeur IS NULL;

ALTER TABLE cours
    ADD INDEX IF NOT EXISTS idx_cours_professeur (id_professeur);

ALTER TABLE cours
    ADD CONSTRAINT fk_cours_professeur
    FOREIGN KEY (id_professeur) REFERENCES professeurs(id_professeur)
    ON DELETE SET NULL;

ALTER TABLE horaires
    ADD COLUMN IF NOT EXISTS date_cours DATE NULL AFTER jour;

UPDATE horaires
SET date_cours = CURRENT_DATE
WHERE date_cours IS NULL;

ALTER TABLE horaires
    MODIFY date_cours DATE NOT NULL;

ALTER TABLE horaires
    MODIFY statut ENUM('actif', 'en_cours', 'termine', 'annule') DEFAULT 'actif';

ALTER TABLE horaires
    ADD INDEX IF NOT EXISTS idx_horaires_date_cours (date_cours);

COMMIT;
