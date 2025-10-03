<?php
require_once '../config.php';
require_once '../auth_helper.php';

// Vérifier si l'utilisateur est admin
if (!isAdminLoggedIn()) {
    header('Location: ../admin.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize_input($_POST['nom'] ?? '');
    $prenom = sanitize_input($_POST['prenom'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $telephone = sanitize_input($_POST['telephone'] ?? '');
    $poste = sanitize_input($_POST['poste'] ?? '');
    $entreprise = sanitize_input($_POST['entreprise'] ?? '');
    $lieu = sanitize_input($_POST['lieu'] ?? '');
    $site_annonce = sanitize_input($_POST['site_annonce'] ?? '');
    $url_annonce = sanitize_input($_POST['url_annonce'] ?? '');
    $remuneration = sanitize_input($_POST['remuneration'] ?? '');
    $statut = $_POST['statut'] ?? 'en_attente';
    $lettre_motivation = sanitize_input($_POST['lettre_motivation'] ?? '');
    $notes = sanitize_input($_POST['notes'] ?? '');
    $date_candidature = $_POST['date_candidature'] ?? date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_candidature)) {
        $date_candidature = date('Y-m-d');
    }

    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($poste) || empty($entreprise)) {
        $error = 'Les champs nom, prénom, email, poste et entreprise sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format d\'email invalide.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO candidatures (
                    nom, prenom, email, telephone, poste, entreprise, lieu, 
                    site_annonce, url_annonce, remuneration, statut, 
                    lettre_motivation, notes, date_candidature
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $nom, $prenom, $email, $telephone, $poste, $entreprise, $lieu,
                $site_annonce, $url_annonce, $remuneration ?: null, $statut,
                $lettre_motivation, $notes, $date_candidature
            ]);

            $success = 'Candidature ajoutée avec succès !';
            // Réinitialiser les variables
            $nom = $prenom = $email = $telephone = $poste = $entreprise = $lieu = '';
            $site_annonce = $url_annonce = $remuneration = $lettre_motivation = $notes = '';
            $statut = 'en_attente';
            $date_candidature = date('Y-m-d');
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'ajout : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Ajouter une candidature</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="icon" type="image/x-icon" href="../favicon.png">
</head>
<body>
    <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
    
    <!-- Navigation -->
    <?= generateNavigation('candidatures/index') ?>

    <div class="admin-layout">
        <header class="admin-header">
            <h1>➕ Ajouter une candidature</h1>
            <div class="admin-actions">
                <a href="index.php" class="btn btn-secondary">
                    ⬅️ Retour à la liste
                </a>
            </div>
        </header>

        <main class="admin-main">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✅ Succès !</strong> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>❌ Erreur !</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-grid">
                <div class="form-section">
                    <h3>👤 Informations du candidat</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($prenom ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nom ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" name="telephone" id="telephone" value="<?= htmlspecialchars($telephone ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>💼 Informations du poste</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="poste">Intitulé du poste *</label>
                            <input type="text" name="poste" id="poste" value="<?= htmlspecialchars($poste ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="entreprise">Entreprise *</label>
                            <input type="text" name="entreprise" id="entreprise" value="<?= htmlspecialchars($entreprise ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="lieu">Lieu</label>
                            <input type="text" name="lieu" id="lieu" value="<?= htmlspecialchars($lieu ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="remuneration">Rémunération</label>
                            <input type="text" name="remuneration" id="remuneration" value="<?= htmlspecialchars($remuneration ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_candidature">Date de candidature</label>
                            <input type="date" name="date_candidature" id="date_candidature" value="<?= $date_candidature ?? date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <select name="statut" id="statut">
                                <option value="en_attente" <?= ($statut ?? 'en_attente') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="entretien" <?= ($statut ?? '') === 'entretien' ? 'selected' : '' ?>>Entretien</option>
                                <option value="acceptee" <?= ($statut ?? '') === 'acceptee' ? 'selected' : '' ?>>Acceptée</option>
                                <option value="refusee" <?= ($statut ?? '') === 'refusee' ? 'selected' : '' ?>>Refusée</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔗 Informations complémentaires</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="site_annonce">Site de l'annonce</label>
                            <input type="text" name="site_annonce" id="site_annonce" value="<?= htmlspecialchars($site_annonce ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="url_annonce">URL de l'annonce</label>
                            <input type="url" name="url_annonce" id="url_annonce" value="<?= htmlspecialchars($url_annonce ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lettre_motivation">Lettre de motivation</label>
                        <textarea name="lettre_motivation" id="lettre_motivation" rows="4"><?= htmlspecialchars($lettre_motivation ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes" rows="3"><?= htmlspecialchars($notes ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        ✅ Enregistrer la candidature
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        ❌ Annuler
                    </a>
                </div>
            </form>
        </main>
    </div>

    <script src="../script.js"></script>
    <script src="../admin.js"></script>
</body>
</html>
            <label for="site_annonce" class="form-label">Site de l'annonce</label>
            <input type="text" name="site_annonce" id="site_annonce" list="liste_site_annonce" class="form-control" required autocomplete="off">
            <datalist id="liste_site_annonce"></datalist>
        </div>
        <div class="mb-3">
            <label for="url_annonce" class="form-label">Lien de l'annonce</label>
            <input type="text" name="url_annonce" id="url_annonce" class="form-control" value="<?= htmlspecialchars($candidature['url_annonce'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="remuneration" class="form-label">Rémunération (optionnel)</label>
            <input type="text" name="remuneration" id="remuneration" class="form-control">
        </div>
        <div class="mb-3">
            <label for="date_candidature" class="form-label">Date de candidature</label>
            <input type="date" name="date_candidature" id="date_candidature" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="liste_candidatures.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

</body>
</html>
