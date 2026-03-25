<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestion des Cours</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCours">
        <i class="fas fa-plus me-2"></i> Ajouter un cours
    </button>
</div>

<div class="card admin-card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Intitulé du Cours</th>
                    <th>Enseignant</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="table-cours">
                <!-- Chargement dynamique -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Cours -->
<div class="modal fade" id="modalCours" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-cours">
                <div class="modal-header">
                    <h5 class="modal-title">Détails du Cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cours-id">
                    <div class="mb-3">
                        <label class="form-label">Nom du cours</label>
                        <input type="text" id="cours-nom" class="form-control" required placeholder="Ex: Algorithmique">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enseignant</label>
                        <input type="text" id="cours-enseignant" class="form-control" required placeholder="Ex: M. Jean Dupont">
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
