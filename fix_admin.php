<?php
require_once 'config/db.php';

try {
    // Supprimer l'admin existant
    $stmt = $pdo->prepare("DELETE FROM admins WHERE username = ?");
    $stmt->execute(['ADMIN']);
    echo "Admin existant supprimé.\n";

    // Créer un nouveau hash pour ISPT2026
    $password = 'ISPT2026';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insérer le nouvel admin
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, nom_complet) VALUES (?, ?, ?)");
    $stmt->execute(['ADMIN', $hashedPassword, 'Administrateur Principal']);

    echo "Nouvel admin créé avec succès !\n";
    echo "Username: ADMIN\n";
    echo "Password: ISPT2026\n";
    echo "Hash généré: $hashedPassword\n";

    // Vérifier que ça fonctionne
    if (password_verify($password, $hashedPassword)) {
        echo "✅ Vérification du hash réussie !\n";
    } else {
        echo "❌ Erreur dans la génération du hash.\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>