<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once __DIR__ . '/attribution_helper.php';

if (!isset($_POST['id_cours'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit;
}

$id_cours = (int)$_POST['id_cours'];

try {
    $pdo->beginTransaction();

    $coursStmt = $pdo->prepare("SELECT id_professeur FROM cours WHERE id_cours = ?");
    $coursStmt->execute([$id_cours]);
    $cours = $coursStmt->fetch(PDO::FETCH_ASSOC);
    $id_professeur = $cours && !empty($cours['id_professeur']) ? (int)$cours['id_professeur'] : null;

    $selectStmt = $pdo->prepare("SELECT id_horaire, id_salle FROM horaires WHERE id_cours = ?");
    $selectStmt->execute([$id_cours]);
    $horaires = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
    $salleIds = array_values(array_unique(array_map('intval', array_column($horaires, 'id_salle'))));

    $deleteHorairesStmt = $pdo->prepare("DELETE FROM horaires WHERE id_cours = ?");
    $deleteHorairesStmt->execute([$id_cours]);

    $deleteCoursStmt = $pdo->prepare("DELETE FROM cours WHERE id_cours = ?");
    $deleteCoursStmt->execute([$id_cours]);

    $horairesReattribues = [];
    foreach ($salleIds as $id_salle) {
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM horaires
            WHERE id_salle = ?
              AND statut IN ('actif', 'en_cours')
        ");
        $checkStmt->execute([$id_salle]);

        if ((int)$checkStmt->fetchColumn() === 0) {
            attributionUpdateSalleEtat($pdo, $id_salle, 'libre', 'Admin', 'Suppression du cours et de tous ses horaires');
            $reattribution = attributionReactivateWaitingForSalle($pdo, $id_salle, null, null, null, null, 'Admin');
            if ($reattribution) {
                $horairesReattribues[] = (int)$reattribution['id_horaire'];
            }
        }
    }

    if ($id_professeur) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE id_professeur = ?");
        $countStmt->execute([$id_professeur]);

        if ((int)$countStmt->fetchColumn() === 0) {
            $releaseUidStmt = $pdo->prepare("UPDATE professeurs SET uid_badge = NULL WHERE id_professeur = ?");
            $releaseUidStmt->execute([$id_professeur]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Cours et horaires supprimes avec succes',
        'horaires_deleted' => count($horaires),
        'horaires_reattribues' => $horairesReattribues
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur suppression: ' . $e->getMessage()
    ]);
}
?>
