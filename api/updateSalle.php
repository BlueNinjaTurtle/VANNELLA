<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once __DIR__ . '/attribution_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_salle'], $data['etat'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Donnees incompletes']);
    exit;
}

$id_salle = (int)$data['id_salle'];
$etat = (string)$data['etat'];

$valid_states = ['libre', 'occupée', 'occupee', 'réservée', 'reservee', 'indisponible'];
if (!in_array($etat, $valid_states, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Etat invalide']);
    exit;
}

if ($etat === 'occupee') {
    $etat = 'occupée';
}
if ($etat === 'reservee') {
    $etat = 'réservée';
}

try {
    $stmt = $pdo->prepare("UPDATE etat_salles SET etat = ?, date_update = NOW() WHERE id_salle = ?");
    $stmt->execute([$etat, $id_salle]);

    $check = $pdo->prepare("SELECT id_salle FROM salles WHERE id_salle = ?");
    $check->execute([$id_salle]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Salle non trouvee']);
        exit;
    }

    $reattribution = null;
    if ($etat === 'libre') {
        $reattribution = attributionReactivateWaitingForSalle($pdo, $id_salle, null, null, null, null, 'Admin');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Etat de la salle mis a jour',
        'horaire_reattribue' => $reattribution ? (int)$reattribution['id_horaire'] : null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
