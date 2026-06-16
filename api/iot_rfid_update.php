<?php
/**
 * API IoT RFID - Reçoit les lectures de cartes RFID depuis les lecteurs
 * Envoie les données par le SBC-PT (ou émulateur Python)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Données JSON invalides');
    }

    // Valider les champs requis
    $required = ['id_carte', 'id_lecteur', 'nom_lecteur', 'id_salle'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Champ requis manquant: $field");
        }
    }

    $id_carte = trim($input['id_carte']);
    $id_lecteur = trim($input['id_lecteur']);
    $nom_lecteur = trim($input['nom_lecteur']);
    $id_salle = intval($input['id_salle']);
    $timestamp = date('Y-m-d H:i:s');

    // Vérifier que la salle existe
    $stmtSalle = $conn->prepare("SELECT id_salle FROM salles WHERE id_salle = ?");
    $stmtSalle->bind_param("i", $id_salle);
    $stmtSalle->execute();
    if ($stmtSalle->get_result()->num_rows === 0) {
        throw new Exception("Salle #$id_salle inexistante");
    }

    // 1️⃣ Insérer la lecture RFID dans l'historique
    $stmtLecture = $conn->prepare(
        "INSERT INTO lectures_rfid (id_carte, id_lecteur, nom_lecteur, id_salle, timestamp)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmtLecture->bind_param("sssss", $id_carte, $id_lecteur, $nom_lecteur, $id_salle, $timestamp);
    if (!$stmtLecture->execute()) {
        throw new Exception("Erreur insertion lecture: " . $stmtLecture->error);
    }
    $id_lecture = $stmtLecture->insert_id;

    // 2️⃣ Mettre à jour l'état de la salle (OCCUPÉE quand quelqu'un entre)
    $new_etat = 'occupée';

    $stmtUpdate = $conn->prepare(
        "UPDATE etat_salles SET etat = ?, modified_by = 'IoT'
         WHERE id_salle = ?"
    );
    $stmtUpdate->bind_param("si", $new_etat, $id_salle);
    if (!$stmtUpdate->execute()) {
        throw new Exception("Erreur mise à jour état: " . $stmtUpdate->error);
    }

    // 3️⃣ Enregistrer dans l'historique des changements
    $stmtHistory = $conn->prepare(
        "INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison, timestamp)
         SELECT id_salle, 'libre', ?, 'IoT', CONCAT('Lecture RFID: ', ?), ? FROM etat_salles WHERE id_salle = ? LIMIT 1"
    );
    $stmtHistory->bind_param("sssi", $new_etat, $id_carte, $timestamp, $id_salle);
    $stmtHistory->execute();

    // 4️⃣ Récupérer les informations de la salle
    $stmtSalleInfo = $conn->prepare(
        "SELECT s.nom_salle, s.capacite, s.batiment, es.etat
         FROM salles s
         LEFT JOIN etat_salles es ON s.id_salle = es.id_salle
         WHERE s.id_salle = ?"
    );
    $stmtSalleInfo->bind_param("i", $id_salle);
    $stmtSalleInfo->execute();
    $salleInfo = $stmtSalleInfo->get_result()->fetch_assoc();

    // Réponse réussie
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Lecture RFID enregistrée',
        'data' => [
            'id_lecture' => $id_lecture,
            'id_carte' => $id_carte,
            'id_lecteur' => $id_lecteur,
            'nom_lecteur' => $nom_lecteur,
            'id_salle' => $id_salle,
            'nom_salle' => $salleInfo['nom_salle'] ?? 'Inconnue',
            'etat_salle' => $salleInfo['etat'] ?? 'libre',
            'timestamp' => $timestamp
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
