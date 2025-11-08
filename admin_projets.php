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

    <div class="admin-layout">
        <!-- Hamburger Menu Button (Mobile) -->
        <button class="admin-hamburger" id="adminHamburger" aria-label="Toggle Menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
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
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['total'] ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--info);">🚀</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--info);"><?= $stats['en_cours'] ?></div>
                        <div class="stat-label">En cours</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success);">✅</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--success);"><?= $stats['actif'] ?></div>
                        <div class="stat-label">Actifs</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--text-muted);">📦</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--text-muted);"><?= $stats['archive'] ?></div>
                        <div class="stat-label">Archivés</div>
                    </div>
                </div>
            </div>
            
            <!-- Filtres et actions -->
            <div class="admin-toolbar">
                <div class="filter-group">
                    <span class="filter-label">Filtrer :</span>
                    <a href="?filter=all&search=<?= urlencode($search) ?>" 
                       class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                       Tous (<?= $stats['total'] ?>)
                    </a>
                    <a href="?filter=en_cours&search=<?= urlencode($search) ?>" 
                       class="filter-btn <?= $filter === 'en_cours' ? 'active' : '' ?>">
                       En cours (<?= $stats['en_cours'] ?>)
                    </a>
                    <a href="?filter=actif&search=<?= urlencode($search) ?>" 
                       class="filter-btn <?= $filter === 'actif' ? 'active' : '' ?>">
                       Actifs (<?= $stats['actif'] ?>)
                    </a>
                </div>
                
                <div class="toolbar-actions">
                    <form method="GET" class="search-form">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <input type="text" name="search" placeholder="Rechercher..." 
                               value="<?= htmlspecialchars($search) ?>" class="form-input">
                        <button type="submit" class="btn btn-secondary">
                            <span class="btn-icon">🔍</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Liste des projets -->
            <div class="admin-content">
                <?php if (empty($projets)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>Aucun projet</h3>
                        <p class="text-muted">Aucun projet ne correspond à vos critères.</p>
                        <button onclick="openModal('addModal')" class="btn btn-primary">
                            <span class="btn-icon">➕</span>
                            Ajouter le premier projet
                        </button>
                    </div>
                <?php else: ?>
                    <div class="data-grid">
                        <?php foreach ($projets as $projet): ?>
                            <div class="data-card">
                                <div class="card-header">
                                    <h3>🚀 <?= htmlspecialchars($projet['nom']) ?></h3>
                                    <div class="status-badge status-<?= $projet['statut'] ?>">
                                        <?= $statuts[$projet['statut']]['label'] ?>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <p class="project-description"><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
                                    
                                    <?php if ($projet['technologies']): ?>
                                        <div class="tech-tags">
                                            <?php foreach (explode(',', $projet['technologies']) as $tech): ?>
                                                <span class="tech-tag"><?= trim(htmlspecialchars($tech)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($projet['date_debut']): ?>
                                        <p class="project-dates">
                                            <strong>📅 Période :</strong> 
                                            <?= date('d/m/Y', strtotime($projet['date_debut'])) ?>
                                            <?php if ($projet['date_fin']): ?>
                                                → <?= date('d/m/Y', strtotime($projet['date_fin'])) ?>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="project-order">
                                        <strong>🔢 Ordre d'affichage :</strong> <?= $projet['ordre_affichage'] ?>
                                    </p>
                                    
                                    <?php if ($projet['url_demo'] || $projet['url_github']): ?>
                                        <div class="project-links">
                                            <?php if ($projet['url_demo']): ?>
                                                <a href="<?= htmlspecialchars($projet['url_demo']) ?>" target="_blank" 
                                                   class="btn btn-small btn-accent">
                                                    🌐 Voir la démo
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($projet['url_github']): ?>
                                                <a href="<?= htmlspecialchars($projet['url_github']) ?>" target="_blank" 
                                                   class="btn btn-small btn-dark">
                                                    📂 GitHub
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <button onclick="editProjet(<?= json_encode($projet, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)" 
                                            class="btn btn-small btn-secondary">
                                        ✏️ Modifier
                                    </button>
                                    
                                    <a href="?delete=<?= $projet['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Supprimer ce projet ?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
    
    <!-- Modal Ajouter projet -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Nouveau projet</h2>
                <button onclick="closeModal('addModal')" class="modal-close">✖️</button>
            </div>
            
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom" class="form-label">
                            <span class="label-icon">🚀</span>
                            Nom du projet *
                        </label>
                        <input type="text" 
                               id="nom" 
                               name="nom" 
                               class="form-input"
                               required 
                               placeholder="Mon super projet"
                               autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="ordre_affichage" class="form-label">
                            <span class="label-icon">🔢</span>
                            Ordre d'affichage
                        </label>
                        <input type="number" 
                               id="ordre_affichage" 
                               name="ordre_affichage" 
                               class="form-input"
                               value="0" 
                               min="0" 
                               max="999"
                               placeholder="0 = affiché en premier">
                        <small class="form-help">Plus le nombre est petit, plus le projet apparaît en premier</small>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="description" class="form-label">
                            <span class="label-icon">📝</span>
                            Description du projet *
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  class="form-textarea"
                                  rows="4" 
                                  required 
                                  placeholder="Décrivez votre projet, ses objectifs, fonctionnalités principales..."></textarea>
                        <small class="form-help">Description qui apparaîtra sur votre portfolio</small>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="technologies" class="form-label">
                            <span class="label-icon">💻</span>
                            Technologies utilisées
                        </label>
                        <input type="text" 
                               id="technologies" 
                               name="technologies" 
                               class="form-input"
                               placeholder="PHP, JavaScript, MySQL, React, Vue.js..."
                               list="tech-datalist">
                        <datalist id="tech-datalist">
                            <option value="PHP">
                            <option value="JavaScript">
                            <option value="TypeScript">
                            <option value="React">
                            <option value="Vue.js">
                            <option value="Angular">
                            <option value="Node.js">
                            <option value="MySQL">
                            <option value="PostgreSQL">
                            <option value="MongoDB">
                            <option value="Laravel">
                            <option value="Symfony">
                            <option value="Express.js">
                            <option value="HTML5">
                            <option value="CSS3">
                            <option value="SASS">
                            <option value="Bootstrap">
                            <option value="Tailwind CSS">
                            <option value="Docker">
                            <option value="Git">
                            <option value="AWS">
                            <option value="Firebase">
                        </datalist>
                        <small class="form-help">Séparez les technologies par des virgules</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="url_demo" class="form-label">
                            <span class="label-icon">🌐</span>
                            URL de démonstration
                        </label>
                        <input type="url" 
                               id="url_demo" 
                               name="url_demo" 
                               class="form-input"
                               placeholder="https://mon-projet.com"
                               pattern="https?://.+">
                        <small class="form-help">Lien vers la version en ligne de votre projet</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="url_github" class="form-label">
                            <span class="label-icon">📂</span>
                            URL GitHub / Dépôt
                        </label>
                        <input type="url" 
                               id="url_github" 
                               name="url_github" 
                               class="form-input"
                               placeholder="https://github.com/username/repository"
                               pattern="https?://.+">
                        <small class="form-help">Lien vers le code source (GitHub, GitLab, etc.)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="statut" class="form-label">
                            <span class="label-icon">📊</span>
                            Statut du projet
                        </label>
                        <select id="statut" name="statut" class="form-select">
                            <?php foreach ($statuts as $key => $statut): ?>
                                <option value="<?= $key ?>" 
                                        <?= $key === 'en_cours' ? 'selected' : '' ?>
                                        data-color="<?= $statut['color'] ?>">
                                    <?= $statut['label'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_debut" class="form-label">
                            <span class="label-icon">📅</span>
                            Date de début
                        </label>
                        <input type="date" 
                               id="date_debut" 
                               name="date_debut" 
                               class="form-input"
                               max="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_fin" class="form-label">
                            <span class="label-icon">🏁</span>
                            Date de fin
                        </label>
                        <input type="date" 
                               id="date_fin" 
                               name="date_fin" 
                               class="form-input">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('addModal')" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-icon">✅</span>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Modifier projet -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Modifier projet</h2>
                <button onclick="closeModal('editModal')" class="modal-close">✖️</button>
            </div>
            
            <form method="POST" id="editForm" class="modal-form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div id="editFormContent"></div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('editModal')" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-icon">✅</span>
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="admin-modern.js"></script>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Focus sur le premier champ du formulaire
            setTimeout(() => {
                const firstInput = document.querySelector(`#${modalId} .form-input`);
                if (firstInput) firstInput.focus();
            }, 100);
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Réinitialiser le formulaire
            const form = document.querySelector(`#${modalId} form`);
            if (form) {
                form.reset();
                // Remettre la valeur par défaut pour l'ordre
                const ordreInput = form.querySelector('input[name="ordre_affichage"]');
                if (ordreInput && modalId === 'addModal') {
                    ordreInput.value = '0';
                }
                // Nettoyer les classes de validation
                form.querySelectorAll('.form-group').forEach(group => {
                    group.classList.remove('success', 'error');
                });
            }
        }
        
        // Validation en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            setupProjectValidation();
            setupDateValidation();
            setupUrlValidation();
            setupTechAutoComplete();
        });
        
        function setupProjectValidation() {
            const forms = document.querySelectorAll('#addModal form, #editModal form');
            forms.forEach(form => {
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    field.addEventListener('blur', validateProjectField);
                    field.addEventListener('input', clearProjectErrors);
                });
                
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!validateProjectField({ target: field })) {
                            isValid = false;
                        }
                    });
                    
                    // Validation des URLs
                    const urlFields = form.querySelectorAll('input[type="url"]');
                    urlFields.forEach(field => {
                        if (field.value && !validateUrl({ target: field })) {
                            isValid = false;
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        showProjectFormError('Veuillez corriger les erreurs avant de soumettre.');
                    }
                });
            });
        }
        
        function validateProjectField(e) {
            const field = e.target;
            const formGroup = field.closest('.form-group');
            const value = field.value.trim();
            
            formGroup.classList.remove('success', 'error');
            removeProjectFieldError(formGroup);
            
            if (field.hasAttribute('required') && !value) {
                showProjectFieldError(formGroup, 'Ce champ est obligatoire');
                return false;
            }
            
            if (value) {
                switch (field.name) {
                    case 'nom':
                        if (value.length < 2) {
                            showProjectFieldError(formGroup, 'Le nom doit contenir au moins 2 caractères');
                            return false;
                        }
                        break;
                    case 'description':
                        if (value.length < 10) {
                            showProjectFieldError(formGroup, 'La description doit contenir au moins 10 caractères');
                            return false;
                        }
                        break;
                    case 'ordre_affichage':
                        if (parseInt(value) < 0 || parseInt(value) > 999) {
                            showProjectFieldError(formGroup, 'L\'ordre doit être entre 0 et 999');
                            return false;
                        }
                        break;
                }
                
                formGroup.classList.add('success');
            }
            
            return true;
        }
        
        function setupDateValidation() {
            document.addEventListener('change', function(e) {
                if (e.target.name === 'date_debut' || e.target.name === 'date_fin') {
                    const form = e.target.closest('form');
                    const dateDebut = form.querySelector('[name="date_debut"]').value;
                    const dateFin = form.querySelector('[name="date_fin"]').value;
                    
                    if (dateDebut && dateFin && new Date(dateDebut) > new Date(dateFin)) {
                        const dateFinGroup = form.querySelector('[name="date_fin"]').closest('.form-group');
                        showProjectFieldError(dateFinGroup, 'La date de fin doit être postérieure à la date de début');
                    }
                }
            });
        }
        
        function setupUrlValidation() {
            document.addEventListener('blur', function(e) {
                if (e.target.type === 'url' && e.target.value) {
                    validateUrl(e);
                }
            });
        }
        
        function validateUrl(e) {
            const field = e.target;
            const formGroup = field.closest('.form-group');
            const url = field.value.trim();
            
            if (url && !isValidUrl(url)) {
                showProjectFieldError(formGroup, 'Veuillez saisir une URL valide (doit commencer par http:// ou https://)');
                return false;
            }
            
            if (url) {
                formGroup.classList.add('success');
            }
            return true;
        }
        
        function isValidUrl(string) {
            try {
                const url = new URL(string);
                return url.protocol === 'http:' || url.protocol === 'https:';
            } catch (_) {
                return false;
            }
        }
        
        function setupTechAutoComplete() {
            document.addEventListener('input', function(e) {
                if (e.target.name === 'technologies') {
                    const value = e.target.value;
                    const lastCommaIndex = value.lastIndexOf(',');
                    const currentTech = value.substring(lastCommaIndex + 1).trim().toLowerCase();
                    
                    if (currentTech.length >= 2) {
                        const suggestions = [
                            'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular',
                            'Node.js', 'PHP', 'Laravel', 'Symfony', 'MySQL',
                            'PostgreSQL', 'MongoDB', 'HTML5', 'CSS3', 'SASS',
                            'Bootstrap', 'Tailwind CSS', 'Docker', 'Git', 'AWS'
                        ];
                        
                        const match = suggestions.find(tech => 
                            tech.toLowerCase().startsWith(currentTech) && 
                            tech.toLowerCase() !== currentTech
                        );
                        
                        if (match) {
                            const beforeLastComma = lastCommaIndex >= 0 ? value.substring(0, lastCommaIndex + 1) + ' ' : '';
                            e.target.value = beforeLastComma + match;
                            e.target.setSelectionRange(
                                beforeLastComma.length + currentTech.length, 
                                e.target.value.length
                            );
                        }
                    }
                }
            });
        }
        
        function showProjectFieldError(formGroup, message) {
            formGroup.classList.add('error');
            
            let errorDiv = formGroup.querySelector('.form-error');
            if (!errorDiv) {
                errorDiv = document.createElement('span');
                errorDiv.className = 'form-error';
                formGroup.appendChild(errorDiv);
            }
            errorDiv.textContent = message;
        }
        
        function removeProjectFieldError(formGroup) {
            const errorDiv = formGroup.querySelector('.form-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        
        function clearProjectErrors(e) {
            const formGroup = e.target.closest('.form-group');
            formGroup.classList.remove('error');
            removeProjectFieldError(formGroup);
        }
        
        function showProjectFormError(message) {
            let notification = document.querySelector('.notification-form-error');
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'notification notification-error notification-form-error';
                notification.innerHTML = `<span class="notification-icon">❌</span><span></span>`;
                
                const activeModal = document.querySelector('.modal[style*="block"] form');
                if (activeModal) {
                    activeModal.insertBefore(notification, activeModal.firstChild);
                }
            }
            
            notification.querySelector('span:last-child').textContent = message;
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        function editProjet(projet) {
            try {
                // Vérifier que les données du projet sont valides
                if (!projet || !projet.id) {
                    console.error('Données du projet invalides:', projet);
                    alert('Erreur: Données du projet invalides');
                    return;
                }
                
                document.getElementById('edit_id').value = projet.id;
            
            const statuts = <?= json_encode($statuts) ?>;
            let statutsOptions = '';
            for (const [key, statut] of Object.entries(statuts)) {
                statutsOptions += `<option value="${key}" ${projet.statut === key ? 'selected' : ''}>${statut.label}</option>`;
            }
            
            const formContent = `
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_nom" class="form-label">
                            <span class="label-icon">🚀</span>
                            Nom du projet *
                        </label>
                        <input type="text" 
                               id="edit_nom" 
                               name="nom" 
                               class="form-input"
                               value="${projet.nom}" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_ordre_affichage" class="form-label">
                            <span class="label-icon">🔢</span>
                            Ordre d'affichage
                        </label>
                        <input type="number" 
                               id="edit_ordre_affichage" 
                               name="ordre_affichage" 
                               class="form-input"
                               value="${projet.ordre_affichage}" 
                               min="0" 
                               max="999">
                        <small class="form-help">Plus le nombre est petit, plus le projet apparaît en premier</small>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="edit_description" class="form-label">
                            <span class="label-icon">📝</span>
                            Description du projet *
                        </label>
                        <textarea id="edit_description" 
                                  name="description" 
                                  class="form-textarea"
                                  rows="4" 
                                  required>${projet.description}</textarea>
                        <small class="form-help">Description qui apparaîtra sur votre portfolio</small>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="edit_technologies" class="form-label">
                            <span class="label-icon">💻</span>
                            Technologies utilisées
                        </label>
                        <input type="text" 
                               id="edit_technologies" 
                               name="technologies" 
                               class="form-input"
                               value="${projet.technologies || ''}"
                               list="tech-datalist-edit">
                        <datalist id="tech-datalist-edit">
                            <option value="PHP">
                            <option value="JavaScript">
                            <option value="TypeScript">
                            <option value="React">
                            <option value="Vue.js">
                            <option value="Angular">
                            <option value="Node.js">
                            <option value="MySQL">
                            <option value="PostgreSQL">
                            <option value="MongoDB">
                            <option value="Laravel">
                            <option value="Symfony">
                            <option value="Express.js">
                        </datalist>
                        <small class="form-help">Séparez les technologies par des virgules</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_url_demo" class="form-label">
                            <span class="label-icon">🌐</span>
                            URL de démonstration
                        </label>
                        <input type="url" 
                               id="edit_url_demo" 
                               name="url_demo" 
                               class="form-input"
                               value="${projet.url_demo || ''}"
                               pattern="https?://.+">
                        <small class="form-help">Lien vers la version en ligne de votre projet</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_url_github" class="form-label">
                            <span class="label-icon">📂</span>
                            URL GitHub / Dépôt
                        </label>
                        <input type="url" 
                               id="edit_url_github" 
                               name="url_github" 
                               class="form-input"
                               value="${projet.url_github || ''}"
                               pattern="https?://.+">
                        <small class="form-help">Lien vers le code source (GitHub, GitLab, etc.)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_statut" class="form-label">
                            <span class="label-icon">📊</span>
                            Statut du projet
                        </label>
                        <select id="edit_statut" 
                                name="statut" 
                                class="form-select">${statutsOptions}</select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_date_debut" class="form-label">
                            <span class="label-icon">📅</span>
                            Date de début
                        </label>
                        <input type="date" 
                               id="edit_date_debut" 
                               name="date_debut" 
                               class="form-input"
                               value="${projet.date_debut || ''}">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_date_fin" class="form-label">
                            <span class="label-icon">🏁</span>
                            Date de fin
                        </label>
                        <input type="date" 
                               id="edit_date_fin" 
                               name="date_fin" 
                               class="form-input"
                               value="${projet.date_fin || ''}">
                    </div>
                </div>
            `;
            
            document.getElementById('editFormContent').innerHTML = formContent;
            
            // Réactiver la validation pour le formulaire d'édition
            setupProjectValidation();
            setupDateValidation();
            setupUrlValidation();
            setupTechAutoComplete();
            
            openModal('editModal');
            } catch (error) {
                console.error('Erreur dans editProjet:', error);
                alert('Erreur lors de l\'ouverture du formulaire d\'édition: ' + error.message);
            }
        }
        
        // Fermer modal en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        }
        
        // Fermer modal avec échap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.style.display === 'block') {
                        closeModal(modal.id);
                    }
                });
            }
        });
    </script>
        </main>
    </div>
</body>
</html>