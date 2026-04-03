<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary-soft p-2 rounded-3 me-3 text-primary">
            <i class="fas fa-door-open fs-4"></i>
        </div>
        <h5 class="mb-0 fw-bold">Inventaire des Salles</h5>
    </div>
    <button class="btn btn-primary shadow-sm rounded-3 px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalSalle">
        <i class="fas fa-plus me-2"></i> Nouvelle Salle
    </button>
</div>

<div class="card admin-card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 py-3 ps-4 text-muted small text-uppercase fw-bold">Nom de la salle</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold text-center">Capacité</th>
                    <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Localisation / Bâtiment</th>
                    <th class="border-0 py-3 pe-4 text-muted small text-uppercase fw-bold text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="table-salles">
                <!-- Chargement dynamique via salles.js -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter/Modifier -->
<div class="modal fade" id="modalSalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="form-salle">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="modalSalleLabel">Configuration de la salle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="salle-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Nom de la salle</label>
                        <input type="text" id="salle-nom" class="form-control border-0 bg-light py-2 px-3" required placeholder="Ex: Salle A1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">Capacité maximale</label>
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light"><i class="fas fa-users text-muted"></i></span>
                            <input type="number" id="salle-capacite" class="form-control border-0 bg-light py-2" required placeholder="Nombre d'étudiants">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold text-muted small">Bâtiment / Zone</label>
                        <input type="text" id="salle-batiment" class="form-control border-0 bg-light py-2 px-3" required placeholder="Ex: Bâtiment Polytechnique">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>
