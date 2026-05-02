<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISPT-LIKASI - Gestion Intelligente des Horaires et Salles</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
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
            font-size: 1.4rem;
            letter-spacing: 0.5px;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #ffcc00 !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #ffcc00;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 74, 153, 0.7), rgba(0, 58, 122, 0.7)), 
                        url('bg.jpg') center/cover no-repeat;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255,204,0,0.1), transparent),
                        radial-gradient(circle at 80% 80%, rgba(0,178,226,0.1), transparent);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            animation: fadeInUp 0.8s ease;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }

        .hero-accent {
            color: #ffcc00;
            font-weight: 700;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta {
            padding: 14px 40px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-size: 1rem;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary-cta {
            background: #ffcc00;
            color: #004a99;
        }

        .btn-primary-cta:hover {
            background: #ffd700;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255,204,0,0.3);
            color: #004a99;
        }

        .btn-secondary-cta {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
        }

        .btn-secondary-cta:hover {
            background: white;
            color: #004a99;
            transform: translateY(-3px);
        }

        /* Features Section */
        .features-section {
            padding: 80px 20px;
            background: #f8f9fa;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            text-align: center;
            color: #004a99;
        }

        .section-title .accent {
            color: #ffcc00;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border-left: 5px solid #004a99;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            border-left-color: #ffcc00;
        }

        .feature-icon {
            font-size: 3rem;
            color: #004a99;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-text {
            color: #666;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Main Actions Section */
        .actions-section {
            padding: 80px 20px;
            background: white;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .action-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 35px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .action-item:hover {
            border-color: #004a99;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,74,153,0.15);
        }

        .action-icon {
            font-size: 3rem;
            color: #004a99;
            margin-bottom: 20px;
        }

        .action-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .action-text {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Footer */
        .footer-main {
            background: #004a99;
            color: white;
            padding: 40px 20px 20px;
            text-align: center;
            border-top: 3px solid #ffcc00;
        }

        .footer-text {
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .footer-copy {
            font-size: 0.9rem;
            opacity: 0.8;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 20px;
            margin-top: 20px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .nav-link {
                margin: 5px 0;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-cta {
                width: 100%;
                max-width: 300px;
            }
        }

        /* Special badges */
        .badge-new {
            display: inline-block;
            background: #ffcc00;
            color: #004a99;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-badge::before {
            content: '●';
            font-size: 1.2rem;
            animation: pulse 1s infinite;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-main sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
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
                        <i class="fas fa-door-open me-1"></i> Horaires
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="simulation-iot.php">
                        <i class="fas fa-gamepad me-1"></i> Simulation IOT
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

<!-- Hero Section -->
<div class="hero">
    <div class="hero-content">
        <div class="badge-new">
            <i class="fas fa-spark"></i> Système D'Attribution Des Salles
        </div>
        <h1 class="hero-title">
            ISPT-LIKASI
        </h1>
        <p class="hero-subtitle">
            Bienvenue Dans Le module D'attribution Optimale Des Salles
            <br>
        </p>
        <div class="cta-buttons">
            <a href="salles_view.php" class="btn-cta btn-primary-cta">
                <i class="fas fa-door-open"></i> Consulter l'horaire
            </a>
            <a href="admin.php" class="btn-cta btn-secondary-cta">
                <i class="fas fa-tachometer-alt"></i> Espace Administrateur
            </a>
        </div>
    </div>
</div>

<!-- Actions Section -->
<section class="actions-section" id="actions">
    <div class="container">
        <h2 class="section-title">
            Les fonctionnalités principales <span class="accent">du module</span>
        </h2>
        <div class="actions-grid">
            <!-- Planning -->
            <a href="salles_view.php" class="action-item">
                <div class="action-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="action-title">Horaire</div>
                <p class="action-text">
                    Consultez les horaires de la semaine
                </p>
            </a>
            <!-- État des Salles -->
            <a href="salles_view.php" class="action-item">
                <div class="action-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="action-title">État des Salles</div>
                <p class="action-text">
                    Consultez la disponibilité de chaque salle
                </p>
            </a>

            <!-- Administration -->
            <a href="login.php" class="action-item">
                <div class="action-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="action-title">Administration</div>
                <p class="action-text">
                    Gestion des horaires, promotions et salles du système
                </p>
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer-main">
    <div class="container">
        <p class="footer-text">
            <strong>ISPT-LIKASI</strong> - Module D'attribution Optimale Salles
        </p>
        <p class="footer-text">
            <span class="status-badge">Système Actif</span>
        </p>
        <p class="footer-copy">
            © 2026 ISPT-Likasi. Tous droits réservés.
        </p>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
