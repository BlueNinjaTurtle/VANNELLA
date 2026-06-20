<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once __DIR__ . '/attribution_helper.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_horaire'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID horaire manquant']);
    exit;
}

try {
    $pdo->beginTransaction();

    $id_horaire = (int)$data['id_horaire'];

    $stmt = $pdo->prepare("SELECT id_salle FROM horaires WHERE id_horaire = ?");
    $stmt->execute([$id_horaire]);
    $horaire = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horaire) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Horaire non trouve']);
        exit;
    }

    $id_salle = (int)$horaire['id_salle'];

    $deleteStmt = $pdo->prepare("DELETE FROM horaires WHERE id_horaire = ?");
    $deleteStmt->execute([$id_horaire]);

    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM horaires
        WHERE id_salle = ?
          AND statut IN ('actif', 'en_cours')
    ");
    $checkStmt->execute([$id_salle]);

    $reattribution = null;
    if ((int)$checkStmt->fetchColumn() === 0) {
        attributionUpdateSalleEtat($pdo, $id_salle, 'libre', 'Admin', "Suppression d'horaire");
        $reattribution = attributionReactivateWaitingForSalle(
            $pdo,
            $id_salle,
            null,
            null,
            null,
            null,
            'Admin'
        );
    }

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Horaire supprime avec succes',
        'horaire_reattribue' => $reattribution ? (int)$reattribution['id_horaire'] : null
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur BD: ' . $e->getMessage()]);
}
?>
