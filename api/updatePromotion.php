<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_promotion'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE promotions SET nom_promotion = ?, id_departement = ?, filiere = ?, niveau = ?, effectif = ? WHERE id_promotion = ?");
    $stmt->execute([
        $input['nom_promotion'],
        $input['id_departement'] ?: null,
        $input['filiere'],
        $input['niveau'],
        $input['effectif'],
        $input['id_promotion']
    ]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
