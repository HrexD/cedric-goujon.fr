<?php
require_once '../config.php';



if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = (int)$_GET['id'];

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $poste = sanitize_input($_POST['poste'] ?? '');
    $lieu = sanitize_input($_POST['lieu'] ?? '');
    $site_annonce = sanitize_input($_POST['site_annonce'] ?? '');
    $url_annonce = sanitize_input($_POST['url_annonce'] ?? '');
    $entreprise = sanitize_input($_POST['entreprise'] ?? '');
    $remuneration = sanitize_input($_POST['remuneration'] ?? '');
    $date_candidature = $_POST['date_candidature'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_candidature)) {
        $date_candidature = date('Y-m-d');
    }

    $stmt = $pdo->prepare("UPDATE candidatures SET poste = ?, lieu = ?, site_annonce = ?, url_annonce = ?, date_candidature = ?, entreprise = ?, remuneration = ? WHERE id = ?");
    $stmt->execute([
        $poste,
        $lieu,
        $site_annonce,
        $url_annonce,
        $date_candidature,
        $entreprise,
        $remuneration ?: null,
        $id
    ]);

    header("Location: liste_candidatures.php");
    exit;
}

// Récupération des données à modifier
$stmt = $pdo->prepare("SELECT * FROM candidatures WHERE id = ?");
$stmt->execute([$id]);
$candidature = $stmt->fetch();

if (!$candidature) {
    die("Candidature introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une candidature</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Modifier une candidature</h2>

    <form method="post" class="bg-white p-4 shadow-sm rounded">
        <div class="mb-3">
            <label for="poste" class="form-label">Intitulé du poste</label>
            <input type="text" name="poste" id="poste" list="liste_poste" class="form-control" required value="<?= htmlspecialchars($candidature['poste']) ?>" autocomplete="off">
            <datalist id="liste_poste"></datalist>
        </div>
        <div class="mb-3">
            <label for="entreprise" class="form-label">Entreprise</label>
            <input type="text" name="entreprise" id="entreprise" list="liste_entreprise" class="form-control" required value="<?= htmlspecialchars($candidature['entreprise']) ?>" autocomplete="off">
            <datalist id="liste_entreprise"></datalist>
        </div>
        <div class="mb-3">
            <label for="lieu" class="form-label">Lieu</label>
            <input type="text" name="lieu" id="lieu" list="liste_lieu" class="form-control" required value="<?= htmlspecialchars($candidature['lieu']) ?>" autocomplete="off">
            <datalist id="liste_lieu"></datalist>
        </div>
        <div class="mb-3">
            <label for="site_annonce" class="form-label">Site de l'annonce</label>
            <input type="text" name="site_annonce" id="site_annonce" list="liste_site_annonce" class="form-control" required value="<?= htmlspecialchars($candidature['site_annonce']) ?>" autocomplete="off">
            <datalist id="liste_site_annonce"></datalist>
        </div>
        <div class="mb-3">
            <label for="url_annonce" class="form-label">Lien de l'annonce</label>
            <input type="text" name="url_annonce" id="url_annonce" class="form-control" value="<?= htmlspecialchars($candidature['url_annonce'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="remuneration" class="form-label">Rémunération (optionnel)</label>
            <input type="text" name="remuneration" id="remuneration" class="form-control" value="<?= htmlspecialchars($candidature['remuneration'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="date_candidature" class="form-label">Date de candidature</label>
            <input type="date" name="date_candidature" id="date_candidature" class="form-control" value="<?= htmlspecialchars($candidature['date_candidature']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="liste_candidatures.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

</body>
</html>
