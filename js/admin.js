/**
 * js/admin.js
 * Logique pour l'attribution optimale des salles.
 */

$(document).ready(function() {
    let selectedSalleId = null;

    // Charger les cours et promotions au démarrage
    init();

    function init() {
        // Charger Cours
        $.get('api/getCours.php', function(res) {
            if(res.status === 'success') {
                let html = '<option value="">Choisir un cours...</option>';
                res.data.forEach(c => {
                    html += `<option value="${c.id_cours}">${c.nom_cours} (${c.enseignant})</option>`;
                });
                $('#select-cours').html(html);
            }
        });

        // Charger Promotions
        $.get('api/getPromotions.php', function(res) {
            if(res.status === 'success') {
                let html = '<option value="">Choisir une promotion...</option>';
                res.data.forEach(p => {
                    html += `<option value="${p.id_promotion}">${p.nom_promotion} (${p.effectif} étud.)</option>`;
                });
                $('#select-promotion').html(html);
            }
        });

        $('#current-date').text(new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
    }

    // Algorithme d'optimisation
    $('#btn-optimize').on('click', function() {
        const promoId = $('#select-promotion').val();
        const jour = $('#select-jour').val();
        const debut = $('#heure-debut').val();
        const fin = $('#heure-fin').val();

        if(!promoId || !jour || !debut || !fin) {
            alert("Veuillez remplir tous les champs avant de lancer l'optimisation.");
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Calcul...');

        $.get('api/optimize.php', {
            id_promotion: promoId,
            jour: jour,
            heure_debut: debut,
            heure_fin: fin
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Attribution Optimale');
            
            if(res.status === 'success') {
                const salle = res.data;
                selectedSalleId = salle.id_salle;
                $('#selected-room-info').html(`${salle.nom_salle} (${salle.capacite} places, ${salle.batiment})`);
                $('#optimization-log').html(`
                    <div class="alert alert-success py-2 small">
                        <i class="fas fa-check-circle me-1"></i> Salle optimale trouvée !
                    </div>
                `);
            } else {
                selectedSalleId = null;
                $('#selected-room-info').html('<span class="text-danger">Aucune salle disponible</span>');
                $('#optimization-log').html(`
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-exclamation-triangle me-1"></i> ${res.message}
                    </div>
                `);
            }
        });
    });

    // Enregistrement
    $('#planning-form').on('submit', function(e) {
        e.preventDefault();

        if(!selectedSalleId) {
            alert("Veuillez d'abord lancer l'attribution optimale pour choisir une salle.");
            return;
        }

        const data = {
            id_cours: $('#select-cours').val(),
            id_promotion: $('#select-promotion').val(),
            jour: $('#select-jour').val(),
            heure_debut: $('#heure-debut').val(),
            heure_fin: $('#heure-fin').val(),
            id_salle: selectedSalleId
        };

        $.ajax({
            url: 'api/addHoraire.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(res) {
                if(res.status === 'success') {
                    alert("Planning enregistré avec succès !");
                    location.reload();
                } else {
                    alert("Erreur: " + res.message);
                }
            }
        });
    });
});
