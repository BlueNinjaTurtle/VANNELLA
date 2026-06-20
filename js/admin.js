/**
 * js/admin.js
 * Logique pour l'attribution optimale des salles.
 */

$(document).ready(function() {
    let selectedSalleId = null;
    let selectedSallePending = false;
    let allPromotions = [];
    let allCours = [];
    const joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

    function todayIsoDate() {
        const now = new Date();
        const offset = now.getTimezoneOffset();
        return new Date(now.getTime() - offset * 60000).toISOString().slice(0, 10);
    }

    function syncJourFromDate() {
        const dateValue = $('#date-cours').val();
        if (!dateValue) {
            return;
        }

        const dayName = joursSemaine[new Date(`${dateValue}T12:00:00`).getDay()];
        if (dayName) {
            $('#select-jour').val(dayName);
        }
    }

    function renderSuggestions(suggestions) {
        if (!suggestions || suggestions.length === 0) {
            return '<div class="small opacity-75 mt-2">Aucun autre creneau compatible trouve.</div>';
        }

        const buttons = suggestions.map(s => `
            <button type="button"
                    class="btn btn-sm btn-light text-start w-100 mb-2 btn-slot-suggestion"
                    data-id-salle="${s.id_salle}"
                    data-nom-salle="${s.nom_salle}"
                    data-capacite="${s.capacite}"
                    data-batiment="${s.batiment || ''}"
                    data-date-cours="${s.date_cours}"
                    data-jour="${s.jour}"
                    data-heure-debut="${s.heure_debut}"
                    data-heure-fin="${s.heure_fin}">
                <strong>${s.jour} ${s.date_cours}</strong><br>
                <span>${s.heure_debut} - ${s.heure_fin} - ${s.nom_salle} (${s.capacite} places)</span>
            </button>
        `).join('');

        return `
            <div class="small fw-bold mt-3 mb-2">Creneaux suggeres</div>
            ${buttons}
        `;
    }

    init();

    function init() {
        $.getJSON('api/getCours.php', function(res) {
            if (res.status === 'success') {
                allCours = res.data;
                renderAllCours();
            }
        });

        $.getJSON('api/getPromotionsFull.php', function(res) {
            if (res.status === 'success') {
                allPromotions = res.data;
                let html = '<option value="">Choisir une promotion...</option>';
                res.data.forEach(p => {
                    html += `<option value="${p.id_promotion}" data-dept="${p.id_departement}">${p.nom_promotion} (${p.effectif} etud.)</option>`;
                });
                $('#select-promotion').html(html);

                $('#select-promotion').on('change', function() {
                    filterCoursByDepartment();
                });
            }
        });

        loadHoraires();
        $('#date-cours').val(todayIsoDate());
        syncJourFromDate();
        $('#date-cours').on('change', syncJourFromDate);

        $('#current-date').text(new Date().toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }));
    }

    function renderAllCours() {
        let html = '<option value="">Choisir un cours...</option>';
        allCours.forEach(c => {
            html += `<option value="${c.id_cours}" data-dept="${c.id_departement || ''}">${c.nom_cours} (${c.enseignant})</option>`;
        });
        $('#select-cours').html(html);
    }

    function filterCoursByDepartment() {
        const selectedPromoId = $('#select-promotion').val();

        if (!selectedPromoId) {
            renderAllCours();
            return;
        }

        const promo = allPromotions.find(p => p.id_promotion == selectedPromoId);
        if (!promo) {
            renderAllCours();
            return;
        }

        const deptId = promo.id_departement;
        let html = '<option value="">Choisir un cours...</option>';

        allCours.filter(c => c.id_departement == deptId).forEach(c => {
            html += `<option value="${c.id_cours}" data-dept="${c.id_departement}">${c.nom_cours} (${c.enseignant}) Dept.</option>`;
        });

        allCours.filter(c => !c.id_departement).forEach(c => {
            html += `<option value="${c.id_cours}" data-dept="">${c.nom_cours} (${c.enseignant})</option>`;
        });

        $('#select-cours').html(html);
    }

    function loadHoraires() {
        $.getJSON('api/getHorairesByWeek.php', function(res) {
            if (res.status === 'success' && res.data.length > 0) {
                let html = '';
                res.data.forEach(h => {
                    const heureDebut = h.heure_debut.slice(0, 5);
                    const heureFin = h.heure_fin.slice(0, 5);
                    const dateCours = h.date_cours || '';
                    const statut = h.statut || 'actif';
                    const typeCours = h.type_cours || 'specifique';

                    let badgeStatut = '';
                    if (statut === 'annule') {
                        badgeStatut = '<span class="badge bg-danger">Annule</span>';
                    } else if (statut === 'en_cours') {
                        badgeStatut = '<span class="badge bg-primary">En cours</span>';
                    } else if (statut === 'en_attente') {
                        badgeStatut = '<span class="badge bg-warning text-dark">En attente</span>';
                    } else if (statut === 'termine') {
                        badgeStatut = '<span class="badge bg-secondary">Termine</span>';
                    } else {
                        badgeStatut = '<span class="badge bg-success">Actif</span>';
                    }

                    if (typeCours === 'ensemble') {
                        badgeStatut += ' <span class="badge bg-info">Ensemble</span>';
                    }

                    const btnStatut = (statut === 'annule')
                        ? `<button type="button" class="btn btn-warning btn-sm me-1" onclick="updateHoraireStatus(${h.id_horaire}, 'actif')"><i class="fas fa-redo me-1"></i>Reactiver</button>`
                        : (statut === 'actif'
                            ? `<button type="button" class="btn btn-warning btn-sm me-1" onclick="updateHoraireStatus(${h.id_horaire}, 'annule')"><i class="fas fa-ban me-1"></i>Annuler</button>`
                            : '<button type="button" class="btn btn-light btn-sm me-1" disabled><i class="fas fa-lock me-1"></i>Verrouille</button>');

                    html += `
                        <tr>
                            <td class="ps-4">${h.nom_cours}</td>
                            <td>${h.nom_promotion}</td>
                            <td>${dateCours}</td>
                            <td>${h.jour}</td>
                            <td>${heureDebut} - ${heureFin} ${badgeStatut}</td>
                            <td><span class="badge bg-light text-dark">${h.nom_salle}</span></td>
                            <td class="pe-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    ${btnStatut}
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteHoraire(${h.id_horaire})">
                                        <i class="fas fa-trash-alt me-1"></i>Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                $('#table-horaires').html(html);
            } else {
                $('#table-horaires').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox me-2"></i>Aucun horaire planifie
                        </td>
                    </tr>
                `);
            }
        });
    }

    window.deleteHoraire = function(idHoraire) {
        if (!confirm("Etes-vous sur de vouloir supprimer cet horaire ?")) {
            return;
        }

        $.ajax({
            url: 'api/deleteHoraire.php',
            method: 'POST',
            data: JSON.stringify({ id_horaire: idHoraire }),
            contentType: 'application/json',
            success: function(res) {
                if (res.status === 'success') {
                    alert("Horaire supprime avec succes !");
                    loadHoraires();
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Horaire supprime'
                    }));
                } else {
                    alert("Erreur: " + res.message);
                }
            },
            error: function() {
                alert("Erreur lors de la suppression");
            }
        });
    };

    window.updateHoraireStatus = function(idHoraire, nouveauStatut) {
        const action = nouveauStatut === 'annule' ? 'annuler' : 'reactiver';
        if (!confirm(`Etes-vous sur de vouloir ${action} cet horaire ?`)) {
            return;
        }

        $.ajax({
            url: 'api/updateHoraireStatus.php',
            method: 'POST',
            data: JSON.stringify({
                id_horaire: idHoraire,
                statut: nouveauStatut
            }),
            contentType: 'application/json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(`Horaire ${nouveauStatut === 'annule' ? 'annule' : 'reactive'} avec succes !`);
                    loadHoraires();
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Horaire modifie'
                    }));
                } else {
                    alert("Erreur: " + res.message);
                }
            },
            error: function() {
                alert("Erreur lors de la mise a jour");
            }
        });
    };

    window.detectCoursEnsemble = function() {
        if (!confirm("Analyser les horaires pour detecter les cours d'ensemble ?")) {
            return;
        }

        $.ajax({
            url: 'api/detectCoursEnsemble.php',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(`Detection completee !\n${res.cours_ensemble_detectes} cours d'ensemble detectes\n${res.horaires_modifies} horaires modifies`);
                    loadHoraires();
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: "Cours d'ensemble detectes"
                    }));
                } else {
                    alert("Erreur: " + res.message);
                }
            },
            error: function() {
                alert("Erreur lors de la detection");
            }
        });
    };

    $('#btn-optimize').on('click', function() {
        const promoId = $('#select-promotion').val();
        const dateCours = $('#date-cours').val();
        const jour = $('#select-jour').val();
        const debut = $('#heure-debut').val();
        const fin = $('#heure-fin').val();

        if (!promoId || !dateCours || !jour || !debut || !fin) {
            alert("Veuillez remplir tous les champs avant de lancer l'optimisation.");
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Calcul...');

        $.getJSON('api/optimize.php', {
            id_promotion: promoId,
            date_cours: dateCours,
            jour: jour,
            heure_debut: debut,
            heure_fin: fin
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Attribution Optimale');

            if (res.status === 'success') {
                const salle = res.data;
                const suggestionsHtml = salle.pending_assignment ? renderSuggestions(res.suggestions || []) : '';
                selectedSalleId = salle.id_salle;
                selectedSallePending = !!salle.pending_assignment;
                const replacementInfo = salle.replaces_existing
                    ? ' <span class="badge bg-warning text-dark">remplace un horaire</span>'
                    : (salle.pending_assignment ? ' <span class="badge bg-warning text-dark">en attente</span>' : '');

                $('#selected-room-info').html(`${salle.nom_salle} (${salle.capacite} places, ${salle.batiment})${replacementInfo}`);
                $('#optimization-log').html(`
                    <div class="alert ${salle.pending_assignment ? 'alert-warning' : 'alert-success'} py-2 small">
                        <i class="fas ${salle.pending_assignment ? 'fa-clock' : 'fa-check-circle'} me-1"></i>
                        ${salle.pending_assignment ? 'Toutes les salles sont occupees sur ce creneau. Choisis une suggestion ou enregistre en attente.' : 'Salle optimale trouvee !'}
                    </div>
                    ${suggestionsHtml}
                `);
            } else {
                selectedSalleId = null;
                selectedSallePending = false;
                $('#selected-room-info').html('<span class="text-danger">Aucune salle disponible</span>');
                $('#optimization-log').html(`
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-exclamation-triangle me-1"></i> ${res.message}
                    </div>
                `);
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Attribution Optimale');
            alert("Erreur lors de l'optimisation");
        });
    });

    $(document).on('click', '.btn-slot-suggestion', function() {
        const $btn = $(this);
        selectedSalleId = $btn.data('id-salle');
        selectedSallePending = false;

        $('#date-cours').val($btn.data('date-cours'));
        $('#select-jour').val($btn.data('jour'));
        $('#heure-debut').val($btn.data('heure-debut'));
        $('#heure-fin').val($btn.data('heure-fin'));

        const nomSalle = $btn.data('nom-salle');
        const capacite = $btn.data('capacite');
        const batiment = $btn.data('batiment');
        $('#selected-room-info').html(`${nomSalle} (${capacite} places${batiment ? ', ' + batiment : ''}) <span class="badge bg-success">suggestion choisie</span>`);
        $('#optimization-log').html(`
            <div class="alert alert-success py-2 small">
                <i class="fas fa-check-circle me-1"></i> Creneau suggere applique.
            </div>
        `);
    });

    $('#planning-form').on('submit', function(e) {
        e.preventDefault();

        if (!selectedSalleId) {
            alert("Veuillez d'abord lancer l'attribution optimale pour choisir une salle.");
            return;
        }

        const data = {
            id_cours: $('#select-cours').val(),
            id_promotion: $('#select-promotion').val(),
            date_cours: $('#date-cours').val(),
            jour: $('#select-jour').val(),
            heure_debut: $('#heure-debut').val(),
            heure_fin: $('#heure-fin').val(),
            id_salle: selectedSalleId,
            statut: selectedSallePending ? 'en_attente' : 'actif'
        };

        $.ajax({
            url: 'api/addHoraire.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(res) {
                if (res.status === 'success') {
                    const attenteMessage = res.horaires_en_attente > 0
                        ? `\n${res.horaires_en_attente} horaire(s) mis en attente automatiquement.`
                        : '';
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Nouvel horaire ajoute'
                    }));

                    alert("Planning enregistre avec succes !" + attenteMessage);
                    location.reload();
                } else {
                    alert("Erreur: " + res.message);
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON;
                alert("Erreur: " + (res && res.message ? res.message : "Enregistrement impossible"));
            }
        });
    });
});
