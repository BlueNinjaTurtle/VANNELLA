/**
 * js/admin.js
 * Logique pour l'attribution optimale des salles.
 */

$(document).ready(function() {
    let selectedSalleId = null;
    let allPromotions = [];
    let allCours = [];

    // Charger les cours et promotions au démarrage
    init();

    function init() {
        // Charger tous les cours
        $.getJSON('api/getCours.php', function(res) {
            if(res.status === 'success') {
                allCours = res.data;
                renderAllCours();
            }
        });

        // Charger Promotions avec départements
        $.getJSON('api/getPromotionsFull.php', function(res) {
            if(res.status === 'success') {
                allPromotions = res.data;
                let html = '<option value="">Choisir une promotion...</option>';
                res.data.forEach(p => {
                    html += `<option value="${p.id_promotion}" data-dept="${p.id_departement}">${p.nom_promotion} (${p.effectif} étud.)</option>`;
                });
                $('#select-promotion').html(html);
                
                // Ajouter l'événement de changement
                $('#select-promotion').on('change', function() {
                    filterCoursByDepartment();
                });
            }
        });

        // Charger les horaires existants
        loadHoraires();
        
        $('#current-date').text(new Date().toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
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
        
        if(!selectedPromoId) {
            renderAllCours();
            return;
        }

        // Trouver le département de la promotion sélectionnée
        const promo = allPromotions.find(p => p.id_promotion == selectedPromoId);
        if(!promo) {
            renderAllCours();
            return;
        }

        const deptId = promo.id_departement;

        // Filtrer les cours du même département
        let html = '<option value="">Choisir un cours...</option>';
        
        // D'abord les cours assignés au département
        allCours.filter(c => c.id_departement == deptId).forEach(c => {
            html += `<option value="${c.id_cours}" data-dept="${c.id_departement}">${c.nom_cours} (${c.enseignant}) <span class="badge badge-info">Dept.</span></option>`;
        });

        // Puis les cours non assignés
        allCours.filter(c => !c.id_departement).forEach(c => {
            html += `<option value="${c.id_cours}" data-dept="">↳ ${c.nom_cours} (${c.enseignant})</option>`;
        });

        $('#select-cours').html(html);
    }

    // Charger et afficher les horaires existants
    function loadHoraires() {
        $.getJSON('api/getHorairesByWeek.php', function(res) {
            if(res.status === 'success' && res.data.length > 0) {
                let html = '';
                res.data.forEach(h => {
                    const heureDebut = h.heure_debut.slice(0, 5);
                    const heureFin = h.heure_fin.slice(0, 5);
                    const statut = h.statut || 'actif';
                    const typeCours = h.type_cours || 'specifique';
                    
                    let badgeStatut = '';
                    if (statut === 'annulé' || statut === 'annule') {
                        badgeStatut = '<span class="badge bg-danger">Annulé</span>';
                    } else {
                        badgeStatut = '<span class="badge bg-success">Actif</span>';
                    }
                    
                    if (typeCours === 'ensemble') {
                        badgeStatut += ' <span class="badge bg-info">Ensemble</span>';
                    }
                    
                    const btnStatut = (statut === 'annulé' || statut === 'annule')
                        ? `<button type="button" class="btn btn-warning btn-sm me-1" onclick="updateHoraireStatus(${h.id_horaire}, 'actif')"><i class="fas fa-redo me-1"></i>Réactiver</button>`
                        : `<button type="button" class="btn btn-warning btn-sm me-1" onclick="updateHoraireStatus(${h.id_horaire}, 'annule')"><i class="fas fa-ban me-1"></i>Annuler</button>`;
                    
                    html += `
                        <tr>
                            <td class="ps-4">${h.nom_cours}</td>
                            <td>${h.nom_promotion}</td>
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
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox me-2"></i>Aucun horaire planifié
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Fonction globale pour supprimer un horaire
    window.deleteHoraire = function(idHoraire) {
        if(!confirm("Êtes-vous sûr de vouloir supprimer cet horaire ?")) {
            return;
        }

        $.ajax({
            url: 'api/deleteHoraire.php',
            method: 'POST',
            data: JSON.stringify({ id_horaire: idHoraire }),
            contentType: 'application/json',
            success: function(res) {
                if(res.status === 'success') {
                    alert("Horaire supprimé avec succès !");
                    loadHoraires();
                    // Notifier les autres onglets
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Horaire supprimé'
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

    // Fonction globale pour mettre à jour le statut d'un horaire
    window.updateHoraireStatus = function(idHoraire, nouveauStatut) {
        const action = nouveauStatut === 'annule' ? 'annuler' : 'réactiver';
        if(!confirm(`Êtes-vous sûr de vouloir ${action} cet horaire ?`)) {
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
                if(res.status === 'success') {
                    alert(`Horaire ${nouveauStatut === 'annule' ? 'annulé' : 'réactivé'} avec succès !`);
                    loadHoraires();
                    // Notifier les autres onglets
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Horaire modifié'
                    }));
                } else {
                    alert("Erreur: " + res.message);
                }
            },
            error: function() {
                alert("Erreur lors de la mise à jour");
            }
        });
    };

    // Fonction globale pour détecter les cours d'ensemble
    window.detectCoursEnsemble = function() {
        if(!confirm("Analyser les horaires pour détecter les cours d'ensemble ?")) {
            return;
        }

        $.ajax({
            url: 'api/detectCoursEnsemble.php',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    alert(`Détection complétée !\n${res.cours_ensemble_detectes} cours d'ensemble détectés\n${res.horaires_modifies} horaires modifiés`);
                    loadHoraires();
                    // Notifier les autres onglets
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Cours d\'ensemble détectés'
                    }));
                } else {
                    alert("Erreur: " + res.message);
                }
            },
            error: function() {
                alert("Erreur lors de la détection");
            }
        });
    };

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

        $.getJSON('api/optimize.php', {
            id_promotion: promoId,
            jour: jour,
            heure_debut: debut,
            heure_fin: fin
        }, function(res) {
            $btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Attribution Optimale');
            
            if(res.status === 'success') {
                const salle = res.data;
                selectedSalleId = salle.id_salle;
                const replacementInfo = salle.replaces_existing
                    ? ' <span class="badge bg-warning text-dark">remplace un horaire</span>'
                    : '';
                $('#selected-room-info').html(`${salle.nom_salle} (${salle.capacite} places, ${salle.batiment})${replacementInfo}`);
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
                    const annulationMessage = res.horaires_annules > 0
                        ? `\n${res.horaires_annules} ancien(s) horaire(s) annulé(s) automatiquement.`
                        : '';
                    // Signaler aux autres onglets/fenêtres que les salles ont changé
                    localStorage.setItem('salles_updated', JSON.stringify({
                        timestamp: new Date().getTime(),
                        message: 'Nouvel horaire ajouté'
                    }));
                    
                    alert("Planning enregistré avec succès !" + annulationMessage);
                    location.reload();
                } else {
                    alert("Erreur: " + res.message);
                }
            }
        });
    });
});
