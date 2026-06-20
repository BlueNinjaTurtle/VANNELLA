<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Récupérer TOUS les horaires de la semaine avec infos complètes
    // Support de deux modes:
    // 1. Sans param: tous les horaires
    // 2. Avec id_promotion: juste cette promotion
    
    $id_promotion = $_GET['id_promotion'] ?? null;

    if ($id_promotion) {
        // Mode: Une promotion spécifique
        // ⚠️ IMPORTANT: Utiliser LEFT JOIN pour cours en cas de suppression de cours orpheline
        $sql = "
            SELECT 
                h.id_horaire,
                h.id_salle,
                h.jour,
                h.date_cours,
                h.heure_debut,
                h.heure_fin,
                h.statut,
                h.type_cours,
                COALESCE(c.nom_cours, '[COURS SUPPRIMÉ]') as nom_cours,
                COALESCE(c.enseignant, 'N/A') as enseignant,
                s.nom_salle,
                s.capacite,
                s.batiment,
                p.id_promotion,
                p.nom_promotion,
                p.effectif,
                es.etat,
                CASE 
                    WHEN TIMESTAMPDIFF(SECOND, es.date_update, NOW()) > 30 THEN 'offline'
                    ELSE 'online'
                END as iot_status
            FROM horaires h
            LEFT JOIN cours c ON h.id_cours = c.id_cours
            JOIN salles s ON h.id_salle = s.id_salle
            JOIN promotions p ON h.id_promotion = p.id_promotion
            LEFT JOIN etat_salles es ON s.id_salle = es.id_salle
            WHERE h.id_promotion = ?
            AND h.jour IN ('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche')
            ORDER BY h.date_cours,
                     FIELD(h.jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
                     h.heure_debut
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_promotion]);
    } else {
        // Mode: TOUS les horaires (pour le planning interactif)
        // ⚠️ IMPORTANT: Utiliser LEFT JOIN pour cours en cas de suppression de cours orpheline
        $sql = "
            SELECT 
                h.id_horaire,
                h.id_salle,
                h.jour,
                h.date_cours,
                h.heure_debut,
                h.heure_fin,
                h.statut,
                h.type_cours,
                COALESCE(c.nom_cours, '[COURS SUPPRIMÉ]') as nom_cours,
                COALESCE(c.enseignant, 'N/A') as enseignant,
                s.nom_salle,
                s.capacite,
                s.batiment,
                p.id_promotion,
                p.nom_promotion,
                p.effectif,
                es.etat,
                CASE 
                    WHEN TIMESTAMPDIFF(SECOND, es.date_update, NOW()) > 30 THEN 'offline'
                    ELSE 'online'
                END as iot_status
            FROM horaires h
            LEFT JOIN cours c ON h.id_cours = c.id_cours
            JOIN salles s ON h.id_salle = s.id_salle
            JOIN promotions p ON h.id_promotion = p.id_promotion
            LEFT JOIN etat_salles es ON s.id_salle = es.id_salle
            WHERE h.jour IN ('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche')
            ORDER BY h.date_cours,
                     FIELD(h.jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
                     h.heure_debut
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'count' => count($horaires),
        'data' => $horaires
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur DB: ' . $e->getMessage()
    ]);
}
?>
