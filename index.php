<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISPT-Likasi - Gestion des Salles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --ispt-primary: #004a99;
            --ispt-secondary: #f0f4f8;
            --ispt-accent: #ffcc00;
            --text-dark: #2d3748;
            --text-muted: #718096;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        }

        .navbar-home {
            background: var(--ispt-primary);
            border-bottom: 4px solid var(--ispt-accent);
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .hero {
            background: linear-gradient(135deg, var(--ispt-primary) 0%, #003a7a 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-bottom: 4px solid var(--ispt-accent);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        .stats-section {
            background: white;
            padding: 40px 20px;
            margin: -30px auto 40px auto;
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            max-width: 1200px;
            border-radius: 12px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: var(--ispt-secondary);
            border-left: 4px solid var(--ispt-primary);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ispt-primary);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .container-home {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .action-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .action-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .card-icon {
            background: linear-gradient(135deg, var(--ispt-primary), #003a7a);
            color: white;
            padding: 40px;
            text-align: center;
            font-size: 3rem;
        }

        .card-content {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .card-description {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .card-link {
            background: var(--ispt-primary);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
            text-align: center;
            display: inline-block;
            align-self: flex-start;
        }

        .card-link:hover {
            background: #003a7a;
            color: white;
            text-decoration: none;
        }

        .card-link-secondary {
            background: transparent;
            color: var(--ispt-primary);
            border: 2px solid var(--ispt-primary);
        }

        .card-link-secondary:hover {
            background: var(--ispt-secondary);
        }

        .features-section {
            background: white;
            padding: 50px 20px;
            border-radius: 16px;
            margin: 40px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .features-title {
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            color: var(--text-dark);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .feature-item {
            text-align: center;
            padding: 20px;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--ispt-primary);
            margin-bottom: 15px;
        }

        .feature-name {
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .feature-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Modal Schedule */
        .modal-schedule {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-schedule.active {
            display: flex;
        }

        .modal-content-schedule {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-header-schedule {
            background: linear-gradient(135deg, var(--ispt-primary), #003a7a);
            color: white;
            padding: 25px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title-schedule {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body-schedule {
            padding: 25px;
        }

        .room-info-modal {
            background: var(--ispt-secondary);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid var(--ispt-primary);
        }

        .room-info-item {
            display: flex;
            align-items: center;
            margin: 8px 0;
            font-size: 0.95rem;
        }

        .room-info-item i {
            color: var(--ispt-primary);
            margin-right: 10px;
            width: 20px;
        }

        .schedule-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .creneau-item {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid #ccc;
            transition: all 0.2s;
        }

        .creneau-libre {
            background: #f0f9ff;
            border-left-color: #28a745;
            color: #155724;
        }

        .creneau-occupe {
            background: #fff5f5;
            border-left-color: #dc3545;
            color: #721c24;
        }

        .creneau-pause {
            background: #fffaed;
            border-left-color: #ffc107;
            color: #856404;
            font-style: italic;
            text-align: center;
        }

        .creneau-heure {
            font-weight: 700;
            font-size: 0.9rem;
            display: block;
        }

        .creneau-cours {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-top: 4px;
        }

        .creneau-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 6px;
        }

        .status-libre {
            background: #28a745;
            color: white;
        }

        .status-occupe {
            background: #dc3545;
            color: white;
        }

        .footer-home {
            background: var(--ispt-primary);
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 50px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .cards-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-home">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-school me-2"></i> ISPT-LIKASI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="ms-auto">
                    <a href="planning_view.php" class="btn btn-outline-light btn-sm rounded-pill px-3 me-2">
                        <i class="fas fa-calendar-week me-1"></i> Planning
                    </a>
                    <a href="login.php" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="fas fa-lock me-1"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="container-home">
            <h1 class="hero-title">Gestion Intelligente des Salles</h1>
            <p class="hero-subtitle">Optimisez l'utilisation de vos infrastructures avec VANNELLA</p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container-home">

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="stat-salles">0</div>
                    <div class="stat-label">Salles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="stat-promotions">0</div>
                    <div class="stat-label">Promotions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="stat-cours">0</div>
                    <div class="stat-label">Cours</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="stat-horaires">0</div>
                    <div class="stat-label">Horaires</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="cards-grid">

            <!-- Planning Card -->
            <div class="action-card">
                <div class="card-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Planning Hebdomadaire</h3>
                    <p class="card-description">Consultez les horaires de cours par promotion et semaine. Visualisez l'attribution optimale des salles.</p>
                    <a href="planning_view.php" class="card-link">
                        <i class="fas fa-arrow-right me-2"></i> Accéder
                    </a>
                </div>
            </div>

            <!-- Salles Card -->
            <div class="action-card">
                <div class="card-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">État des Salles</h3>
                    <p class="card-description">Consultez l'état temps réel de chaque salle et leur disponibilité en direct.</p>
                    <a href="#salles-live" class="card-link" onclick="scrollToSalles(); return false;">
                        <i class="fas fa-arrow-down me-2"></i> Voir
                    </a>
                </div>
            </div>

            <!-- Admin Card -->
            <div class="action-card">
                <div class="card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Administration</h3>
                    <p class="card-description">Gérez les horaires, promotions et cours. Utilisez l'algorithme d'optimisation automatique.</p>
                    <a href="login.php" class="card-link card-link-secondary">
                        <i class="fas fa-sign-in-alt me-2"></i> Connexion
                    </a>
                </div>
            </div>

        </div>

        <!-- Live Salles -->
        <div id="salles-live" style="margin: 40px 0;">
            <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 30px; color: var(--text-dark);">
                <i class="fas fa-door-open me-2"></i> État des Salles en Direct
            </h2>
            <div id="salles-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2">Chargement...</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer-home">
        <p>&copy; 2026 ISPT-Likasi - Module VANNELLA</p>
    </div>

    <!-- Modal Schedule -->
    <div class="modal-schedule" id="scheduleModal">
        <div class="modal-content-schedule">
            <div class="modal-header-schedule">
                <div class="modal-title-schedule" id="modalTitle">Horaires</div>
                <button class="modal-close" onclick="closeScheduleModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-schedule">
                <div class="room-info-modal">
                    <div class="room-info-item">
                        <i class="fas fa-door-open"></i>
                        <strong id="modalRoomName">Nom</strong>
                    </div>
                    <div class="room-info-item">
                        <i class="fas fa-map-pin"></i>
                        <span id="modalRoomBuilding">Bâtiment</span>
                    </div>
                    <div class="room-info-item">
                        <i class="fas fa-users"></i>
                        <span id="modalRoomCapacity">Capacité</span>
                    </div>
                </div>

                <div class="schedule-title">
                    <i class="fas fa-calendar-day"></i>
                    <span>Emploi du temps - <span id="modalDay">Jour</span></span>
                </div>

                <div id="creneauxContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="text-muted mt-2">Chargement...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openScheduleModal(salleName, salleId, batiment, capacite) {
            $.get('api/getSalleSchedule.php', {
                id_salle: salleId,
                jour: 'Lundi'
            }, function(res) {
                if (res.status === 'success') {
                    const data = res.data;
                    
                    $('#modalTitle').text('Horaires - ' + salleName);
                    $('#modalRoomName').text(salleName);
                    $('#modalRoomBuilding').text(batiment);
                    $('#modalRoomCapacity').text(capacite + ' places');
                    $('#modalDay').text(data.jour);

                    const creneauxHtml = data.creneaux.map(c => {
                        if (c.est_pause) {
                            return `<div class="creneau-item creneau-pause">
                                <span class="creneau-heure">${c.heure}</span>
                            </div>`;
                        } else {
                            const classe = c.occupe ? 'creneau-occupe' : 'creneau-libre';
                            const status = c.occupe ? 'OCCUPÉE' : 'LIBRE';
                            const statusClass = c.occupe ? 'status-occupe' : 'status-libre';
                            
                            return `<div class="creneau-item ${classe}">
                                <span class="creneau-heure">${c.heure}</span>
                                ${c.occupe ? `<div class="creneau-cours">${c.cours}</div>` : ''}
                                <div class="creneau-status ${statusClass}">${status}</div>
                            </div>`;
                        }
                    }).join('');

                    $('#creneauxContainer').html(creneauxHtml);
                    $('#scheduleModal').addClass('active');
                }
            });
        }

        function closeScheduleModal() {
            $('#scheduleModal').removeClass('active');
        }

        $(document).ready(function() {
            function loadStats() {
                $.get('api/getSalles.php', function(res) {
                    if (res.status === 'success') {
                        $('#stat-salles').text(res.data.length);
                        renderSalles(res.data);
                    }
                });

                $.get('api/getPromotions.php', function(res) {
                    if (res.status === 'success') {
                        $('#stat-promotions').text(res.data.length);
                    }
                });

                $.get('api/getCours.php', function(res) {
                    if (res.status === 'success') {
                        $('#stat-cours').text(res.data.length);
                    }
                });
            }

            function renderSalles(salles) {
                const html = salles.map(salle => {
                    const badgeClass = salle.etat === 'libre' ? 'success' : (salle.etat === 'occupée' ? 'danger' : 'warning');
                    const icon = salle.etat === 'libre' ? 'fa-door-open' : 'fa-door-closed';
                    
                    return `<div class="card border-0 shadow-sm" style="border-top: 4px solid var(--ispt-primary); cursor: pointer;" onclick="openScheduleModal('${salle.nom_salle}', ${salle.id_salle}, '${salle.batiment}', ${salle.capacite})">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">${salle.nom_salle}</h5>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas ${icon} me-2 text-secondary fs-5"></i>
                                <span class="badge bg-${badgeClass}">${salle.etat}</span>
                            </div>
                            <small class="text-muted d-block mb-2"><i class="fas fa-map-pin me-1"></i> ${salle.batiment}</small>
                            <small class="text-muted d-block"><i class="fas fa-users me-1"></i> ${salle.capacite} places</small>
                            <small style="color: #004a99; cursor: pointer; margin-top: 10px; display: block; font-weight: 600;">
                                <i class="fas fa-eye me-1"></i> Voir les horaires
                            </small>
                        </div>
                    </div>`;
                }).join('');
                $('#salles-container').html(html);
            }

            window.scrollToSalles = function() {
                document.getElementById('salles-live').scrollIntoView({ behavior: 'smooth' });
            };

            // Fermer la modal au clic en dehors
            $('#scheduleModal').click(function(e) {
                if (e.target === this) {
                    closeScheduleModal();
                }
            });

            loadStats();
            setInterval(loadStats, 30000);
        });
    </script>

</body>
</html>
