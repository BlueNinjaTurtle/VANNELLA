<?php
/**
 * API: Force l'état d'une salle (Admin only)
 * 
 * Permet à l'administrateur de forcer manuellement l'état d'une salle
 * si par exemple elle affiche "occupée" mais est vide.
 * 
 * Requête:
 *   POST /api/forceEtat.php
 *   Content-Type: application/json
 *   {
 *     "id_salle": 2,
 *     "etat": "libre",
 *     "raison": "La salle était pleine, elle est maintenant vide" (optionnel)
 *   }
 * 
 * Réponse:
 *   {
 *     "status": "OK",
 *     "message": "...",
 *     "data": {
 *       "id_salle": 2,
 *       "nom_salle": "CISCO",
 *       "etat_ancien": "occupée",
 *       "etat_nouveau": "libre",
 *       "modified_by": "Admin",
 *       "timestamp": "2026-04-19 14:30:45"
 *     }
 *   }
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Vérification authentification Admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Accès refusé: authentification admin requise'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/attribution_helper.php';
    
    // Récupérer les données
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    $id_salle = isset($input['id_salle']) ? intval($input['id_salle']) : null;
    $etat = isset($input['etat']) ? strtolower(trim($input['etat'])) : null;
    $raison = isset($input['raison']) ? substr(trim($input['raison']), 0, 255) : 'Override admin';
    
    if ($id_salle === null || $id_salle <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'id_salle invalide ou manquant'
        ]);
        exit;
    }
    
    // Normaliser l'état
    if ($etat === 'occupee') $etat = 'occupée';
    if ($etat === 'reservee') $etat = 'réservée';
    
    // Valider l'état
    $etats_autorises = ['libre', 'occupée', 'occupee', 'réservée', 'reservee', 'indisponible'];
    if (!in_array($etat, $etats_autorises)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'État invalide. Autorisés: libre, occupée, réservée, indisponible'
        ]);
        exit;
    }
    
    // =========================================================================
    // VÉRIFIER QUE LA SALLE EXISTE
    // =========================================================================
    
    $check_salle = $pdo->prepare("SELECT id_salle, nom_salle FROM salles WHERE id_salle = ?");
    $check_salle->execute([$id_salle]);
    $salle = $check_salle->fetch();
    
    if (!$salle) {
        http_response_code(404);
        echo json_encode([
            'status' => 'ERROR',
            'message' => 'Salle non trouvée (id_salle: ' . $id_salle . ')'
        ]);
        exit;
    }
    
    // =========================================================================
    // RÉCUPÉRER L'ÉTAT ACTUEL (POUR HISTORIQUE)
    // =========================================================================
    
    $get_current = $pdo->prepare("SELECT etat FROM etat_salles WHERE id_salle = ?");
    $get_current->execute([$id_salle]);
    $current_row = $get_current->fetch();
    $etat_ancien = $current_row ? $current_row['etat'] : null;
    
    // =========================================================================
    // METTRE À JOUR L'ÉTAT
    // =========================================================================
    
    if ($current_row) {
        // UPDATE
        $update = $pdo->prepare("
            UPDATE etat_salles 
            SET etat = ?, modified_by = 'Admin', modified_at = NOW(), date_update = NOW() 
            WHERE id_salle = ?
        ");
        $update->execute([$etat, $id_salle]);
        $action = "mise à jour";
    } else {
        // INSERT
        $insert = $pdo->prepare("
            INSERT INTO etat_salles (id_salle, etat, modified_by, modified_at, date_update) 
            VALUES (?, ?, 'Admin', NOW(), NOW())
        ");
        $insert->execute([$id_salle, $etat]);
        $action = "création";
    }
    
    // =========================================================================
    // ENREGISTRER L'HISTORIQUE
    // =========================================================================
    
    $hist = $pdo->prepare("
        INSERT INTO etat_salles_history 
        (id_salle, etat_ancien, etat_nouveau, modified_by, raison) 
        VALUES (?, ?, ?, 'Admin', ?)
    ");
    $hist->execute([$id_salle, $etat_ancien, $etat, $raison]);

    $reattribution = null;
    if ($etat === 'libre') {
        $reattribution = attributionReactivateWaitingForSalle($pdo, (int)$id_salle, null, null, null, null, 'Admin');
    }
    
    // =========================================================================
    // RETOURNER LE SUCCÈS
    // =========================================================================
    
    http_response_code(200);
    echo json_encode([
        'status' => 'OK',
        'message' => 'État de la salle ' . $salle['nom_salle'] . ' forcé à ' . $etat,
        'data' => [
            'id_salle' => intval($id_salle),
            'nom_salle' => $salle['nom_salle'],
            'etat_ancien' => $etat_ancien,
            'etat_nouveau' => $etat,
            'modified_by' => 'Admin',
            'raison' => $raison,
            'horaire_reattribue' => $reattribution ? (int)$reattribution['id_horaire'] : null,
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Erreur base de données',
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
