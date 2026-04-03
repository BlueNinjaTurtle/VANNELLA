$(document).ready(function() {
    loadCours();

    function loadCours() {
        $.get('api/getCours.php', function(res) {
            if(res.status === 'success') {
                let html = '';
                if(res.data.length === 0) {
                    html = '<tr><td colspan="3" class="text-center py-4 text-muted">Aucun cours trouvé</td></tr>';
                } else {
                    res.data.forEach(c => {
                        html += `
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded me-3 text-muted">
                                            <i class="fas fa-book small"></i>
                                        </div>
                                        ${c.nom_cours}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-muted small">
                                        <i class="fas fa-user-tie me-2"></i>${c.enseignant}
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-white border shadow-sm btn-edit-cours px-3" data-cours='${JSON.stringify(c)}' data-bs-toggle="modal" data-bs-target="#modalCours" title="Modifier">
                                            <i class="fas fa-pen text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white border shadow-sm btn-delete-cours px-3 ms-2" data-id="${c.id_cours}" title="Supprimer">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#table-cours').html(html);
            }
        });
    }

    // Modal
    $('#modalCours').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        if (button.hasClass('btn-edit-cours')) {
            const c = button.data('cours');
            $('#cours-id').val(c.id_cours);
            $('#cours-nom').val(c.nom_cours);
            $('#cours-enseignant').val(c.enseignant);
        } else {
            $('#form-cours')[0].reset();
            $('#cours-id').val('');
        }
    });

    // Save
    $('#form-cours').on('submit', function(e) {
        e.preventDefault();
        const id = $('#cours-id').val();
        const url = id ? 'api/updateCours.php' : 'api/addCours.php';
        const data = {
            id_cours: id,
            nom_cours: $('#cours-nom').val(),
            enseignant: $('#cours-enseignant').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalCours').modal('hide');
                    loadCours();
                } else {
                    alert("Erreur: " + res.message);
                }
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete-cours', function() {
        if(confirm("Supprimer ce cours ?")) {
            $.post('api/deleteCours.php', { id_cours: $(this).data('id') }, function(res) {
                if(res.status === 'success') loadCours();
            }, 'json');
        }
    });
});
