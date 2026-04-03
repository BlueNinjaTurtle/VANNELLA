<?php
require_once 'config/db.php';

try {
    // Vérifier si la table admins existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() == 0) {
        echo "La table 'admins' n'existe pas.\n";
        exit();
    }

    // Récupérer tous les admins
    $stmt = $pdo->query("SELECT * FROM admins");
    $admins = $stmt->fetchAll();

    if (empty($admins)) {
        echo "Aucun admin trouvé dans la base de données.\n";
    } else {
        echo "Admins trouvés :\n";
        foreach ($admins as $admin) {
            echo "- ID: {$admin['id_admin']}, Username: {$admin['username']}, Nom: {$admin['nom_complet']}\n";
            echo "  Hash du mot de passe: {$admin['password']}\n";

            // Tester si le hash correspond à ISPT2026
            if (password_verify('ISPT2026', $admin['password'])) {
                echo "  ✅ Le mot de passe 'ISPT2026' correspond au hash.\n";
            } else {
                echo "  ❌ Le mot de passe 'ISPT2026' NE correspond PAS au hash.\n";
            }
            echo "\n";
        }
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>