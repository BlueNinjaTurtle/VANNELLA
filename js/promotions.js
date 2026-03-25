$(document).ready(function() {
    loadAll();

    function loadAll() {
        loadPromotions();
        loadDepartments();
    }

    function loadPromotions() {
        $.get('api/getPromotionsFull.php', function(res) {
            if(res.status === 'success') {
                let html = '';
                res.data.forEach(p => {
                    html += `
                        <tr>
                            <td>${p.nom_promotion}</td>
                            <td>${p.nom_departement || '<span class="text-danger">Non défini</span>'}</td>
                            <td>${p.filiere}</td>
                            <td>${p.niveau}</td>
                            <td><span class="badge bg-secondary">${p.effectif} étud.</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-edit-promo" data-promo='${JSON.stringify(p)}'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-promo" data-id="${p.id_promotion}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#table-promotions').html(html);
            }
        });
    }

    function loadDepartments() {
        $.get('api/getDepartments.php', function(res) {
            if(res.status === 'success') {
                // Pour le select
                let selectHtml = '<option value="">Choisir un département...</option>';
                // Pour la liste simple
                let listHtml = '';

                res.data.forEach(d => {
                    selectHtml += `<option value="${d.id_departement}">${d.nom_departement}</option>`;
                    listHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${d.nom_departement}
                            <button class="btn btn-sm btn-link text-danger btn-delete-dept" data-id="${d.id_departement}">
                                <i class="fas fa-times"></i>
                            </button>
                        </li>
                    `;
                });
                $('#promo-dept').html(selectHtml);
                $('#list-depts').html(listHtml);
            }
        });
    }

    // Modal Promotion
    $('#modalPromo').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        if (button.hasClass('btn-edit-promo')) {
            const p = button.data('promo');
            $('#promo-id').val(p.id_promotion);
            $('#promo-nom').val(p.nom_promotion);
            $('#promo-dept').val(p.id_departement);
            $('#promo-filiere').val(p.filiere);
            $('#promo-niveau').val(p.niveau);
            $('#promo-effectif').val(p.effectif);
        } else {
            $('#form-promo')[0].reset();
            $('#promo-id').val('');
        }
    });

    // Save Promo
    $('#form-promo').on('submit', function(e) {
        e.preventDefault();
        const id = $('#promo-id').val();
        const url = id ? 'api/updatePromotion.php' : 'api/addPromotion.php';
        const data = {
            id_promotion: id,
            nom_promotion: $('#promo-nom').val(),
            id_departement: $('#promo-dept').val(),
            filiere: $('#promo-filiere').val(),
            niveau: $('#promo-niveau').val(),
            effectif: $('#promo-effectif').val()
        };

        $.ajax({
            url: url,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalPromo').modal('hide');
                    loadPromotions();
                } else {
                    alert("Erreur: " + res.message);
                }
            }
        });
    });

    // Delete Promo
    $(document).on('click', '.btn-delete-promo', function() {
        if(confirm("Supprimer cette promotion ?")) {
            $.post('api/deletePromotion.php', { id_promotion: $(this).data('id') }, function(res) {
                if(res.status === 'success') loadPromotions();
            }, 'json');
        }
    });

    // Save Dept
    $('#form-dept').on('submit', function(e) {
        e.preventDefault();
        $.post('api/addDepartment.php', { nom_departement: $('#dept-nom').val() }, function(res) {
            if(res.status === 'success') {
                $('#dept-nom').val('');
                loadDepartments();
            }
        }, 'json');
    });

    // Delete Dept
    $(document).on('click', '.btn-delete-dept', function() {
        if(confirm("Supprimer ce département ?")) {
            $.post('api/deleteDepartment.php', { id_departement: $(this).data('id') }, function(res) {
                if(res.status === 'success') loadDepartments();
                else alert(res.message);
            }, 'json');
        }
    });
});
