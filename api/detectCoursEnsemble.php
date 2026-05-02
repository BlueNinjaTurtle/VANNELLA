<?php
header('Content-Type: application/json');
require_once '../config/db.php';

/**
 * API: Détecter les cours d'ensemble
 * Un cours d'ensemble = même cours, même salle, même horaire pour TOUTES les promotions L1 du même département
 * Requête GET: /api/detectCoursEnsemble.php
 */

try {
    $pdo->beginTransaction();
    
    // 1. Récupérer les groupes d'horaires (même cours, même salle, même heure)
    $sql = "
        SELECT 
            h.id_cours,
            h.jour,
            h.heure_debut,
            h.heure_fin,
            h.id_salle,
            p.id_departement,
            COUNT(DISTINCT h.id_promotion) as count_promotions
        FROM horaires h
        JOIN promotions p ON h.id_promotion = p.id_promotion
        WHERE h.statut = 'actif'
        AND p.niveau LIKE '%1%'
        GROUP BY h.id_cours, h.jour, h.heure_debut, h.heure_fin, h.id_salle, p.id_departement
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $coursEnsemble = [];
    $horairesModifies = 0;
    
    foreach ($groupes as $groupe) {
        // Compter le nombre TOTAL de promotions L1 dans ce département
        $sqlCount = "
            SELECT COUNT(DISTINCT id_promotion) as total_l1
            FROM promotions 
            WHERE id_departement = ?
            AND niveau LIKE '%1%'
        ";
        
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute([$groupe['id_departement']]);
        $resultCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $totalL1 = $resultCount['total_l1'];
        
        // Si le nombre de promotions avec ce cours = nombre total de L1, c'est un cours d'ensemble
        if ($groupe['count_promotions'] >= $totalL1 && $totalL1 > 0) {
            $coursEnsemble[] = [
                'id_cours' => (int)$groupe['id_cours'],
                'id_salle' => (int)$groupe['id_salle'],
                'jour' => $groupe['jour'],
                'heure_debut' => $groupe['heure_debut'],
                'heure_fin' => $groupe['heure_fin'],
                'id_departement' => (int)$groupe['id_departement'],
                'promotions_count' => (int)$groupe['count_promotions'],
                'total_l1' => (int)$totalL1
            ];
            
            // Mettre à jour tous les horaires correspondants à 'ensemble'
            $updateSQL = "
                UPDATE horaires h
                SET h.type_cours = 'ensemble'
                WHERE h.id_cours = ?
                AND h.jour = ?
                AND h.heure_debut = ?
                AND h.heure_fin = ?
                AND h.id_salle = ?
                AND h.statut = 'actif'
                AND h.id_promotion IN (
                    SELECT id_promotion FROM promotions WHERE id_departement = ? AND niveau LIKE '%1%'
                )
            ";
            
            $stmtUpdate = $pdo->prepare($updateSQL);
            $stmtUpdate->execute([
                $groupe['id_cours'],
                $groupe['jour'],
                $groupe['heure_debut'],
                $groupe['heure_fin'],
                $groupe['id_salle'],
                $groupe['id_departement']
            ]);
            
            $horairesModifies += $stmtUpdate->rowCount();
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Détection des cours d\'ensemble complétée',
        'cours_ensemble_detectes' => count($coursEnsemble),
        'horaires_modifies' => $horairesModifies,
        'data' => $coursEnsemble
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur BD: ' . $e->getMessage()]);
}
