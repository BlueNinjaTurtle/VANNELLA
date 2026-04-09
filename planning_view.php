<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planning Hebdomadaire - ISPT-Likasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --ispt-primary: #004a99;
            --ispt-secondary: #f0f4f8;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        }

        .navbar-custom {
            background: var(--ispt-primary);
            border-bottom: 4px solid var(--warning);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .controls-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .week-dates {
            background: var(--ispt-secondary);
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid var(--ispt-primary);
            font-weight: 600;
            color: var(--ispt-primary);
        }

        .planning-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .day-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .day-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .day-header {
            background: var(--ispt-primary);
            color: white;
            padding: 15px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .day-header small {
            display: block;
            font-size: 0.85rem;
            opacity: 0.8;
            margin-top: 5px;
        }

        .courses-container {
            padding: 15px;
            min-height: 100px;
        }

        .course-item {
            background: var(--ispt-secondary);
            border-left: 4px solid var(--ispt-primary);
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .course-item:last-child {
            margin-bottom: 0;
        }

        .course-item:hover {
            background: #e8ecf5;
        }

        .course-time {
            font-weight: 700;
            color: var(--ispt-primary);
            font-size: 0.9rem;
        }

        .course-name {
            font-weight: 600;
            margin: 5px 0;
            color: #2d3748;
        }

        .course-room {
            font-size: 0.85rem;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .no-courses {
            text-align: center;
            color: #a0aec0;
            padding: 20px 10px;
            font-style: italic;
        }

        .salles-section {
            margin-top: 40px;
        }

        .salles-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--ispt-primary);
        }

        .salles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .salle-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-top: 4px solid var(--ispt-primary);
            text-align: center;
            position: relative;
            transition: all 0.2s;
        }

        .salle-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        .salle-status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-libre {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-occupee {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-reservee {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .salle-name {
            font-weight: 700;
            color: var(--ispt-primary);
            margin-bottom: 10px;
        }

        .salle-info {
            font-size: 0.9rem;
            color: #718096;
            line-height: 1.6;
        }

        .capacity-badge {
            display: inline-block;
            background: var(--ispt-secondary);
            color: var(--ispt-primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 10px;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        .nav-btn {
            background: var(--ispt-primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
            font-weight: 600;
        }

        .nav-btn:hover {
            background: #003a7a;
            color: white;
            text-decoration: none;
        }

        .select-promotion {
            min-width: 250px;
        }

        .footer-section {
            text-align: center;
            padding: 20px;
            color: #718096;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .planning-grid {
                grid-template-columns: 1fr;
            }

            .controls-container {
                flex-direction: column;
                align-items: stretch;
            }

            .select-promotion {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-school me-2"></i> ISPT-LIKASI
            </a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="fas fa-user-shield me-1"></i> Admin
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container mt-5 pb-5">

        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-2 fw-bold">
                        <i class="fas fa-calendar-week me-2" style="color: var(--ispt-primary);"></i>
                        Planning Hebdomadaire
                    </h1>
                    <p class="text-muted mb-0">Visualisez les horaires des cours par semaine et promotion</p>
                </div>
            </div>

            <div class="controls-container">
                <select id="select-promotion" class="form-select select-promotion">
                    <option value="">Charger une promotion...</option>
                </select>

                <div class="week-dates" id="week-dates" style="display: none;">
                    <i class="fas fa-calendar me-2"></i>
                    <span id="date-range">Semaine du</span>
                </div>

                <div class="nav-buttons">
                    <button id="prev-week" class="nav-btn" style="display: none;">
                        <i class="fas fa-chevron-left me-2"></i> Semaine Précédente
                    </button>
                    <button id="next-week" class="nav-btn" style="display: none;">
                        Semaine Suivante <i class="fas fa-chevron-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Planning Section -->
        <div id="planning-container" style="display: none;">

            <!-- Days Grid -->
            <div class="planning-grid" id="planning-grid">
                <!-- Injecté par le JS -->
            </div>

            <!-- Salles Disponibles Section -->
            <div class="salles-section">
                <h2 class="salles-title">
                    <i class="fas fa-door-open me-2"></i> Toutes les Salles & État en Direct
                </h2>
                <div class="salles-grid" id="salles-grid">
                    <!-- Injecté par le JS -->
                </div>
            </div>

        </div>

        <!-- Empty State -->
        <div id="empty-state" class="text-center py-5">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #cbd5e0;"></i>
            <p class="text-muted mt-3">Sélectionnez une promotion pour afficher le planning</p>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer-section">
        <small>&copy; 2026 ISPT-Likasi - Module VANNELLA</small>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentDate = new Date();
            currentDate.setDate(currentDate.getDate() - currentDate.getDay() + 1); // Lundi

            // Charger les promotions
            function loadPromotions() {
                $.get('api/getPromotionsFull.php', function(res) {
                    if (res.status === 'success') {
                        let html = '<option value="">Sélectionner une promotion...</option>';
                        res.data.forEach(p => {
                            html += `<option value="${p.id_promotion}">${p.nom_promotion} (${p.effectif} étudiants)</option>`;
                        });
                        $('#select-promotion').html(html);
                    }
                });
            }

            // Charger le planning
            function loadPlanning(promotionId, date) {
                if (!promotionId) return;

                const dateStr = date.toISOString().split('T')[0];
                $.get('api/getHorairesByWeek.php', {
                    id_promotion: promotionId,
                    start_date: dateStr
                }, function(res) {
                    if (res.status === 'success') {
                        renderPlanning(res.data);
                        updateDateDisplay(res.data);
                        $('#planning-container').show();
                        $('#empty-state').hide();
                    }
                });
            }

            // Afficher le planning
            function renderPlanning(data) {
                const html = data.jours.map(jour => {
                    const courses = data.planning[jour] || [];
                    const coursesHtml = courses.map(course => `
                        <div class="course-item">
                            <div class="course-time">${course.heure_debut} - ${course.heure_fin}</div>
                            <div class="course-name">${course.nom_cours}</div>
                            <div class="course-room">
                                <i class="fas fa-door-open"></i>
                                ${course.nom_salle} (${course.capacite} places)
                            </div>
                            <small style="color: #a0aec0;">${course.batiment}</small>
                        </div>
                    `).join('');

                    return `
                        <div class="day-card">
                            <div class="day-header">
                                ${jour}
                                <small id="date-${jour}"></small>
                            </div>
                            <div class="courses-container">
                                ${coursesHtml || '<div class="no-courses">Pas de cours prévus</div>'}
                            </div>
                        </div>
                    `;
                }).join('');

                $('#planning-grid').html(html);

                // Afficher les dates
                const start = new Date(data.start_date);
                const jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                jours.forEach((jour, i) => {
                    const date = new Date(start);
                    date.setDate(date.getDate() + i);
                    $(`#date-${jour}`).text(date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }));
                });

                // Afficher les salles
                const sallesHtml = data.toutes_salles.map(salle => `
                    <div class="salle-card">
                        <div class="salle-status-badge status-${salle.etat}">
                            <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i>
                            ${salle.etat.charAt(0).toUpperCase() + salle.etat.slice(1)}
                        </div>
                        <div class="salle-name">${salle.nom_salle}</div>
                        <div class="salle-info">
                            <div><i class="fas fa-map-pin me-1"></i> ${salle.batiment}</div>
                            <div class="capacity-badge">
                                <i class="fas fa-users me-1"></i> ${salle.capacite} places
                            </div>
                        </div>
                    </div>
                `).join('');

                $('#salles-grid').html(sallesHtml);
            }

            // Mettre à jour l'affichage des dates
            function updateDateDisplay(data) {
                $('#date-range').text(`Semaine du ${data.start_date_display} au ${data.end_date_display}`);
                $('#week-dates').show();
            }

            // Navigation
            $('#prev-week').click(function() {
                currentDate.setDate(currentDate.getDate() - 7);
                loadPlanning($('#select-promotion').val(), new Date(currentDate));
            });

            $('#next-week').click(function() {
                currentDate.setDate(currentDate.getDate() + 7);
                loadPlanning($('#select-promotion').val(), new Date(currentDate));
            });

            $('#select-promotion').change(function() {
                const promotionId = $(this).val();
                if (promotionId) {
                    currentDate = new Date();
                    currentDate.setDate(currentDate.getDate() - currentDate.getDay() + 1);
                    $('#prev-week, #next-week').show();
                    loadPlanning(promotionId, currentDate);
                } else {
                    $('#planning-container').hide();
                    $('#empty-state').show();
                    $('#prev-week, #next-week').hide();
                }
            });

            loadPromotions();
        });
    </script>

</body>
</html>
