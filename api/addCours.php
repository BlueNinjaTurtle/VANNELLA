<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once 'course_professeur_helper.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['nom_cours']) || !isset($input['enseignant'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes (nom_cours et enseignant requis)']);
    exit();
}

try {
    $id_departement = $input['id_departement'] ?? null;
    $uid_badge = normalizeOptionalUid($input['uid_badge'] ?? null);

    $pdo->beginTransaction();
    $id_professeur = resolveProfesseurForCours($pdo, $input['enseignant'], $uid_badge);
    
    $stmt = $pdo->prepare("INSERT INTO cours (nom_cours, enseignant, id_professeur, id_departement) VALUES (?, ?, ?, ?)");
    $stmt->execute([$input['nom_cours'], trim($input['enseignant']), $id_professeur, $id_departement]);
    
    $id_cours = $pdo->lastInsertId();
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Cours ajouté avec succès',
        'id_cours' => (int)$id_cours
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
