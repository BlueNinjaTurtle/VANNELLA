<?php
/**
 * API IoT - Mise à jour de l'état des salles
 * 
 * Reçoit les mises à jour d'état provenant du bridge IoT Python
 * et met à jour la table etat_salles en base de données.
 * 
 * Paramètres GET :
 *   - id_salle : ID de la salle (integer) [REQUIS]
 *   - etat : État de la salle (libre, occupée, réservée) [REQUIS]
 * 
 * Réponse JSON :
 *   {
 *     "status": "OK|ERROR",
 *     "message": "Description",
 *     "data": {
 *       "id_salle": 2,
 *       "nom_salle": "CISCO",
 *       "etat": "occupée",
 *       "timestamp": "2026-04-19 14:30:45"
 *     }
 *   }
 * 
 * Exemples d'appel:
 *   GET /api/iot_update.php?id_salle=2&etat=occupée
 *   GET /api/iot_update.php?id_salle=1&etat=libre
 * 
 * @author Équipe IoT VANNELLA
 * @version 2.0
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Charger la configuration de base de données
    require_once __DIR__ . '/../config/db.php';
    
    // Récupérer les paramètres GET
    $id_salle = isset($_GET['id_salle']) ? intval($_GET['id_salle']) : null;
    $etat = isset($_GET['etat']) ? strtolower(trim($_GET['etat'])) : null;
    
    // =========================================================================
    // VALIDATION DES PARAMÈTRES
    // =========================================================================
    
    // Valider id_salle
    if ($id_salle === null || $id_salle <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Paramètre id_salle invalide ou manquant (doit être > 0)'
        ]);
        exit;
    }
    
    // Valider etat
    if ($etat === null || empty($etat)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Paramètre etat invalide ou manquant'
        ]);
        exit;
    }
    
    // États autorisés (avec variations possibles)
    $etats_autorises = ['libre', 'occupée', 'occupee', 'réservée', 'reservee'];
    if (!in_array($etat, $etats_autorises)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'État invalide. États autorisés: libre, occupée, réservée'
        ]);
        exit;
    }
    
    // Normaliser les états (gérer les accents)
    if ($etat === 'occupee') $etat = 'occupée';
    if ($etat === 'reservee') $etat = 'réservée';
    
    // =========================================================================
    // VÉRIFIER QUE LA SALLE EXISTE
    // =========================================================================
    
    $query_check = "SELECT id_salle, nom_salle FROM salles WHERE id_salle = ?";
    $stmt_check = $pdo->prepare($query_check);
    
    if (!$stmt_check->execute([$id_salle])) {
        throw new Exception("Erreur lors de la vérification de la salle: " . implode(", ", $stmt_check->errorInfo()));
    }
    
    $salle = $stmt_check->fetch();
    
    if (!$salle) {
        http_response_code(404);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Salle non trouvée (id_salle: ' . $id_salle . ')'
        ]);
        exit;
    }
    
    // =========================================================================
    // VÉRIFIER SI UN ENREGISTREMENT D'ÉTAT EXISTE
    // =========================================================================
    
    $query_check_etat = "SELECT id FROM etat_salles WHERE id_salle = ?";
    $stmt_check_etat = $pdo->prepare($query_check_etat);
    
    if (!$stmt_check_etat->execute([$id_salle])) {
        throw new Exception("Erreur lors de la vérification de l'état: " . implode(", ", $stmt_check_etat->errorInfo()));
    }
    
    $exists = $stmt_check_etat->rowCount() > 0;
    
    // =========================================================================
    // METTRE À JOUR OU INSÉRER L'ÉTAT
    // =========================================================================
    
    if ($exists) {
        // Récupérer l'état précédent pour l'historique
        $get_old = $pdo->prepare("SELECT etat FROM etat_salles WHERE id_salle = ?");
        $get_old->execute([$id_salle]);
        $old_state = $get_old->fetch()['etat'] ?? null;
        
        // UPDATE l'état existant
        $query = "UPDATE etat_salles 
                  SET etat = ?, modified_by = 'IoT', modified_at = NOW(), date_update = NOW() 
                  WHERE id_salle = ?";
        $stmt = $pdo->prepare($query);
        
        if (!$stmt->execute([$etat, $id_salle])) {
            throw new Exception("Erreur lors de la mise à jour: " . implode(", ", $stmt->errorInfo()));
        }
        
        // Enregistrer l'historique (si changement réel)
        if ($old_state !== $etat) {
            $hist = $pdo->prepare("
                INSERT INTO etat_salles_history 
                (id_salle, etat_ancien, etat_nouveau, modified_by, raison) 
                VALUES (?, ?, ?, 'IoT', ?)
            ");
            $hist->execute([$id_salle, $old_state, $etat, "Changement détecté par Arduino"]);
        }
        
        $action = "mise à jour";
    } else {
        // INSERT nouvel état
        $query = "INSERT INTO etat_salles (id_salle, etat, modified_by, modified_at, date_update) 
                  VALUES (?, ?, 'IoT', NOW(), NOW())";
        $stmt = $pdo->prepare($query);
        
        if (!$stmt->execute([$id_salle, $etat])) {
            throw new Exception("Erreur lors de l'insertion: " . implode(", ", $stmt->errorInfo()));
        }
        
        // Enregistrer l'historique (création)
        $hist = $pdo->prepare("
            INSERT INTO etat_salles_history 
            (id_salle, etat_ancien, etat_nouveau, modified_by, raison) 
            VALUES (?, NULL, ?, 'IoT', ?)
        ");
        $hist->execute([$id_salle, $etat, "Première détection par Arduino"]);
        
        $action = "création";
    }
    
    // =========================================================================
    // RETOURNER LE SUCCÈS
    // =========================================================================
    
    http_response_code(200);
    echo json_encode([
        'status' => 'OK',
        'message' => 'État de la salle ' . $salle['nom_salle'] . ' (' . $action . ') réussi',
        'data' => [
            'id_salle' => intval($id_salle),
            'nom_salle' => $salle['nom_salle'],
            'etat' => $etat,
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Erreur de base de données',
        'error' => $e->getMessage()
    ]);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Erreur générale',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>
