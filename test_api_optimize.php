<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST API OPTIMIZE ===\n\n";

header('Content-Type: application/json');
require_once 'config/db.php';

$id_promotion = 1;
$jour = 'Lundi';
$heure_debut = '08:00';
$heure_fin = '12:15';

echo "Paramètres:\n";
echo "- id_promotion: $id_promotion\n";
echo "- jour: $jour\n";
echo "- heure_debut: $heure_debut\n";
echo "- heure_fin: $heure_fin\n\n";

try {
    // 1. Vérifier la promotion
    echo "1. Récupération de la promotion...\n";
    $stmtPromo = $pdo->prepare("SELECT effectif FROM promotions WHERE id_promotion = ?");
    $stmtPromo->execute([$id_promotion]);
    $promo = $stmtPromo->fetch();
    
    if (!$promo) {
        echo "❌ Promotion non trouvée\n";
        exit;
    }
    
    $effectif = $promo['effectif'];
    echo "✅ Promotion trouvée - Effectif: $effectif\n\n";

    // 2. Tester la requête SQL
    echo "2. Exécution de la requête SQL...\n";
    $sqlSallesDispo = "
        SELECT * FROM salles 
        WHERE id_salle NOT IN (
            SELECT id_salle FROM horaires 
            WHERE jour = :jour 
            AND (
                (heure_debut <= :debut AND heure_fin > :debut) OR 
                (heure_debut < :fin AND heure_fin >= :fin) OR
                (:debut <= heure_debut AND :fin >= heure_fin)
            )
        )
        AND capacite >= :effectif
        ORDER BY capacite ASC 
        LIMIT 1
    ";

    echo "SQL: " . trim(preg_replace('/\s+/', ' ', $sqlSallesDispo)) . "\n\n";

    $stmt = $pdo->prepare($sqlSallesDispo);
    
    echo "Bindage des paramètres:\n";
    echo "- jour: $jour\n";
    echo "- debut: $heure_debut\n";
    echo "- fin: $heure_fin\n";
    echo "- effectif: $effectif\n\n";

    $stmt->execute([
        'jour' => $jour,
        'debut' => $heure_debut,
        'fin' => $heure_fin,
        'effectif' => $effectif
    ]);

    echo "✅ Requête exécutée avec succès\n\n";

    $bestSalle = $stmt->fetch();

    if ($bestSalle) {
        echo "✅ Salle trouvée:\n";
        echo "- ID: " . $bestSalle['id_salle'] . "\n";
        echo "- Nom: " . $bestSalle['nom_salle'] . "\n";
        echo "- Capacité: " . $bestSalle['capacite'] . "\n";
        echo "- Bâtiment: " . $bestSalle['batiment'] . "\n";
    } else {
        echo "⚠️ Aucune salle disponible\n";
    }

} catch (PDOException $e) {
    echo "❌ Erreur PDO:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur générale:\n";
    echo "Message: " . $e->getMessage() . "\n";
}
?>
