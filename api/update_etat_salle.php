<?php
/**
 * API de simulation IoT
 * Endpoint: POST /api/update_etat_salle.php
 */

require_once '../config/db.php';
require_once __DIR__ . '/attribution_helper.php';

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Paris');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Methode HTTP non autorisee. Utilisez POST.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('Donnees JSON invalides.');
    }

    $id_salle = isset($input['id_salle']) ? (int)$input['id_salle'] : 0;
    if ($id_salle <= 0) {
        throw new Exception('ID salle invalide ou manquant.');
    }

    $id_horaire = isset($input['id_horaire']) ? (int)$input['id_horaire'] : 0;
    $periode = isset($input['periode']) ? strtolower(trim((string)$input['periode'])) : '';
    if (!in_array($periode, ['avant', 'apres'], true)) {
        $periode = '';
    }

    // On travaille avec des cles ASCII, puis SQL les convertit vers l'enum reelle.
    $etat_input = trim((string)($input['etat'] ?? ''));
    $etat_ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $etat_input);
    if ($etat_ascii === false || $etat_ascii === '') {
        $etat_ascii = $etat_input;
    }
    $etat_key = strtolower($etat_ascii);
    $etat_key = preg_replace('/[^a-z]/', '', $etat_key);

    $etats_valides = ['libre', 'occupee', 'reservee', 'indisponible'];
    if (!in_array($etat_key, $etats_valides, true)) {
        throw new Exception('Etat invalide. Valides: libre, occupee, reservee, indisponible');
    }

    $etat_labels = [
        'libre' => 'libre',
        'occupee' => 'occupée',
        'reservee' => 'réservée',
        'indisponible' => 'indisponible'
    ];

    $check = $pdo->prepare('SELECT nom_salle FROM salles WHERE id_salle = ?');
    $check->execute([$id_salle]);
    $salle = $check->fetch();

    if (!$salle) {
        throw new Exception("Salle ID $id_salle non trouvee.");
    }

    $current = $pdo->prepare('SELECT etat FROM etat_salles WHERE id_salle = ?');
    $current->execute([$id_salle]);
    $currentRow = $current->fetch();
    $etatAncien = $currentRow && !empty($currentRow['etat']) ? $currentRow['etat'] : 'libre';

    $pdo->beginTransaction();

    try {
        $horairesAnnules = [];
        $horairesReattribues = [];

        if ($currentRow) {
            $update = $pdo->prepare("
                UPDATE etat_salles
                SET etat = ?,
                modified_by = 'IoT',
                modified_at = NOW(),
                date_update = NOW()
                WHERE id_salle = ?
            ");
            $update->execute([$etat_key, $id_salle]);
        } else {
            $insert = $pdo->prepare("
                INSERT INTO etat_salles (id_salle, etat, date_update, modified_by, modified_at)
                VALUES (?, ?, NOW(), 'IoT', NOW())
            ");
            $insert->execute([$id_salle, $etat_key]);
        }

        if ($etat_key === 'libre') {
            $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $jourActuel = $jours[(int)date('w')];

            if ($id_horaire > 0) {
                $selectHoraires = $pdo->prepare("
                    SELECT id_horaire, id_salle, date_cours, jour, heure_debut, heure_fin
                    FROM horaires
                    WHERE id_horaire = ?
                      AND id_salle = ?
                      AND statut = 'actif'
                ");
                $selectHoraires->execute([$id_horaire, $id_salle]);
                $horairesAnnules = $selectHoraires->fetchAll(PDO::FETCH_ASSOC);
            }

            if (empty($horairesAnnules)) {
                if ($periode === '') {
                    $periode = ((int)date('H') < 12) ? 'avant' : 'apres';
                }

                $conditionPeriode = $periode === 'avant'
                    ? "heure_debut < '12:00:00'"
                    : "heure_debut >= '12:00:00'";

                $selectHoraires = $pdo->prepare("
                    SELECT id_horaire, id_salle, date_cours, jour, heure_debut, heure_fin
                    FROM horaires
                    WHERE id_salle = ?
                      AND jour = ?
                      AND $conditionPeriode
                      AND statut = 'actif'
                ");
                $selectHoraires->execute([$id_salle, $jourActuel]);
                $horairesAnnules = $selectHoraires->fetchAll(PDO::FETCH_ASSOC);
            }

            if (!empty($horairesAnnules)) {
                $horairesAnnulesIds = array_column($horairesAnnules, 'id_horaire');
                $placeholders = implode(',', array_fill(0, count($horairesAnnules), '?'));
                $annulerHoraires = $pdo->prepare("
                    UPDATE horaires
                    SET statut = 'annule'
                    WHERE id_horaire IN ($placeholders)
                ");
                $annulerHoraires->execute($horairesAnnulesIds);

                foreach ($horairesAnnules as $horaireAnnule) {
                    $reattribution = attributionReactivateWaitingForSalle(
                        $pdo,
                        (int)$horaireAnnule['id_salle'],
                        null,
                        null,
                        null,
                        null,
                        'IoT'
                    );
                    if ($reattribution) {
                        $horairesReattribues[] = (int)$reattribution['id_horaire'];
                    }
                }
            }

            if (empty($horairesReattribues)) {
                $reattribution = attributionReactivateWaitingForSalle(
                    $pdo,
                    $id_salle,
                    null,
                    null,
                    null,
                    null,
                    'IoT'
                );
                if ($reattribution) {
                    $horairesReattribues[] = (int)$reattribution['id_horaire'];
                }
            }
        }

        try {
            $hist = $pdo->prepare("
                INSERT INTO etat_salles_history
                (id_salle, etat_ancien, etat_nouveau, modified_by, raison)
                VALUES (?, ?, ?, 'IoT', 'Changement manuel via simulation IoT')
            ");
            $hist->execute([$id_salle, $etatAncien, $etat_labels[$etat_key]]);
        } catch (Exception $e) {
            error_log('Historique non disponible: ' . $e->getMessage());
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    echo json_encode([
        'status' => 'success',
        'message' => "Salle {$salle['nom_salle']} mise a jour",
        'id_salle' => $id_salle,
        'etat' => $etat_labels[$etat_key],
        'horaires_annules' => count($horairesAnnules),
        'horaires_reattribues' => $horairesReattribues,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>
