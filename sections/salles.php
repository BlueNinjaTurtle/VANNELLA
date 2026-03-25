<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestion des Salles</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSalle">
        <i class="fas fa-plus me-2"></i> Ajouter une salle
    </button>
</div>

<div class="card admin-card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nom de la salle</th>
                    <th>Capacité</th>
                    <th>Bâtiment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="table-salles">
                <!-- Chargement dynamique -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter/Modifier -->
<div class="modal fade" id="modalSalle" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-salle">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la salle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="salle-id">
                    <div class="mb-3">
                        <label class="form-label">Nom de la salle</label>
                        <input type="text" id="salle-nom" class="form-control" required placeholder="Ex: A1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacité (Nombre d'étudiants)</label>
                        <input type="number" id="salle-capacite" class="form-control" required placeholder="Ex: 50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bâtiment</label>
                        <input type="text" id="salle-batiment" class="form-control" required placeholder="Ex: Bâtiment Central">
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
