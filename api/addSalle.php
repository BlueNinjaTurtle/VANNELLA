<?php
// api/addSalle.php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['nom_salle']) || !isset($input['capacite']) || !isset($input['batiment'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO salles (nom_salle, capacite, batiment) VALUES (?, ?, ?)");
    $stmt->execute([$input['nom_salle'], $input['capacite'], $input['batiment']]);
    $id_salle = $pdo->lastInsertId();

    // Initialiser l'état de la salle
    $stmt2 = $pdo->prepare("INSERT INTO etat_salles (id_salle, etat) VALUES (?, 'libre')");
    $stmt2->execute([$id_salle]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Salle ajoutée avec succès']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
