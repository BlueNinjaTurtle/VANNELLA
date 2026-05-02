$(document).ready(function() {
    loadSalles();

    function loadSalles() {
        $.get('api/getSalles.php', function(res) {
            if(res.status === 'success') {
                let html = '';
                if(res.data.length === 0) {
                    html = '<tr><td colspan="6" class="text-center py-4 text-muted">Aucune salle trouvée</td></tr>';
                } else {
                    res.data.forEach(s => {
                        // Couleur pour la source
                        let sourceColor = 'bg-secondary';
                        if (s.etat_source === 'Planning') sourceColor = 'bg-info';
                        else if (s.etat_source === 'IoT') sourceColor = 'bg-warning';
                        else if (s.etat_source === 'Admin') sourceColor = 'bg-danger';
                        
                        // Couleur pour le statut IoT
                        let iotStatusColor = 'bg-secondary';
                        let iotStatusIcon = 'fa-question-circle';
                        if (s.iot_status === 'online') {
                            iotStatusColor = 'bg-success';
                            iotStatusIcon = 'fa-check-circle';
                        } else if (s.iot_status === 'offline') {
                            iotStatusColor = 'bg-danger';
                            iotStatusIcon = 'fa-times-circle';
                        }
                        
                        html += `
                            <tr>
                                <td class="ps-4 fw-bold text-dark">${s.nom_salle}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-primary-soft text-primary px-3 py-2 border-0" style="font-size: 0.85rem;">
                                        <i class="fas fa-users me-1"></i> ${s.capacite}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted"><i class="fas fa-map-marker-alt me-2 small"></i>${s.batiment}</span>
                                </td>
                                <td class="text-center">
                                    <div>
                                        <span class="badge ${sourceColor} me-2" title="Source de modification">
                                            ${s.etat_source}
                                        </span>
                                        <span class="badge ${iotStatusColor}" title="Statut Arduino">
                                            <i class="fas ${iotStatusIcon} me-1"></i> ${s.iot_status}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-success btn-force-libre px-2" 
                                                data-id="${s.id_salle}" 
                                                title="Forcer l'état à LIBRE">
                                            <i class="fas fa-door-open"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-force-occupee px-2" 
                                                data-id="${s.id_salle}" 
                                                title="Forcer l'état à OCCUPÉE">
                                            <i class="fas fa-door-closed"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-white border shadow-sm btn-edit px-3" data-salle='${JSON.stringify(s)}' data-bs-toggle="modal" data-bs-target="#modalSalle" title="Modifier">
                                            <i class="fas fa-pen text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white border shadow-sm btn-delete px-3 ms-2" data-id="${s.id_salle}" title="Supprimer">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#table-salles').html(html);
            }
        });
    }

    // Ouvrir modal pour ajout
    $('#modalSalle').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        if (button.hasClass('btn-edit')) {
            const salle = button.data('salle');
            $('#salle-id').val(salle.id_salle);
            $('#salle-nom').val(salle.nom_salle);
            $('#salle-capacite').val(salle.capacite);
            $('#salle-batiment').val(salle.batiment);
        } else {
            $('#form-salle')[0].reset();
            $('#salle-id').val('');
        }
    });

    // Enregistrer (Add/Update)
    $('#form-salle').on('submit', function(e) {
        e.preventDefault();
        const id = $('#salle-id').val();
        const url = id ? 'api/updateSalleAdmin.php' : 'api/addSalle.php';
        
        const data = {
            id_salle: id,
            nom_salle: $('#salle-nom').val(),
            capacite: $('#salle-capacite').val(),
            batiment: $('#salle-batiment').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalSalle').modal('hide');
                    loadSalles();
                    alert('✓ Salle enregistrée avec succès!');
                } else {
                    alert("❌ Erreur: " + res.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr, status, error);
                let errorMsg = 'Erreur serveur';
                try {
                    const res = xhr.responseJSON || JSON.parse(xhr.responseText);
                    errorMsg = res.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.responseText || error || 'Erreur inconnue';
                }
                alert('❌ Impossible d\'enregistrer la salle: ' + errorMsg);
            }
        });
    });

    // Supprimer
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        if(confirm("Voulez-vous vraiment supprimer cette salle ?")) {
            $.post('api/deleteSalle.php', { id_salle: id }, function(res) {
                if(res.status === 'success') {
                    loadSalles();
                } else {
                    alert("Erreur: " + res.message);
                }
            }, 'json');
        }
    });

    // Force État: LIBRE
    $(document).on('click', '.btn-force-libre', function() {
        const id = $(this).data('id');
        const raison = prompt("Raison du changement (optionnel):", "Admin force l'état à LIBRE");
        
        if (raison !== null) {
            $.ajax({
                url: 'api/forceEtat.php',
                method: 'POST',
                data: JSON.stringify({
                    id_salle: id,
                    etat: 'libre',
                    raison: raison
                }),
                contentType: 'application/json',
                success: function(res) {
                    if(res.status === 'OK' || res.status === 'success') {
                        alert("✓ " + res.message);
                        loadSalles();
                    } else {
                        alert("✗ Erreur: " + res.message);
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || "Erreur serveur";
                    alert("✗ Erreur: " + error);
                }
            });
        }
    });

    // Force État: OCCUPÉE
    $(document).on('click', '.btn-force-occupee', function() {
        const id = $(this).data('id');
        const raison = prompt("Raison du changement (optionnel):", "Admin force l'état à OCCUPÉE");
        
        if (raison !== null) {
            $.ajax({
                url: 'api/forceEtat.php',
                method: 'POST',
                data: JSON.stringify({
                    id_salle: id,
                    etat: 'occupée',
                    raison: raison
                }),
                contentType: 'application/json',
                success: function(res) {
                    if(res.status === 'OK' || res.status === 'success') {
                        alert("✓ " + res.message);
                        loadSalles();
                    } else {
                        alert("✗ Erreur: " + res.message);
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || "Erreur serveur";
                    alert("✗ Erreur: " + error);
                }
            });
        }
    });
});
