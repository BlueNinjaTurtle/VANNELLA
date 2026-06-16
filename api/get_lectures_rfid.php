<?php
/**
 * API - Récupérer les lectures RFID (historique)
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $id_salle = isset($_GET['id_salle']) ? intval($_GET['id_salle']) : null;

    $query = "
        SELECT
            id,
            id_carte,
            id_lecteur,
            nom_lecteur,
            id_salle,
            timestamp
        FROM lectures_rfid
    ";

    $params = [];
    $types = "";

    if ($id_salle) {
        $query .= " WHERE id_salle = ?";
        $params[] = $id_salle;
        $types .= "i";
    }

    $query .= " ORDER BY timestamp DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";

    $stmt = $conn->prepare($query);

    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $lectures = [];
    while ($row = $result->fetch_assoc()) {
        $lectures[] = $row;
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => array_reverse($lectures) // Chronologique
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
