<?php
header('Content-Type: application/json');
require_once '../config/db.php';

/**
 * API: Récupérer les cours par département
 * GET: /api/getCoursByDepartment.php?id_departement=1
 * Optionnel: ?include_departement=true (pour inclure les infos du département)
 */

try {
    $id_departement = $_GET['id_departement'] ?? null;
    $include_departement = $_GET['include_departement'] ?? false;
    
    if (!$id_departement) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'id_departement requis']);
        exit;
    }
    
    // Récupérer les cours du département
    $sql = "
        SELECT 
            c.id_cours,
            c.nom_cours,
            c.enseignant,
            c.id_professeur,
            p.uid_badge,
            c.id_departement";
    
    if ($include_departement) {
        $sql .= ", d.nom_departement";
    }
    
    $sql .= "
        FROM cours c
        LEFT JOIN professeurs p ON c.id_professeur = p.id_professeur
        LEFT JOIN departements d ON c.id_departement = d.id_departement
        WHERE c.id_departement = ? OR (c.id_departement IS NULL)
        ORDER BY 
            CASE WHEN c.id_departement = ? THEN 0 ELSE 1 END,
            c.nom_cours
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_departement, $id_departement]);
    $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'count' => count($cours),
        'data' => $cours
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur BD: ' . $e->getMessage()]);
}
?>
