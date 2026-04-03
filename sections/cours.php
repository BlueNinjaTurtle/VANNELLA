<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary-soft p-2 rounded-3 me-3 text-primary">
            <i class="fas fa-book-reader fs-4"></i>
        </div>
        <h5 class="mb-0 fw-bold">Catalogue des Cours</h5>
    </div>
    <button class="btn btn-primary shadow-sm rounded-3 px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalCours">
        <i class="fas fa-plus me-2"></i> Nouveau Cours
    </button>
</div>

<div class="card admin-card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 py-3 ps-4 text-muted small text-uppercase fw-bold">Intitulé du Cours</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Enseignant Responsable</th>
                    <th class="border-0 py-3 pe-4 text-muted small text-uppercase fw-bold text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="table-cours">
                <!-- Chargement dynamique via cours.js -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Cours -->
<div class="modal fade" id="modalCours" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="form-cours">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Configuration du Cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="cours-id">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small">Nom du cours / Matière</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light"><i class="fas fa-book text-muted"></i></span>
                            <input type="text" id="cours-nom" class="form-control border-0 bg-light py-2" required placeholder="Ex: Algorithmique Avancée">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Nom de l'enseignant</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light"><i class="fas fa-chalkboard-teacher text-muted"></i></span>
                            <input type="text" id="cours-enseignant" class="form-control border-0 bg-light py-2" required placeholder="Ex: Prof. Kasongo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">Enregistrer le cours</button>
                </div>
            </form>
        </div>
    </div>
</div>
