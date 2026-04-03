<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Salles - ISPT-Likasi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Adaptation aux couleurs de l'ISPT-Likasi */
        :root {
            --ispt-blue: #004a99;
            --ispt-yellow: #ffcc00;
        }
        .navbar-ispt {
            background-color: var(--ispt-blue);
            border-bottom: 4px solid var(--ispt-yellow);
        }
        .card-header-ispt {
            background-color: var(--ispt-blue);
            color: white;
        }
        .status-indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        .bg-libre { background-color: #28a745; }
        .bg-occupee { background-color: #dc3545; }
        .bg-reservee { background-color: #ffc107; }
    </style>
</head>
<body class="bg-home">

    <!-- Navbar simulée ISPT-Likasi -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ispt mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fa-solid fa-school me-2"></i>
                <strong>ISPT-LIKASI</strong>
            </a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="fas fa-user-shield me-1"></i> Connexion Admin
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark">Tableau de Bord des Salles</h2>
                <p class="text-muted">Visualisez la disponibilité des salles de cours à l'ISPT/Likasi.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div id="connection-status" class="badge bg-success">
                    <i class="fas fa-sync fa-spin me-1"></i> Actualisation Auto
                </div>
            </div>
        </div>

        <!-- Zone d'affichage des salles -->
        <div id="dashboard-row" class="row g-4">
            <!-- Injecté par jQuery -->
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement des données en temps réel...</p>
            </div>
        </div>
    </div>

    <footer class="container text-center mt-5 mb-4 text-muted border-top pt-3">
        <small>&copy; 2026 ISPT-Likasi - Module Additionnel VANNELLA</small>
    </footer>

    <!-- Scripts: jQuery et Bootstrap Bundle -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
