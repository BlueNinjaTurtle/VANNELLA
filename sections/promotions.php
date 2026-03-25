<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Promotions & Départements</h2>
    <div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalDept">
            <i class="fas fa-building me-2"></i> Gérer Départements
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPromo">
            <i class="fas fa-plus me-2"></i> Ajouter une promotion
        </button>
    </div>
</div>

<div class="card admin-card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nom Promotion</th>
                    <th>Département</th>
                    <th>Filière</th>
                    <th>Niveau</th>
                    <th>Effectif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="table-promotions">
                <!-- Chargement dynamique -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Promotion -->
<div class="modal fade" id="modalPromo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-promo">
                <div class="modal-header">
                    <h5 class="modal-title">Détails Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="promo-id">
                    <div class="mb-3">
                        <label class="form-label">Nom Promotion</label>
                        <input type="text" id="promo-nom" class="form-control" required placeholder="Ex: L1 Informatique">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Département</label>
                        <select id="promo-dept" class="form-select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Filière</label>
                        <input type="text" id="promo-filiere" class="form-control" required placeholder="Ex: Informatique de Gestion">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Niveau</label>
                        <input type="text" id="promo-niveau" class="form-control" required placeholder="Ex: Licence 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effectif</label>
                        <input type="number" id="promo-effectif" class="form-control" required placeholder="Ex: 30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Département (Simple list) -->
<div class="modal fade" id="modalDept" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestion des Départements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-dept" class="mb-4">
                    <div class="input-group">
                        <input type="text" id="dept-nom" class="form-control" placeholder="Nouveau département" required>
                        <button class="btn btn-success" type="submit">Ajouter</button>
                    </div>
                </form>
                <ul class="list-group" id="list-depts">
                    <!-- Chargement dynamique -->
                </ul>
            </div>
        </div>
    </div>
</div>
