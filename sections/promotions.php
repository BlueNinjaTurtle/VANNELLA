<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary-soft p-2 rounded-3 me-3 text-primary">
            <i class="fas fa-user-graduate fs-4"></i>
        </div>
        <h5 class="mb-0 fw-bold">Gestion des Promotions</h5>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary border-2 shadow-sm rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#modalDept">
            <i class="fas fa-building me-2"></i> Départements
        </button>
        <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#modalPromo">
            <i class="fas fa-plus me-2"></i> Nouvelle Promotion
        </button>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 py-3 ps-4 text-muted small text-uppercase fw-bold">Promotion</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Département</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Filière / Niveau</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold text-center">Effectif</th>
                    <th class="border-0 py-3 pe-4 text-muted small text-uppercase fw-bold text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="table-promotions">
                <!-- Chargement dynamique via promotions.js -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Promotion -->
<div class="modal fade" id="modalPromo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="form-promo">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Configuration Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="promo-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Nom de la Promotion</label>
                        <input type="text" id="promo-nom" class="form-control border-0 bg-light py-2 px-3" required placeholder="Ex: BAC4 Informatique">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small">Département</label>
                            <select id="promo-dept" class="form-select border-0 bg-light py-2 px-3" required>
                                <option value="">Sélectionner...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted small">Effectif</label>
                            <input type="number" id="promo-effectif" class="form-control border-0 bg-light py-2 px-3" required placeholder="Ex: 45">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Filière</label>
                        <input type="text" id="promo-filiere" class="form-control border-0 bg-light py-2 px-3" required placeholder="Ex: Informatique">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Niveau d'études</label>
                        <input type="text" id="promo-niveau" class="form-control border-0 bg-light py-2 px-3" required placeholder="Ex: BAC2">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Département -->
<div class="modal fade" id="modalDept" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Gestion des Départements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-dept" class="mb-4">
                    <label class="form-label fw-semibold text-muted small">Nouveau département</label>
                    <div class="input-group">
                        <input type="text" id="dept-nom" class="form-control border-0 bg-light py-2 px-3" placeholder="Nom du département" required>
                        <button class="btn btn-primary px-3" type="submit">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </form>
                <div class="border-top pt-3">
                    <label class="form-label fw-semibold text-muted small mb-3">Départements existants</label>
                    <ul class="list-group list-group-flush" id="list-depts">
                        <!-- Chargement dynamique via promotions.js -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
