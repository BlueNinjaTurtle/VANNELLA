<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_POST['id_promotion'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id_promotion = ?");
    $stmt->execute([$_POST['id_promotion']]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
