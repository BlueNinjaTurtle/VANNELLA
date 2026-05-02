<?php
/**
 * sections/dashboard_iot_pro.php
 * Dashboard SCADA professionnel - Supervision IoT temps réel
 * Inspiré SCADA industriel avec états visuels et indicateurs temps réel
 */
?>

<div class="container-fluid scada-container">
    <!-- Header SCADA -->
    <div class="scada-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="scada-title">
                    <i class="fas fa-microchip"></i>
                    Système de Supervision IoT
                </div>
                <div class="scada-subtitle">Monitoring temps réel des salles en cours</div>
            </div>
            <div class="col-md-4 text-end">
                <div class="scada-stats">
                    <div class="stat-box">
                        <div class="stat-value" id="stat-libre">-</div>
                        <div class="stat-label">
                            <i class="fas fa-door-open"></i> Libres
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="stat-occupee">-</div>
                        <div class="stat-label">
                            <i class="fas fa-door-closed"></i> Occupées
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="stat-offline">-</div>
                        <div class="stat-label">
                            <i class="fas fa-wifi-slash"></i> Hors ligne
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connexion et Statuts -->
    <div class="scada-connection-bar">
        <div class="connection-status" id="connection-indicator">
            <span class="pulse-dot online"></span>
            <span class="connection-text">Serveur connecté</span>
        </div>
        <div class="connection-stats">
            <span id="last-update">Dernière mise à jour : --:--:--</span>
            <span class="ms-3" id="update-frequency">Polling : 2s</span>
        </div>
    </div>

    <!-- Carte des Salles SCADA -->
    <div class="scada-grid" id="scada-salles-grid">
        <!-- Salles chargées dynamiquement par AJAX -->
        <div class="scada-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p>Chargement des salles...</p>
        </div>
    </div>

    <!-- Historique en temps réel -->
    <div class="scada-timeline">
        <div class="timeline-header">
            <i class="fas fa-history"></i>
            Activité en temps réel
        </div>
        <div class="timeline-container" id="timeline-events">
            <!-- Événements ajoutés dynamiquement -->
            <div class="timeline-empty">
                <p>En attente d'activité...</p>
            </div>
        </div>
    </div>

    <!-- Modal pour détails salle -->
    <div class="modal fade" id="salle-detail-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header scada-header-modal">
                    <h5 class="modal-title" id="modal-salle-name">Détails Salle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label>État actuel</label>
                                <div class="detail-value" id="modal-etat">-</div>
                            </div>
                            <div class="detail-group">
                                <label>Source modification</label>
                                <div class="detail-value" id="modal-source">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-group">
                                <label>Statut IoT</label>
                                <div class="detail-value" id="modal-iot-status">-</div>
                            </div>
                            <div class="detail-group">
                                <label>Dernière mise à jour</label>
                                <div class="detail-value small" id="modal-last-update">-</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="detail-group">
                        <label>Historique récent</label>
                        <div id="modal-history" class="history-list">
                            <!-- Historique chargé dynamiquement -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Toast (système) -->
    <div class="toast-container" id="toast-container">
        <!-- Toasts ajoutés dynamiquement -->
    </div>
</div>

<!-- Script SCADA -->
<script src="js/iot_scada.js"></script>

<style>
    /* Styles SCADA importés directement pour éviter les conflits */
    @import url('css/iot_scada.css');
</style>
