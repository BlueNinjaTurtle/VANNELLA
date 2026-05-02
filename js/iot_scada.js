/**
 * js/iot_scada.js
 * Système SCADA temps réel - Polling intelligent + Notifications
 * Supervision IoT professionnelle
 */

$(document).ready(function() {
    // Configuration
    const config = {
        pollingInterval: 2000,        // 2 secondes
        maxTimelineEvents: 30,
        offlineThreshold: 30000,      // 30 secondes
        updateAnimationDuration: 500,
    };

    // État global
    let state = {
        salles: {},
        previousStates: {},
        timeline: [],
        lastUpdate: null,
        isPolling: true,
    };

    // =============================================
    // INITIALISATION
    // =============================================

    function init() {
        console.log('🎯 SCADA Dashboard initialisation...');
        loadSalles();
        startPolling();
        initEventListeners();
    }

    function initEventListeners() {
        // Clic sur carte → Afficher détails
        $(document).on('click', '.scada-salle-card', function() {
            const id = $(this).data('id');
            showSalleDetail(id);
        });

        // Modal détails
        $('#salle-detail-modal').on('show.bs.modal', function(e) {
            const id = $(e.relatedTarget).data('id');
            loadSalleDetail(id);
        });
    }

    // =============================================
    // POLLING EN TEMPS RÉEL
    // =============================================

    function startPolling() {
        // Premier chargement immédiat
        loadSalles();

        // Puis polling régulier
        setInterval(function() {
            if (state.isPolling) {
                loadSalles();
            }
        }, config.pollingInterval);

        // Mettre à jour l'affichage du temps depuis dernière mise à jour
        setInterval(updateLastUpdateDisplay, 1000);
    }

    function updateLastUpdateDisplay() {
        if (state.lastUpdate) {
            const now = new Date();
            const seconds = Math.floor((now - state.lastUpdate) / 1000);
            const time = state.lastUpdate.toLocaleTimeString('fr-FR');
            $('#last-update').text(`Dernière mise à jour : ${time} (${seconds}s)`);
        }
    }

    // =============================================
    // CHARGEMENT SALLES (AJAX)
    // =============================================

    function loadSalles() {
        $.ajax({
            url: 'api/getSalles.php?t=' + Date.now(),
            method: 'GET',
            dataType: 'json',
            cache: false,
            timeout: 5000,
            success: function(response) {
                if (response.status === 'success') {
                    state.lastUpdate = new Date();
                    renderSalles(response.data);
                    updateConnectionStatus(true);
                    updateStats(response.data);
                } else {
                    updateConnectionStatus(false);
                    showToast('Erreur API', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                updateConnectionStatus(false);
                console.error('Erreur AJAX:', error);
            }
        });
    }

    // =============================================
    // RENDU SALLES
    // =============================================

    function renderSalles(salles) {
        const $grid = $('#scada-salles-grid');

        // Si premier chargement
        if (Object.keys(state.salles).length === 0) {
            $grid.empty();
        }

        salles.forEach(salle => {
            const id = salle.id_salle;
            const $card = $(`[data-id="${id}"]`);

            if ($card.length === 0) {
                // Nouvelle salle → créer la carte
                const $newCard = createSalleCard(salle);
                $grid.append($newCard);
                state.salles[id] = salle;
            } else {
                // Salle existante → vérifier changement
                const previousState = state.previousStates[id];
                const hasChanged = previousState && 
                    (previousState.etat !== salle.etat || 
                     previousState.iot_status !== salle.iot_status);

                if (hasChanged) {
                    // Animation de changement
                    $card.addClass('updating');
                    setTimeout(() => $card.removeClass('updating'), config.updateAnimationDuration);

                    // Notification
                    showToast(
                        `📍 ${salle.nom_salle}`,
                        `${salle.etat.toUpperCase()} (via ${salle.etat_source})`,
                        salle.etat === 'occupée' ? 'occupee' : 'libre'
                    );

                    // Historique
                    addTimelineEvent(
                        salle.nom_salle,
                        previousState?.etat,
                        salle.etat,
                        salle.etat_source
                    );
                }

                // Mettre à jour la carte
                updateSalleCard($card, salle);
                state.salles[id] = salle;
            }

            // Mémoriser l'état pour prochaine comparaison
            state.previousStates[id] = JSON.parse(JSON.stringify(salle));
        });

        // Nettoyer les salles qui n'existent plus
        $grid.find('.scada-salle-card').each(function() {
            const id = $(this).data('id');
            if (!salles.find(s => s.id_salle == id)) {
                $(this).fadeOut(300, function() { $(this).remove(); });
            }
        });
    }

    function createSalleCard(salle) {
        const classe = salle.etat === 'libre' ? 'libre' : 
                      salle.etat === 'occupée' ? 'occupee' : 'offline';

        const icon = salle.etat === 'libre' ? '🟢' : 
                    salle.etat === 'occupée' ? '🔴' : '⚫';

        const iotIcon = salle.iot_status === 'online' ? '✓' : 
                       salle.iot_status === 'offline' ? '✗' : '?';

        const html = `
            <div class="scada-salle-card ${classe}" data-id="${salle.id_salle}" data-bs-toggle="modal" data-bs-target="#salle-detail-modal">
                <div class="scada-salle-header">
                    <div class="salle-name">${salle.nom_salle}</div>
                    <div class="salle-id">ID: ${salle.id_salle}</div>
                </div>

                <div class="scada-salle-state">
                    <div class="state-icon">${icon}</div>
                    <div class="state-text">${salle.etat}</div>
                </div>

                <div class="scada-salle-info">
                    <div class="info-row">
                        <span class="info-label">Source:</span>
                        <span class="badge-source ${salle.etat_source.toLowerCase()}">${salle.etat_source}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Statut IoT:</span>
                        <span class="info-value">
                            <span style="color: ${salle.iot_status === 'online' ? '#10b981' : '#6b7280'}">
                                ${iotIcon}
                            </span>
                            ${salle.iot_status}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mise à jour:</span>
                        <span class="info-value" title="${formatDateTime(new Date(salle.date_update))}">
                            ${getTimeAgo(new Date(salle.date_update))}
                        </span>
                    </div>
                </div>
            </div>
        `;

        return $(html);
    }

    function updateSalleCard($card, salle) {
        const classe = salle.etat === 'libre' ? 'libre' : 
                      salle.etat === 'occupée' ? 'occupee' : 'offline';

        const icon = salle.etat === 'libre' ? '🟢' : 
                    salle.etat === 'occupée' ? '🔴' : '⚫';

        const iotIcon = salle.iot_status === 'online' ? '✓' : 
                       salle.iot_status === 'offline' ? '✗' : '?';

        // Mettre à jour la classe
        $card.removeClass('libre occupee offline').addClass(classe);

        // Mettre à jour le contenu
        $card.find('.state-icon').text(icon);
        $card.find('.state-text').text(salle.etat);
        $card.find('.badge-source').text(salle.etat_source).removeClass('iot admin planning').addClass(salle.etat_source.toLowerCase());
        $card.find('.info-value:eq(0)').html(`
            <span style="color: ${salle.iot_status === 'online' ? '#10b981' : '#6b7280'}">
                ${iotIcon}
            </span>
            ${salle.iot_status}
        `);
        $card.find('.info-value:eq(1)').text(getTimeAgo(new Date(salle.date_update)))
            .attr('title', formatDateTime(new Date(salle.date_update)));
    }

    // =============================================
    // STATS ET COMPTEURS
    // =============================================

    function updateStats(salles) {
        let libre = 0, occupee = 0, offline = 0;

        salles.forEach(salle => {
            if (salle.iot_status === 'offline') {
                offline++;
            } else if (salle.etat === 'libre') {
                libre++;
            } else {
                occupee++;
            }
        });

        $('#stat-libre').text(libre);
        $('#stat-occupee').text(occupee);
        $('#stat-offline').text(offline);
    }

    // =============================================
    // STATUS CONNEXION
    // =============================================

    function updateConnectionStatus(connected) {
        const $indicator = $('#connection-indicator');
        if (connected) {
            $indicator.find('.pulse-dot').removeClass('offline').addClass('online');
            $indicator.find('.connection-text').text('Serveur connecté');
        } else {
            $indicator.find('.pulse-dot').removeClass('online').addClass('offline');
            $indicator.find('.connection-text').text('Serveur déconnecté');
        }
    }

    // =============================================
    // TIMELINE ÉVÉNEMENTS
    // =============================================

    function addTimelineEvent(salleName, oldState, newState, source) {
        const now = new Date();
        const time = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        const event = {
            time: time,
            salle: salleName,
            oldState: oldState,
            newState: newState,
            source: source,
            timestamp: now
        };

        state.timeline.unshift(event);

        // Limiter à X événements
        if (state.timeline.length > config.maxTimelineEvents) {
            state.timeline.pop();
        }

        renderTimeline();
    }

    function renderTimeline() {
        const $timeline = $('#timeline-events');

        if (state.timeline.length === 0) {
            $timeline.html('<div class="timeline-empty"><p>En attente d\'activité...</p></div>');
            return;
        }

        let html = '';
        state.timeline.forEach(event => {
            const classe = event.newState === 'libre' ? 'libre' : 'occupee';
            html += `
                <div class="timeline-event ${classe}">
                    <span class="timeline-time">${event.time}</span>
                    <div class="timeline-text">
                        <span class="timeline-salle">${event.salle}</span>
                        <span class="mx-2">→</span>
                        <span class="text-uppercase">${event.newState}</span>
                        <span class="ms-2" style="font-size: 0.75rem; opacity: 0.7;">(${event.source})</span>
                    </div>
                </div>
            `;
        });

        $timeline.html(html);
    }

    // =============================================
    // DÉTAILS SALLE (MODAL)
    // =============================================

    function showSalleDetail(id) {
        loadSalleDetail(id);
    }

    function loadSalleDetail(id) {
        const salle = state.salles[id];
        if (!salle) return;

        $('#modal-salle-name').text(salle.nom_salle);
        $('#modal-etat').text(salle.etat.toUpperCase());
        $('#modal-source').html(`<span class="badge-source ${salle.etat_source.toLowerCase()}">${salle.etat_source}</span>`);
        $('#modal-iot-status').html(`
            <span style="color: ${salle.iot_status === 'online' ? '#10b981' : '#6b7280'}">
                ${salle.iot_status === 'online' ? '✓ Online' : '✗ Offline'}
            </span>
        `);
        $('#modal-last-update').text(formatDateTime(new Date(salle.date_update)));

        // Charger l'historique
        loadSalleHistory(id);
    }

    function loadSalleHistory(id) {
        $.ajax({
            url: `api/getEtatHistory.php?id_salle=${id}&limit=10`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    renderHistoryList(response.data);
                }
            }
        });
    }

    function renderHistoryList(history) {
        const $historyContainer = $('#modal-history');

        if (history.length === 0) {
            $historyContainer.html('<p class="text-muted">Aucun historique disponible</p>');
            return;
        }

        let html = '';
        history.forEach(item => {
            const time = formatDateTime(new Date(item.timestamp));
            html += `
                <div class="history-item">
                    <div class="history-time">${time}</div>
                    <div class="history-change">
                        <strong>${item.etat_ancien}</strong> → <strong>${item.etat_nouveau}</strong>
                        <span class="badge-source ${item.modified_by.toLowerCase()} ms-2">${item.modified_by}</span>
                    </div>
                    ${item.raison ? `<div class="text-muted small mt-1"><em>${item.raison}</em></div>` : ''}
                </div>
            `;
        });

        $historyContainer.html(html);
    }

    // =============================================
    // NOTIFICATIONS (TOAST)
    // =============================================

    function showToast(title, message, type = 'info') {
        const icons = {
            'libre': 'fa-door-open',
            'occupee': 'fa-door-closed',
            'error': 'fa-exclamation-circle',
            'info': 'fa-info-circle'
        };

        const icon = icons[type] || icons['info'];

        const html = `
            <div class="scada-toast">
                <i class="fas ${icon} toast-icon"></i>
                <div class="toast-content">
                    <p class="toast-title">${title}</p>
                    <p class="toast-message">${message}</p>
                </div>
                <button type="button" class="toast-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        const $toast = $(html);
        $('#toast-container').append($toast);

        $toast.find('.toast-close').on('click', function() {
            $toast.fadeOut(300, function() { $(this).remove(); });
        });

        // Auto-remove après 5 secondes
        setTimeout(() => {
            $toast.fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }

    // =============================================
    // UTILITÉS
    // =============================================

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        if (seconds < 60) return `${seconds}s`;
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`;
        return `${Math.floor(seconds / 86400)}j`;
    }

    function formatDateTime(date) {
        return date.toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // =============================================
    // DÉMARRAGE
    // =============================================

    init();
});
