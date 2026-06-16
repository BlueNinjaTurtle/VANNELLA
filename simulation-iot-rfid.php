<?php
/**
 * PARTIE 3 - Page Simulation IoT RFID (Cisco Packet Tracer)
 * Affiche les lectures RFID en temps réel
 */

require_once 'config/db.php';

$colors = [
    'LIBRE' => '#10b981',
    'OCCUPEE' => '#ef4444',
    'RESERVEE' => '#f59e0b',
    'INDISPONIBLE' => '#6b7280'
];

$emojis = [
    'LIBRE' => '🟢',
    'OCCUPEE' => '🔴',
    'RESERVEE' => '🟡',
    'INDISPONIBLE' => '⚫'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulation RFID Cisco PT - ISPT-Likasi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .navbar-main {
            background: rgba(0, 74, 153, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-bottom: 3px solid #ffcc00;
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffcc00 !important;
        }

        .header-section {
            background: linear-gradient(135deg, #004a99 0%, #003a7a 100%);
            color: white;
            padding: 40px 20px;
            border-bottom: 3px solid #ffcc00;
        }

        .header-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .header-subtitle {
            font-size: 0.95rem;
            opacity: 0.95;
        }

        .container-main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .card-info {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border-left: 5px solid #004a99;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border: 2px solid #e5e7eb;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            color: #004a99;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .lecteurs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .lecteur-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .lecteur-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #004a99;
        }

        .lecteur-header {
            background: linear-gradient(135deg, #004a99 0%, #002a5e 100%);
            color: white;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lecteur-header h4 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.2);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.3) !important;
            color: #059669;
        }

        .lecteur-body {
            padding: 16px;
        }

        .lecteur-detail {
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .lecteur-detail strong {
            color: #004a99;
        }

        .lecteur-detail-value {
            color: #666;
            margin-left: 8px;
        }

        .last-reading {
            background: #f0f9ff;
            border-left: 3px solid #0284c7;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .readings-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .readings-table {
            font-size: 0.9rem;
        }

        .readings-table thead {
            background: #f3f4f6;
        }

        .readings-table th {
            font-weight: 700;
            color: #004a99;
            border-bottom: 2px solid #004a99;
        }

        .readings-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s ease;
        }

        .readings-table tbody tr:hover {
            background: #f9fafb;
        }

        .timestamp-cell {
            color: #666;
            font-size: 0.85rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #004a99;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #ffcc00;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .footer-main {
            background: #004a99;
            color: white;
            padding: 30px 20px 20px;
            text-align: center;
            border-top: 3px solid #ffcc00;
            margin-top: 50px;
        }

        .tooltip-icon {
            cursor: help;
            margin-left: 8px;
            color: #0284c7;
            font-size: 0.8rem;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #0284c7;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .info-box i {
            color: #0284c7;
            margin-right: 10px;
        }

        .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(0,74,153,.2);
            border-radius: 50%;
            border-top-color: #004a99;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }

            .lecteurs-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-main sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="index.php">
            <img src="logo.png" alt="Logo ISPT" style="height: 42px; margin-right: 10px;">
            ISPT-LIKASI
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="salles_view.php">
                        <i class="fas fa-calendar-alt me-1"></i> Horaires
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="simulation-iot.php">
                        <i class="fas fa-gamepad me-1"></i> Simulation IoT (Manuel)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="simulation-iot-rfid.php">
                        <i class="fas fa-microchip me-1"></i> Simulation RFID
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="header-section">
    <div class="header-content" style="max-width: 1400px; margin: 0 auto;">
        <h1 class="header-title">
            <i class="fas fa-radio me-2" style="color: #ffcc00;"></i>Simulation RFID - Cisco Packet Tracer
        </h1>
        <p class="header-subtitle">Système de lecteurs RFID distribués. Un lecteur par salle avec SBC-PT central pour traiter les données.</p>
    </div>
</div>

<div class="container-main">
    <div class="info-box">
        <i class="fas fa-circle-info"></i>
        <strong>Fonctionnement:</strong> Les lecteurs RFID (Cisco PT) détectent les cartes et le SBC-PT envoie les données à l'API qui met à jour l'état des salles.
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-number" id="total-lecteurs">-</div>
            <div class="stat-label">Lecteurs</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="total-cartes">-</div>
            <div class="stat-label">Cartes RFID</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="total-lectures">-</div>
            <div class="stat-label">Lectures</div>
        </div>
        <div class="stat-box">
            <div class="stat-number" id="lecteurs-actifs">-</div>
            <div class="stat-label">Actifs</div>
        </div>
    </div>

    <h3 class="section-title">
        <i class="fas fa-broadcast-tower"></i>Lecteurs RFID
    </h3>
    <div class="lecteurs-grid" id="lecteurs-container">
        <div class="empty-state">
            <div class="spinner"><span class="loading-spinner"></span></div>
            <p style="margin-top: 15px;">Chargement des lecteurs...</p>
        </div>
    </div>

    <h3 class="section-title" style="margin-top: 40px;">
        <i class="fas fa-history"></i>Dernières Lectures RFID
    </h3>
    <div class="readings-section">
        <table class="table readings-table">
            <thead>
                <tr>
                    <th>Heure</th>
                    <th>Carte RFID</th>
                    <th>Lecteur</th>
                    <th>Salle</th>
                    <th>État</th>
                </tr>
            </thead>
            <tbody id="readings-tbody">
                <tr>
                    <td colspan="5" class="empty-state">Aucune lecture pour le moment...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<footer class="footer-main">
    <p style="margin-bottom: 10px;">
        <strong>ISPT-LIKASI</strong> • Module IoT RFID - Cisco Packet Tracer
    </p>
    <p style="font-size: 0.9rem; margin-bottom: 0;">
        © 2026 ISPT-Likasi. Tous droits réservés.
    </p>
</footer>

<script>
const colors = <?php echo json_encode($colors); ?>;
const emojis = <?php echo json_encode($emojis); ?>;

async function loadLecteurs() {
    try {
        const response = await fetch('api/get_lecteurs_rfid.php?t=' + Date.now());
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message || 'Erreur');
        }

        renderLecteurs(data.data.lecteurs);
        updateStats(data.data);
    } catch (error) {
        document.getElementById('lecteurs-container').innerHTML =
            `<div class="empty-state" style="grid-column: 1/-1;"><i class="fas fa-triangle-exclamation" style="font-size:2rem; color:#ef4444;"></i><p style="margin-top:15px;">Erreur: ${error.message}</p></div>`;
    }
}

function renderLecteurs(lecteurs) {
    const container = document.getElementById('lecteurs-container');
    container.innerHTML = '';

    if (lecteurs.length === 0) {
        container.innerHTML = '<div class="empty-state">Aucun lecteur RFID configuré</div>';
        return;
    }

    lecteurs.forEach(lecteur => {
        const card = document.createElement('div');
        card.className = 'lecteur-card';
        card.innerHTML = `
            <div class="lecteur-header">
                <h4>📡 ${lecteur.nom_salle}</h4>
                <div class="status-badge ${lecteur.actif ? 'status-active' : ''}">
                    <i class="fas ${lecteur.actif ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    ${lecteur.actif ? 'Actif' : 'Inactif'}
                </div>
            </div>
            <div class="lecteur-body">
                <div class="lecteur-detail">
                    <strong>ID Lecteur:</strong>
                    <span class="lecteur-detail-value">${lecteur.id_lecteur}</span>
                </div>
                <div class="lecteur-detail">
                    <strong>Salle:</strong>
                    <span class="lecteur-detail-value">#${lecteur.id_salle}</span>
                </div>
                <div class="lecteur-detail">
                    <strong>Localisation:</strong>
                    <span class="lecteur-detail-value">${lecteur.localisation || 'Non spécifiée'}</span>
                </div>
                <div class="lecteur-detail">
                    <strong>Ajouté:</strong>
                    <span class="lecteur-detail-value">${new Date(lecteur.date_ajout).toLocaleDateString('fr-FR')}</span>
                </div>
                ${lecteur.derniere_lecture ? `
                    <div class="last-reading" style="margin-top: 12px;">
                        📍 Dernière lecture: <strong>${lecteur.derniere_lecture}</strong>
                    </div>
                ` : ''}
            </div>
        `;
        container.appendChild(card);
    });
}

async function loadReadings() {
    try {
        const response = await fetch('api/get_lectures_rfid.php?limit=20&t=' + Date.now());
        const data = await response.json();

        if (data.status !== 'success') {
            throw new Error(data.message);
        }

        renderReadings(data.data);
    } catch (error) {
        console.error('Erreur lectures:', error);
    }
}

function renderReadings(lectures) {
    const tbody = document.getElementById('readings-tbody');

    if (lectures.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-state">Aucune lecture pour le moment...</td></tr>';
        return;
    }

    tbody.innerHTML = lectures.map(lecture => `
        <tr>
            <td class="timestamp-cell">${new Date(lecture.timestamp).toLocaleTimeString('fr-FR')}</td>
            <td><strong>${lecture.id_carte}</strong></td>
            <td>${lecture.id_lecteur}</td>
            <td>${lecture.nom_lecteur}</td>
            <td>
                <span style="color: ${colors['OCCUPEE']};">
                    ${emojis['OCCUPEE']} DÉTECTÉE
                </span>
            </td>
        </tr>
    `).join('');

    // Mettre à jour le nombre de lectures
    document.getElementById('total-lectures').textContent = lectures.length > 0 ? '📡' : '0';
}

function updateStats(data) {
    document.getElementById('total-lecteurs').textContent = data.lecteurs.length;
    document.getElementById('lecteurs-actifs').textContent = data.lecteurs.filter(l => l.actif).length;
    document.getElementById('total-cartes').textContent = data.total_cartes || '?';
}

// Charger les données au démarrage
loadLecteurs();
loadReadings();

// Rafraîchir chaque 3 secondes
setInterval(() => {
    loadLecteurs();
    loadReadings();
}, 3000);
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
