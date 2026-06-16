<?php
/**
 * API - Récupérer les lecteurs RFID
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    // Récupérer les lecteurs RFID
    $stmt = $conn->prepare("
        SELECT
            l.id,
            l.id_lecteur,
            l.id_salle,
            l.nom_salle,
            l.localisation,
            l.date_ajout,
            l.actif,
            lr.timestamp as derniere_lecture,
            lr.id_carte
        FROM lecteurs_rfid l
        LEFT JOIN lectures_rfid lr ON l.id_lecteur = lr.id_lecteur
            AND lr.timestamp = (
                SELECT MAX(timestamp)
                FROM lectures_rfid
                WHERE id_lecteur = l.id_lecteur
            )
        ORDER BY l.id_salle ASC
    ");

    $stmt->execute();
    $result = $stmt->get_result();
    $lecteurs = [];

    while ($row = $result->fetch_assoc()) {
        $lecteurs[] = [
            'id' => $row['id'],
            'id_lecteur' => $row['id_lecteur'],
            'id_salle' => $row['id_salle'],
            'nom_salle' => $row['nom_salle'],
            'localisation' => $row['localisation'],
            'date_ajout' => $row['date_ajout'],
            'actif' => (bool)$row['actif'],
            'derniere_lecture' => $row['id_carte'] ? $row['id_carte'] . ' (' . $row['derniere_lecture'] . ')' : null
        ];
    }

    // Compter les cartes RFID
    $stmtCartes = $conn->query("SELECT COUNT(*) as total FROM cartes_rfid WHERE actif = TRUE");
    $totalCartes = $stmtCartes->fetch_assoc()['total'];

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'lecteurs' => $lecteurs,
            'total_lecteurs' => count($lecteurs),
            'total_cartes' => $totalCartes
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
