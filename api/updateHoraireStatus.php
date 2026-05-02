<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id_horaire'], $data['statut'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Données incomplètes (id_horaire et statut requis)']);
    exit;
}

$id_horaire = $data['id_horaire'];
$statut = strtolower(trim($data['statut']));
$statut = str_replace(['é', 'è', 'ê', 'ë'], 'e', $statut);

// Valider que le statut est valide
$statuts_valides = ['actif', 'annule'];
if (!in_array($statut, $statuts_valides)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Statut invalide. Doit être "actif" ou "annule"']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Récupérer les infos de l'horaire avant mise à jour
    $sql = "SELECT id_salle, statut as ancien_statut FROM horaires WHERE id_horaire = ?";
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
    $ancien_statut = $horaire['ancien_statut'];
    
    // Mettre à jour le statut de l'horaire
    $updateSQL = "UPDATE horaires SET statut = ? WHERE id_horaire = ?";
    $updateStmt = $pdo->prepare($updateSQL);
    $updateStmt->execute([$statut, $id_horaire]);
    
    // Si statut = 'annulé', marquer la salle comme libre (sauf si d'autres horaires actifs existent)
    if ($statut === 'annule') {
        // Vérifier s'il y a d'autres horaires ACTIFS pour cette salle
        $checkSQL = "SELECT COUNT(*) as count FROM horaires WHERE id_salle = ? AND statut = 'actif'";
        $checkStmt = $pdo->prepare($checkSQL);
        $checkStmt->execute([$id_salle]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Si aucun horaire actif, marquer la salle comme libre
        if ($result['count'] == 0) {
            $updateEtatSQL = "UPDATE etat_salles SET etat = 'libre', date_update = NOW(), modified_by = 'Admin' WHERE id_salle = ?";
            $updateEtatStmt = $pdo->prepare($updateEtatSQL);
            $updateEtatStmt->execute([$id_salle]);
            
            // Historique
            try {
                $histSQL = "INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison) VALUES (?, 'réservée', 'libre', 'Admin', 'Cours annulé')";
                $histStmt = $pdo->prepare($histSQL);
                $histStmt->execute([$id_salle]);
            } catch (Exception $e) {
                // Historique optionnel
            }
        }
    } else if (($ancien_statut === 'annulé' || $ancien_statut === 'annule') && $statut === 'actif') {
        // Si on réactive un cours, marquer la salle comme réservée
        $updateEtatSQL = "UPDATE etat_salles SET etat = 'réservée', date_update = NOW(), modified_by = 'Admin' WHERE id_salle = ?";
        $updateEtatStmt = $pdo->prepare($updateEtatSQL);
        $updateEtatStmt->execute([$id_salle]);
        
        // Historique
        try {
            $histSQL = "INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison) VALUES (?, 'libre', 'réservée', 'Admin', 'Cours réactivé')";
            $histStmt = $pdo->prepare($histSQL);
            $histStmt->execute([$id_salle]);
        } catch (Exception $e) {
            // Historique optionnel
        }
    }
    
    $pdo->commit();
    echo json_encode([
        'status' => 'success', 
        'message' => 'Statut de l\'horaire mis à jour avec succès',
        'new_statut' => $statut
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur BD: ' . $e->getMessage()]);
}
?>
