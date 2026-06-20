<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$id_salle = $_GET['id_salle'] ?? null;
$jour = $_GET['jour'] ?? 'Lundi';

if (!$id_salle) {
    echo json_encode(['status' => 'error', 'message' => 'Salle requise']);
    exit;
}

// Mapper les jours français
$jours_map = [
    'lundi' => 'Lundi',
    'mardi' => 'Mardi',
    'mercredi' => 'Mercredi',
    'jeudi' => 'Jeudi',
    'vendredi' => 'Vendredi',
    'samedi' => 'Samedi'
];

$jour_fr = $jours_map[strtolower($jour)] ?? 'Lundi';

try {
    // Récupérer la salle
    $stmtSalle = $pdo->prepare("SELECT * FROM salles WHERE id_salle = ?");
    $stmtSalle->execute([$id_salle]);
    $salle = $stmtSalle->fetch();

    if (!$salle) {
        echo json_encode(['status' => 'error', 'message' => 'Salle non trouvée']);
        exit;
    }

    // Récupérer les horaires de la salle pour ce jour
    if (strtolower($jour) === 'dimanche') {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'salle' => $salle,
                'jour' => 'Dimanche',
                'creneaux' => []
            ],
            'message' => "Le dimanche n'est pas un jour de cours"
        ]);
        exit;
    }

    $stmtHoraires = $pdo->prepare("
        SELECT h.*, c.nom_cours, p.nom_promotion
        FROM horaires h
        JOIN cours c ON h.id_cours = c.id_cours
        JOIN promotions p ON h.id_promotion = p.id_promotion
        WHERE h.id_salle = ?
          AND h.jour = ?
          AND COALESCE(NULLIF(h.statut, ''), 'actif') = 'actif'
        ORDER BY h.heure_debut
    ");
    $stmtHoraires->execute([$id_salle, $jour_fr]);
    $horaires = $stmtHoraires->fetchAll();

    // Générer les créneaux horaires (08:00 - 17:15)
    $creneaux = [];
    $heure_debut = new DateTime('08:00');
    $heure_fin = new DateTime('17:15');

    // Pauses (heures de début)
    $pauses = ['10:15', '12:45'];
    $pauses_durees = ['10:15-10:30', '12:45-14:00'];

    while ($heure_debut < $heure_fin) {
        $heure_str = $heure_debut->format('H:i');
        
        // Vérifier si c'est une pause
        $est_pause = false;
        $pause_label = '';
        
        if (in_array($heure_str, $pauses)) {
            if ($heure_str === '10:15') {
                $est_pause = true;
                $pause_label = '10:15 - 10:30 (Pause)';
                $heure_debut->add(new DateInterval('PT15M'));
            } elseif ($heure_str === '12:45') {
                $est_pause = true;
                $pause_label = '12:45 - 14:00 (Pause)';
                $heure_debut->add(new DateInterval('PT75M'));
            }
        } else {
            // Créneau normal de 45 min
            $heure_fin_creneau = clone $heure_debut;
            $heure_fin_creneau->add(new DateInterval('PT45M'));
            
            $creneau_label = $heure_str . ' - ' . $heure_fin_creneau->format('H:i');
            
            // Vérifier si ce créneau est occupé
            $occupe = false;
            $cours_info = '';
            
            foreach ($horaires as $h) {
                if ($h['heure_debut'] === $heure_str && $h['heure_fin'] === $heure_fin_creneau->format('H:i')) {
                    $occupe = true;
                    $cours_info = $h['nom_cours'] . ' - ' . $h['nom_promotion'];
                    break;
                }
            }
            
            $creneaux[] = [
                'heure' => $creneau_label,
                'occupe' => $occupe,
                'cours' => $cours_info,
                'est_pause' => false
            ];
            
            $heure_debut->add(new DateInterval('PT45M'));
        }
        
        if ($est_pause) {
            $creneaux[] = [
                'heure' => $pause_label,
                'occupe' => false,
                'cours' => '',
                'est_pause' => true
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'salle' => $salle,
            'jour' => $jour_fr,
            'creneaux' => $creneaux
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
