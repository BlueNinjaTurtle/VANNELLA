<?php
$hash = '$2y$10$vDhfLhfCHu88ZxriNe91c.Q/JRhYnJWTH.1X6qfNtsj8ZvRtnh2Q2';
$password = 'ISPT2026';

if (password_verify($password, $hash)) {
    echo "Le mot de passe correspond au hash.\n";
} else {
    echo "Le mot de passe NE correspond PAS au hash.\n";
    echo "Hash actuel: " . $hash . "\n";
    echo "Nouveau hash pour ISPT2026: " . password_hash($password, PASSWORD_DEFAULT) . "\n";
}
?>