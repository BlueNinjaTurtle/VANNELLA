<?php
/**
 * api/optimize.php
 * Algorithme d'attribution optimale de salle.
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

function promotionPriority(array $promotion): int {
    $label = ($promotion['nom_promotion'] ?? '') . ' ' . ($promotion['niveau'] ?? '');
    if (preg_match('/bac\s*\+?\s*([1-4])/i', $label, $matches)) {
        return (int)$matches[1];
    }

    if (preg_match('/(?:licence|l|graduat|g)\s*([1-4])/i', $label, $matches)) {
        return (int)$matches[1];
    }
    return 0;
}

function isPromotionEnsemble(array $promotion): bool {
    $label = strtolower(($promotion['nom_promotion'] ?? '') . ' ' . ($promotion['filiere'] ?? ''));
    return strpos($label, 'toutes') !== false || strpos($label, 'tous') !== false;
}

function canReplaceConflict(array $newPromotion, string $newTypeCours, array $conflict): bool {
    $newIsEnsemble = $newTypeCours === 'ensemble';
    $conflictIsEnsemble = ($conflict['type_cours'] ?? 'specifique') === 'ensemble';

    if ($newIsEnsemble && !$conflictIsEnsemble) {
        return true;
    }

    if (!$newIsEnsemble && $conflictIsEnsemble) {
        return false;
    }

    return promotionPriority($newPromotion) > promotionPriority($conflict);
}

function minutesFromTime(string $time): int {
    [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));
    return $hours * 60 + $minutes;
}

function timeFromMinutes(int $minutes): string {
    return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
}

function courseWindows(): array {
    return [
        ['start' => 8 * 60, 'end' => 12 * 60 + 15],
        ['start' => 14 * 60, 'end' => 18 * 60 + 15],
    ];
}

function slotFitsCourseWindowsByMinutes(int $startMinutes, int $endMinutes): bool {
    if ($endMinutes <= $startMinutes) {
        return false;
    }

    foreach (courseWindows() as $window) {
        if ($startMinutes >= $window['start'] && $endMinutes <= $window['end']) {
            return true;
        }
    }

    return false;
}

function slotFitsCourseWindows(string $heureDebut, string $heureFin): bool {
    return slotFitsCourseWindowsByMinutes(minutesFromTime($heureDebut), minutesFromTime($heureFin));
}

function slotIsPast(string $dateCours, string $heureDebut): bool {
    return strtotime($dateCours . ' ' . substr($heureDebut, 0, 5)) <= time();
}

function frenchDayName(DateTime $date): string {
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    return $jours[(int)$date->format('w')];
}

function isCourseDay(string $dateCours, ?string $jour = null): bool {
    $date = new DateTime($dateCours);
    $dayFromDate = frenchDayName($date);
    $dayValue = trim((string)$jour);

    return $dayFromDate !== 'Dimanche' && strcasecmp($dayValue, 'Dimanche') !== 0;
}

function roomHasActiveConflict(PDO $pdo, int $idSalle, string $dateCours, string $jour, string $heureDebut, string $heureFin): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM horaires h
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
    $stmt->execute([
        $idSalle,
        $dateCours,
        $jour,
        $heureDebut,
        $heureDebut,
        $heureFin,
        $heureFin,
        $heureDebut,
        $heureFin
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function buildSlotSuggestions(PDO $pdo, array $salles, string $dateCours, string $heureDebut, string $heureFin, int $limit = 6): array {
    if (!$salles) {
        return [];
    }

    $duration = minutesFromTime($heureFin) - minutesFromTime($heureDebut);
    if ($duration <= 0) {
        return [];
    }

    $requestedStart = minutesFromTime($heureDebut);
    $candidateStarts = array_unique([
        $requestedStart,
        8 * 60,
        9 * 60,
        10 * 60,
        11 * 60,
        14 * 60,
        15 * 60,
        16 * 60,
        17 * 60
    ]);
    sort($candidateStarts);

    $baseDate = new DateTime($dateCours);
    $suggestions = [];
    $seen = [];

    for ($offset = 0; $offset < 7 && count($suggestions) < $limit; $offset++) {
        $candidateDate = clone $baseDate;
        $candidateDate->modify("+{$offset} day");
        $candidateDateValue = $candidateDate->format('Y-m-d');
        $candidateJour = frenchDayName($candidateDate);

        if ($candidateJour === 'Dimanche') {
            continue;
        }

        foreach ($candidateStarts as $startMinutes) {
            $endMinutes = $startMinutes + $duration;
            if (!slotFitsCourseWindowsByMinutes($startMinutes, $endMinutes)) {
                continue;
            }

            $candidateDebut = timeFromMinutes($startMinutes);
            $candidateFin = timeFromMinutes($endMinutes);

            if (slotIsPast($candidateDateValue, $candidateDebut)) {
                continue;
            }

            foreach ($salles as $salle) {
                $idSalle = (int)$salle['id_salle'];
                if (roomHasActiveConflict($pdo, $idSalle, $candidateDateValue, $candidateJour, $candidateDebut, $candidateFin)) {
                    continue;
                }

                $key = $candidateDateValue . '|' . $candidateDebut . '|' . $candidateFin . '|' . $idSalle;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;

                $suggestions[] = [
                    'id_salle' => $idSalle,
                    'nom_salle' => $salle['nom_salle'],
                    'capacite' => (int)$salle['capacite'],
                    'batiment' => $salle['batiment'],
                    'date_cours' => $candidateDateValue,
                    'jour' => $candidateJour,
                    'heure_debut' => $candidateDebut,
                    'heure_fin' => $candidateFin
                ];

                if (count($suggestions) >= $limit) {
                    break 3;
                }
            }
        }
    }

    return $suggestions;
}

$id_promotion = $_GET['id_promotion'] ?? null;
$date_cours = $_GET['date_cours'] ?? date('Y-m-d');
$jour = $_GET['jour'] ?? null;
$heure_debut = $_GET['heure_debut'] ?? null;
$heure_fin = $_GET['heure_fin'] ?? null;

if (!$id_promotion || !$jour || !$heure_debut || !$heure_fin) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parametres manquants']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_cours)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Date de cours invalide']);
    exit;
}

if (!isCourseDay($date_cours, $jour)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Horaire invalide: le dimanche n'est pas un jour de cours"]);
    exit;
}

if (!slotFitsCourseWindows($heure_debut, $heure_fin)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Horaire invalide: utilisez 08:00-12:15 ou 14:00-18:15']);
    exit;
}

if (slotIsPast($date_cours, $heure_debut)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Horaire invalide: ce creneau est deja depasse']);
    exit;
}

try {
    $stmtPromo = $pdo->prepare("SELECT nom_promotion, filiere, niveau, effectif FROM promotions WHERE id_promotion = ?");
    $stmtPromo->execute([$id_promotion]);
    $promo = $stmtPromo->fetch();

    if (!$promo) {
        echo json_encode(['status' => 'error', 'message' => 'Promotion non trouvee']);
        exit;
    }

    $effectif = (int)$promo['effectif'];
    $isEnsemble = isPromotionEnsemble($promo);
    $newTypeCours = $isEnsemble ? 'ensemble' : 'specifique';

    $sqlSallesDispo = "
        SELECT s.* FROM salles s
        WHERE s.capacite >= ?
        AND s.id_salle NOT IN (
            SELECT DISTINCT h.id_salle FROM horaires h
            WHERE h.date_cours = ?
            AND h.jour = ?
            AND COALESCE(NULLIF(h.statut, ''), 'actif') IN ('actif', 'en_cours')
            AND (
                (h.heure_debut <= ? AND h.heure_fin > ?) OR
                (h.heure_debut < ? AND h.heure_fin >= ?) OR
                (? <= h.heure_debut AND ? >= h.heure_fin)
            )
        )
        ORDER BY s.capacite ASC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sqlSallesDispo);
    $stmt->execute([
        $effectif,
        $date_cours,
        $jour,
        $heure_debut,
        $heure_debut,
        $heure_fin,
        $heure_fin,
        $heure_debut,
        $heure_fin
    ]);

    $bestSalle = $stmt->fetch();
    $salles = [];

    if (!$bestSalle) {
        $stmtSalles = $pdo->prepare("
            SELECT s.* FROM salles s
            WHERE s.capacite >= ?
            ORDER BY s.capacite ASC
        ");
        $stmtSalles->execute([$effectif]);
        $salles = $stmtSalles->fetchAll();

        $sqlConflicts = "
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
        ";
        $stmtConflicts = $pdo->prepare($sqlConflicts);

        foreach ($salles as $salle) {
            $stmtConflicts->execute([
                $salle['id_salle'],
                $date_cours,
                $jour,
                $heure_debut,
                $heure_debut,
                $heure_fin,
                $heure_fin,
                $heure_debut,
                $heure_fin
            ]);
            $conflicts = $stmtConflicts->fetchAll();

            if (empty($conflicts)) {
                $bestSalle = $salle;
                break;
            }

            $canReplace = true;
            foreach ($conflicts as $conflict) {
                if (!canReplaceConflict($promo, $newTypeCours, $conflict)) {
                    $canReplace = false;
                    break;
                }
            }

            if ($canReplace) {
                $salle['replaces_existing'] = true;
                $bestSalle = $salle;
                break;
            }
        }
    }

    $suggestions = [];

    if (!$bestSalle && !empty($salles)) {
        $bestSalle = $salles[0];
        $bestSalle['pending_assignment'] = true;
        $suggestions = buildSlotSuggestions($pdo, $salles, $date_cours, $heure_debut, $heure_fin);
    }

    if (!$bestSalle) {
        echo json_encode(['status' => 'error', 'message' => 'Aucune salle ne peut contenir cet effectif']);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $bestSalle, 'suggestions' => $suggestions]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
