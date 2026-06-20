<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once __DIR__ . '/attribution_helper.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_cours'], $data['id_promotion'], $data['jour'], $data['heure_debut'], $data['heure_fin'], $data['id_salle'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Donnees incompletes']);
    exit;
}

try {
    $date_cours = $data['date_cours'] ?? date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_cours)) {
        throw new Exception('Date de cours invalide');
    }

    if (!attributionIsCourseDay($date_cours, $data['jour'])) {
        throw new Exception("Horaire invalide: le dimanche n'est pas un jour de cours.");
    }

    if (!attributionSlotFitsCourseWindows($data['heure_debut'], $data['heure_fin'])) {
        throw new Exception("Horaire invalide: les cours doivent rester dans 08:00-12:15 ou 14:00-18:15.");
    }

    if (attributionSlotIsPast($date_cours, $data['heure_debut'])) {
        throw new Exception("Horaire invalide: ce creneau est deja depasse.");
    }

    $statutHoraire = (($data['statut'] ?? '') === 'en_attente') ? 'en_attente' : 'actif';

    $pdo->beginTransaction();

    $promoStmt = $pdo->prepare("SELECT nom_promotion, filiere, niveau FROM promotions WHERE id_promotion = ?");
    $promoStmt->execute([$data['id_promotion']]);
    $newPromo = $promoStmt->fetch();

    if (!$newPromo) {
        throw new Exception('Promotion non trouvee');
    }

    $conflictStmt = $pdo->prepare("
        SELECT h.id_horaire, h.type_cours, p.nom_promotion, p.filiere, p.niveau
        FROM horaires h
        JOIN promotions p ON h.id_promotion = p.id_promotion
        WHERE h.id_salle = ?
          AND h.date_cours = ?
          AND h.jour = ?
          AND COALESCE(NULLIF(h.statut, ''), 'actif') IN ('actif', 'en_cours')
          AND (
              (h.heure_debut <= ? AND h.heure_fin > ?) OR
              (h.heure_debut < ? AND h.heure_fin >= ?) OR
              (? <= h.heure_debut AND ? >= h.heure_fin)
          )
    ");
    $conflictStmt->execute([
        $data['id_salle'],
        $date_cours,
        $data['jour'],
        $data['heure_debut'],
        $data['heure_debut'],
        $data['heure_fin'],
        $data['heure_fin'],
        $data['heure_debut'],
        $data['heure_fin']
    ]);
    $conflicts = $conflictStmt->fetchAll();

    $isEnsemble = attributionIsPromotionEnsemble($newPromo);
    $newTypeCours = $isEnsemble ? 'ensemble' : 'specifique';
    $canReplaceConflicts = true;

    foreach ($conflicts as $conflict) {
        if (!attributionCanReplaceConflict($newPromo, $newTypeCours, $conflict)) {
            $canReplaceConflicts = false;
            break;
        }
    }

    if (!empty($conflicts) && !$canReplaceConflicts && $statutHoraire !== 'en_attente') {
        throw new Exception("Conflit d'horaire: cette salle est occupee par une promotion de priorite egale ou superieure.");
    }

    $horairesMisEnAttente = 0;
    if (!empty($conflicts) && $statutHoraire !== 'en_attente') {
        $conflictIds = array_column($conflicts, 'id_horaire');
        $placeholders = implode(',', array_fill(0, count($conflictIds), '?'));
        $attenteStmt = $pdo->prepare("UPDATE horaires SET statut = 'en_attente' WHERE id_horaire IN ($placeholders)");
        $attenteStmt->execute($conflictIds);
        $horairesMisEnAttente = count($conflicts);
    }

    $stmt = $pdo->prepare("
        INSERT INTO horaires (id_cours, id_promotion, jour, date_cours, heure_debut, heure_fin, id_salle, type_cours, statut)
        VALUES (:id_cours, :id_promotion, :jour, :date_cours, :heure_debut, :heure_fin, :id_salle, :type_cours, :statut)
    ");
    $stmt->execute([
        'id_cours' => $data['id_cours'],
        'id_promotion' => $data['id_promotion'],
        'jour' => $data['jour'],
        'date_cours' => $date_cours,
        'heure_debut' => $data['heure_debut'],
        'heure_fin' => $data['heure_fin'],
        'id_salle' => $data['id_salle'],
        'type_cours' => $newTypeCours,
        'statut' => $statutHoraire
    ]);

    if ($statutHoraire !== 'en_attente') {
        attributionUpdateSalleEtat(
            $pdo,
            (int)$data['id_salle'],
            'réservée',
            'System',
            'Horaire associe par algorithme'
        );
    }

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => $statutHoraire === 'en_attente'
            ? 'Horaire ajoute en attente'
            : 'Horaire ajoute avec succes',
        'horaires_annules' => 0,
        'horaires_en_attente' => $statutHoraire === 'en_attente' ? 1 : $horairesMisEnAttente,
        'statut_horaire' => $statutHoraire
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
