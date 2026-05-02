<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary-soft p-2 rounded-3 me-3 text-primary">
            <i class="fas fa-history fs-4"></i>
        </div>
        <h5 class="mb-0 fw-bold">Historique des Changements d'État</h5>
    </div>
    <div>
        <select id="filter-salle" class="form-select form-select-sm d-inline-block" style="width: auto;">
            <option value="">Toutes les salles...</option>
        </select>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 py-3 ps-4 text-muted small text-uppercase fw-bold">Salle</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold text-center">État Ancien</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">→</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold text-center">État Nouveau</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Source</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Raison</th>
                    <th class="border-0 py-3 pe-4 text-muted small text-uppercase fw-bold text-center">Date/Heure</th>
                </tr>
            </thead>
            <tbody id="table-history">
                <tr><td colspan="7" class="text-center py-4 text-muted">Chargement...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    const pageSize = 100;
    let currentOffset = 0;

    // Charger les salles dans le filtre
    $.get('api/getSalles.php', function(res) {
        if(res.status === 'success') {
            let options = '<option value="">Toutes les salles...</option>';
            res.data.forEach(s => {
                options += `<option value="${s.id_salle}">${s.nom_salle}</option>`;
            });
            $('#filter-salle').html(options);
        }
    });

    // Charger l'historique
    function loadHistory() {
        const salleId = $('#filter-salle').val();
        const url = salleId ? 
            `api/getEtatHistory.php?id_salle=${salleId}&limit=${pageSize}` :
            'api/getEtatHistory.php';  // À améliorer: créer un endpoint pour tous

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    renderHistory(res.data);
                } else {
                    $('#table-history').html(`
                        <tr><td colspan="7" class="text-center py-4 text-danger">
                            Erreur: ${res.message}
                        </td></tr>
                    `);
                }
            },
            error: function() {
                $('#table-history').html(`
                    <tr><td colspan="7" class="text-center py-4 text-danger">
                        Erreur de connexion
                    </td></tr>
                `);
            }
        });
    }

    function renderHistory(data) {
        if(data.length === 0) {
            $('#table-history').html(`
                <tr><td colspan="7" class="text-center py-4 text-muted">
                    Aucun historique trouvé
                </td></tr>
            `);
            return;
        }

        let html = '';
        data.forEach(h => {
            // Couleur selon la source
            let sourceBadgeClass = 'bg-secondary';
            if (h.modified_by === 'IoT') sourceBadgeClass = 'bg-warning';
            else if (h.modified_by === 'Admin') sourceBadgeClass = 'bg-danger';
            else if (h.modified_by === 'Planning') sourceBadgeClass = 'bg-info';

            // Couleur ancien état
            let oldStateColor = 'bg-light';
            if (h.etat_ancien === 'occupée') oldStateColor = 'bg-danger-soft text-danger';
            else if (h.etat_ancien === 'libre') oldStateColor = 'bg-success-soft text-success';
            else if (h.etat_ancien === 'réservée') oldStateColor = 'bg-warning-soft text-warning';

            // Couleur nouvel état
            let newStateColor = 'bg-light';
            if (h.etat_nouveau === 'occupée') newStateColor = 'bg-danger-soft text-danger';
            else if (h.etat_nouveau === 'libre') newStateColor = 'bg-success-soft text-success';
            else if (h.etat_nouveau === 'réservée') newStateColor = 'bg-warning-soft text-warning';

            const timestamp = new Date(h.timestamp);
            const formattedTime = timestamp.toLocaleString('fr-FR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            html += `
                <tr>
                    <td class="ps-4 fw-bold text-dark">${h.nom_salle}</td>
                    <td class="text-center">
                        <span class="badge px-2 py-1 ${oldStateColor}">
                            ${h.etat_ancien || '—'}
                        </span>
                    </td>
                    <td class="text-center fw-bold">→</td>
                    <td class="text-center">
                        <span class="badge px-2 py-1 ${newStateColor}">
                            ${h.etat_nouveau}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${sourceBadgeClass}">
                            ${h.modified_by}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">${h.raison || '—'}</small>
                    </td>
                    <td class="pe-4 text-center">
                        <small class="text-muted">${formattedTime}</small>
                    </td>
                </tr>
            `;
        });

        $('#table-history').html(html);
    }

    // Premier chargement
    loadHistory();

    // Charger quand filtre change
    $('#filter-salle').on('change', function() {
        loadHistory();
    });

    // Actualiser toutes les 10 secondes
    setInterval(loadHistory, 10000);
});
</script>
