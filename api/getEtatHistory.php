<?php
/**
 * API: Récupère l'historique des changements d'état d'une salle
 * 
 * Requête:
 *   GET /api/getEtatHistory.php?id_salle=2&limit=50
 * 
 * Réponse:
 *   {
 *     "status": "success",
 *     "data": [
 *       {
 *         "id": 1,
 *         "id_salle": 2,
 *         "nom_salle": "CISCO",
 *         "etat_ancien": "libre",
 *         "etat_nouveau": "occupée",
 *         "modified_by": "IoT",
 *         "raison": "Changement détecté par Arduino",
 *         "timestamp": "2026-04-19 14:30:45"
 *       },
 *       ...
 *     ],
 *     "total": 15
 *   }
 */

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/db.php';
    
    // Récupérer les paramètres
    $id_salle = isset($_GET['id_salle']) ? intval($_GET['id_salle']) : null;
    $limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 500) : 50; // Max 500 pour pas surcharger
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    // Validation
    if ($id_salle === null || $id_salle <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Paramètre id_salle invalide ou manquant'
        ]);
        exit;
    }
    
    // =========================================================================
    // VÉRIFIER QUE LA SALLE EXISTE
    // =========================================================================
    
    $check = $pdo->prepare("SELECT nom_salle FROM salles WHERE id_salle = ?");
    $check->execute([$id_salle]);
    $salle = $check->fetch();
    
    if (!$salle) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Salle non trouvée (id_salle: ' . $id_salle . ')'
        ]);
        exit;
    }
    
    // =========================================================================
    // RÉCUPÉRER L'HISTORIQUE
    // =========================================================================
    
    $query = "
        SELECT 
            eh.id,
            eh.id_salle,
            :nom_salle as nom_salle,
            eh.etat_ancien,
            eh.etat_nouveau,
            eh.modified_by,
            eh.raison,
            eh.timestamp
        FROM etat_salles_history eh
        WHERE eh.id_salle = ?
        ORDER BY eh.timestamp DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_salle, $limit, $offset]);
    $historique = $stmt->fetchAll();
    
    // =========================================================================
    // RÉCUPÉRER LE TOTAL
    // =========================================================================
    
    $count_query = "SELECT COUNT(*) as total FROM etat_salles_history WHERE id_salle = ?";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute([$id_salle]);
    $count = $count_stmt->fetch()['total'];
    
    // =========================================================================
    // RETOURNER LE RÉSULTAT
    // =========================================================================
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $historique,
        'pagination' => [
            'total' => intval($count),
            'limit' => $limit,
            'offset' => $offset,
            'pages' => ceil($count / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()
    ]);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur générale',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>
