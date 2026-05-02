<?php
// api/getSallesWithDetails.php
// Retourne les salles avec toutes les promotions et départements associés
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $jourActuel = $jours[date('w')];
    $heureActuelle = date('H:i:s');

    // Première requête: infos des salles + état actuel
    $sql_salles = "
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
            AND :heure_debut_actuelle >= h.heure_debut
            AND :heure_fin_actuelle < h.heure_fin
            AND COALESCE(NULLIF(h.statut, ''), 'actif') = 'actif'
        LEFT JOIN cours c ON h.id_cours = c.id_cours
        LEFT JOIN promotions p ON h.id_promotion = p.id_promotion
        ORDER BY s.batiment, s.nom_salle
    ";

    $stmt_salles = $pdo->prepare($sql_salles);
    $stmt_salles->execute([
        'jour' => $jourActuel,
        'heure_debut_actuelle' => $heureActuelle,
        'heure_fin_actuelle' => $heureActuelle
    ]);
    $salles = $stmt_salles->fetchAll(PDO::FETCH_ASSOC);

    // Deuxième requête: toutes les promotions et départements associés à chaque salle
    $sql_promos = "
        SELECT DISTINCT
            h.id_salle,
            p.id_promotion,
            p.nom_promotion,
            p.id_departement,
            d.nom_departement
        FROM horaires h
        JOIN promotions p ON h.id_promotion = p.id_promotion
        LEFT JOIN departements d ON p.id_departement = d.id_departement
        WHERE h.id_salle IS NOT NULL
        ORDER BY h.id_salle, d.nom_departement, p.nom_promotion
    ";

    $stmt_promos = $pdo->query($sql_promos);
    $promos = $stmt_promos->fetchAll(PDO::FETCH_ASSOC);

    // Mapper les promotions et départements pour chaque salle
    $promo_map = [];
    foreach ($promos as $p) {
        $salle_id = $p['id_salle'];
        if (!isset($promo_map[$salle_id])) {
            $promo_map[$salle_id] = [
                'promotions' => [],
                'departements' => []
            ];
        }
        if (!in_array($p['nom_promotion'], $promo_map[$salle_id]['promotions'])) {
            $promo_map[$salle_id]['promotions'][] = $p['nom_promotion'];
        }
        if ($p['nom_departement'] && !in_array($p['nom_departement'], $promo_map[$salle_id]['departements'])) {
            $promo_map[$salle_id]['departements'][] = $p['nom_departement'];
        }
    }

    // Enrichir les salles avec leurs promotions et départements
    foreach ($salles as &$salle) {
        $salle_id = $salle['id_salle'];
        if (isset($promo_map[$salle_id])) {
            $salle['all_promotions'] = $promo_map[$salle_id]['promotions'];
            $salle['all_departements'] = $promo_map[$salle_id]['departements'];
        } else {
            $salle['all_promotions'] = [];
            $salle['all_departements'] = [];
        }
    }
    unset($salle);

    echo json_encode([
        'status' => 'success',
        'data' => $salles,
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
