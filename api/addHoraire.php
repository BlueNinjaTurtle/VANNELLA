<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_cours'], $data['id_promotion'], $data['jour'], $data['heure_debut'], $data['heure_fin'], $data['id_salle'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit;
}

try {
    $sql = "INSERT INTO horaires (id_cours, id_promotion, jour, heure_debut, heure_fin, id_salle) 
            VALUES (:id_cours, :id_promotion, :jour, :heure_debut, :heure_fin, :id_salle)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    // Mettre à jour l'état de la salle à "réservée" après ajout d'horaire
    $stmtEtat = $pdo->prepare("
        UPDATE etat_salles 
        SET etat = 'réservée' 
        WHERE id_salle = ?
    ");
    $stmtEtat->execute([$data['id_salle']]);
    
    echo json_encode(['status' => 'success', 'message' => 'Horaire ajouté avec succès']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
