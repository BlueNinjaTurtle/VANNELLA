/**
 * js/salles_view.js
 * Logique page consultation salles
 * Affiche salles avec état IoT temps réel + horaires
 * Synchronisé avec simulation-iot.php
 */

$(document).ready(function() {
    let previousStates = {};
    let allSalles = [];
    let allHoraires = [];
    let openAccordions = {}; // Mémoriser l'état des accordéons

    const stateEmojis = {
        'LIBRE': '🟢',
        'OCCUPÉE': '🔴',
        'RÉSERVÉE': '🟡',
        'INDISPONIBLE': '⚫'
    };

    const stateClasses = {
        'LIBRE': 'state-libre',
        'OCCUPÉE': 'state-occupee',
        'RÉSERVÉE': 'state-reservee',
        'INDISPONIBLE': 'state-offline'
    };

    function isHoraireAnnule(horaire) {
        const statut = (horaire.statut || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        return statut === 'annule' || statut === 'annulee';
    }

    function renderAnnuleLabel(extraStyle = '') {
        return ` <span style="color: #dc3545; font-weight: 600; margin-left: 6px; ${extraStyle}">Annulé</span>`;
    }

    function renderHoraireStatusLabel(horaire, extraStyle = '') {
        const statut = (horaire.statut || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        if (statut === 'annule' || statut === 'annulee') {
            return renderAnnuleLabel(extraStyle);
        }
        if (statut === 'en_cours') {
            return ` <span style="color: #0d6efd; font-weight: 600; margin-left: 6px; ${extraStyle}">En cours</span>`;
        }
        if (statut === 'termine') {
            return ` <span style="color: #6c757d; font-weight: 600; margin-left: 6px; ${extraStyle}">Termine</span>`;
        }
        return '';
    }

    loadData();
    setInterval(loadData, 2000);

    function loadData() {
        $.ajax({
            url: 'api/getSallesWithDetails.php?t=' + Date.now(),
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response.status === 'success') {
                    allSalles = response.data || [];
                    renderSalles();
                    renderHoraires();
                    updateConnectionStatus(true);
                }
            },
            error: function() {
                updateConnectionStatus(false);
            }
        });

        $.ajax({
            url: 'api/getHorairesByWeek.php?t=' + Date.now(),
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response.status === 'success') {
                    allHoraires = response.data || [];
                    // 🔍 DEBUG: Afficher les infos du premier horaire
                    if (allHoraires.length > 0) {
                        console.log('📋 Premier horaire reçu:', allHoraires[0]);
                        console.log('🏷️ Statut du 1er horaire:', allHoraires[0].statut);
                    }
                    renderHoraires();
                }
            }
        });
    }

    function renderSalles() {
        const filterDept = $('#filter-departement').val();
        const filterPromo = $('#filter-promotion').val();
        const filterEtat = $('#filter-etat').val();

        let filteredSalles = allSalles.filter(s => {
            const etatUpper = (s.etat || 'LIBRE').toUpperCase();
            
            // Filtrer par département
            if (filterDept) {
                if (!s.all_departements || !s.all_departements.includes(filterDept)) {
                    return false;
                }
            }
            
            // Filtrer par promotion
            if (filterPromo) {
                if (!s.all_promotions || !s.all_promotions.includes(filterPromo)) {
                    return false;
                }
            }
            
            // Filtrer par état
            if (filterEtat && etatUpper !== filterEtat.toUpperCase()) {
                return false;
            }
            
            return true;
        });

        updateFilterOptions();

        let html = '';

        if (filteredSalles.length === 0) {
            html = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">' +
                   '<i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>' +
                   '<p>Aucune salle ne correspond aux filtres</p></div>';
        } else {
            filteredSalles.forEach(salle => {
                html += renderSalleCard(salle);
            });
        }

        $('#salles-container').html(html);
    }

    function renderSalleCard(salle) {
        const etatUpper = (salle.etat || 'LIBRE').toUpperCase();
        const emoji = stateEmojis[etatUpper] || '🟢';
        const stateClass = stateClasses[etatUpper] || 'state-libre';

        let badgeSource = 'badge-system';
        let sourceText = 'Système';
        if (salle.etat_source === 'IoT') {
            badgeSource = 'badge-iot';
            sourceText = '📱 IoT';
        } else if (salle.etat_source === 'Admin') {
            badgeSource = 'badge-admin';
            sourceText = '⚙️ Admin';
        } else if (salle.etat_source === 'Planning') {
            badgeSource = 'badge-planning';
            sourceText = '📅 Planning';
        }

        let badgeIoT = (salle.iot_status === 'online') ? 'badge-online' : 'badge-offline';
        let iotText = (salle.iot_status === 'online') ? '✓ En ligne' : '✗ Hors ligne';

        // 🆕 Récupérer les horaires de cette salle
        let horairesSalle = allHoraires.filter(h => h.id_salle == salle.id_salle);
        
        // Trouver le cours actuel (basé sur l'heure actuelle)
        let courseContent = '';
        if (horairesSalle.length > 0) {
            const now = new Date();
            const dayOfWeek = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            const jourAujourdhui = dayOfWeek[now.getDay()];
            
            // Horaires du jour (filtrer par jour actuel)
            let horairesAujourd = horairesSalle
                .filter(h => h.jour === jourAujourdhui && !isHoraireAnnule(h))
                .sort((a, b) => (a.heure_debut || '').localeCompare(b.heure_debut || ''));
            
            if (horairesAujourd.length > 0) {
                // Trouver le cours en cours ou le prochain
                const currentTime = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
                
                let courseEnCours = null;
                let courseProchain = null;
                
                for (let h of horairesAujourd) {
                    if (h.heure_debut <= currentTime && h.heure_fin >= currentTime) {
                        courseEnCours = h;
                        break;
                    }
                    if (!courseProchain && h.heure_debut > currentTime) {
                        courseProchain = h;
                    }
                }
                
                let coursDisplay = courseEnCours || courseProchain;
                
                if (coursDisplay) {
                    const statusBadge = renderHoraireStatusLabel(coursDisplay);
                    
                    const courseLabel = courseEnCours ? '📚 ' : '⏭️ ';
                    courseContent = `
                        <div style="margin-top: 12px; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 6px; font-size: 0.85rem; line-height: 1.4;">
                            <div style="font-weight: 600; color: white;">${courseLabel}${coursDisplay.nom_cours}${statusBadge}</div>
                            <div style="color: rgba(255,255,255,0.9);">🕐 ${coursDisplay.heure_debut} - ${coursDisplay.heure_fin}</div>
                        </div>
                    `;
                }
            }
        }

        return `
            <div class="salle-card" data-salle-id="${salle.id_salle}">
                <div class="salle-card-header">
                    <div class="salle-name">
                        <span style="font-size: 1.4em; margin-right: 8px;">${emoji}</span>
                        ${salle.nom_salle}
                    </div>
                </div>
                <div class="salle-card-body ${stateClass}">
                    <div class="state-emoji">${emoji}</div>
                    <div class="state-text">${etatUpper}</div>
                    
                    ${courseContent}

                    <button class="btn-details" onclick="showDetails(${salle.id_salle})">
                        <i class="fas fa-info-circle me-1"></i>Voir détails
                    </button>
                </div>
            </div>
        `;
    }

    function renderHoraires() {
        if (allHoraires.length === 0) {
            $('#horaires-container').html(
                '<p style="text-align: center; color: #999; padding: 30px;">' +
                'Aucun horaire planifié pour cette semaine' +
                '</p>'
            );
            return;
        }

        // Sauvegarder l'état des accordéons avant de redessiner
        let expandedDepts = [];
        let expandedPromos = [];
        
        $('#horaires-container').find('.accordion-header:not(.collapsed)').each(function() {
            expandedDepts.push($(this).text().trim());
        });
        
        $('#horaires-container').find('.promo-header:not(.collapsed)').each(function() {
            expandedPromos.push($(this).text().trim());
        });

        let sallesById = {};
        allSalles.forEach(s => {
            sallesById[s.id_salle] = s;
        });

        // Récupérer promotions et départements
        $.when(
            $.get('api/getDepartments.php'),
            $.get('api/getPromotions.php')
        ).done(function(deptRes, promoRes) {
            if(deptRes[0].status !== 'success' || promoRes[0].status !== 'success') {
                $('#horaires-container').html('<p style="text-align: center; color: #999; padding: 30px;">Erreur de chargement</p>');
                return;
            }

            let depts = deptRes[0].data || [];
            let promos = promoRes[0].data || [];

            let html = '';

            // Parcourir chaque département
            depts.forEach(dept => {
                // Trouver les promotions de ce département
                let promosOfDept = promos.filter(p => p.id_departement == dept.id_departement);

                if(promosOfDept.length === 0) return; // Ignorer si pas de promotions

                // Vérifier si ce département était ouvert
                let deptWasOpen = expandedDepts.some(d => d.includes(dept.nom_departement));

                html += `
                    <div class="accordion-dept">
                        <div class="accordion-header${deptWasOpen ? '' : ' collapsed'}" onclick="$(this).toggleClass('collapsed').next().slideToggle(300);">
                            <i class="fas fa-university"></i> ${dept.nom_departement}
                        </div>
                        <div class="accordion-body"${deptWasOpen ? '' : ' style="display: none;"'}>
                `;

                let deptHasHoraires = false;

                // Parcourir les promotions de ce département
                promosOfDept.forEach(promo => {
                    // Trouver les horaires de cette promotion
                    let horairesPromo = allHoraires.filter(h => h.id_promotion == promo.id_promotion);
                    
                    if(horairesPromo.length === 0) return;
                    
                    deptHasHoraires = true;

                    // Vérifier si cette promotion était ouverte
                    let promoWasOpen = expandedPromos.some(p => p.includes(promo.nom_promotion));

                    html += `
                        <div class="accordion-promo">
                            <div class="promo-header${promoWasOpen ? '' : ' collapsed'}" onclick="$(this).toggleClass('collapsed').next().slideToggle(200);">
                                <i class="fas fa-graduation-cap"></i> ${promo.nom_promotion}
                                <span style="margin-left: 10px; font-size: 0.85rem; color: #666;">(${horairesPromo.length} cours)</span>
                            </div>
                            <div class="promo-body"${promoWasOpen ? '' : ' style="display: none;"'}>
                    `;

                    // Grouper les horaires par jour et demi-journée
                    let horairesByDay = {};
                    horairesPromo.forEach(h => {
                        if(!horairesByDay[h.jour]) {
                            horairesByDay[h.jour] = { avant: [], apres: [] };
                        }
                        const heure = parseInt(h.heure_debut.split(':')[0]);
                        if(heure < 12) {
                            horairesByDay[h.jour].avant.push(h);
                        } else {
                            horairesByDay[h.jour].apres.push(h);
                        }
                    });

                    // Jours de la semaine
                    const jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                    
                    html += `
                                <table class="horaires-promo-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" style="vertical-align: middle; text-align: center;">JOUR</th>
                                            <th colspan="2" style="text-align: center; border-bottom: 2px solid #004a99;">AVANT - MIDI</th>
                                            <th colspan="2" style="text-align: center; border-bottom: 2px solid #004a99;">APRÈS - MIDI</th>
                                        </tr>
                                        <tr>
                                            <th style="text-align: center;">HEURE</th>
                                            <th>INTITULÉ DE L'U.E</th>
                                            <th style="text-align: center;">HEURE</th>
                                            <th>INTITULÉ DE L'U.E</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    jours.forEach(jour => {
                        const dayData = horairesByDay[jour];
                        if(!dayData) return;

                        const maxRows = Math.max(dayData.avant.length, dayData.apres.length) || 1;

                        for(let i = 0; i < maxRows; i++) {
                            let row = '';
                            
                            // Ajouter le jour en première colonne (seulement pour la première ligne du jour)
                            if(i === 0) {
                                row += `<td rowspan="${maxRows}" style="font-weight: 700; text-align: center; vertical-align: middle; border-right: 2px solid #e0e0e0;"><strong>${jour}</strong></td>`;
                            }

                            // Avant-midi
                            if(dayData.avant[i]) {
                                const h = dayData.avant[i];
                                const statusBadge = renderHoraireStatusLabel(h, 'font-size: 1.05rem;');
                                row += `
                                    <td style="text-align: center; font-weight: 600;">${h.heure_debut} - ${h.heure_fin}</td>
                                    <td><strong>${h.nom_cours}</strong>${statusBadge} <em style="color: #666;">(${h.nom_salle})</em></td>
                                `;
                            } else {
                                row += `<td colspan="2" style="background: #f9f9f9;"></td>`;
                            }

                            // Après-midi
                            if(dayData.apres[i]) {
                                const h = dayData.apres[i];
                                const statusBadge = renderHoraireStatusLabel(h, 'font-size: 1.05rem;');
                                row += `
                                    <td style="text-align: center; font-weight: 600;">${h.heure_debut} - ${h.heure_fin}</td>
                                    <td><strong>${h.nom_cours}</strong>${statusBadge} <em style="color: #666;">(${h.nom_salle})</em></td>
                                `;
                            } else {
                                row += `<td colspan="2" style="background: #f9f9f9;"></td>`;
                            }

                            html += `<tr>${row}</tr>`;
                        }
                    });

                    html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                });

                if(!deptHasHoraires) {
                    html += '<p style="text-align: center; color: #999; padding: 15px;">Aucun horaire pour ce département</p>';
                }

                html += `
                        </div>
                    </div>
                `;
            });

            $('#horaires-container').html(html || '<p style="text-align: center; color: #999; padding: 30px;">Aucun horaire planifié</p>');
        });
    }

    function updateFilterOptions() {
        // 💾 Sauvegarder les valeurs actuelles des filtres
        const savedDeptValue = $('#filter-departement').val();
        const savedPromoValue = $('#filter-promotion').val();

        // Récupérer tous les départements depuis l'API
        $.get('api/getDepartments.php', function(res) {
            if(res.status === 'success') {
                let deptHtml = '<option value="">Tous les départements</option>';
                res.data.forEach(d => {
                    deptHtml += `<option value="${d.nom_departement}">${d.nom_departement}</option>`;
                });
                $('#filter-departement').html(deptHtml);
                // 🔄 Restaurer la valeur sauvegardée
                $('#filter-departement').val(savedDeptValue);
            }
        });

        // Récupérer toutes les promotions depuis l'API
        $.get('api/getPromotions.php', function(res) {
            if(res.status === 'success') {
                let promoHtml = '<option value="">Toutes les promotions</option>';
                res.data.forEach(p => {
                    promoHtml += `<option value="${p.nom_promotion}">${p.nom_promotion}</option>`;
                });
                $('#filter-promotion').html(promoHtml);
                // 🔄 Restaurer la valeur sauvegardée
                $('#filter-promotion').val(savedPromoValue);
            }
        });
    }

    window.showDetails = function(salleId) {
        const salle = allSalles.find(s => s.id_salle == salleId);
        if (!salle) return;

        const salleEmoji = salle.etat === 'libre' ? '🟢' :
                           salle.etat === 'occupée' ? '🔴' :
                           salle.etat === 'indisponible' ? '⚫' : '🟡';

        let modalBody = `
            <div style="text-align: center; margin-bottom: 20px;">
                <h3>${salle.nom_salle}</h3>
                <p style="color: #666;">${salle.batiment}</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">
                        ${salleEmoji}
                    </div>
                    <div style="font-size: 1.2rem; font-weight: 700; text-transform: uppercase;">${salle.etat}</div>
                </div>
                <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <div style="font-size: 1rem; margin-bottom: 5px; color: #666;">Capacité</div>
                    <div style="font-size: 1.5rem; font-weight: 700;">${salle.capacite} places</div>
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong>Source:</strong> ${salle.etat_source}<br>
                <strong>Statut IoT:</strong> ${salle.iot_status === 'online' ? '✓ En ligne' : '✗ Hors ligne'}<br>
                <strong>Dernière mise à jour:</strong> ${salle.seconds_since_update || 0}s ago
            </div>

            <div style="margin-top: 20px;">
                <strong style="display: block; margin-bottom: 10px;">Horaires semaine:</strong>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <tr style="background: #f0f0f0;">
                        <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Jour</th>
                        <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Horaire</th>
                        <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Cours</th>
                    </tr>
        `;

        const horairesRoom = allHoraires.filter(h => h.id_salle == salleId);
        if (horairesRoom.length > 0) {
            horairesRoom.forEach(h => {
                const coursLabel = `${h.nom_cours}${renderHoraireStatusLabel(h)}`;

                modalBody += `
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">${h.jour}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">${h.heure_debut} - ${h.heure_fin}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">${coursLabel}</td>
                    </tr>
                `;
            });
        } else {
            modalBody += '<tr><td colspan="3" style="padding: 8px; text-align: center; border: 1px solid #ddd;">Aucun horaire</td></tr>';
        }

        modalBody += '</table></div>';

        $('#modalTitle').text(`Détails - ${salle.nom_salle}`);
        $('#modalBody').html(modalBody);

        new bootstrap.Modal(document.getElementById('modalDetailsSalle')).show();
    };

    function updateConnectionStatus(connected) {
        if (connected) {
            $('#connection-status').html('<span style="color: #10b981;">✓ Connecté</span>');
        } else {
            $('#connection-status').html('<span style="color: #ef4444;">✗ Erreur connexion</span>');
        }
    }

    $('#filter-departement, #filter-promotion, #filter-etat').on('change', function() {
        renderSalles();
    });
});
