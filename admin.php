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
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --ispt-primary: #004a99;
            --ispt-primary-soft: rgba(0, 74, 153, 0.1);
            --ispt-secondary: #f0f4f8;
            --sidebar-width: 280px;
            --topbar-height: 70px;
            --text-main: #2d3748;
            --text-muted: #718096;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Sidebar Style */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-header {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 25px;
            border-bottom: 1px solid #f1f5f9;
        }

        .sidebar-logo {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--ispt-primary);
            letter-spacing: -0.5px;
        }

        .nav-custom {
            padding: 20px 15px;
        }

        .nav-custom .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 5px;
            color: var(--text-muted);
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-custom .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .nav-custom .nav-link:hover {
            background: var(--ispt-secondary);
            color: var(--ispt-primary);
        }

        .nav-custom .nav-link.active {
            background: var(--ispt-primary-soft);
            color: var(--ispt-primary);
        }

        /* Main Content & Topbar */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            transition: all 0.3s;
        }

        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-area {
            padding: 30px;
        }

        /* Card Modernization */
        .admin-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 24px;
        }

        .page-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0;
            color: var(--text-main);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px;
            border-radius: 50px;
            background: var(--ispt-secondary);
            cursor: pointer;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--ispt-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #main-wrapper { margin-left: 0; width: 100%; }
            #sidebar.active { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <span class="sidebar-logo">
                <img src="logo.png" alt="Logo ISPT" style="height: 42px; margin-right: 8px; vertical-align: middle;">
                ISPT Likasi
            </span>
        </div>
        <div class="nav flex-column nav-custom">
            <small class="text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.65rem; letter-spacing: 1px;">Menu Principal</small>
            <a href="admin.php?page=planning" class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'planning') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Planning
            </a>
            <a href="admin.php?page=salles" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'salles') ? 'active' : ''; ?>">
                <i class="fas fa-door-open"></i> Gestion Salles
            </a>
            <a href="admin.php?page=promotions" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'promotions') ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> Promotions
            </a>
            <a href="admin.php?page=cours" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'cours') ? 'active' : ''; ?>">
                <i class="fas fa-book-reader"></i> Cours & Matières
            </a>
            
            <div class="mt-4 pt-4 border-top">
                <small class="text-uppercase fw-bold text-muted mb-2 px-3" style="font-size: 0.65rem; letter-spacing: 1px;">Système</small>
                <a href="index.php" class="nav-link">
                    <i class="fas fa-external-link-alt"></i> Voir le site
                </a>
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-power-off"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Wrapper -->
    <div id="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="d-flex align-items-center">
                <button id="sidebarCollapse" class="btn btn-link d-lg-none p-0 me-3">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
                <h1 class="page-title">
                    <?php 
                    $page = $_GET['page'] ?? 'planning';
                    echo ucfirst($page);
                    ?>
                </h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="user-profile">
                    <div class="user-avatar">AD</div>
                    <span class="d-none d-sm-inline fw-semibold small text-dark">Administrateur</span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="content-area">
            <div class="container-fluid">
                <?php
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
    <script src="<?php echo $js_file; ?>"></script>
</body>
</html>
