<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Attribution de Salle</h2>
    <div class="text-muted" id="current-date"></div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card admin-card p-4">
            <h5 class="mb-4">Nouvel Horaire de Cours</h5>
            <form id="planning-form">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cours</label>
                        <select id="select-cours" class="form-select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Promotion</label>
                        <select id="select-promotion" class="form-select" required>
                            <option value="">Chargement...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jour</label>
                        <select id="select-jour" class="form-select" required>
                            <option value="Lundi">Lundi</option>
                            <option value="Mardi">Mardi</option>
                            <option value="Mercredi">Mercredi</option>
                            <option value="Jeudi">Jeudi</option>
                            <option value="Vendredi">Vendredi</option>
                            <option value="Samedi">Samedi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Heure Début</label>
                        <input type="time" id="heure-debut" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Heure Fin</label>
                        <input type="time" id="heure-fin" class="form-control" required>
                    </div>
                </div>

                <hr class="my-4">

                <div class="bg-light p-3 rounded mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold">Salle Attribuée :</h6>
                        <div id="selected-room-info" class="text-primary fw-bold">Aucune salle sélectionnée</div>
                    </div>
                    <button type="button" id="btn-optimize" class="btn btn-dark btn-sm shadow-sm">
                        <i class="fas fa-magic me-1"></i> Attribution Optimale
                    </button>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                    ENREGISTRER L'HORAIRE
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card admin-card p-4 h-100">
            <h5 class="mb-3">Aide à l'Optimisation</h5>
            <p class="small text-muted">L'algorithme analyse :</p>
            <ul class="small text-muted">
                <li>L'effectif réel de la promotion.</li>
                <li>Les conflits d'horaires existants.</li>
                <li>La capacité résiduelle des salles (évite le gaspillage).</li>
            </ul>
            <div id="optimization-log" class="mt-auto">
                <!-- Feedback messages -->
            </div>
        </div>
    </div>
</div>
