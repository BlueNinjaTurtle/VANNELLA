<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_horaire'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID horaire manquant']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $id_horaire = $data['id_horaire'];
    
    // Récupérer l'horaire avant suppression pour libérer la salle
    $sql = "SELECT id_salle FROM horaires WHERE id_horaire = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_horaire]);
    $horaire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horaire) {
        http_response_code(404);
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Horaire non trouvé']);
        exit;
    }
    
    $id_salle = $horaire['id_salle'];
    
    // Supprimer l'horaire
    $deleteSQL = "DELETE FROM horaires WHERE id_horaire = ?";
    $deleteStmt = $pdo->prepare($deleteSQL);
    $deleteStmt->execute([$id_horaire]);
    
    // Vérifier s'il y a d'autres horaires pour cette salle
    $checkSQL = "SELECT COUNT(*) as count FROM horaires WHERE id_salle = ?";
    $checkStmt = $pdo->prepare($checkSQL);
    $checkStmt->execute([$id_salle]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Si aucun autre horaire, marquer la salle comme libre
    if ($result['count'] == 0) {
        $updateEtatSQL = "UPDATE etat_salles SET etat = 'libre', date_update = NOW(), modified_by = 'Admin' WHERE id_salle = ?";
        $updateEtatStmt = $pdo->prepare($updateEtatSQL);
        $updateEtatStmt->execute([$id_salle]);
        
        // Ajouter à l'historique
        try {
            $histSQL = "INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison) VALUES (?, 'réservée', 'libre', 'Admin', 'Suppression d\\'horaire')";
            $histStmt = $pdo->prepare($histSQL);
            $histStmt->execute([$id_salle]);
        } catch (Exception $e) {
            // Historique optionnel
        }
    }
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Horaire supprimé avec succès']);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur BD: ' . $e->getMessage()]);
}
?>
