$(document).ready(function() {
    loadSalles();

    function loadSalles() {
        $.get('api/getSalles.php', function(res) {
            if(res.status === 'success') {
                let html = '';
                res.data.forEach(s => {
                    html += `
                        <tr>
                            <td>${s.nom_salle}</td>
                            <td><span class="badge bg-info">${s.capacite} places</span></td>
                            <td>${s.batiment}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-edit" data-salle='${JSON.stringify(s)}'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${s.id_salle}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
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
