<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_cours']) || !isset($input['nom_cours']) || !isset($input['enseignant'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE cours SET nom_cours = ?, enseignant = ? WHERE id_cours = ?");
    $stmt->execute([$input['nom_cours'], $input['enseignant'], $input['id_cours']]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
