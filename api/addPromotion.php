<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['nom_promotion'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nom manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO promotions (nom_promotion, id_departement, filiere, niveau, effectif) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $input['nom_promotion'],
        $input['id_departement'] ?: null,
        $input['filiere'],
        $input['niveau'],
        $input['effectif']
    ]);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
