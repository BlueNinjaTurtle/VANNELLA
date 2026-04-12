<?php
// api/getSalles.php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Jour actuel en français
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $jourActuel = $jours[date('w')];
    $heureActuelle = date('H:i:s');

    // On récupère les salles, leur état IoT, et le cours actuel si présent
    $sql = "
        SELECT 
            s.id_salle, s.nom_salle, s.capacite, s.batiment, 
            CASE 
                WHEN h.id_horaire IS NOT NULL THEN 'occupée'
                WHEN es.etat IS NOT NULL THEN es.etat
                ELSE 'libre'
            END as etat,
            es.date_update,
            c.nom_cours as cours_actuel,
            p.nom_promotion as promo_actuelle
        FROM salles s 
        LEFT JOIN etat_salles es ON s.id_salle = es.id_salle
        LEFT JOIN horaires h ON s.id_salle = h.id_salle 
            AND h.jour = :jour 
            AND :heure BETWEEN h.heure_debut AND h.heure_fin
        LEFT JOIN cours c ON h.id_cours = c.id_cours
        LEFT JOIN promotions p ON h.id_promotion = p.id_promotion
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['jour' => $jourActuel, 'heure' => $heureActuelle]);
    $salles = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $salles]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
