<?php
// api/addSalle.php
header('Content-Type: application/json');
require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['nom_salle']) || !isset($input['capacite']) || !isset($input['batiment'])) {
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes']);
    exit();
}

try {
    $pdo->beginTransaction();

    // ✅ Insérer la nouvelle salle
    $stmt = $pdo->prepare("INSERT INTO salles (nom_salle, capacite, batiment) VALUES (?, ?, ?)");
    if (!$stmt->execute([$input['nom_salle'], $input['capacite'], $input['batiment']])) {
        throw new Exception('Erreur insertion salle: ' . implode(' ', $stmt->errorInfo()));
    }
    $id_salle = $pdo->lastInsertId();
    
    if (empty($id_salle)) {
        throw new Exception('Impossible de récupérer l\'ID de la nouvelle salle');
    }

    // ✅ Initialiser l'état de la salle (simple, sans colonnes optionnelles)
    $stmt2 = $pdo->prepare("INSERT INTO etat_salles (id_salle, etat) VALUES (?, 'libre')");
    if (!$stmt2->execute([$id_salle])) {
        throw new Exception('Erreur init état: ' . implode(' ', $stmt2->errorInfo()));
    }
    
    // ✅ Initialiser l'historique si la table existe
    try {
        $stmt3 = $pdo->prepare("INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison) VALUES (?, NULL, 'libre', 'System', 'Création salle')");
        $stmt3->execute([$id_salle]);
    } catch (Exception $e) {
        // Table historique peut ne pas exister, ce n'est pas critique
        error_log('Historique non disponible: ' . $e->getMessage());
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Salle ajoutée avec succès! ✓', 'id_salle' => (int)$id_salle]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
