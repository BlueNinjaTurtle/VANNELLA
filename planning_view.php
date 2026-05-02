<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning Interactif - ISPT-Likasi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
        .header-planning {
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
        .container-planning {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Sync Indicator */
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Promotion Section */
        .promotion-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .promotion-header {
            background: linear-gradient(135deg, #004a99 0%, #003a7a 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin: -30px -30px 25px -30px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .promotion-icon {
            font-size: 1.8rem;
        }

        /* Planning Grid */
        .planning-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .course-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            border-left: 5px solid #004a99;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .course-card.libre {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        }

        .course-card.occupee {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }

        .course-card.offline {
            border-left-color: #6b7280;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .course-time {
            font-size: 0.85rem;
            font-weight: 600;
            color: #004a99;
            margin-bottom: 8px;
        }

        .course-day {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .course-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .course-teacher {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .course-teacher i {
            color: #004a99;
        }

        .room-info {
            background: white;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .room-name {
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .room-name i {
            color: #004a99;
        }

        .room-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 100px;
            justify-content: center;
        }

        .room-status.libre {
            background: #d1fae5;
            color: #065f46;
        }

        .room-status.libre::before {
            content: "●";
            color: #10b981;
            font-size: 1rem;
        }

        .room-status.occupee {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .room-status.occupee::before {
            content: "●";
            color: #ef4444;
            font-size: 1rem;
        }

        .room-status.offline {
            background: #e5e7eb;
            color: #374151;
        }

        .room-status.offline::before {
            content: "⚠";
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Footer */
        .footer-main {
            background: #004a99;
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-top: 3px solid #ffcc00;
            margin-top: 60px;
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 1.5rem;
            }

            .planning-grid {
                grid-template-columns: 1fr;
            }

            .sync-indicator {
                bottom: 10px;
                right: 10px;
                padding: 10px 15px;
                font-size: 0.8rem;
            }
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
                        <i class="fas fa-home me-1"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salles_view.php">
                        <i class="fas fa-door-open me-1"></i> Salles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="planning_view.php">
                        <i class="fas fa-calendar-alt me-1"></i> Horaires
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
<div class="header-planning">
    <div class="header-content">
        <h1 class="header-title">
            <i class="fas fa-calendar-alt me-2"></i>Planning de la Semaine
        </h1>
        <p class="header-subtitle">
            Consultez tous les horaires avec la disponibilité temps réel des salles
        </p>
    </div>
</div>

<!-- Main Content -->
<div class="container-planning">
    <div id="planningContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="text-muted mt-3">Chargement du planning...</p>
        </div>
    </div>
</div>

<!-- Sync Indicator -->
<div class="sync-indicator">
    <div class="sync-dot"></div>
    <span id="syncStatus">Synchronisé</span>
</div>

<!-- Footer -->
<footer class="footer-main">
    <p>&copy; 2026 ISPT-Likasi - Module VANNELLA. Tous droits réservés.</p>
</footer>


</body>
</html>
