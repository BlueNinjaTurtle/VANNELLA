<?php
// api/deleteSalle.php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_POST['id_salle'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

try {
    $id_salle = (int)$_POST['id_salle'];
    $pdo->beginTransaction();

    // Supprimer l'état
    $stmt1 = $pdo->prepare("DELETE FROM etat_salles WHERE id_salle = ?");
    $stmt1->execute([$id_salle]);

    // Supprimer la salle
    $stmt2 = $pdo->prepare("DELETE FROM salles WHERE id_salle = ?");
    $stmt2->execute([$id_salle]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Salle supprimée avec succès']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
