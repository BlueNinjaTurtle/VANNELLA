<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ISPT-Likasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --ispt-blue: #004a99; }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        .login-header {
            background: var(--ispt-blue);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .btn-primary {
            background: var(--ispt-blue);
            border: none;
            padding: 12px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: #003a7a;
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <div class="login-card bg-white">
        <div class="login-header">
            <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                <img src="logo.png" alt="Logo ISPT" style="height: 40px;">
                <h3 class="mb-0 fw-bold">ADMINISTRATION</h3>
            </div>
            <small class="opacity-75">ISPT-Likasi - Module D'attribution Optimale Des Salles</small>
        </div>
        <div class="p-4">
            <form id="login-form">
                <div class="mb-3">
                    <label class="form-label">Nom d'utilisateur</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" id="username" class="form-control bg-light border-0" placeholder="Entrez votre nom" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" id="password" class="form-control bg-light border-0" placeholder="••••••••" required>
                    </div>
                </div>
                
                <div id="login-error" class="alert alert-danger d-none mb-3 py-2 small"></div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    SE CONNECTER <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>
            <div class="text-center">
                <a href="index.php" class="text-muted small text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button');
                const $error = $('#login-error');
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Vérification...');
                $error.addClass('d-none');

                $.ajax({
                    url: 'api/login.php',
                    method: 'POST',
                    data: JSON.stringify({
                        username: $('#username').val(),
                        password: $('#password').val()
                    }),
                    contentType: 'application/json',
                    success: function(res) {
                        if(res.status === 'success') {
                            window.location.href = 'admin.php';
                        } else {
                            $btn.prop('disabled', false).html('SE CONNECTER <i class="fas fa-arrow-right ms-2"></i>');
                            $error.text(res.message).removeClass('d-none');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('SE CONNECTER <i class="fas fa-arrow-right ms-2"></i>');
                        $error.text("Une erreur serveur est survenue.").removeClass('d-none');
                    }
                });
            });
        });
    </script>
</body>
</html>
