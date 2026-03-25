<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_POST['id_cours'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM cours WHERE id_cours = ?");
    $stmt->execute([$_POST['id_cours']]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
