<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_POST['nom_departement'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nom manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO departements (nom_departement) VALUES (?)");
    $stmt->execute([$_POST['nom_departement']]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
