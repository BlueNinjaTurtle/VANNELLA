<?php
// api/getSalles.php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $jourActuel = $jours[date('w')];
    $dateActuelle = date('Y-m-d');
    $heureActuelle = date('H:i:s');

    $sql = "
        SELECT
            s.id_salle,
            s.nom_salle,
            s.capacite,
            s.batiment,
            CASE COALESCE(NULLIF(es.etat, ''), 'libre')
                WHEN 'occupee' THEN 'occupée'
                WHEN 'reservee' THEN 'réservée'
                WHEN 'indisponible' THEN 'indisponible'
                ELSE COALESCE(NULLIF(es.etat, ''), 'libre')
            END AS etat,
            LOWER(
                CASE COALESCE(NULLIF(es.etat, ''), 'libre')
                    WHEN 'occupee' THEN 'occupée'
                    WHEN 'reservee' THEN 'réservée'
                    WHEN 'indisponible' THEN 'indisponible'
                    ELSE COALESCE(NULLIF(es.etat, ''), 'libre')
                END
            ) AS etat_display,
            CASE
                WHEN h.id_horaire IS NOT NULL THEN 'Planning'
                ELSE COALESCE(es.modified_by, 'System')
            END AS etat_source,
            CASE
                WHEN TIMESTAMPDIFF(SECOND, COALESCE(es.date_update, NOW()), NOW()) > 30 THEN 'offline'
                ELSE 'online'
            END AS iot_status,
            COALESCE(es.date_update, NOW()) AS date_update,
            TIMESTAMPDIFF(SECOND, COALESCE(es.date_update, NOW()), NOW()) AS seconds_since_update,
            c.nom_cours AS cours_actuel,
            p.nom_promotion AS promo_actuelle
        FROM salles s
        LEFT JOIN etat_salles es ON s.id_salle = es.id_salle
        LEFT JOIN horaires h ON s.id_salle = h.id_salle
            AND h.jour = :jour
            AND h.date_cours = :date_cours
            AND :heure_debut_actuelle >= h.heure_debut
            AND :heure_fin_actuelle < h.heure_fin
            AND COALESCE(NULLIF(h.statut, ''), 'actif') IN ('actif', 'en_cours')
        LEFT JOIN cours c ON h.id_cours = c.id_cours
        LEFT JOIN promotions p ON h.id_promotion = p.id_promotion
        ORDER BY s.batiment, s.nom_salle
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'jour' => $jourActuel,
        'date_cours' => $dateActuelle,
        'heure_debut_actuelle' => $heureActuelle,
        'heure_fin_actuelle' => $heureActuelle
    ]);

    echo json_encode([
        'status' => 'success',
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>
