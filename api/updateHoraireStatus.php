<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once __DIR__ . '/attribution_helper.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_horaire'], $data['statut'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Donnees incompletes (id_horaire et statut requis)']);
    exit;
}

$id_horaire = (int)$data['id_horaire'];
$statut = strtolower(trim((string)$data['statut']));
$statut = str_replace(['é', 'è', 'ê', 'ë'], 'e', $statut);

$statuts_valides = ['actif', 'annule'];
if (!in_array($statut, $statuts_valides, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Statut invalide. Doit etre "actif" ou "annule"']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT id_salle, date_cours, jour, heure_debut, heure_fin, statut AS ancien_statut
        FROM horaires
        WHERE id_horaire = ?
    ");
    $stmt->execute([$id_horaire]);
    $horaire = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horaire) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Horaire non trouve']);
        exit;
    }

    $id_salle = (int)$horaire['id_salle'];
    $ancien_statut = $horaire['ancien_statut'];
    $reattribution = null;
    $salleLiberee = false;

    $updateStmt = $pdo->prepare("UPDATE horaires SET statut = ? WHERE id_horaire = ?");
    $updateStmt->execute([$statut, $id_horaire]);

    if ($statut === 'annule') {
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM horaires
            WHERE id_salle = ?
              AND id_horaire <> ?
              AND statut IN ('actif', 'en_cours')
              AND date_cours = ?
              AND jour = ?
              AND (
                  (heure_debut <= ? AND heure_fin > ?) OR
                  (heure_debut < ? AND heure_fin >= ?) OR
                  (? <= heure_debut AND ? >= heure_fin)
              )
        ");
        $checkStmt->execute([
            $id_salle,
            $id_horaire,
            $horaire['date_cours'],
            $horaire['jour'],
            $horaire['heure_debut'],
            $horaire['heure_debut'],
            $horaire['heure_fin'],
            $horaire['heure_fin'],
            $horaire['heure_debut'],
            $horaire['heure_fin']
        ]);

        if ((int)$checkStmt->fetchColumn() === 0) {
            attributionUpdateSalleEtat($pdo, $id_salle, 'libre', 'Admin', 'Cours annule');
            $salleLiberee = true;
        }

        if ($salleLiberee) {
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
    } elseif (($ancien_statut === 'annule' || $ancien_statut === 'annulé') && $statut === 'actif') {
        attributionUpdateSalleEtat($pdo, $id_salle, 'réservée', 'Admin', 'Cours reactive');
    }

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => "Statut de l'horaire mis a jour avec succes",
        'new_statut' => $statut,
        'horaire_reattribue' => $reattribution ? (int)$reattribution['id_horaire'] : null
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur BD: ' . $e->getMessage()]);
}
?>
