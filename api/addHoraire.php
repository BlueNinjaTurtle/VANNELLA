<?php
header('Content-Type: application/json');
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

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_cours'], $data['id_promotion'], $data['jour'], $data['heure_debut'], $data['heure_fin'], $data['id_salle'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit;
}

try {
    $pdo->beginTransaction();

    $promoStmt = $pdo->prepare("SELECT nom_promotion, filiere, niveau FROM promotions WHERE id_promotion = ?");
    $promoStmt->execute([$data['id_promotion']]);
    $newPromo = $promoStmt->fetch();

    if (!$newPromo) {
        throw new Exception('Promotion non trouvée');
    }

    $conflictStmt = $pdo->prepare("
        SELECT h.id_horaire, p.nom_promotion, p.filiere, p.niveau
        FROM horaires h
        JOIN promotions p ON h.id_promotion = p.id_promotion
        WHERE h.id_salle = ?
          AND h.jour = ?
          AND COALESCE(NULLIF(h.statut, ''), 'actif') = 'actif'
          AND (
              (h.heure_debut <= ? AND h.heure_fin > ?) OR
              (h.heure_debut < ? AND h.heure_fin >= ?) OR
              (? <= h.heure_debut AND ? >= h.heure_fin)
          )
    ");
    $conflictStmt->execute([
        $data['id_salle'],
        $data['jour'],
        $data['heure_debut'],
        $data['heure_debut'],
        $data['heure_fin'],
        $data['heure_fin'],
        $data['heure_debut'],
        $data['heure_fin']
    ]);
    $conflicts = $conflictStmt->fetchAll();

    $newPriority = promotionPriority($newPromo);
    $isEnsemble = isPromotionEnsemble($newPromo);
    $canReplaceConflicts = $isEnsemble;

    if (!$canReplaceConflicts) {
        $canReplaceConflicts = true;
        foreach ($conflicts as $conflict) {
            if ($newPriority <= promotionPriority($conflict)) {
                $canReplaceConflicts = false;
                break;
            }
        }
    }

    if (!empty($conflicts) && !$canReplaceConflicts) {
        throw new Exception("Conflit d'horaire: cette salle est occupée par une promotion de priorité égale ou supérieure.");
    }

    if (!empty($conflicts)) {
        $conflictIds = array_column($conflicts, 'id_horaire');
        $placeholders = implode(',', array_fill(0, count($conflictIds), '?'));
        $annuleStmt = $pdo->prepare("UPDATE horaires SET statut = 'annule' WHERE id_horaire IN ($placeholders)");
        $annuleStmt->execute($conflictIds);
    }
    
    // Insérer l'horaire
    $sql = "INSERT INTO horaires (id_cours, id_promotion, jour, heure_debut, heure_fin, id_salle, type_cours)
            VALUES (:id_cours, :id_promotion, :jour, :heure_debut, :heure_fin, :id_salle, :type_cours)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id_cours' => $data['id_cours'],
        'id_promotion' => $data['id_promotion'],
        'jour' => $data['jour'],
        'heure_debut' => $data['heure_debut'],
        'heure_fin' => $data['heure_fin'],
        'id_salle' => $data['id_salle'],
        'type_cours' => $isEnsemble ? 'ensemble' : 'specifique'
    ]);
    
    // Mettre à jour l'état de la salle à "réservée" dans la table etat_salles
    $id_salle = $data['id_salle'];
    
    // Vérifier si l'entrée existe
    $check = $pdo->prepare("SELECT id_salle FROM etat_salles WHERE id_salle = ?");
    $check->execute([$id_salle]);
    
    if ($check->fetch()) {
        $stmtEtat = $pdo->prepare("UPDATE etat_salles SET etat = 'réservée', date_update = NOW() WHERE id_salle = ?");
    } else {
        $stmtEtat = $pdo->prepare("INSERT INTO etat_salles (id_salle, etat, date_update) VALUES (?, 'réservée', NOW())");
    }
    $stmtEtat->execute([$id_salle]);
    
    // Ajouter à l'historique si la table existe
    try {
        $hist = $pdo->prepare("INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison) VALUES (?, ?, 'réservée', 'System', 'Horaire associé par algorithme')");
        $hist->execute([$id_salle, 'libre']);
    } catch (Exception $e) {
        // Historique optionnel
    }
    
    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Horaire ajouté avec succès',
        'horaires_annules' => count($conflicts)
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
