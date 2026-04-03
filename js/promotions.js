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
                if(res.data.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Aucune promotion trouvée</td></tr>';
                } else {
                    res.data.forEach(p => {
                        const deptBadge = p.nom_departement 
                            ? `<span class="badge bg-light text-primary border px-2 py-1">${p.nom_departement}</span>`
                            : '<span class="badge bg-light text-danger border px-2 py-1">Non défini</span>';
                        
                        html += `
                            <tr>
                                <td class="ps-4 fw-bold text-dark">${p.nom_promotion}</td>
                                <td>${deptBadge}</td>
                                <td>
                                    <div class="small fw-semibold text-dark">${p.filiere}</div>
                                    <div class="small text-muted">${p.niveau}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-primary-soft text-primary px-3 py-2 border-0" style="font-size: 0.85rem;">
                                        ${p.effectif} étud.
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-white border shadow-sm btn-edit-promo px-3" data-promo='${JSON.stringify(p)}' data-bs-toggle="modal" data-bs-target="#modalPromo" title="Modifier">
                                            <i class="fas fa-pen text-primary"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white border shadow-sm btn-delete-promo px-3 ms-2" data-id="${p.id_promotion}" title="Supprimer">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('#table-promotions').html(html);
            }
        });
    }

    function loadDepartments() {
        $.get('api/getDepartments.php', function(res) {
            if(res.status === 'success') {
                let selectHtml = '<option value="">Choisir un département...</option>';
                let listHtml = '';

                res.data.forEach(d => {
                    selectHtml += `<option value="${d.id_departement}">${d.nom_departement}</option>`;
                    listHtml += `
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3 border-bottom">
                            <span class="fw-semibold text-dark"><i class="fas fa-building text-muted me-3"></i>${d.nom_departement}</span>
                            <button class="btn btn-sm btn-light-danger btn-delete-dept p-2" data-id="${d.id_departement}" title="Supprimer">
                                <i class="fas fa-times-circle fs-6"></i>
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
