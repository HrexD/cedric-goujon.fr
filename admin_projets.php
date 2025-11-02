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
            $nom = sanitize_input($_POST['nom'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $technologies = sanitize_input($_POST['technologies'] ?? '');
            $url_demo = sanitize_input($_POST['url_demo'] ?? '');
            $url_github = sanitize_input($_POST['url_github'] ?? '');
            $statut = $_POST['statut'] ?? 'actif';
            $date_debut = $_POST['date_debut'] ?? null;
            $date_fin = $_POST['date_fin'] ?? null;
            $ordre_affichage = (int)($_POST['ordre_affichage'] ?? 0);
            
            if ($nom && $description) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO projets (nom, description, technologies, url_demo, url_github, statut, date_debut, date_fin, ordre_affichage) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$nom, $description, $technologies, $url_demo, $url_github, $statut, $date_debut, $date_fin, $ordre_affichage]);
                    $success = "Projet ajouté avec succès !";
                } catch (PDOException $e) {
                    $error = "Erreur lors de l'ajout : " . $e->getMessage();
                }
            } else {
                $error = "Le nom et la description sont obligatoires.";
            }
            break;
            
        case 'edit':
            $id = (int)$_POST['id'];
            $nom = sanitize_input($_POST['nom'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $technologies = sanitize_input($_POST['technologies'] ?? '');
            $url_demo = sanitize_input($_POST['url_demo'] ?? '');
            $url_github = sanitize_input($_POST['url_github'] ?? '');
            $statut = $_POST['statut'] ?? 'actif';
            $date_debut = $_POST['date_debut'] ?? null;
            $date_fin = $_POST['date_fin'] ?? null;
            $ordre_affichage = (int)($_POST['ordre_affichage'] ?? 0);
            
            if ($nom && $description) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE projets 
                        SET nom = ?, description = ?, technologies = ?, url_demo = ?, url_github = ?, 
                            statut = ?, date_debut = ?, date_fin = ?, ordre_affichage = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$nom, $description, $technologies, $url_demo, $url_github, $statut, $date_debut, $date_fin, $ordre_affichage, $id]);
                    $success = "Projet mis à jour avec succès !";
                } catch (PDOException $e) {
                    $error = "Erreur lors de la modification : " . $e->getMessage();
                }
            } else {
                $error = "Le nom et la description sont obligatoires.";
            }
            break;
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM projets WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Projet supprimé !";
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupération des projets
try {
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT * FROM projets WHERE 1=1";
    $params = [];
    
    // Filtres par statut
    if ($filter !== 'all') {
        $sql .= " AND statut = ?";
        $params[] = $filter;
    }
    
    // Recherche
    if (!empty($search)) {
        $sql .= " AND (nom LIKE ? OR description LIKE ? OR technologies LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }
    
    $sql .= " ORDER BY ordre_affichage ASC, date_debut DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projets = $stmt->fetchAll();
    
    // Statistiques
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn(),
        'actif' => $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'actif'")->fetchColumn(),
        'archive' => $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'archive'")->fetchColumn(),
        'en_cours' => $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'en_cours'")->fetchColumn()
    ];
    
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
    $projets = [];
    $stats = ['total' => 0, 'actif' => 0, 'archive' => 0, 'en_cours' => 0];
}

// Statuts disponibles
$statuts = [
    'en_cours' => ['label' => 'En cours', 'color' => '#17a2b8'],
    'actif' => ['label' => 'Actif', 'color' => '#28a745'],
    'archive' => ['label' => 'Archivé', 'color' => '#6c757d'],
    'pause' => ['label' => 'En pause', 'color' => '#ffc107']
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🚀 Administration - Projets</title>
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
                    <li><a href="admin_candidatures.php">💼 Candidatures</a></li>
                    <li><a href="admin_messages.php">📧 Messages</a></li>
                    <li><a href="admin_projets.php" class="active">🚀 Projets</a></li>
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
                <h1>🚀 Gestion des Projets</h1>
                <p class="admin-subtitle">Administration du portfolio de projets</p>
                <div class="header-actions">
                    <button onclick="openModal('addModal')" class="btn btn-primary">
                        <span class="btn-icon">➕</span>
                        Nouveau projet
                    </button>
                </div>
            </div>
    
    <!-- Messages -->
    <?php if ($success): ?>
        <div class="notification notification-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="notification notification-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?= $stats['total'] ?></span>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #17a2b8;"><?= $stats['en_cours'] ?></span>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #28a745;"><?= $stats['actif'] ?></span>
            <div class="stat-label">Actifs</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #6c757d;"><?= $stats['archive'] ?></span>
            <div class="stat-label">Archivés</div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters-candidatures">
        <span><strong>Filtrer :</strong></span>
        <a href="?filter=all&search=<?= urlencode($search) ?>" 
           class="btn-small" style="background: <?= $filter === 'all' ? 'var(--accent)' : 'var(--text-muted)' ?>">
           Tous (<?= $stats['total'] ?>)
        </a>
        <a href="?filter=en_cours&search=<?= urlencode($search) ?>" 
           class="btn-small" style="background: <?= $filter === 'en_cours' ? '#17a2b8' : 'var(--text-muted)' ?>">
           En cours (<?= $stats['en_cours'] ?>)
        </a>
        <a href="?filter=actif&search=<?= urlencode($search) ?>" 
           class="btn-small" style="background: <?= $filter === 'actif' ? '#28a745' : 'var(--text-muted)' ?>">
           Actifs (<?= $stats['actif'] ?>)
        </a>
        
        <div style="margin-left: auto; display: flex; gap: 0.5rem;">
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <input type="text" name="search" placeholder="Rechercher..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                <button type="submit" class="btn-small btn-read">🔍</button>
            </form>
        </div>
    </div>
    
    <!-- Liste des projets -->
    <?php if (empty($projets)): ?>
        <div class="projet-card">
            <div class="projet-content" style="text-align: center; padding: 3rem;">
                <h3>📭 Aucun projet</h3>
                <p style="color: var(--text-muted);">Aucun projet ne correspond à vos critères.</p>
                <button onclick="openModal('addModal')" class="cta" style="margin-top: 1rem;">
                    + Ajouter le premier projet
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($projets as $projet): ?>
            <div class="projet-card">
                <div class="projet-header">
                    <div>
                        <h3 style="margin: 0; color: var(--primary);">
                            🚀 <?= htmlspecialchars($projet['nom']) ?>
                        </h3>
                        <div style="color: var(--text-muted); margin-top: 0.3rem; font-size: 0.9em;">
                            Ordre d'affichage : <?= $projet['ordre_affichage'] ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="status-badge" style="background-color: <?= $statuts[$projet['statut']]['color'] ?>">
                            <?= $statuts[$projet['statut']]['label'] ?>
                        </span>
                        <?php if ($projet['date_debut']): ?>
                            <div style="font-size: 0.8em; color: var(--text-muted); margin-top: 0.5rem;">
                                📅 <?= date('d/m/Y', strtotime($projet['date_debut'])) ?>
                                <?php if ($projet['date_fin']): ?>
                                    → <?= date('d/m/Y', strtotime($projet['date_fin'])) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="projet-content">
                    <div style="margin-bottom: 1rem; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($projet['description'])) ?>
                    </div>
                    
                    <?php if ($projet['technologies']): ?>
                        <div class="tech-tags">
                            <?php foreach (explode(',', $projet['technologies']) as $tech): ?>
                                <span class="tech-tag"><?= trim(htmlspecialchars($tech)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="projet-links">
                        <?php if ($projet['url_demo']): ?>
                            <a href="<?= htmlspecialchars($projet['url_demo']) ?>" target="_blank" 
                               class="btn-small" style="background: var(--accent);">
                                🌐 Voir la démo
                            </a>
                        <?php endif; ?>
                        <?php if ($projet['url_github']): ?>
                            <a href="<?= htmlspecialchars($projet['url_github']) ?>" target="_blank" 
                               class="btn-small" style="background: #333;">
                                📂 GitHub
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quick-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $projet['id'] ?>">
                            <label style="font-size: 0.8em; margin-right: 0.5rem;">Ordre :</label>
                            <input type="number" name="ordre_affichage" value="<?= $projet['ordre_affichage'] ?>" 
                                   class="ordre-input" onchange="this.form.submit()">
                            <input type="hidden" name="nom" value="<?= htmlspecialchars($projet['nom']) ?>">
                            <input type="hidden" name="description" value="<?= htmlspecialchars($projet['description']) ?>">
                            <input type="hidden" name="technologies" value="<?= htmlspecialchars($projet['technologies']) ?>">
                            <input type="hidden" name="url_demo" value="<?= htmlspecialchars($projet['url_demo']) ?>">
                            <input type="hidden" name="url_github" value="<?= htmlspecialchars($projet['url_github']) ?>">
                            <input type="hidden" name="statut" value="<?= $projet['statut'] ?>">
                            <input type="hidden" name="date_debut" value="<?= $projet['date_debut'] ?>">
                            <input type="hidden" name="date_fin" value="<?= $projet['date_fin'] ?>">
                        </form>
                        
                        <button onclick="editProjet(<?= htmlspecialchars(json_encode($projet)) ?>)" 
                                class="btn-small" style="background: var(--secondary);">
                            ✏️ Modifier
                        </button>
                        
                        <a href="?delete=<?= $projet['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" 
                           class="btn-small" style="background: #dc3545;" 
                           onclick="return confirm('Supprimer ce projet ?')">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Modal Ajouter projet -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>➕ Nouveau projet</h2>
                <button onclick="closeModal('addModal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">✖️</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>🚀 Nom du projet *</label>
                    <input type="text" name="nom" required placeholder="Mon super projet">
                </div>
                
                <div class="form-group">
                    <label>📝 Description *</label>
                    <textarea name="description" rows="4" required placeholder="Description détaillée du projet..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>💻 Technologies utilisées</label>
                    <input type="text" name="technologies" placeholder="PHP, JavaScript, MySQL, React... (séparés par des virgules)">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>🌐 URL de démonstration</label>
                        <input type="url" name="url_demo" placeholder="https://mon-projet.com">
                    </div>
                    <div class="form-group">
                        <label>📂 URL GitHub</label>
                        <input type="url" name="url_github" placeholder="https://github.com/user/repo">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>📊 Statut</label>
                        <select name="statut">
                            <?php foreach ($statuts as $key => $statut): ?>
                                <option value="<?= $key ?>" <?= $key === 'en_cours' ? 'selected' : '' ?>>
                                    <?= $statut['label'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>📅 Date début</label>
                        <input type="date" name="date_debut">
                    </div>
                    <div class="form-group">
                        <label>🏁 Date fin</label>
                        <input type="date" name="date_fin">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>🔢 Ordre d'affichage</label>
                    <input type="number" name="ordre_affichage" value="0" min="0" 
                           placeholder="0 = affiché en premier">
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('addModal')" class="btn-small" style="background: var(--text-muted);">
                        Annuler
                    </button>
                    <button type="submit" class="cta">✅ Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Modifier projet -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>✏️ Modifier projet</h2>
                <button onclick="closeModal('editModal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">✖️</button>
            </div>
            
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div id="editFormContent"></div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('editModal')" class="btn-small" style="background: var(--text-muted);">
                        Annuler
                    </button>
                    <button type="submit" class="cta">✅ Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function editProjet(projet) {
            document.getElementById('edit_id').value = projet.id;
            
            const statuts = <?= json_encode($statuts) ?>;
            let statutsOptions = '';
            for (const [key, statut] of Object.entries(statuts)) {
                statutsOptions += `<option value="${key}" ${projet.statut === key ? 'selected' : ''}>${statut.label}</option>`;
            }
            
            const formContent = `
                <div class="form-group">
                    <label>🚀 Nom du projet *</label>
                    <input type="text" name="nom" value="${projet.nom}" required>
                </div>
                <div class="form-group">
                    <label>📝 Description *</label>
                    <textarea name="description" rows="4" required>${projet.description}</textarea>
                </div>
                <div class="form-group">
                    <label>💻 Technologies utilisées</label>
                    <input type="text" name="technologies" value="${projet.technologies || ''}">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>🌐 URL de démonstration</label>
                        <input type="url" name="url_demo" value="${projet.url_demo || ''}">
                    </div>
                    <div class="form-group">
                        <label>📂 URL GitHub</label>
                        <input type="url" name="url_github" value="${projet.url_github || ''}">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>📊 Statut</label>
                        <select name="statut">${statutsOptions}</select>
                    </div>
                    <div class="form-group">
                        <label>📅 Date début</label>
                        <input type="date" name="date_debut" value="${projet.date_debut || ''}">
                    </div>
                    <div class="form-group">
                        <label>🏁 Date fin</label>
                        <input type="date" name="date_fin" value="${projet.date_fin || ''}">
                    </div>
                </div>
                <div class="form-group">
                    <label>🔢 Ordre d'affichage</label>
                    <input type="number" name="ordre_affichage" value="${projet.ordre_affichage}" min="0">
                </div>
            `;
            
            document.getElementById('editFormContent').innerHTML = formContent;
            openModal('editModal');
        }
        
        // Fermer modal en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
        </main>
    </div>

    <script src="admin-modern.js"></script>
</body>
</html>