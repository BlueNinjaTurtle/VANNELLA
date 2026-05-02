<?php
// api/login.php
header('Content-Type: application/json');
require_once '../config/db.php';

session_start();

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Veuillez remplir tous les champs.']);
    exit();
}

$username = $input['username'];
$password = $input['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_name'] = $admin['nom_complet'];
        
        echo json_encode(['status' => 'success', 'message' => 'Connexion réussie.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identifiants incorrects.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
