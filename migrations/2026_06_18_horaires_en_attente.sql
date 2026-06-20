ALTER TABLE horaires
    MODIFY statut ENUM('actif', 'en_attente', 'en_cours', 'termine', 'annule') DEFAULT 'actif';
