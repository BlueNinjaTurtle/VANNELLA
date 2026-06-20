<div class="row">
    <div class="col-lg-8">
        <div class="card admin-card">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary-soft p-2 rounded-3 me-3">
                    <i class="fas fa-calendar-plus text-primary fs-4"></i>
                </div>
                <h5 class="mb-0 fw-bold">Nouvel Horaire de Cours</h5>
            </div>
            
            <form id="planning-form">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Cours / Matière</label>
                        <select id="select-cours" class="form-select border-0 bg-light py-2" required>
                            <option value="">Sélectionner un cours...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Promotion</label>
                        <select id="select-promotion" class="form-select border-0 bg-light py-2" required>
                            <option value="">Sélectionner une promotion...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Date</label>
                        <input type="date" id="date-cours" class="form-control border-0 bg-light py-2" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Jour</label>
                        <select id="select-jour" class="form-select border-0 bg-light py-2" required>
                            <option value="Lundi">Lundi</option>
                            <option value="Mardi">Mardi</option>
                            <option value="Mercredi">Mercredi</option>
                            <option value="Jeudi">Jeudi</option>
                            <option value="Vendredi">Vendredi</option>
                            <option value="Samedi">Samedi</option>
                            <option value="Dimanche">Dimanche</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Heure Début</label>
                        <input type="time" id="heure-debut" class="form-control border-0 bg-light py-2" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-muted">Heure Fin</label>
                        <input type="time" id="heure-fin" class="form-control border-0 bg-light py-2" required>
                    </div>
                </div>

                <div class="my-4 border-top border-light"></div>

                <div class="p-3 rounded-4 mb-4 d-flex justify-content-between align-items-center" style="background: #f8fafc; border: 1px dashed #e2e8f0;">
                    <div>
                        <h6 class="mb-1 fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">Salle Attribuée :</h6>
                        <div id="selected-room-info" class="text-primary fw-bold fs-5">Aucune salle sélectionnée</div>
                    </div>
                    <button type="button" id="btn-optimize" class="btn btn-white shadow-sm border py-2 px-3">
                        <i class="fas fa-magic text-primary me-2"></i> Optimiser
                    </button>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                    <i class="fas fa-save me-2"></i> ENREGISTRER L'HORAIRE
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card admin-card border-0" style="background: linear-gradient(135deg, var(--ispt-primary), #003366); color: white;">
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-lightbulb fs-4 me-3 opacity-75"></i>
                <h5 class="mb-0 fw-bold">Optimisation</h5>
            </div>
            <p class="small opacity-75">L'algorithme intelligent de l'ISPT analyse plusieurs critères pour garantir le meilleur confort :</p>
            <ul class="list-unstyled small mb-4">
                <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle me-2 mt-1 opacity-50"></i>
                    Effectif réel vs Capacité
                </li>
                <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle me-2 mt-1 opacity-50"></i>
                    Conflits d'horaires
                </li>
                <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle me-2 mt-1 opacity-50"></i>
                    Proximité des départements
                </li>
            </ul>
            <div id="optimization-log" class="mt-auto bg-white bg-opacity-10 p-3 rounded-3" style="min-height: 100px;">
                <em class="small opacity-50">En attente d'analyse...</em>
            </div>
        </div>
    </div>
</div>

<!-- Section Horaires Existants -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card admin-card">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary-soft p-2 rounded-3 me-3">
                    <i class="fas fa-list text-primary fs-4"></i>
                </div>
                <h5 class="mb-0 fw-bold">Horaires Existants</h5>
                <button type="button" class="btn btn-sm btn-outline-primary ms-auto" onclick="detectCoursEnsemble()">
                    <i class="fas fa-search me-1"></i>Détecter cours d'ensemble
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3 ps-4 text-muted small text-uppercase fw-bold">Cours</th>
                            <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Promotion</th>
                            <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Date</th>
                            <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Jour</th>
                            <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Horaire</th>
                            <th class="border-0 py-3 text-muted small text-uppercase fw-bold">Salle</th>
                            <th class="border-0 py-3 pe-4 text-muted small text-uppercase fw-bold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="table-horaires">
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-spinner fa-spin me-2"></i>Chargement...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
