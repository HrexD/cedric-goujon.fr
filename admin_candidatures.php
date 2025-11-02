<?php
require 'config.php';

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin');
    exit;
}

$success = '';
$error = '';

// Actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $entreprise = sanitize_input($_POST['entreprise'] ?? '');
            $poste = sanitize_input($_POST['poste'] ?? '');
            $type_contrat = sanitize_input($_POST['type_contrat'] ?? '');
            $localisation = sanitize_input($_POST['localisation'] ?? '');
            $salaire = sanitize_input($_POST['salaire'] ?? '');
            $date_candidature = $_POST['date_candidature'] ?? date('Y-m-d');
            $statut = $_POST['statut'] ?? 'en_attente';
            $source = sanitize_input($_POST['source'] ?? '');
            $notes = sanitize_input($_POST['notes'] ?? '');
            
            if ($entreprise && $poste) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO candidatures (entreprise, poste, type_contrat, localisation, salaire, date_candidature, statut, source, notes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$entreprise, $poste, $type_contrat, $localisation, $salaire, $date_candidature, $statut, $source, $notes]);
                    $success = "Candidature ajoutée avec succès !";
                } catch (PDOException $e) {
                    $error = "Erreur lors de l'ajout : " . $e->getMessage();
                }
            } else {
                $error = "L'entreprise et le poste sont obligatoires.";
            }
            break;
            
        case 'update_status':
            $id = (int)$_POST['id'];
            $statut = $_POST['statut'];
            
            try {
                $stmt = $pdo->prepare("UPDATE candidatures SET statut = ? WHERE id = ?");
                $stmt->execute([$statut, $id]);
                $success = "Statut mis à jour !";
            } catch (PDOException $e) {
                $error = "Erreur lors de la mise à jour : " . $e->getMessage();
            }
            break;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM candidatures WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Candidature supprimée !";
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupération des candidatures avec filtres
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'date_candidature_desc';

try {
    $sql = "SELECT * FROM candidatures WHERE 1=1";
    $params = [];
    
    // Filtres par statut
    if ($filter !== 'all') {
        $sql .= " AND statut = ?";
        $params[] = $filter;
    }
    
    // Recherche
    if (!empty($search)) {
        $sql .= " AND (entreprise LIKE ? OR poste LIKE ? OR localisation LIKE ? OR notes LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    // Tri
    switch ($sort) {
        case 'date_candidature_asc':
            $sql .= " ORDER BY date_candidature ASC";
            break;
        case 'entreprise':
            $sql .= " ORDER BY entreprise ASC";
            break;
        case 'statut':
            $sql .= " ORDER BY statut ASC";
            break;
        default:
            $sql .= " ORDER BY date_candidature DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $candidatures = $stmt->fetchAll();
    
    // Statistiques
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM candidatures")->fetchColumn(),
        'en_attente' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'en_attente'")->fetchColumn(),
        'accepte' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'accepte'")->fetchColumn(),
        'refuse' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'refuse'")->fetchColumn(),
        'entretien' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'entretien'")->fetchColumn()
    ];
    
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
    $candidatures = [];
    $stats = ['total' => 0, 'en_attente' => 0, 'accepte' => 0, 'refuse' => 0, 'entretien' => 0];
}

// Statuts disponibles avec leurs couleurs
$statuts = [
    'en_attente' => ['label' => 'En attente', 'color' => '#ffc107'],
    'entretien' => ['label' => 'Entretien', 'color' => '#17a2b8'],
    'accepte' => ['label' => 'Accepté', 'color' => '#28a745'],
    'refuse' => ['label' => 'Refusé', 'color' => '#dc3545'],
    'relance' => ['label' => 'Relance', 'color' => '#fd7e14']
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>💼 Administration - Candidatures</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-modern.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <button id="theme-toggle" aria-label="Basculer thème" class="theme-toggle">☀️</button>
    
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="user-info">
                <strong>� Admin</strong>
                <div style="font-size: 0.8em; opacity: 0.8; margin-top: 0.5rem;">
                    Interface d'administration
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin">📊 Tableau de bord</a></li>
                    <li><a href="admin_candidatures.php" class="active">💼 Candidatures</a></li>
                    <li><a href="admin_messages.php">📧 Messages</a></li>
                    <li><a href="admin_projets.php">🚀 Projets</a></li>
                    <li><a href="admin_gallery.php">🖼️ Galerie</a></li>
                    <li><a href="admin_utilisateur.php">👤 Utilisateur</a></li>
                    <li><a href="admin_systeme.php">⚙️ Système</a></li>
                    <li style="margin-top: var(--spacing-xl); border-top: 1px solid var(--border-color); padding-top: var(--spacing-lg);">
                        <a href="index">🌐 Voir le site</a>
                    </li>
                    <li><a href="?logout=1" style="color: var(--danger-color);">🚪 Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>💼 Gestion des Candidatures</h1>
                <p class="admin-subtitle">Administration des candidatures d'emploi</p>
                <div class="header-actions">
                    <button onclick="openModal('addModal')" class="btn btn-primary">
                        <span class="btn-icon">➕</span>
                        Nouvelle candidature
                    </button>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if ($success): ?>
                <div class="notification notification-success">
                    <span class="notification-icon">✅</span>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="notification notification-error">
                    <span class="notification-icon">❌</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['total'] ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--warning);">⏳</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--warning);"><?= $stats['en_attente'] ?></div>
                        <div class="stat-label">En attente</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--info);">🎯</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--info);"><?= $stats['entretien'] ?></div>
                        <div class="stat-label">Entretiens</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success);">✅</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--success);"><?= $stats['accepte'] ?></div>
                        <div class="stat-label">Acceptées</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--danger);">❌</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--danger);"><?= $stats['refuse'] ?></div>
                        <div class="stat-label">Refusées</div>
                    </div>
                </div>
            </div>
            
            <!-- Filtres et actions -->
            <div class="admin-toolbar">
                <div class="filter-group">
                    <span class="filter-label">Filtrer :</span>
                    <a href="?filter=all&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                       class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                       Toutes (<?= $stats['total'] ?>)
                    </a>
                    <a href="?filter=en_attente&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                       class="filter-btn <?= $filter === 'en_attente' ? 'active' : '' ?>">
                       En attente (<?= $stats['en_attente'] ?>)
                    </a>
                    <a href="?filter=entretien&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                       class="filter-btn <?= $filter === 'entretien' ? 'active' : '' ?>">
                       Entretiens (<?= $stats['entretien'] ?>)
                    </a>
                </div>
                
                <div class="toolbar-actions">
                    <select onchange="window.location.href='?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&sort=' + this.value" 
                            class="form-select">
                        <option value="date_candidature_desc" <?= $sort === 'date_candidature_desc' ? 'selected' : '' ?>>
                            Date (récent)
                        </option>
                        <option value="date_candidature_asc" <?= $sort === 'date_candidature_asc' ? 'selected' : '' ?>>
                            Date (ancien)
                        </option>
                        <option value="entreprise" <?= $sort === 'entreprise' ? 'selected' : '' ?>>
                            Entreprise A-Z
                        </option>
                        <option value="statut" <?= $sort === 'statut' ? 'selected' : '' ?>>
                            Statut
                        </option>
                    </select>
                    
                    <form method="GET" class="search-form">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <input type="text" name="search" placeholder="Rechercher..." 
                               value="<?= htmlspecialchars($search) ?>" class="form-input">
                        <button type="submit" class="btn btn-secondary">
                            <span class="btn-icon">🔍</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Liste des candidatures -->
            <div class="admin-content">
                <?php if (empty($candidatures)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>Aucune candidature</h3>
                        <p class="text-muted">Aucune candidature ne correspond à vos critères.</p>
                        <button onclick="openModal('addModal')" class="btn btn-primary">
                            <span class="btn-icon">➕</span>
                            Ajouter la première candidature
                        </button>
                    </div>
                <?php else: ?>
                    <div class="data-grid">
                        <?php foreach ($candidatures as $candidature): ?>
                            <div class="data-card">
                                <div class="card-header">
                                    <h3>🏢 <?= htmlspecialchars($candidature['entreprise']) ?></h3>
                                    <div class="status-badge status-<?= $candidature['statut'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $candidature['statut'])) ?>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <p><strong>Poste :</strong> <?= htmlspecialchars($candidature['poste']) ?></p>
                                    <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($candidature['date_candidature'])) ?></p>
                                    <?php if ($candidature['localisation']): ?>
                                        <p><strong>Lieu :</strong> <?= htmlspecialchars($candidature['localisation']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($candidature['salaire']): ?>
                                        <p><strong>Salaire :</strong> <?= htmlspecialchars($candidature['salaire']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?= $candidature['id'] ?>">
                                        <select name="statut" onchange="this.form.submit()" class="form-select">
                                            <?php foreach ($statuts as $key => $statut): ?>
                                                <option value="<?= $key ?>" <?= $candidature['statut'] === $key ? 'selected' : '' ?>>
                                                    <?= $statut['label'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    
                                    <a href="?delete=<?= $candidature['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Supprimer cette candidature ?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="admin-modern.js"></script>
</body>
</html>