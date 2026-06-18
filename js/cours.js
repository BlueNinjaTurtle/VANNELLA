$(document).ready(function() {
    let allCours = [];
    
    loadDepartements();
    loadCours();

    // Charger les départements pour le dropdown
    function loadDepartements() {
        $.get('api/getDepartments.php', function(res) {
            if(res.status === 'success') {
                let html = '<option value="">-- Sélectionner un département --</option>';
                res.data.forEach(d => {
                    html += `<option value="${d.id_departement}">${d.nom_departement}</option>`;
                });
                $('#cours-departement').html(html);
                $('#filter-departement').html('<option value="">Tous les départements</option>' + html.replace('-- Sélectionner un département --', 'Tous les départements'));
            }
        });
    }

    function loadCours() {
        $.get('api/getCours.php', function(res) {
            if(res.status === 'success') {
                allCours = res.data;
                filterAndRenderCours();
            }
        });
    }

    function filterAndRenderCours() {
        const selectedDept = $('#filter-departement').val();
        let filtered = allCours;
        
        if(selectedDept) {
            filtered = allCours.filter(c => c.id_departement == selectedDept);
        }

        let html = '';
        if(filtered.length === 0) {
            html = '<tr><td colspan="5" class="text-center py-4 text-muted">Aucun cours trouvé</td></tr>';
        } else {
            filtered.forEach(c => {
                const uidBadge = c.uid_badge ?
                    `<span class="badge bg-primary-subtle text-primary rounded-pill small">${c.uid_badge}</span>` :
                    '<span class="badge bg-light text-muted rounded-pill small">Non attribue</span>';
                const deptBadge = c.nom_departement ? 
                    `<span class="badge bg-info-soft text-info rounded-pill small">${c.nom_departement}</span>` : 
                    '<span class="badge bg-light text-muted rounded-pill small">Non assigné</span>';
                
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
                        <td>
                            ${uidBadge}
                        </td>
                        <td>
                            ${deptBadge}
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

    // Filtre changé
    $('#filter-departement').on('change', function() {
        filterAndRenderCours();
    });

    // Modal
    $('#modalCours').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        if (button.hasClass('btn-edit-cours')) {
            const c = button.data('cours');
            $('#cours-id').val(c.id_cours);
            $('#cours-nom').val(c.nom_cours);
            $('#cours-enseignant').val(c.enseignant);
            $('#cours-uid-badge').val(c.uid_badge || '');
            $('#cours-departement').val(c.id_departement || '');
        } else {
            $('#form-cours')[0].reset();
            $('#cours-id').val('');
            $('#cours-uid-badge').val('');
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
            enseignant: $('#cours-enseignant').val(),
            uid_badge: $('#cours-uid-badge').val(),
            id_departement: $('#cours-departement').val() || null
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
        const coursNom = $(this).closest('tr').find('td:first').text();
        if(confirm(`⚠️ Supprimer le cours "${coursNom}" ?\n\n✓ Le cours sera supprimé\n✓ TOUS les horaires liés seront SUPPRIMÉS\n✓ Les salles seront marquées comme LIBRES`)) {
            $.post('api/deleteCours.php', { id_cours: $(this).data('id') }, function(res) {
                if(res.status === 'success') {
                    // Afficher feedback
                    let msg = `✓ Cours supprimé`;
                    if(res.horaires_deleted > 0) {
                        msg += ` + ${res.horaires_deleted} horaire(s) supprimé(s)`;
                    }
                    alert(msg);
                    loadCours();
                } else {
                    alert('❌ Erreur: ' + res.message);
                }
            }, 'json').fail(function(err) {
                alert('❌ Erreur suppression: Impossible de contacter l\'API');
            });
        }
    });
});
