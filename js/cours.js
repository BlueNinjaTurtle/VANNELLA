$(document).ready(function() {
    loadCours();

    function loadCours() {
        $.get('api/getCours.php', function(res) {
            if(res.status === 'success') {
                let html = '';
                res.data.forEach(c => {
                    html += `
                        <tr>
                            <td>${c.nom_cours}</td>
                            <td>${c.enseignant}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-edit-cours" data-cours='${JSON.stringify(c)}'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-cours" data-id="${c.id_cours}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
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
