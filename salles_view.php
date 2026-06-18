<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État des Salles - ISPT-Likasi</title>
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

        /* Navigation */
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

        /* Header */
        .header-salles {
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

        /* Main Container */
        .container-salles {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Filtres */
        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .filter-group label {
            font-weight: 600;
            color: #004a99;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
        }

        .filter-group select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .filter-group select:hover {
            border-color: #004a99;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #004a99;
            box-shadow: 0 0 0 3px rgba(0,74,153,0.1);
        }

        /* Grille Salles */
        .salles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 40px;
        }

        .salle-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .salle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #004a99;
        }

        .salle-card-header {
            padding: 10px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #e0e0e0;
        }

        .salle-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #004a99;
            margin-bottom: 3px;
        }

        .salle-batiment {
            font-size: 0.85rem;
            color: #666;
        }

        .salle-card-body {
            padding: 10px;
            text-align: center;
        }

        /* État Emoji Énorme */
        .state-emoji {
            font-size: 2rem;
            margin: 6px 0;
            animation: pulse-state 2s infinite;
        }

        @keyframes pulse-state {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }

        .state-text {
            font-size: 0.9rem;
            font-weight: 700;
            margin: 6px 0;
            text-transform: uppercase;
        }

        .state-libre .state-text { color: #10b981; }
        .state-occupee .state-text { color: #ef4444; }
        .state-reservee .state-text { color: #fbbf24; }
        .state-offline .state-text { color: #6b7280; }

        /* Infos Salle */
        .salle-info {
            display: flex;
            justify-content: space-around;
            margin: 10px 0;
            padding: 10px 0;
            border-top: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-item {
            text-align: center;
        }

        .info-label {
            font-size: 0.75rem;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: #333;
        }

        /* Badges Source et Statut */
        .badges-section {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 15px 0;
        }

        .badge-source {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-iot { background: #dbeafe; color: #1e40af; }
        .badge-admin { background: #fee2e2; color: #991b1b; }
        .badge-planning { background: #dcfce7; color: #166534; }
        .badge-system { background: #f3f4f6; color: #374151; }

        .badge-iot-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-online { background: #d1fae5; color: #065f46; }
        .badge-offline { background: #f3f4f6; color: #6b7280; }

        /* Bouton Détails */
        .btn-details {
            width: 100%;
            padding: 12px;
            background: #004a99;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 15px;
        }

        .btn-details:hover {
            background: #003a7a;
            transform: scale(1.02);
        }

        /* Section Horaires */
        .horaires-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #004a99;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #ffcc00;
        }

        .horaires-table {
            width: 100%;
            border-collapse: collapse;
        }

        .horaires-table thead {
            background: #f8f9fa;
        }

        .horaires-table th {
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #004a99;
            border-bottom: 2px solid #e0e0e0;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .horaires-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .horaires-table tbody tr:hover {
            background: #f8f9fa;
        }

        /* Indicateur Connexion */
        .sync-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            padding: 12px 20px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            z-index: 1000;
        }

        .sync-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Footer */
        .footer-main {
            background: #004a99;
            color: white;
            padding: 30px 20px 20px;
            text-align: center;
            border-top: 3px solid #ffcc00;
        }

        .footer-text {
            margin-bottom: 10px;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }

            .salles-grid {
                grid-template-columns: 1fr;
            }

            .salle-card-body {
                padding: 15px 10px;
            }

            .state-emoji {
                font-size: 3rem;
            }

            .sync-indicator {
                bottom: 15px;
                right: 15px;
                font-size: 0.8rem;
                padding: 10px 15px;
            }
        }

        /* Loading Animation */
        .loading {
            text-align: center;
            padding: 40px 20px;
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

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Accordéons Horaires */
        .accordion-dept {
            margin-bottom: 20px;
        }

        .accordion-header {
            display: flex;
            align-items: center;
            padding: 15px;
            background: linear-gradient(135deg, #004a99 0%, #003a7a 100%);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            user-select: none;
            font-weight: 700;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .accordion-header:hover {
            background: linear-gradient(135deg, #003a7a 0%, #002a5a 100%);
            border-color: #ffcc00;
        }

        .accordion-header.collapsed::before {
            content: "▼";
            display: inline-block;
            margin-right: 10px;
            transform: rotate(-90deg);
            transition: transform 0.3s;
        }

        .accordion-header::before {
            content: "▼";
            display: inline-block;
            margin-right: 10px;
            transition: transform 0.3s;
        }

        .accordion-body {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 0 0 8px 8px;
            margin-bottom: 15px;
        }

        .accordion-promo {
            margin-bottom: 15px;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .promo-header {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: #e9ecef;
            color: #004a99;
            border-left: 4px solid #ffcc00;
            cursor: pointer;
            user-select: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .promo-header:hover {
            background: #dee2e6;
        }

        .promo-header.collapsed::after {
            content: "▶";
            margin-left: auto;
            transition: transform 0.2s;
        }

        .promo-header::after {
            content: "▶";
            margin-left: auto;
            transform: rotate(90deg);
            transition: transform 0.2s;
        }

        .promo-body {
            padding: 15px;
        }

        .horaires-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .horaires-table thead {
            background: #f0f0f0;
        }

        .horaires-table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #004a99;
            border-bottom: 2px solid #004a99;
        }

        .horaires-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .horaires-table tbody tr:hover {
            background: #f8f9fa;
        }

        .etat-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Table horaires par promotion */
        .horaires-promo-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .horaires-promo-table thead {
            background: #f8f9fa;
        }

        .horaires-promo-table th {
            padding: 12px;
            text-align: center;
            font-weight: 700;
            color: #004a99;
            border: 1px solid #e0e0e0;
            border-bottom: 2px solid #004a99;
        }

        .horaires-promo-table td {
            padding: 12px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        .horaires-promo-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .horaires-promo-table tbody tr:hover {
            background: #f0f4f8;
        }

        .horaires-promo-table tbody tr td:first-child {
            font-weight: 700;
            background: #f0f0f0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Navigation -->
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
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-calendar-alt me-1"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="simulation-iot.php">
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

<!-- Header -->
<div class="header-salles">
    <div class="header-content">
        <h1 class="header-title">
            <i class="fas fa-door-open me-3" style="color: #ffcc00;"></i>Horaires et États des Salles
        </h1>
        <p class="header-subtitle">Consultation des horaires hebdomadaires • Disponibilité en temps réel des salles • Synchronisation IoT</p>
    </div>
</div>

<!-- Main Container -->
<div class="container-salles">

    <!-- Filtres -->
    <div class="filters-section">
        <h5 style="margin-bottom: 20px; color: #004a99; font-weight: 700;">
            <i class="fas fa-filter me-2"></i>Filtres de Recherche
        </h5>
        <div class="filter-row">
            <div class="filter-group">
                <label for="filter-departement">Département</label>
                <select id="filter-departement">
                    <option value="">Tous les départements</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-promotion">Promotion</label>
                <select id="filter-promotion">
                    <option value="">Toutes les promotions</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-etat">État</label>
                <select id="filter-etat">
                    <option value="">Tous les états</option>
                    <option value="libre">🟢 Libre</option>
                    <option value="occupée">🔴 Occupée</option>
                    <option value="réservée">🟡 Réservée</option>
                    <option value="indisponible">⚫ Indisponible</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Grille Salles -->
    <h3 style="color: #004a99; font-weight: 700; margin-bottom: 25px;">
        <i class="fas fa-th me-2" style="color: #ffcc00;"></i>Disponiblité des Salles en Temps Réel
    </h3>
    <div id="salles-container" class="salles-grid">
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; color: #666;">Chargement des salles...</p>
        </div>
    </div>

    <!-- Section Horaires -->
    <div class="horaires-section">
        <h4 class="section-title">
            <i class="fas fa-calendar-alt"></i>Horaires par Département et Promotion
        </h4>
        <div id="horaires-container">
            <div class="loading">
                <div class="spinner"></div>
                <p style="margin-top: 15px; color: #666;">Chargement des horaires...</p>
            </div>
        </div>
    </div>

</div>

<!-- Indicateur Connexion -->
<div class="sync-indicator">
    <div class="sync-dot"></div>
    <span id="connection-status">Connecté</span>
</div>

<!-- Modal Détails Salle -->
<div class="modal fade" id="modalDetailsSalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalTitle">Détails Salle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalBody">
                <!-- Contenu dynamique -->
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer-main">
    <p class="footer-text">
        <strong>ISPT-LIKASI</strong> • Module D'attribution Optimale des Salles
    </p>
    <p class="footer-text" style="font-size: 0.9rem;">
        © 2026 ISPT-Likasi. Tous droits réservés.
    </p>
</footer>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/salles_view.js"></script>

</body>
</html>
