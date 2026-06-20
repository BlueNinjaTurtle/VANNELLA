<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once 'course_professeur_helper.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id_cours']) || !isset($input['nom_cours']) || !isset($input['enseignant'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes (id_cours, nom_cours, enseignant requis)']);
    exit();
}

try {
    $id_departement = $input['id_departement'] ?? null;
    $uid_badge = normalizeOptionalUid($input['uid_badge'] ?? null);

    $pdo->beginTransaction();
    $currentStmt = $pdo->prepare("SELECT id_professeur FROM cours WHERE id_cours = ?");
    $currentStmt->execute([$input['id_cours']]);
    $currentCours = $currentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentCours) {
        throw new InvalidArgumentException("Cours introuvable");
    }

    $current_professeur_id = isset($currentCours['id_professeur']) ? (int)$currentCours['id_professeur'] : null;
    $id_professeur = resolveProfesseurForCours($pdo, $input['enseignant'], $uid_badge, $current_professeur_id);
    
    $stmt = $pdo->prepare("UPDATE cours SET nom_cours = ?, enseignant = ?, id_professeur = ?, id_departement = ? WHERE id_cours = ?");
    $stmt->execute([$input['nom_cours'], trim($input['enseignant']), $id_professeur, $id_departement, $input['id_cours']]);
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cours mis à jour avec succès'
    ]);
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
