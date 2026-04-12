<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$id_salle = $_GET['id_salle'] ?? null;

if (!$id_salle) {
    echo json_encode(['status' => 'error', 'message' => 'Salle requise']);
    exit;
}

try {
    // Récupérer la salle
    $stmtSalle = $pdo->prepare("SELECT * FROM salles WHERE id_salle = ?");
    $stmtSalle->execute([$id_salle]);
    $salle = $stmtSalle->fetch();

    if (!$salle) {
        echo json_encode(['status' => 'error', 'message' => 'Salle non trouvée']);
        exit;
    }

    // Récupérer les horaires pour chaque jour
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $occupation = [];

    foreach ($jours as $jour) {
        $stmtHoraires = $pdo->prepare("
            SELECT h.*, c.nom_cours, p.nom_promotion
            FROM horaires h
            JOIN cours c ON h.id_cours = c.id_cours
            JOIN promotions p ON h.id_promotion = p.id_promotion
            WHERE h.id_salle = ? AND h.jour = ?
            ORDER BY h.heure_debut
        ");
        $stmtHoraires->execute([$id_salle, $jour]);
        $horaires = $stmtHoraires->fetchAll();

        $occupation[$jour] = [
            'libre' => count($horaires) === 0,
            'horaires' => $horaires
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'salle' => $salle,
            'occupation' => $occupation,
            'jours' => $jours
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
