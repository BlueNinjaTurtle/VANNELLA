<?php

function attributionPromotionPriority(array $promotion): int {
    $label = ($promotion['nom_promotion'] ?? '') . ' ' . ($promotion['niveau'] ?? '');
    if (preg_match('/bac\s*\+?\s*([1-4])/i', $label, $matches)) {
        return (int)$matches[1];
    }

    if (preg_match('/(?:licence|l|graduat|g)\s*([1-4])/i', $label, $matches)) {
        return (int)$matches[1];
    }

    return 0;
}

function attributionIsPromotionEnsemble(array $promotion): bool {
    $label = strtolower(($promotion['nom_promotion'] ?? '') . ' ' . ($promotion['filiere'] ?? ''));
    return strpos($label, 'toutes') !== false || strpos($label, 'tous') !== false;
}

function attributionCanReplaceConflict(array $newPromotion, string $newTypeCours, array $conflict): bool {
    $newIsEnsemble = $newTypeCours === 'ensemble';
    $conflictIsEnsemble = ($conflict['type_cours'] ?? 'specifique') === 'ensemble';

    if ($newIsEnsemble && !$conflictIsEnsemble) {
        return true;
    }

    if (!$newIsEnsemble && $conflictIsEnsemble) {
        return false;
    }

    return attributionPromotionPriority($newPromotion) > attributionPromotionPriority($conflict);
}

function attributionMinutesFromTime(string $time): int {
    [$hours, $minutes] = array_map('intval', explode(':', substr($time, 0, 5)));
    return $hours * 60 + $minutes;
}

function attributionCourseWindows(): array {
    return [
        ['start' => 8 * 60, 'end' => 12 * 60 + 15],
        ['start' => 14 * 60, 'end' => 18 * 60 + 15],
    ];
}

function attributionSlotFitsCourseWindows(string $heureDebut, string $heureFin): bool {
    $start = attributionMinutesFromTime($heureDebut);
    $end = attributionMinutesFromTime($heureFin);

    if ($end <= $start) {
        return false;
    }

    foreach (attributionCourseWindows() as $window) {
        if ($start >= $window['start'] && $end <= $window['end']) {
            return true;
        }
    }

    return false;
}

function attributionSlotIsPast(string $dateCours, string $heureDebut): bool {
    return strtotime($dateCours . ' ' . substr($heureDebut, 0, 5)) <= time();
}

function attributionFrenchDayName(string $dateCours): string {
    $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    return $jours[(int)date('w', strtotime($dateCours))];
}

function attributionIsCourseDay(string $dateCours, ?string $jour = null): bool {
    $dayFromDate = attributionFrenchDayName($dateCours);
    $dayValue = trim((string)$jour);

    if ($dayFromDate === 'Dimanche' || strcasecmp($dayValue, 'Dimanche') === 0) {
        return false;
    }

    return true;
}

function attributionUpdateSalleEtat(PDO $pdo, int $idSalle, string $etat, string $modifiedBy, string $raison): void {
    $stmt = $pdo->prepare("SELECT etat FROM etat_salles WHERE id_salle = ?");
    $stmt->execute([$idSalle]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldEtat = $current['etat'] ?? 'libre';

    if ($current) {
        $stmt = $pdo->prepare("
            UPDATE etat_salles
            SET etat = ?, date_update = NOW(), modified_by = ?, modified_at = NOW()
            WHERE id_salle = ?
        ");
        $stmt->execute([$etat, $modifiedBy, $idSalle]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO etat_salles (id_salle, etat, date_update, modified_by, modified_at)
            VALUES (?, ?, NOW(), ?, NOW())
        ");
        $stmt->execute([$idSalle, $etat, $modifiedBy]);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO etat_salles_history (id_salle, etat_ancien, etat_nouveau, modified_by, raison, timestamp)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$idSalle, $oldEtat, $etat, $modifiedBy, $raison]);
    } catch (Exception $e) {
        // Historique optionnel.
    }
}

function attributionHasActiveConflict(PDO $pdo, int $idSalle, array $horaire): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM horaires
        WHERE id_salle = ?
          AND id_horaire <> ?
          AND date_cours = ?
          AND jour = ?
          AND COALESCE(NULLIF(statut, ''), 'actif') IN ('actif', 'en_cours')
          AND (
              (heure_debut <= ? AND heure_fin > ?) OR
              (heure_debut < ? AND heure_fin >= ?) OR
              (? <= heure_debut AND ? >= heure_fin)
          )
    ");
    $stmt->execute([
        $idSalle,
        $horaire['id_horaire'],
        $horaire['date_cours'],
        $horaire['jour'],
        $horaire['heure_debut'],
        $horaire['heure_debut'],
        $horaire['heure_fin'],
        $horaire['heure_fin'],
        $horaire['heure_debut'],
        $horaire['heure_fin']
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function attributionReactivateWaitingForSalle(
    PDO $pdo,
    int $idSalle,
    ?string $dateCours = null,
    ?string $jour = null,
    ?string $heureDebut = null,
    ?string $heureFin = null,
    string $modifiedBy = 'System'
): ?array {
    $salleStmt = $pdo->prepare("SELECT id_salle, capacite FROM salles WHERE id_salle = ?");
    $salleStmt->execute([$idSalle]);
    $salle = $salleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$salle) {
        return null;
    }

    $where = [
        "h.statut = 'en_attente'",
        "h.date_cours >= CURDATE()",
        "TIMESTAMP(h.date_cours, h.heure_fin) > NOW()",
        "h.jour <> 'Dimanche'",
        "DAYOFWEEK(h.date_cours) <> 1",
        "p.effectif <= ?"
    ];
    $params = [(int)$salle['capacite']];

    if ($dateCours !== null) {
        $where[] = "h.date_cours = ?";
        $params[] = $dateCours;
    }
    if ($jour !== null) {
        $where[] = "h.jour = ?";
        $params[] = $jour;
    }
    if ($heureDebut !== null && $heureFin !== null) {
        $where[] = "(
            (h.heure_debut <= ? AND h.heure_fin > ?) OR
            (h.heure_debut < ? AND h.heure_fin >= ?) OR
            (? <= h.heure_debut AND ? >= h.heure_fin)
        )";
        array_push($params, $heureDebut, $heureDebut, $heureFin, $heureFin, $heureDebut, $heureFin);
    }

    $stmt = $pdo->prepare("
        SELECT
            h.id_horaire,
            h.id_salle,
            h.date_cours,
            h.jour,
            h.heure_debut,
            h.heure_fin,
            h.type_cours,
            p.nom_promotion,
            p.filiere,
            p.niveau,
            p.effectif
        FROM horaires h
        JOIN promotions p ON h.id_promotion = p.id_promotion
        WHERE " . implode(' AND ', $where) . "
    ");
    $stmt->execute($params);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$candidates) {
        return null;
    }

    usort($candidates, function (array $a, array $b): int {
        $ensembleCompare = (int)(($b['type_cours'] ?? 'specifique') === 'ensemble') <=> (int)(($a['type_cours'] ?? 'specifique') === 'ensemble');
        if ($ensembleCompare !== 0) {
            return $ensembleCompare;
        }

        $priorityCompare = attributionPromotionPriority($b) <=> attributionPromotionPriority($a);
        if ($priorityCompare !== 0) {
            return $priorityCompare;
        }

        $effectifCompare = ((int)$b['effectif']) <=> ((int)$a['effectif']);
        if ($effectifCompare !== 0) {
            return $effectifCompare;
        }

        return strcmp($a['heure_debut'], $b['heure_debut']);
    });

    foreach ($candidates as $candidate) {
        if (attributionHasActiveConflict($pdo, $idSalle, $candidate)) {
            continue;
        }

        $stmt = $pdo->prepare("UPDATE horaires SET id_salle = ?, statut = 'actif' WHERE id_horaire = ?");
        $stmt->execute([$idSalle, $candidate['id_horaire']]);

        attributionUpdateSalleEtat(
            $pdo,
            $idSalle,
            'réservée',
            $modifiedBy,
            "Réattribution automatique de l'horaire en attente #{$candidate['id_horaire']}"
        );

        return $candidate;
    }

    return null;
}

?>
