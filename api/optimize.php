<?php
/**
 * api/optimize.php
 * Algorithme d'attribution optimale de salle.
 */
header('Content-Type: application/json');
require_once '../config/db.php';

$id_promotion = $_GET['id_promotion'] ?? null;
$jour = $_GET['jour'] ?? null;
$heure_debut = $_GET['heure_debut'] ?? null;
$heure_fin = $_GET['heure_fin'] ?? null;

if (!$id_promotion || !$jour || !$heure_debut || !$heure_fin) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants']);
    exit;
}

try {
    // 1. Récupérer l'effectif de la promotion
    $stmtPromo = $pdo->prepare("SELECT effectif FROM promotions WHERE id_promotion = ?");
    $stmtPromo->execute([$id_promotion]);
    $promo = $stmtPromo->fetch();
    
    if (!$promo) {
        echo json_encode(['status' => 'error', 'message' => 'Promotion non trouvée']);
        exit;
    }
    
    $effectif = $promo['effectif'];

    // 2. Trouver les salles disponibles sur ce créneau horaire
    // On exclut les salles déjà réservées dans la table 'horaires' pour ce jour et ce créneau
    $sqlSallesDispo = "
        SELECT * FROM salles 
        WHERE id_salle NOT IN (
            SELECT id_salle FROM horaires 
            WHERE jour = :jour 
            AND (
                (heure_debut <= :debut AND heure_fin > :debut) OR 
                (heure_debut < :fin AND heure_fin >= :fin) OR
                (:debut <= heure_debut AND :fin >= heure_fin)
            )
        )
        AND capacite >= :effectif
        ORDER BY capacite ASC 
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sqlSallesDispo);
    $stmt->execute([
        'jour' => $jour,
        'debut' => $heure_debut,
        'fin' => $heure_fin,
        'effectif' => $effectif
    ]);

    $bestSalle = $stmt->fetch();

    if ($bestSalle) {
        echo json_encode(['status' => 'success', 'data' => $bestSalle]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Aucune salle disponible pour cet effectif sur ce créneau']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
