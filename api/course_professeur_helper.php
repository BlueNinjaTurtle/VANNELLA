<?php

function normalizeOptionalUid($value): ?string {
    if (!isset($value)) {
        return null;
    }

    $uid = trim((string)$value);
    return $uid === '' ? null : $uid;
}

function resolveProfesseurForCours(PDO $pdo, string $enseignant, ?string $uid_badge, ?int $current_professeur_id = null): int {
    $nom_professeur = trim($enseignant);

    if ($nom_professeur === '') {
        throw new InvalidArgumentException("Le nom de l'enseignant est requis");
    }

    $currentProfesseur = null;
    if ($current_professeur_id !== null && $current_professeur_id > 0) {
        $stmt = $pdo->prepare("SELECT id_professeur, nom_professeur, uid_badge FROM professeurs WHERE id_professeur = ?");
        $stmt->execute([$current_professeur_id]);
        $currentProfesseur = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $stmt = $pdo->prepare("SELECT id_professeur, nom_professeur, uid_badge FROM professeurs WHERE nom_professeur = ?");
    $stmt->execute([$nom_professeur]);
    $professeurByName = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($uid_badge !== null) {
        $stmt = $pdo->prepare("SELECT id_professeur, nom_professeur FROM professeurs WHERE uid_badge = ?");
        $stmt->execute([$uid_badge]);
        $professeurByUid = $stmt->fetch(PDO::FETCH_ASSOC);

        $uidBelongsToCurrent = $professeurByUid && $currentProfesseur && (int)$professeurByUid['id_professeur'] === (int)$currentProfesseur['id_professeur'];
        $uidBelongsToSameName = $professeurByUid && $professeurByName && (int)$professeurByUid['id_professeur'] === (int)$professeurByName['id_professeur'];

        if ($professeurByUid && !$uidBelongsToCurrent && !$uidBelongsToSameName) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE id_professeur = ?");
            $stmt->execute([$professeurByUid['id_professeur']]);
            $linkedCours = (int)$stmt->fetchColumn();

            if ($linkedCours > 0) {
                throw new RuntimeException("Cet UID RFID est deja attribue a " . $professeurByUid['nom_professeur']);
            }

            $stmt = $pdo->prepare("UPDATE professeurs SET uid_badge = NULL WHERE id_professeur = ?");
            $stmt->execute([$professeurByUid['id_professeur']]);
        }
    }

    if ($currentProfesseur) {
        $stmt = $pdo->prepare("UPDATE professeurs SET nom_professeur = ?, uid_badge = ? WHERE id_professeur = ?");
        $stmt->execute([$nom_professeur, $uid_badge, $currentProfesseur['id_professeur']]);

        return (int)$currentProfesseur['id_professeur'];
    }

    if ($professeurByName) {
        if ($uid_badge !== null && $professeurByName['uid_badge'] !== $uid_badge) {
            $stmt = $pdo->prepare("UPDATE professeurs SET uid_badge = ? WHERE id_professeur = ?");
            $stmt->execute([$uid_badge, $professeurByName['id_professeur']]);
        }

        return (int)$professeurByName['id_professeur'];
    }

    $stmt = $pdo->prepare("INSERT INTO professeurs (nom_professeur, uid_badge) VALUES (?, ?)");
    $stmt->execute([$nom_professeur, $uid_badge]);

    return (int)$pdo->lastInsertId();
}

?>
