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

try {
    $stmtPromo = $pdo->prepare("SELECT nom_promotion, filiere, niveau, effectif FROM promotions WHERE id_promotion = ?");
    $stmtPromo->execute([$id_promotion]);
    $promo = $stmtPromo->fetch();

    if (!$promo) {
        echo json_encode(['status' => 'error', 'message' => 'Promotion non trouvee']);
        exit;
    }

    $effectif = (int)$promo['effectif'];
    $newPriority = promotionPriority($promo);
    $isEnsemble = isPromotionEnsemble($promo);

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
            SELECT h.id_horaire, p.nom_promotion, p.filiere, p.niveau
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
                if (!$isEnsemble && $newPriority <= promotionPriority($conflict)) {
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

    if (!$bestSalle && !empty($salles)) {
        $bestSalle = $salles[0];
        $bestSalle['pending_assignment'] = true;
    }

    if (!$bestSalle) {
        echo json_encode(['status' => 'error', 'message' => 'Aucune salle ne peut contenir cet effectif']);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $bestSalle]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
