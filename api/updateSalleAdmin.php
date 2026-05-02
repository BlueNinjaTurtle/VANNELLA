<?php
// api/updateSalleAdmin.php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_salle']) || !isset($input['nom_salle']) || !isset($input['capacite']) || !isset($input['batiment'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE salles SET nom_salle = ?, capacite = ?, batiment = ? WHERE id_salle = ?");
    $stmt->execute([$input['nom_salle'], $input['capacite'], $input['batiment'], $input['id_salle']]);

    echo json_encode(['status' => 'success', 'message' => 'Salle mise à jour avec succès']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
