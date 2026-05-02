<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT p.*, d.nom_departement 
        FROM promotions p 
        LEFT JOIN departements d ON p.id_departement = d.id_departement
        ORDER BY p.nom_promotion ASC
    ");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
