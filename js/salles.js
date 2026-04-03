$(document).ready(function() {
    loadSalles();

    function loadSalles() {
        $.get('api/getSalles.php', function(res) {
            if(res.status === 'success') {
                let html = '';
                if(res.data.length === 0) {
                    html = '<tr><td colspan="4" class="text-center py-4 text-muted">Aucune salle trouvée</td></tr>';
                } else {
                    res.data.forEach(s => {
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
                } else {
                    alert("Erreur: " + res.message);
                }
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
});
