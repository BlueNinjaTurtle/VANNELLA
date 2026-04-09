<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$id_promotion = $_GET['id_promotion'] ?? null;
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('Monday this week'));

if (!$id_promotion) {
    echo json_encode(['status' => 'error', 'message' => 'Promotion requise']);
    exit;
}

try {
    // Calculer la date de fin (samedi)
    $start = new DateTime($start_date);
    $end = new DateTime($start_date);
    $end->modify('+6 days');

    // Récupérer les jours de la semaine (Lundi à Samedi)
    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    
    // Récupérer tous les horaires pour cette promotion cette semaine
    $sql = "
        SELECT 
            h.*,
            c.nom_cours,
            c.enseignant,
            s.nom_salle,
            s.capacite,
            s.batiment,
            p.nom_promotion,
            p.effectif
        FROM horaires h
        JOIN cours c ON h.id_cours = c.id_cours
        JOIN salles s ON h.id_salle = s.id_salle
        JOIN promotions p ON h.id_promotion = p.id_promotion
        WHERE h.id_promotion = ?
        AND h.jour IN ('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi')
        ORDER BY FIELD(h.jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
                 h.heure_debut
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_promotion]);
    $horaires = $stmt->fetchAll();

    // Structurer par jour
    $planning = [];
    foreach ($jours as $jour) {
        $planning[$jour] = [];
        foreach ($horaires as $h) {
            if ($h['jour'] === $jour) {
                $planning[$jour][] = $h;
            }
        }
    }

    // Récupérer toutes les salles disponibles et leur état
    $stmtSalles = $pdo->query("
        SELECT s.*, 
               COALESCE(e.etat, 'libre') as etat
        FROM salles s
        LEFT JOIN etat_salles e ON s.id_salle = e.id_salle
        ORDER BY s.nom_salle
    ");
    $toutes_salles = $stmtSalles->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'start_date_display' => $start->format('d/m/Y'),
            'end_date_display' => $end->format('d/m/Y'),
            'planning' => $planning,
            'toutes_salles' => $toutes_salles,
            'jours' => $jours
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
