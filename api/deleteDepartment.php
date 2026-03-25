<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_POST['id_departement'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

try {
    $id = $_POST['id_departement'];
    
    // Vérifier si des promotions sont liées
    $check = $pdo->prepare("SELECT COUNT(*) FROM promotions WHERE id_departement = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Impossible de supprimer : des promotions sont liées à ce département.']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM departements WHERE id_departement = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
