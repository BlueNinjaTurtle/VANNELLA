/**
 * js/app.js - Version jQuery/Bootstrap
 * Gère l'actualisation du dashboard en temps réel avec détection IoT
 */

$(document).ready(function() {
    const $dashboardRow = $('#dashboard-row');
    const $statusIndicator = $('#connection-status');
    
    // Stockage des états précédents pour détecter les changements IoT
    let previousStates = {};

    /**
     * Récupère les données des salles via l'API (avec cache-busting).
     */
    function loadSalles() {
        $.ajax({
            url: 'api/getSalles.php?t=' + Date.now(),
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response.status === 'success') {
                    renderSalles(response.data);
                    $statusIndicator.removeClass('bg-danger').addClass('bg-success')
                        .html('<i class="fas fa-check me-1"></i> Connecté');
                } else {
                    handleError('Erreur API: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                handleError('Erreur de connexion serveur.');
                $statusIndicator.removeClass('bg-success').addClass('bg-danger')
                    .html('<i class="fas fa-times me-1"></i> Erreur');
            }
        });
    }

    /**
     * Affiche une notification toast temporaire.
     */
    function showNotification(salleName, oldStatus, newStatus) {
        const icon = newStatus === 'occupée' ? 'fa-door-closed' : 
                     newStatus === 'libre' ? 'fa-door-open' : 'fa-lock';
        const color = newStatus === 'occupée' ? '#dc3545' : 
                      newStatus === 'libre' ? '#28a745' : '#ffc107';
        
        const toast = `
            <div class="alert alert-info position-fixed bottom-0 end-0 m-3" 
                 style="z-index: 9999; max-width: 400px; animation: slideIn 0.3s ease;" 
                 role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas ${icon} me-2" style="color: ${color}; font-size: 1.5rem;"></i>
                    <div>
                        <strong>${salleName}</strong>
                        <div class="small">
                            <span class="badge" style="background-color: ${color};">${newStatus}</span>
                            <span class="ms-2">📱 Mise à jour IoT détectée</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const $toast = $(toast);
        $('body').append($toast);
        
        // Auto-remove après 4 secondes
        setTimeout(() => {
            $toast.fadeOut(300, function() { $(this).remove(); });
        }, 4000);
    }

    /**
     * Génère le HTML pour chaque salle avec détection de changement IoT.
     */
    function renderSalles(salles) {
        $dashboardRow.empty();

        if (salles.length === 0) {
            $dashboardRow.append(
                '<div class="col-12 text-center alert alert-info">Aucune salle configurée.</div>'
            );
            return;
        }

        salles.forEach(salle => {
            // Nettoyage de l'état pour les classes CSS
            const cleanStatus = salle.etat.normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "").toLowerCase();
            
            // Détection de changement d'état (IoT update)
            const oldStatus = previousStates[salle.id_salle];
            const hasChanged = oldStatus && oldStatus !== salle.etat;
            
            if (hasChanged) {
                showNotification(salle.nom_salle, oldStatus, salle.etat);
            }
            
            // Mise à jour de l'état stocké
            previousStates[salle.id_salle] = salle.etat;
            
            // Configuration de l'icône et couleur
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

            // Animation de mise à jour si changement détecté
            const animationClass = hasChanged ? 'highlight-change' : '';

            const salleHtml = `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 transition-all ${animationClass}" 
                         data-salle-id="${salle.id_salle}">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center bg-white pt-3 px-3">
                            <h5 class="card-title fw-bold mb-0">${salle.nom_salle}</h5>
                            <div class="d-flex gap-2">
                                ${hasChanged ? '<span class="badge bg-info animate-pulse">🔄 IoT</span>' : ''}
                                <span class="badge ${badgeClass} text-uppercase">${salle.etat}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            ${salle.cours_actuel ? `
                                <div class="alert alert-primary py-2 mb-3">
                                    <small class="d-block text-uppercase fw-bold" style="font-size: 0.7rem;">
                                        Cours en cours :
                                    </small>
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
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted italic">
                                    <i class="fa-regular fa-clock me-1"></i> 
                                    ${salle.date_update ? formatTime(salle.date_update) : 'Jamais'}
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                <div>
                                    <span class="badge bg-info">
                                        <i class="fa-solid fa-code-branch me-1"></i> 
                                        ${salle.etat_source || 'System'}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge ${salle.iot_status === 'online' ? 'bg-success' : salle.iot_status === 'offline' ? 'bg-danger' : 'bg-secondary'}">
                                        <i class="fa-solid ${salle.iot_status === 'online' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-1"></i>
                                        ${salle.iot_status === 'online' ? 'IoT ✓' : salle.iot_status === 'offline' ? 'IoT ✗' : 'IoT ?'}
                                    </span>
                                </div>
                            </div>
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

    // ⚡ ACTUALISATION RAPIDE: Toutes les 2 secondes au lieu de 5
    // (IoT change l'état rapidement, on veut le voir en quasi-temps réel)
    setInterval(loadSalles, 2000);

    // Optionnel: Permettre un refresh manuel
    $(document).on('keydown', function(e) {
        if (e.key === 'r' && e.ctrlKey) {
            e.preventDefault();
            loadSalles();
            console.log('🔄 Refresh manuel!');
        }
    });
});

