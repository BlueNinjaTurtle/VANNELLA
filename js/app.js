/**
 * js/app.js - Version jQuery/Bootstrap
 * Gère l'actualisation du dashboard en temps réel.
 */

$(document).ready(function() {
    const $dashboardRow = $('#dashboard-row');
    const $statusIndicator = $('#connection-status');

    /**
     * Récupère les données des salles via l'API.
     */
    function loadSalles() {
        $.ajax({
            url: 'api/getSalles.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderSalles(response.data);
                    $statusIndicator.removeClass('bg-danger').addClass('bg-success').html('<i class="fas fa-check me-1"></i> Connecté');
                } else {
                    handleError('Erreur API: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                handleError('Erreur de connexion serveur.');
                $statusIndicator.removeClass('bg-success').addClass('bg-danger').html('<i class="fas fa-times me-1"></i> Erreur');
            }
        });
    }

    /**
     * Génère le HTML pour chaque salle.
     */
    function renderSalles(salles) {
        $dashboardRow.empty();

        if (salles.length === 0) {
            $dashboardRow.append('<div class="col-12 text-center alert alert-info">Aucune salle configurée.</div>');
            return;
        }

        salles.forEach(salle => {
            // Nettoyage de l'état pour les classes CSS (suppression accents)
            const cleanStatus = salle.etat.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            
            // Configuration de l'icône et couleur de badge selon l'état
            let icon = 'fa-door-open';
            let badgeClass = 'bg-success';
            let statusIconClass = 'bg-libre';

            if (cleanStatus === 'occupee') {
                icon = 'fa-door-closed';
                badgeClass = 'bg-danger';
                statusIconClass = 'bg-occupee';
            } else if (cleanStatus === 'reservee') {
                icon = 'fa-lock';
                badgeClass = 'bg-warning text-dark';
                statusIconClass = 'bg-reservee';
            }

            const salleHtml = `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center bg-white pt-3 px-3">
                             <h5 class="card-title fw-bold mb-0">${salle.nom_salle}</h5>
                             <span class="badge ${badgeClass} text-uppercase">${salle.etat}</span>
                        </div>
                        <div class="card-body">
                            ${salle.cours_actuel ? `
                                <div class="alert alert-primary py-2 mb-3">
                                    <small class="d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Cours en cours :</small>
                                    <div class="fw-bold">${salle.cours_actuel}</div>
                                    <small>${salle.promo_actuelle}</small>
                                </div>
                            ` : `
                                <div class="alert alert-light py-2 mb-3 border text-muted">
                                    <small><i class="fas fa-info-circle me-1"></i> Aucun cours prévu</small>
                                </div>
                            `}
                            
                            <div class="d-flex align-items-center mb-3 opacity-75">
                                <i class="fa-solid ${icon} fa-2x text-secondary me-3"></i>
                                <div>
                                    <div class="text-muted small">Localisation:</div>
                                    <div class="fw-semibold">${salle.batiment}</div>
                                </div>
                            </div>
                            
                            <div class="row text-center mt-3 border-top pt-3">
                                <div class="col-6 border-end">
                                    <div class="text-muted small">Capacité</div>
                                    <div class="fw-bold">${salle.capacite} pl.</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">État Réel</div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="status-indicator ${statusIconClass}"></div>
                                        <div class="fw-bold">${salle.etat}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-0 py-2">
                            <small class="text-muted italic">
                                <i class="fa-regular fa-clock me-1"></i> MàJ IoT: ${salle.date_update ? formatTime(salle.date_update) : 'Jamais'}
                            </small>
                        </div>
                    </div>
                </div>
            `;
            $dashboardRow.append(salleHtml);
        });
    }

    /**
     * Formate une date SQL en heure lisible.
     */
    function formatTime(sqlDate) {
        const date = new Date(sqlDate);
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }

    function handleError(msg) {
        console.error(msg);
    }

    // Premier chargement
    loadSalles();

    // Actualisation toutes les 5 secondes
    setInterval(loadSalles, 5000);
});
