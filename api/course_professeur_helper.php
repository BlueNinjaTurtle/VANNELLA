<?php

function normalizeOptionalUid($value): ?string {
    if (!isset($value)) {
        return null;
    }

    $uid = trim((string)$value);
    return $uid === '' ? null : $uid;
}

function resolveProfesseurForCours(PDO $pdo, string $enseignant, ?string $uid_badge): int {
    $nom_professeur = trim($enseignant);

    if ($nom_professeur === '') {
        throw new InvalidArgumentException("Le nom de l'enseignant est requis");
    }

    $stmt = $pdo->prepare("SELECT id_professeur, nom_professeur, uid_badge FROM professeurs WHERE nom_professeur = ?");
    $stmt->execute([$nom_professeur]);
    $professeurByName = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($uid_badge !== null) {
        $stmt = $pdo->prepare("SELECT id_professeur, nom_professeur FROM professeurs WHERE uid_badge = ?");
        $stmt->execute([$uid_badge]);
        $professeurByUid = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($professeurByUid && (!$professeurByName || (int)$professeurByUid['id_professeur'] !== (int)$professeurByName['id_professeur'])) {
            throw new RuntimeException("Cet UID RFID est deja attribue a " . $professeurByUid['nom_professeur']);
        }
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
