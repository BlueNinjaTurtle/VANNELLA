<?php
/**
 * Script d'installation - Importe les tables RFID automatiquement
 * Accéder à: http://localhost/VANNELLA/install_rfid.php
 */

require_once 'config/db.php';

if (!isset($_GET['confirm'])) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Installation RFID - VANNELLA</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #004a99;
                margin-bottom: 20px;
            }
            p {
                color: #666;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                margin-top: 20px;
                background: #10b981;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }
            .btn:hover {
                background: #059669;
            }
            .warning {
                background: #fef3c7;
                border-left: 4px solid #f59e0b;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
            }
            .warning strong {
                color: #92400e;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔧 Installation - Système RFID VANNELLA</h1>

            <p>Cet script va importer les tables SQL nécessaires pour le système IoT RFID.</p>

            <div class="warning">
                <strong>⚠️ Attention:</strong> Cette opération va créer les tables suivantes:
                <ul>
                    <li><code>lecteurs_rfid</code></li>
                    <li><code>cartes_rfid</code></li>
                    <li><code>lectures_rfid</code></li>
                </ul>
                Les données existantes seront conservées.
            </div>

            <p><strong>Êtes-vous sûr de vouloir continuer?</strong></p>

            <form method="GET">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="btn">✅ Confirmer l'installation</button>
            </form>

            <a href="index.php" style="display: inline-block; margin-top: 20px; color: #004a99;">← Retour</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Exécuter l'installation
try {
    $sql = file_get_contents('database_iot_rfid.sql');

    // Diviser les requêtes
    $queries = explode(';', $sql);

    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query) && strpos($query, '--') === false) {
            if ($conn->query($query) === TRUE) {
                $count++;
            } else {
                throw new Exception("Erreur: " . $conn->error);
            }
        }
    }

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Installation réussie - VANNELLA</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #10b981;
                margin-bottom: 20px;
            }
            .success {
                background: #d1fae5;
                border-left: 4px solid #10b981;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                color: #065f46;
            }
            .next-steps {
                background: #eff6ff;
                border-left: 4px solid #0284c7;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                color: #0c4a6e;
            }
            .next-steps ol {
                margin: 10px 0;
                padding-left: 20px;
            }
            .next-steps li {
                margin: 8px 0;
            }
            a {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background: #004a99;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
            a:hover {
                background: #003a7a;
            }
            code {
                background: #f3f4f6;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>✅ Installation réussie!</h1>

            <div class="success">
                <strong>🎉 Les tables RFID ont été créées avec succès!</strong>
                <p><?php echo $count; ?> requêtes SQL exécutées.</p>
            </div>

            <div class="next-steps">
                <strong>📋 Prochaines étapes:</strong>
                <ol>
                    <li>Installer Python: <code>pip install -r requirements_rfid.txt</code></li>
                    <li>Lancer l'émulateur: <code>python iot_sbc_emulator.py</code></li>
                    <li>Tester en mode INTERACTIF: <code>lecture 1 1</code></li>
                    <li>Visualiser: <a href="simulation-iot-rfid.php" style="display: inline; padding: 0; background: none; color: #0284c7; text-decoration: underline;">simulation-iot-rfid.php</a></li>
                </ol>
            </div>

            <a href="simulation-iot-rfid.php">🔍 Voir la page RFID</a>
            <a href="index.php">← Retour accueil</a>
        </div>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Erreur installation - VANNELLA</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #ef4444;
            }
            .error {
                background: #fee2e2;
                border-left: 4px solid #ef4444;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                color: #7f1d1d;
            }
            code {
                background: #f3f4f6;
                padding: 8px;
                display: block;
                margin: 10px 0;
                border-radius: 3px;
                overflow-x: auto;
                font-family: monospace;
            }
            a {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background: #004a99;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>❌ Erreur d'installation</h1>

            <div class="error">
                <strong>Une erreur s'est produite:</strong>
                <code><?php echo htmlspecialchars($e->getMessage()); ?></code>
            </div>

            <p>Vérifiez que:</p>
            <ul>
                <li>MySQL est en cours d'exécution dans XAMPP</li>
                <li>Le fichier <code>database_iot_rfid.sql</code> existe</li>
                <li>La base de données <code>gestion_salles</code> existe</li>
            </ul>

            <a href="install_rfid.php">↻ Réessayer</a>
        </div>
    </body>
    </html>
    <?php
}
?>
