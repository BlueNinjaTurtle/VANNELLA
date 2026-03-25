<?php
// api/updateSalle.php
header('Content-Type: application/json');
require_once '../config/db.php';

// Récupération des données brutes de la requête
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id_salle']) || !isset($data['etat'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit;
}

$id_salle = (int)$data['id_salle'];
$etat = $data['etat'];

// Validation de l'état (libre, occupée, réservée)
$valid_states = ['libre', 'occupée', 'réservée'];
if (!in_array($etat, $valid_states)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'État invalide']);
    exit;
}

try {
    // Mise à jour de l'état de la salle
    $stmt = $pdo->prepare("UPDATE etat_salles SET etat = ? WHERE id_salle = ?");
    $stmt->execute([$etat, $id_salle]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'État de la salle mis à jour']);
    } else {
        // Si aucune ligne n'est mise à jour, c'est peut-être que l'ID n'existe pas
        // ou que l'état est déjà le même. On vérifie l'existence de la salle.
        $check = $pdo->prepare("SELECT id_salle FROM salles WHERE id_salle = ?");
        $check->execute([$id_salle]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'success', 'message' => 'Aucun changement nécessaire (état identique)']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Salle non trouvée']);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
