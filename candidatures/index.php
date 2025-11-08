<?php
require '../config.php';
require '../auth_helper.php';

// Vérifier si l'utilisateur est admin
if (!isAdminLoggedIn()) {
    header('Location: ../admin.php');
    exit();
}

// Messages de feedback
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Récupérer toutes les candidatures
$candidatures = $pdo->query("
    SELECT id, nom, prenom, email, poste, date_candidature, statut 
    FROM candidatures 
    ORDER BY date_candidature DESC
")->fetchAll();

$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 Gestion des Candidatures</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="icon" type="image/x-icon" href="../favicon.png">
</head>
<body>
    
    <!-- Navigation -->
    <?= generateNavigation('candidatures/index') ?>

    <div class="admin-layout">
        <header class="admin-header">
            <h1>📋 Gestion des Candidatures</h1>
            <div class="admin-actions">
                <a href="../admin.php" class="btn btn-secondary">
                    ⬅️ Retour au Dashboard
                </a>
                <a href="ajouter_candidature.php" class="btn btn-primary">
                    ➕ Nouvelle Candidature
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

            <?php if (empty($candidatures)): ?>
                <div class="empty-state">
                    <h3>Aucune candidature</h3>
                    <p>Vous n'avez encore aucune candidature enregistrée.</p>
                    <a href="ajouter_candidature.php" class="btn btn-primary">
                        ➕ Ajouter votre première candidature
                    </a>
                </div>
            <?php else: ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?= count($candidatures) ?></h3>
                        <p>Total candidatures</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= count(array_filter($candidatures, fn($c) => $c['statut'] === 'en_attente')) ?></h3>
                        <p>En attente</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= count(array_filter($candidatures, fn($c) => $c['statut'] === 'acceptee')) ?></h3>
                        <p>Acceptées</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= count(array_filter($candidatures, fn($c) => $c['statut'] === 'refusee')) ?></h3>
                        <p>Refusées</p>
                    </div>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Poste</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidatures as $candidature): ?>
                                <tr>
                                    <td><?= htmlspecialchars($candidature['prenom'] . ' ' . $candidature['nom']) ?></td>
                                    <td><?= htmlspecialchars($candidature['email']) ?></td>
                                    <td><?= htmlspecialchars($candidature['poste']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($candidature['date_candidature'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $candidature['statut'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $candidature['statut'])) ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="modifier_candidature.php?id=<?= $candidature['id'] ?>" 
                                           class="btn btn-edit" title="Modifier">
                                            ✏️
                                        </a>
                                        <a href="supprimer_candidature.php?id=<?= $candidature['id'] ?>" 
                                           class="btn btn-delete" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?')"
                                           title="Supprimer">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../script.js"></script>
    <script src="../admin.js"></script>
</body>
</html>