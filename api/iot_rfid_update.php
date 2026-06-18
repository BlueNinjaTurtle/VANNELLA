<?php
/**
 * API RFID Cisco Packet Tracer.
 * Payload attendu: { "salle": "CISCO", "uid_badge": "1002" }
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

function currentJourName(): string {
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    return $jours[(int)date('w')];
}

function jsonResponse(int $httpCode, string $status, string $code, string $message, array $data = []): void {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'code' => $code,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}

function setSalleEtat(PDO $pdo, int $idSalle, string $etat, string $raison): void {
    $stmt = $pdo->prepare("SELECT etat FROM etat_salles WHERE id_salle = ?");
    $stmt->execute([$idSalle]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldEtat = $current['etat'] ?? 'libre';

    if ($current) {
        $stmt = $pdo->prepare("
            UPDATE etat_salles
            SET etat = ?, date_update = NOW(), modified_by = 'IoT', modified_at = NOW()
            WHERE id_salle = ?
        ");
        $stmt->execute([$etat, $idSalle]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO etat_salles (id_salle, etat, date_update, modified_by, modified_at)
            VALUES (?, ?, NOW(), 'IoT', NOW())
        ");
        $stmt->execute([$idSalle, $etat]);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison, timestamp)
            VALUES (?, ?, ?, 'IoT', ?, NOW())
        ");
        $stmt->execute([$idSalle, $oldEtat, $etat, $raison]);
    } catch (Exception $e) {
        // Historique optionnel selon l'installation.
    }
}

function logRfidLecture(PDO $pdo, string $uidBadge, ?int $idSalle, string $nomSalle): void {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO lectures_rfid (id_carte, id_lecteur, nom_lecteur, id_salle, timestamp)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$uidBadge, $nomSalle, $nomSalle, $idSalle]);
    } catch (Exception $e) {
        // La table d'historique RFID n'est pas obligatoire pour la logique metier.
    }
}

$rawInput = file_get_contents('php://input');
if ($rawInput === '' && PHP_SAPI === 'cli') {
    $rawInput = file_get_contents('php://stdin');
}

$input = json_decode($rawInput, true);
if (!is_array($input)) {
    jsonResponse(400, 'error', 'INVALID_JSON', 'Donnees JSON invalides');
    exit;
}

$uidBadge = trim((string)($input['uid_badge'] ?? $input['id_carte'] ?? ''));
$nomSalle = trim((string)($input['salle'] ?? $input['nom_salle'] ?? $input['nom_lecteur'] ?? ''));
$idSalleInput = isset($input['id_salle']) ? (int)$input['id_salle'] : null;

if ($uidBadge === '' || ($nomSalle === '' && !$idSalleInput)) {
    jsonResponse(400, 'error', 'INVALID_PAYLOAD', 'Champs requis: uid_badge et salle');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id_professeur, nom_professeur FROM professeurs WHERE uid_badge = ?");
    $stmt->execute([$uidBadge]);
    $professeur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$professeur) {
        $pdo->rollBack();
        jsonResponse(404, 'error', 'UNKNOWN_CARD', 'Badge RFID inconnu', [
            'uid_badge' => $uidBadge
        ]);
        exit;
    }

    if ($idSalleInput) {
        $stmt = $pdo->prepare("SELECT id_salle, nom_salle FROM salles WHERE id_salle = ?");
        $stmt->execute([$idSalleInput]);
    } else {
        $stmt = $pdo->prepare("SELECT id_salle, nom_salle FROM salles WHERE nom_salle = ?");
        $stmt->execute([$nomSalle]);
    }
    $salle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$salle) {
        $pdo->rollBack();
        jsonResponse(404, 'error', 'UNKNOWN_ROOM', 'Salle inconnue', [
            'salle' => $nomSalle,
            'id_salle' => $idSalleInput
        ]);
        exit;
    }

    logRfidLecture($pdo, $uidBadge, (int)$salle['id_salle'], $salle['nom_salle']);

    $dateCours = date('Y-m-d');
    $jour = currentJourName();
    $heure = date('H:i:s');

    $stmt = $pdo->prepare("
        SELECT
            h.id_horaire,
            h.id_salle,
            h.statut,
            h.heure_debut,
            h.heure_fin,
            c.nom_cours,
            p.nom_promotion,
            s.nom_salle
        FROM horaires h
        JOIN cours c ON h.id_cours = c.id_cours
        JOIN promotions p ON h.id_promotion = p.id_promotion
        JOIN salles s ON h.id_salle = s.id_salle
        WHERE c.id_professeur = ?
          AND h.date_cours = ?
          AND h.jour = ?
          AND ? >= h.heure_debut
          AND ? < h.heure_fin
          AND COALESCE(NULLIF(h.statut, ''), 'actif') IN ('actif', 'en_cours')
        ORDER BY h.heure_debut
        LIMIT 1
    ");
    $stmt->execute([
        $professeur['id_professeur'],
        $dateCours,
        $jour,
        $heure,
        $heure
    ]);
    $horaire = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$horaire) {
        $pdo->rollBack();
        jsonResponse(404, 'error', 'NO_SCHEDULE', 'Aucun cours prevu maintenant pour ce professeur', [
            'uid_badge' => $uidBadge,
            'professeur' => $professeur['nom_professeur'],
            'date_cours' => $dateCours,
            'heure' => $heure
        ]);
        exit;
    }

    if ((int)$horaire['id_salle'] !== (int)$salle['id_salle']) {
        $pdo->rollBack();
        jsonResponse(409, 'error', 'WRONG_ROOM', 'Le professeur a cours dans une autre salle', [
            'professeur' => $professeur['nom_professeur'],
            'cours' => $horaire['nom_cours'],
            'salle_attendue' => $horaire['nom_salle'],
            'salle_recue' => $salle['nom_salle']
        ]);
        exit;
    }

    if ($horaire['statut'] === 'en_cours') {
        setSalleEtat($pdo, (int)$salle['id_salle'], 'occupee', 'Double badgeage RFID ignore');
        $pdo->commit();
        jsonResponse(200, 'success', 'ALREADY_VALIDATED', 'Cours deja valide', [
            'id_horaire' => (int)$horaire['id_horaire'],
            'professeur' => $professeur['nom_professeur'],
            'cours' => $horaire['nom_cours'],
            'salle' => $salle['nom_salle']
        ]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE horaires SET statut = 'en_cours' WHERE id_horaire = ?");
    $stmt->execute([$horaire['id_horaire']]);

    setSalleEtat($pdo, (int)$salle['id_salle'], 'occupee', 'Cours valide par badge RFID');

    $pdo->commit();
    jsonResponse(200, 'success', 'AUTHORIZED', 'Acces autorise et cours marque en cours', [
        'id_horaire' => (int)$horaire['id_horaire'],
        'professeur' => $professeur['nom_professeur'],
        'cours' => $horaire['nom_cours'],
        'promotion' => $horaire['nom_promotion'],
        'salle' => $salle['nom_salle'],
        'date_cours' => $dateCours,
        'heure' => $heure
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(500, 'error', 'SERVER_ERROR', $e->getMessage());
}

?>
