<?php
/**
 * Script de maintenance des horaires RFID.
 *
 * - statut actif depasse de plus de 15 minutes => annule + salle libre
 * - statut en_cours depasse heure_fin => termine + salle libre
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/attribution_helper.php';

$dryRun = (PHP_SAPI === 'cli' && in_array('--dry-run', $argv ?? [], true))
    || isset($_GET['dry_run']);

function maintenanceResponse(string $status, array $data, int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}

function updateSalleEtatMaintenance(PDO $pdo, int $idSalle, string $etat, string $raison): void {
    $stmt = $pdo->prepare("SELECT etat FROM etat_salles WHERE id_salle = ?");
    $stmt->execute([$idSalle]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldEtat = $current['etat'] ?? 'libre';

    if ($current) {
        $stmt = $pdo->prepare("
            UPDATE etat_salles
            SET etat = ?, date_update = NOW(), modified_by = 'System', modified_at = NOW()
            WHERE id_salle = ?
        ");
        $stmt->execute([$etat, $idSalle]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO etat_salles (id_salle, etat, date_update, modified_by, modified_at)
            VALUES (?, ?, NOW(), 'System', NOW())
        ");
        $stmt->execute([$idSalle, $etat]);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison, timestamp)
            VALUES (?, ?, ?, 'System', ?, NOW())
        ");
        $stmt->execute([$idSalle, $oldEtat, $etat, $raison]);
    } catch (Exception $e) {
        // Historique optionnel.
    }
}

function releaseSalleIfNoCurrentCourse(PDO $pdo, int $idSalle, string $raison): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM horaires
        WHERE id_salle = ?
          AND statut IN ('actif', 'en_cours')
          AND TIMESTAMP(date_cours, heure_debut) <= NOW()
          AND TIMESTAMP(date_cours, heure_fin) > NOW()
    ");
    $stmt->execute([$idSalle]);
    $total = (int)$stmt->fetchColumn();

    if ($total === 0) {
        updateSalleEtatMaintenance($pdo, $idSalle, 'libre', $raison);
        return true;
    }

    return false;
}

function releaseSalleIfNoActiveScheduleFromWeek(PDO $pdo, int $idSalle, string $weekStart, string $raison): void {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM horaires
        WHERE id_salle = ?
          AND statut IN ('actif', 'en_cours')
          AND date_cours >= ?
    ");
    $stmt->execute([$idSalle, $weekStart]);
    $total = (int)$stmt->fetchColumn();

    if ($total === 0) {
        updateSalleEtatMaintenance($pdo, $idSalle, 'libre', $raison);
    }
}

try {
    $pdo->beginTransaction();

    $weekStart = $pdo->query("SELECT DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)")->fetchColumn();

    $oldStmt = $pdo->prepare("
        SELECT id_horaire, id_salle, date_cours, jour, heure_debut, heure_fin
        FROM horaires
        WHERE date_cours < ?
    ");
    $oldStmt->execute([$weekStart]);
    $oldHoraires = $oldStmt->fetchAll(PDO::FETCH_ASSOC);

    $lateStmt = $pdo->prepare("
        SELECT id_horaire, id_salle, date_cours, jour, heure_debut, heure_fin
        FROM horaires
        WHERE statut = 'actif'
          AND date_cours >= ?
          AND TIMESTAMP(date_cours, heure_debut) < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $lateStmt->execute([$weekStart]);
    $lateHoraires = $lateStmt->fetchAll(PDO::FETCH_ASSOC);

    $endedStmt = $pdo->prepare("
        SELECT id_horaire, id_salle, date_cours, jour, heure_debut, heure_fin
        FROM horaires
        WHERE statut = 'en_cours'
          AND date_cours >= ?
          AND TIMESTAMP(date_cours, heure_fin) <= NOW()
    ");
    $endedStmt->execute([$weekStart]);
    $endedHoraires = $endedStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($dryRun) {
        $pdo->rollBack();
        maintenanceResponse('success', [
            'dry_run' => true,
            'debut_semaine' => $weekStart,
            'anciens_horaires_a_supprimer' => count($oldHoraires),
            'retards_a_annuler' => count($lateHoraires),
            'cours_a_terminer' => count($endedHoraires),
            'horaires_a_supprimer' => array_map('intval', array_column($oldHoraires, 'id_horaire')),
            'horaires_a_annuler' => array_map('intval', array_column($lateHoraires, 'id_horaire')),
            'horaires_a_terminer' => array_map('intval', array_column($endedHoraires, 'id_horaire'))
        ]);
        exit;
    }

    $horairesReattribues = [];

    if ($oldHoraires) {
        $ids = array_column($oldHoraires, 'id_horaire');
        $salleIds = array_unique(array_map('intval', array_column($oldHoraires, 'id_salle')));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM horaires WHERE id_horaire IN ($placeholders)");
        $stmt->execute($ids);

        foreach ($salleIds as $idSalle) {
            releaseSalleIfNoActiveScheduleFromWeek($pdo, $idSalle, $weekStart, 'Nettoyage automatique des horaires des semaines passees');
        }
    }

    if ($lateHoraires) {
        $ids = array_column($lateHoraires, 'id_horaire');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE horaires SET statut = 'annule' WHERE id_horaire IN ($placeholders)");
        $stmt->execute($ids);

        foreach ($lateHoraires as $horaire) {
            $salleLiberee = releaseSalleIfNoCurrentCourse($pdo, (int)$horaire['id_salle'], 'Cours annule automatiquement apres 15 minutes de retard');
            if ($salleLiberee) {
                $reattribution = attributionReactivateWaitingForSalle(
                    $pdo,
                    (int)$horaire['id_salle'],
                    null,
                    null,
                    null,
                    null,
                    'System'
                );
                if ($reattribution) {
                    $horairesReattribues[] = (int)$reattribution['id_horaire'];
                }
            }
        }
    }

    if ($endedHoraires) {
        $ids = array_column($endedHoraires, 'id_horaire');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE horaires SET statut = 'termine' WHERE id_horaire IN ($placeholders)");
        $stmt->execute($ids);

        foreach ($endedHoraires as $horaire) {
            $salleLiberee = releaseSalleIfNoCurrentCourse($pdo, (int)$horaire['id_salle'], 'Cours termine automatiquement en fin de creneau');
            if ($salleLiberee) {
                $reattribution = attributionReactivateWaitingForSalle(
                    $pdo,
                    (int)$horaire['id_salle'],
                    null,
                    null,
                    null,
                    null,
                    'System'
                );
                if ($reattribution) {
                    $horairesReattribues[] = (int)$reattribution['id_horaire'];
                }
            }
        }
    }

    $pdo->commit();

    maintenanceResponse('success', [
        'debut_semaine' => $weekStart,
        'anciens_horaires_supprimes' => count($oldHoraires),
        'retards_annules' => count($lateHoraires),
        'cours_termines' => count($endedHoraires),
        'horaires_supprimes' => array_map('intval', array_column($oldHoraires, 'id_horaire')),
        'horaires_annules' => array_map('intval', array_column($lateHoraires, 'id_horaire')),
        'horaires_termines' => array_map('intval', array_column($endedHoraires, 'id_horaire')),
        'horaires_reattribues' => $horairesReattribues
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    maintenanceResponse('error', [
        'message' => $e->getMessage()
    ], 500);
}

?>
