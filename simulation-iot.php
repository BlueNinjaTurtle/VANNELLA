<?php
/**
 * PARTIE 2 - Page Simulation IoT
 * Permet de simuler les états physiques des salles
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
    <title>Simulation IoT - ISPT-Likasi</title>
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

        .nav-link:hover {
            color: #ffcc00 !important;
        }

        .header-simulation {
            background: linear-gradient(135deg, #004a99 0%, #003a7a 100%);
            color: white;
            padding: 40px 20px;
            border-bottom: 3px solid #ffcc00;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .header-subtitle {
            font-size: 1rem;
            opacity: 0.95;
        }

        .container-simulation {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .intro-card,
        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .intro-card {
            border-left: 5px solid #004a99;
        }

        .intro-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #004a99;
            margin-bottom: 8px;
        }

        .intro-text {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 18px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #004a99;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .salles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .salle-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .salle-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #004a99;
        }

        .salle-header {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            align-items: center;
            padding: 18px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #e0e0e0;
        }

        .salle-info h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #004a99;
        }

        .salle-badge {
            display: inline-block;
            background: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
            border: 1px solid #e5e7eb;
        }

        .etat-current {
            flex-shrink: 0;
        }

        .etat-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .etat-badge.LIBRE {
            background: #10b981;
        }

        .etat-badge.OCCUPEE {
            background: #ef4444;
        }

        .etat-badge.RESERVEE {
            background: #f59e0b;
            color: #1f2937;
        }

        .etat-badge.INDISPONIBLE {
            background: #6b7280;
        }

        .salle-body {
            padding: 20px;
        }

        .actions-label {
            font-size: 0.9rem;
            font-weight: 700;
            color: #004a99;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .salle-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .btn-etat {
            padding: 12px 14px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            box-shadow: inset 0 -2px 0 rgba(0,0,0,0.08);
        }

        .btn-etat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        }

        .btn-etat.occupee {
            background: #ef4444;
            color: white;
        }

        .btn-etat.libre {
            background: #10b981;
            color: white;
        }

        .btn-etat.reservee {
            background: #f59e0b;
            color: #1f2937;
        }

        .btn-etat.indisponible {
            background: #6b7280;
            color: white;
        }

        .btn-etat:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .loading-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.6s linear infinite;
            margin-left: 8px;
            vertical-align: middle;
        }

        .btn-etat.reservee .loading-spinner {
            border: 2px solid rgba(31,41,55,.2);
            border-top-color: #1f2937;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .section-title {
            font-size: 1.5rem;
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
            background: white;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            color: #666;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #004a99;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .footer-main {
            background: #004a99;
            color: white;
            padding: 30px 20px 20px;
            text-align: center;
            border-top: 3px solid #ffcc00;
            margin-top: 50px;
        }

        .footer-text {
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .toast-sim {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            color: white;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            font-weight: 600;
            z-index: 9999;
            animation: slideIn 0.3s ease, slideOut 0.3s ease 3.7s forwards;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 1.7rem;
            }

            .salles-grid {
                grid-template-columns: 1fr;
            }

            .salle-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .salle-actions {
                grid-template-columns: 1fr;
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
                    <a class="nav-link active" href="simulation-iot.php">
                        <i class="fas fa-gamepad me-1"></i> Simulation IoT
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

<div class="header-simulation">
    <div class="header-content">
        <h1 class="header-title">
            <i class="fas fa-microchip me-3" style="color: #ffcc00;"></i>Simulation IoT
        </h1>
        <p class="header-subtitle">Pilotage de l'état des salles en direct avec le même référentiel que le reste de la plateforme. Simulation des requètes envoyées et réçues de la part de la partie IOT du projet.</p>
    </div>
</div>

<div class="container-simulation">
    <div class="intro-card">
        <div class="intro-title">
            <i class="fas fa-circle-info me-2"></i>Simulation synchronisée
        </div>
        <p class="intro-text">
            Chaque action sur les boutons met à jour l'état réel des salles et se répercute sur les autres pages concernées du module.
        </p>
    </div>

    <div class="stats-section">
        <h4 class="section-title">
            <i class="fas fa-chart-column"></i>Vue d'ensemble
        </h4>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="stat-total">-</div>
                <div class="stat-label">Salles total</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: #10b981;" id="stat-libre">-</div>
                <div class="stat-label">Libres</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: #ef4444;" id="stat-occupee">-</div>
                <div class="stat-label">Occupées</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: #f59e0b;" id="stat-reservee">-</div>
                <div class="stat-label">Réservées</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: #6b7280;" id="stat-indisponible">-</div>
                <div class="stat-label">Indisponibles</div>
            </div>
        </div>
    </div>

    <h3 class="section-title">
        <i class="fas fa-sliders"></i>Contrôle des salles
    </h3>
    <div id="salles-container" class="salles-grid">
        <div class="empty-state">
            <div class="spinner mx-auto"></div>
            <p style="margin-top: 15px;">Chargement des salles...</p>
        </div>
    </div>
</div>

<footer class="footer-main">
    <p class="footer-text">
        <strong>ISPT-LIKASI</strong> • Module D'attribution Optimale des Salles
    </p>
    <p class="footer-text" style="font-size: 0.9rem;">
        © 2026 ISPT-Likasi. Tous droits réservés.
    </p>
</footer>

<script>
    const colors = <?php echo json_encode($colors); ?>;
    const emojis = <?php echo json_encode($emojis); ?>;
    
    let allHoraires = []; // 🆕 Stocker tous les horaires

    async function loadSalles() {
        try {
            // 🆕 Charger aussi les horaires
            const [sallesRes, horairesRes] = await Promise.all([
                fetch('api/getSalles.php?t=' + Date.now()),
                fetch('api/getHorairesByWeek.php?t=' + Date.now())
            ]);

            const sallesData = await sallesRes.json();
            const horairesData = await horairesRes.json();

            if (sallesData.status !== 'success') {
                throw new Error(sallesData.message || 'Erreur lors du chargement');
            }

            // 🆕 Stocker les horaires
            if (horairesData.status === 'success') {
                allHoraires = horairesData.data || [];
            }

            renderSalles(sallesData.data);
            updateStats(sallesData.data);
        } catch (error) {
            document.getElementById('salles-container').innerHTML =
                `<div class="empty-state"><i class="fas fa-triangle-exclamation" style="font-size:2rem; color:#ef4444;"></i><p style="margin-top:15px;">Erreur: ${error.message}</p></div>`;
        }
    }

    function renderSalles(salles) {
        const container = document.getElementById('salles-container');
        container.innerHTML = '';

        salles.forEach(salle => {
            const card = document.createElement('div');
            card.className = 'salle-card';
            card.id = `salle-${salle.id_salle}`;

            const etatUpper = (salle.etat || 'libre')
                .toUpperCase()
                .replace('É', 'E');

            const normalizedEtat = etatUpper === 'OCCUPÉE' ? 'OCCUPEE'
                : etatUpper === 'RÉSERVÉE' ? 'RESERVEE'
                : etatUpper;

            const etatEmoji = emojis[normalizedEtat] || '❓';

            // 🆕 Récupérer le cours et horaire de cette salle
            let courseContent = '';
            let annulationSelect = '';
            let horaireAnnulationId = null;
            let periodeAnnulation = (new Date().getHours() < 12) ? 'avant' : 'apres';
            let horairesSalle = allHoraires.filter(h => h.id_salle == salle.id_salle);
            
            if (horairesSalle.length > 0) {
                const now = new Date();
                const dayOfWeek = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                const jourAujourdhui = dayOfWeek[now.getDay()];
                
                // Horaires du jour
                let horairesAujourd = horairesSalle.filter(h => h.jour === jourAujourdhui);
                
                if (horairesAujourd.length > 0) {
                    const currentTime = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
                    
                    let courseEnCours = null;
                    let courseProchain = null;
                    
                    for (let h of horairesAujourd) {
                        if (h.heure_debut <= currentTime && h.heure_fin >= currentTime) {
                            courseEnCours = h;
                            break;
                        }
                        if (!courseProchain && h.heure_debut > currentTime) {
                            courseProchain = h;
                        }
                    }
                    
                    let coursDisplay = courseEnCours || courseProchain;
                    
                    if (coursDisplay) {
                        horaireAnnulationId = coursDisplay.id_horaire;
                        periodeAnnulation = parseInt(coursDisplay.heure_debut.split(':')[0], 10) < 12 ? 'avant' : 'apres';
                        const statutCours = (coursDisplay.statut || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        const statusBadge = statutCours === 'annule' || statutCours === 'annulee'
                            ? ' <span style="color: #dc3545; font-weight: 600; font-size: 0.9rem;">Annulé</span>'
                            : '';
                        
                        const courseLabel = courseEnCours ? '📚' : '⏭️';
                        courseContent = `
                            <div style="margin-top: 12px; padding: 10px; background: rgba(0,74,153,0.1); border-left: 3px solid #004a99; border-radius: 4px;">
                                <div style="font-weight: 600; color: #004a99; margin-bottom: 4px;">${courseLabel} ${coursDisplay.nom_cours}${statusBadge}</div>
                                <div style="font-size: 0.85rem; color: #666;">🕐 ${coursDisplay.heure_debut} - ${coursDisplay.heure_fin}</div>
                            </div>
                        `;
                    }
                }

                const horairesActifs = horairesSalle.filter(h => {
                    const statutCours = (h.statut || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    return statutCours !== 'annule' && statutCours !== 'annulee';
                });

                if (horairesActifs.length > 0) {
                    if (!horaireAnnulationId) {
                        horaireAnnulationId = horairesActifs[0].id_horaire;
                        periodeAnnulation = parseInt(horairesActifs[0].heure_debut.split(':')[0], 10) < 12 ? 'avant' : 'apres';
                    }

                    const options = horairesActifs.map(h => {
                        const periode = parseInt(h.heure_debut.split(':')[0], 10) < 12 ? 'avant' : 'apres';
                        const selected = h.id_horaire == horaireAnnulationId ? ' selected' : '';
                        return `<option value="${h.id_horaire}" data-periode="${periode}"${selected}>${h.jour} • ${h.heure_debut} - ${h.heure_fin} • ${h.nom_cours}</option>`;
                    }).join('');

                    annulationSelect = `
                        <div style="margin-top: 10px;">
                            <select id="annulation-${salle.id_salle}" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.85rem;">
                                ${options}
                            </select>
                        </div>
                    `;
                }
            }

            card.innerHTML = `
                <div class="salle-header">
                    <div class="salle-info">
                        <h3>${salle.nom_salle}</h3>
                        <div class="salle-badge">
                            <i class="fas fa-location-dot me-1"></i>${salle.batiment} • <i class="fas fa-users me-1"></i>${salle.capacite} places
                        </div>
                    </div>
                    <div class="etat-current">
                        <div class="etat-badge ${normalizedEtat}">
                            <span>${etatEmoji}</span>
                            <span>${etatUpper}</span>
                        </div>
                    </div>
                </div>
                <div class="salle-body">
                    ${courseContent}
                    ${annulationSelect}
                    <div class="actions-label" style="margin-top: 14px;">
                        <i class="fas fa-toggle-on"></i>Changer l'état
                    </div>
                    <div class="salle-actions">
                        <button class="btn-etat occupee" onclick="changeEtat(${salle.id_salle}, 'occupée', this)">
                            🔴 OCCUPÉE
                        </button>
                        <button class="btn-etat libre" onclick="changeEtat(${salle.id_salle}, 'libre', this, ${horaireAnnulationId || 'null'}, '${periodeAnnulation}')">
                            🟢 LIBRE
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(card);
        });
    }

    async function changeEtat(id_salle, nouvelEtat, buttonElement, idHoraire = null, periode = null) {
        buttonElement.disabled = true;
        const originalHTML = buttonElement.innerHTML;
        buttonElement.innerHTML += '<span class="loading-spinner"></span>';

        try {
            const annulationSelect = document.getElementById(`annulation-${id_salle}`);
            if (nouvelEtat === 'libre' && annulationSelect) {
                idHoraire = parseInt(annulationSelect.value, 10) || idHoraire;
                periode = annulationSelect.selectedOptions[0]?.dataset.periode || periode;
            }

            const response = await fetch('api/update_etat_salle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_salle: id_salle,
                    etat: nouvelEtat,
                    id_horaire: idHoraire,
                    periode: periode
                })
            });

            const data = await response.json();

            if (data.status !== 'success') {
                throw new Error(data.message || 'Erreur lors de la mise à jour');
            }

            await loadSalles();
            const annulationMessage = data.horaires_annules > 0
                ? ` - ${data.horaires_annules} cours annulé(s)`
                : '';
            showNotification(`Salle mise à jour en ${nouvelEtat.toUpperCase()}${annulationMessage}`, 'success');
        } catch (error) {
            showNotification(`Erreur: ${error.message}`, 'danger');
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalHTML;
        }
    }

    function updateStats(salles) {
        let stats = {
            'libre': 0,
            'occupée': 0,
            'réservée': 0,
            'indisponible': 0,
            'occupee': 0,
            'reservee': 0
        };

        salles.forEach(salle => {
            const etat = (salle.etat || 'libre').toLowerCase();
            if (stats.hasOwnProperty(etat)) {
                stats[etat]++;
            }
        });

        document.getElementById('stat-total').textContent = salles.length;
        document.getElementById('stat-libre').textContent = stats['libre'];
        document.getElementById('stat-occupee').textContent = stats['occupée'] + stats['occupee'];
        document.getElementById('stat-reservee').textContent = stats['réservée'] + stats['reservee'];
        document.getElementById('stat-indisponible').textContent = stats['indisponible'];
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = 'toast-sim';
        notification.style.background = type === 'success' ? '#10b981' : '#ef4444';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 4000);
    }

    loadSalles();
    setInterval(loadSalles, 5000);
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
