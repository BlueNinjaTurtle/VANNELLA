<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - ISPT-Likasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --ispt-blue: #004a99;
            --sidebar-width: 250px;
        }
        body { display: flex; min-height: 100vh; background: #f8f9fa; }
        #sidebar {
            width: var(--sidebar-width);
            background: var(--ispt-blue);
            color: white;
            transition: all 0.3s;
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        #main-content {
            flex-grow: 1;
            padding: 30px;
        }
        .admin-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="p-4 text-center border-bottom border-light border-opacity-25">
            <h4 class="fw-bold mb-0">ISPT ADMIN</h4>
            <small class="opacity-50">Gestion des Salles</small>
        </div>
        <div class="nav flex-column mt-3">
            <a href="index.php" class="nav-link"><i class="fas fa-eye me-2"></i> Vue Live</a>
            <a href="admin.php?page=planning" class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'planning') ? 'active' : ''; ?>"><i class="fas fa-calendar-plus me-2"></i> Planning</a>
            <a href="admin.php?page=salles" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'salles') ? 'active' : ''; ?>"><i class="fas fa-school me-2"></i> Salles</a>
            <a href="admin.php?page=promotions" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'promotions') ? 'active' : ''; ?>"><i class="fas fa-users me-2"></i> Promotions</a>
            <a href="admin.php?page=cours" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'cours') ? 'active' : ''; ?>"><i class="fas fa-book me-2"></i> Cours</a>
            <a href="logout.php" class="nav-link mt-5 text-warning"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content">
        <div class="container-fluid">
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'planning';
            switch ($page) {
                case 'salles':
                    include 'sections/salles.php';
                    $js_file = 'js/salles.js';
                    break;
                case 'promotions':
                    include 'sections/promotions.php';
                    $js_file = 'js/promotions.js';
                    break;
                case 'cours':
                    include 'sections/cours.php';
                    $js_file = 'js/cours.js';
                    break;
                case 'planning':
                default:
                    include 'sections/planning.php';
                    $js_file = 'js/admin.js';
                    break;
            }
            ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $js_file; ?>"></script>
</body>
</html>
