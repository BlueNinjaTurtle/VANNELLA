<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['nom_cours']) || !isset($input['enseignant'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO cours (nom_cours, enseignant) VALUES (?, ?)");
    $stmt->execute([$input['nom_cours'], $input['enseignant']]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
