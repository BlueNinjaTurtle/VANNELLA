<?php
// api/deleteSalle.php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

if (!isset($_POST['id_salle'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

try {
    $id_salle = (int)$_POST['id_salle'];

    if ($id_salle <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de salle invalide']);
        exit();
    }

    $pdo->beginTransaction();

    $stmtCheck = $pdo->prepare("SELECT id_salle FROM salles WHERE id_salle = ?");
    $stmtCheck->execute([$id_salle]);
    if (!$stmtCheck->fetch()) {
        throw new Exception('Salle introuvable ou deja supprimee');
    }

    // Supprimer les tables enfants avant la salle pour respecter les contraintes FK.
    $stmtHistory = $pdo->prepare("DELETE FROM etat_salles_history WHERE id_salle = ?");
    $stmtHistory->execute([$id_salle]);

    $stmtEtat = $pdo->prepare("DELETE FROM etat_salles WHERE id_salle = ?");
    $stmtEtat->execute([$id_salle]);

    $stmtHoraires = $pdo->prepare("DELETE FROM horaires WHERE id_salle = ?");
    $stmtHoraires->execute([$id_salle]);

    $stmtSalle = $pdo->prepare("DELETE FROM salles WHERE id_salle = ?");
    $stmtSalle->execute([$id_salle]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Salle supprimee avec succes',
        'horaires_supprimes' => $stmtHoraires->rowCount(),
        'historiques_supprimes' => $stmtHistory->rowCount()
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
