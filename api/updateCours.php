<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_cours']) || !isset($input['nom_cours']) || !isset($input['enseignant'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes (id_cours, nom_cours, enseignant requis)']);
    exit();
}

try {
    $id_departement = $input['id_departement'] ?? null;
    
    $stmt = $pdo->prepare("UPDATE cours SET nom_cours = ?, enseignant = ?, id_departement = ? WHERE id_cours = ?");
    $stmt->execute([$input['nom_cours'], $input['enseignant'], $id_departement, $input['id_cours']]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cours mis à jour avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
