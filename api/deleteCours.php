<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_POST['id_cours'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID manquant']);
    exit();
}

$id_cours = $_POST['id_cours'];

try {
    $pdo->beginTransaction();

    $coursStmt = $pdo->prepare("SELECT id_professeur FROM cours WHERE id_cours = ?");
    $coursStmt->execute([$id_cours]);
    $cours = $coursStmt->fetch(PDO::FETCH_ASSOC);
    $id_professeur = $cours && !empty($cours['id_professeur']) ? (int)$cours['id_professeur'] : null;
    
    // 1️⃣ Récupérer tous les horaires du cours à supprimer
    $selectSQL = "SELECT id_horaire, id_salle FROM horaires WHERE id_cours = ?";
    $selectStmt = $pdo->prepare($selectSQL);
    $selectStmt->execute([$id_cours]);
    $horaires = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2️⃣ Supprimer les horaires du cours
    $deleteHorairesSQL = "DELETE FROM horaires WHERE id_cours = ?";
    $deleteHorairesStmt = $pdo->prepare($deleteHorairesSQL);
    $deleteHorairesStmt->execute([$id_cours]);
    
    // 3️⃣ Pour chaque salle libérée, vérifier s'il y a d'autres horaires
    foreach ($horaires as $horaire) {
        $id_salle = $horaire['id_salle'];
        
        // Vérifier si cette salle a d'autres horaires
        $checkSQL = "SELECT COUNT(*) as count FROM horaires WHERE id_salle = ?";
        $checkStmt = $pdo->prepare($checkSQL);
        $checkStmt->execute([$id_salle]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Si aucun autre horaire, marquer la salle comme libre
        if ($result['count'] == 0) {
            $updateEtatSQL = "UPDATE etat_salles SET etat = 'LIBRE', date_update = NOW(), modified_by = 'Admin' WHERE id_salle = ?";
            $updateEtatStmt = $pdo->prepare($updateEtatSQL);
            $updateEtatStmt->execute([$id_salle]);
            
            // Ajouter à l'historique (optionnel)
            try {
                $histSQL = "INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison) 
                           VALUES (?, 'RÉSERVÉE', 'LIBRE', 'Admin', 'Suppression du cours et de tous ses horaires')";
                $histStmt = $pdo->prepare($histSQL);
                $histStmt->execute([$id_salle]);
            } catch (Exception $e) {
                // Historique optionnel - on continue
            }
        }
    }
    
    // 4️⃣ Supprimer le cours
    $deleteCoursSQL = "DELETE FROM cours WHERE id_cours = ?";
    $deleteCoursStmt = $pdo->prepare($deleteCoursSQL);
    $deleteCoursStmt->execute([$id_cours]);

    if ($id_professeur) {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE id_professeur = ?");
        $countStmt->execute([$id_professeur]);

        if ((int)$countStmt->fetchColumn() === 0) {
            $releaseUidStmt = $pdo->prepare("UPDATE professeurs SET uid_badge = NULL WHERE id_professeur = ?");
            $releaseUidStmt->execute([$id_professeur]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cours et horaires supprimés avec succès',
        'horaires_deleted' => count($horaires)
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur suppression: ' . $e->getMessage()
    ]);
}
?>
