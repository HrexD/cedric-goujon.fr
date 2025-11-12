<?php
require 'config.php';

// Récupération des paramètres de filtrage
$langage_filter = $_GET['langage'] ?? '';
$difficulte_filter = $_GET['difficulte'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'difficulte_langage';

// Construction de la requête avec filtres
$where = ["statut = 'actif'"];
$params = [];

if ($langage_filter) {
    $where[] = "langage = ?";
    $params[] = $langage_filter;
}

if ($difficulte_filter) {
    $where[] = "difficulte = ?";
    $params[] = $difficulte_filter;
}

if ($search) {
    $where[] = "(titre LIKE ? OR description LIKE ? OR enonce LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where);

// Pagination
$limit = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

try {
    // Compter le total d'exercices
    $count_sql = "SELECT COUNT(*) FROM exercices WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();
    $total_pages = ceil($total / $limit);
    
    // Récupérer les exercices avec pagination
    // Définir l'ordre de tri
    $order_clause = '';
    switch ($sort) {
        case 'titre':
            $order_clause = 'ORDER BY titre ASC';
            break;
        case 'difficulte':
            $order_clause = 'ORDER BY FIELD(difficulte, "facile", "moyen", "difficile"), titre ASC';
            break;
        case 'langage':
            $order_clause = 'ORDER BY langage ASC, titre ASC';
            break;
        case 'points':
            $order_clause = 'ORDER BY points DESC, titre ASC';
            break;
        case 'temps':
            $order_clause = 'ORDER BY temps_estime ASC, titre ASC';
            break;
        case 'difficulte_langage':
        default:
            $order_clause = 'ORDER BY FIELD(difficulte, "facile", "moyen", "difficile"), langage ASC, titre ASC';
            break;
    }
    
    $sql = "SELECT id, titre, description, langage, difficulte, tags, temps_estime, points, date_creation 
            FROM exercices 
            WHERE $where_clause 
            $order_clause
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $exercices = $stmt->fetchAll();
    
    // Récupérer les langages disponibles pour le filtre
    $langages_stmt = $pdo->query("SELECT DISTINCT langage FROM exercices WHERE statut = 'actif' ORDER BY langage");
    $langages = $langages_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $exercices = [];
    $langages = [];
    $total = 0;
    $total_pages = 1;
}

// Fonction pour obtenir la couleur selon la difficulté
function getDifficulteColor($difficulte) {
    switch ($difficulte) {
        case 'facile': return '#10b981';
        case 'moyen': return '#f59e0b';
        case 'difficile': return '#ef4444';
        default: return '#6b7280';
    }
}

// Fonction pour obtenir l'icône du langage
function getLangageIcon($langage) {
    switch (strtolower($langage)) {
        case 'php': return '🐘';
        case 'javascript': return '🟨';
        case 'python': return '🐍';
        case 'java': return '☕';
        case 'c++': return '⚡';
        case 'c#': return '🔵';
        case 'css': return '🎨';
        case 'html': return '📝';
        case 'sql': return '🗄️';
        default: return '💻';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💪 Exercices de Programmation - Entraînement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="cv-modern.css">
    <link rel="stylesheet" href="cv-animations.css">
    <link rel="stylesheet" href="exercices-styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="cv-page"><?php require 'auth_helper.php'; ?>
<?= generateNavigation('exercices') ?>

    <div class="cv-container">
        <!-- Header Section -->
        <section class="cv-hero fade-in">
            <div class="hero-content">
                <h1>💪 Exercices de Programmation</h1>
                <p class="hero-subtitle">Entraînez-vous avec des défis de code classés par langage et difficulté</p>
            </div>
        </section>

        <!-- Filtres et Recherche -->
        <section class="cv-section">
                <div class="advanced-filters">
                    <!-- Recherche principale -->
                    <div class="search-container">
                        <div class="search-wrapper">
                            <input type="text" 
                                   id="searchInput" 
                                   class="search-input" 
                                   placeholder="🔍 Rechercher des exercices..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="clear-search-btn" id="clearSearchBtn" title="Effacer la recherche">
                                <span class="btn-icon">✕</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filtres par tags -->
                    <div class="filters-grid">
                        <!-- Langages -->
                        <div class="filter-group">
                            <h3 class="filter-group-title">
                                <span class="filter-icon">💻</span>
                                Langages
                            </h3>
                            <div class="filter-tags" id="langageTags">
                                <button class="filter-tag <?= !$langage_filter ? 'active' : '' ?>" data-type="langage" data-value="">
                                    Tous
                                </button>
                                <?php foreach ($langages as $lang): ?>
                                <button class="filter-tag <?= $langage_filter === $lang ? 'active' : '' ?>" data-type="langage" data-value="<?= htmlspecialchars($lang) ?>">
                                    <?= getLangageIcon($lang) ?> <?= htmlspecialchars($lang) ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Difficultés -->
                        <div class="filter-group">
                            <h3 class="filter-group-title">
                                <span class="filter-icon">🎯</span>
                                Difficulté
                            </h3>
                            <div class="filter-tags" id="difficulteTags">
                                <button class="filter-tag <?= !$difficulte_filter ? 'active' : '' ?>" data-type="difficulte" data-value="">
                                    Toutes
                                </button>
                                <button class="filter-tag <?= $difficulte_filter === 'facile' ? 'active' : '' ?>" data-type="difficulte" data-value="facile">
                                    🟢 Facile
                                </button>
                                <button class="filter-tag <?= $difficulte_filter === 'moyen' ? 'active' : '' ?>" data-type="difficulte" data-value="moyen">
                                    🟡 Moyen
                                </button>
                                <button class="filter-tag <?= $difficulte_filter === 'difficile' ? 'active' : '' ?>" data-type="difficulte" data-value="difficile">
                                    🔴 Difficile
                                </button>
                            </div>
                        </div>

                        <!-- Tri -->
                        <div class="filter-group">
                            <h3 class="filter-group-title">
                                <span class="filter-icon">🔄</span>
                                Trier par
                            </h3>
                            <div class="filter-tags" id="sortTags">
                                <button class="filter-tag <?= $sort === 'difficulte_langage' ? 'active' : '' ?>" data-type="sort" data-value="difficulte_langage">
                                    🎯 Difficulté → Langage
                                </button>
                                <button class="filter-tag <?= $sort === 'titre' ? 'active' : '' ?>" data-type="sort" data-value="titre">
                                    📝 Titre
                                </button>
                                <button class="filter-tag <?= $sort === 'difficulte' ? 'active' : '' ?>" data-type="sort" data-value="difficulte">
                                    🎯 Difficulté
                                </button>
                                <button class="filter-tag <?= $sort === 'langage' ? 'active' : '' ?>" data-type="sort" data-value="langage">
                                    💻 Langage
                                </button>
                                <button class="filter-tag <?= $sort === 'points' ? 'active' : '' ?>" data-type="sort" data-value="points">
                                    🏆 Points
                                </button>
                                <button class="filter-tag <?= $sort === 'temps' ? 'active' : '' ?>" data-type="sort" data-value="temps">
                                    ⏱️ Temps
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions et résultats -->
                    <div class="filter-actions">
                        <button class="btn-reset-filters" id="resetFilters">
                            <span class="btn-icon">🔄</span>
                            Réinitialiser les filtres
                        </button>
                        
                        <div class="filter-results">
                            <span class="results-count">
                                <span id="exercicesCount"><?= $total ?></span> exercice(s) trouvé(s)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ancien formulaire (caché, pour compatibilité) -->
                <form method="GET" class="filters-form" style="display: none;">
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr 2fr auto; align-items: end;">
                        <div class="form-group">
                            <label for="langage" class="form-label">
                                <span class="label-icon">💻</span>
                                Langage
                            </label>
                            <select id="langage" name="langage" class="form-select">
                                <option value="">Tous les langages</option>
                                <?php foreach ($langages as $lang): ?>
                                    <option value="<?= htmlspecialchars($lang) ?>" 
                                            <?= $langage_filter === $lang ? 'selected' : '' ?>>
                                        <?= getLangageIcon($lang) ?> <?= htmlspecialchars($lang) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="difficulte" class="form-label">
                                <span class="label-icon">📊</span>
                                Difficulté
                            </label>
                            <select id="difficulte" name="difficulte" class="form-select">
                                <option value="">Toutes difficultés</option>
                                <option value="facile" <?= $difficulte_filter === 'facile' ? 'selected' : '' ?>>🟢 Facile</option>
                                <option value="moyen" <?= $difficulte_filter === 'moyen' ? 'selected' : '' ?>>🟡 Moyen</option>
                                <option value="difficile" <?= $difficulte_filter === 'difficile' ? 'selected' : '' ?>>🔴 Difficile</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="search" class="form-label">
                                <span class="label-icon">🔍</span>
                                Rechercher
                            </label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   class="form-input"
                                   placeholder="Titre, description, énoncé..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="sort" class="form-label">
                                <span class="label-icon">📊</span>
                                Trier par
                            </label>
                            <select id="sort" name="sort" class="form-select">
                                <option value="difficulte_langage" <?= $sort === 'difficulte_langage' ? 'selected' : '' ?>>🎯 Difficulté → Langage</option>
                                <option value="titre" <?= $sort === 'titre' ? 'selected' : '' ?>>📝 Titre</option>
                                <option value="difficulte" <?= $sort === 'difficulte' ? 'selected' : '' ?>>🎯 Difficulté</option>
                                <option value="langage" <?= $sort === 'langage' ? 'selected' : '' ?>>💻 Langage</option>
                                <option value="points" <?= $sort === 'points' ? 'selected' : '' ?>>🏆 Points</option>
                                <option value="temps" <?= $sort === 'temps' ? 'selected' : '' ?>>⏱️ Temps estimé</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-icon">🔍</span>
                                Filtrer
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Statistiques -->
            <section class="cv-section">
                <div class="stats-grid">
                    <div class="info-card">
                        <div class="info-header">
                            <span class="info-icon">📚</span>
                            <strong>Total d'exercices</strong>
                        </div>
                        <div class="info-value"><?= $total ?> exercices</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-header">
                            <span class="info-icon">💻</span>
                            <strong>Langages</strong>
                        </div>
                        <div class="info-value"><?= count($langages) ?> langages</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-header">
                            <span class="info-icon">📄</span>
                            <strong>Page</strong>
                        </div>
                        <div class="info-value"><?= $page ?> / <?= $total_pages ?></div>
                    </div>
                </div>
            </section>

            <!-- Liste des exercices -->
            <section class="cv-section">
                <?php if (!empty($exercices)): ?>
                    <div class="sort-indicator" style="margin-bottom: var(--spacing-md); padding: var(--spacing-sm); background: var(--background-card); border-radius: var(--border-radius); font-size: 0.9rem; color: var(--text-muted); text-align: center;">
                        <span class="sort-icon">📊</span>
                        <?php 
                        switch($sort) {
                            case 'titre':
                                echo 'Triés par <strong>titre alphabétique</strong>';
                                break;
                            case 'difficulte':
                                echo 'Triés par <strong>difficulté</strong> (facile → difficile)';
                                break;
                            case 'langage':
                                echo 'Triés par <strong>langage</strong> alphabétique';
                                break;
                            case 'points':
                                echo 'Triés par <strong>points</strong> (plus élevés en premier)';
                                break;
                            case 'temps':
                                echo 'Triés par <strong>temps estimé</strong> (plus court en premier)';
                                break;
                            case 'difficulte_langage':
                            default:
                                echo 'Triés par <strong>difficulté</strong> puis <strong>langage</strong> (🟢 → 🟡 → 🔴)';
                                break;
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="exercices-grid">
                    <?php if (empty($exercices)): ?>
                        <div class="no-results">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                            <h3>Aucun exercice trouvé</h3>
                            <p>Essayez de modifier vos filtres de recherche.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($exercices as $exercice): ?>
                        <?php 
                        $tags = json_decode($exercice['tags'] ?? '[]', true) ?: [];
                        $difficulte_color = getDifficulteColor($exercice['difficulte']);
                        ?>
                        <div class="exercice-card" onclick="openExercice(<?= $exercice['id'] ?>)">
                            
                            <!-- En-tête -->
                            <div class="exercice-header">
                                <h3 class="exercice-title">
                                    <?= getLangageIcon($exercice['langage']) ?>
                                    <?= htmlspecialchars($exercice['titre']) ?>
                                </h3>
                                <div class="exercice-meta">
                                    <span class="badge badge-langage">
                                        <?= htmlspecialchars($exercice['langage']) ?>
                                    </span>
                                    <span class="badge badge-difficulte <?= $exercice['difficulte'] ?>">
                                        <?= ucfirst($exercice['difficulte']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Corps -->
                            <div class="exercice-body">
                                <p class="exercice-description">
                                    <?= htmlspecialchars($exercice['description']) ?>
                                </p>
                                
                                <!-- Tags -->
                                <?php if (!empty($tags)): ?>
                                    <div style="margin-bottom: var(--spacing-md);">
                                        <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                            <span style="background: var(--surface-light); color: var(--text-light); padding: 0.25rem 0.5rem; border-radius: var(--radius); font-size: 0.75rem; margin-right: 0.5rem;">
                                                #<?= htmlspecialchars($tag) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Footer -->
                            <div class="exercice-footer">
                                <div class="exercice-stats">
                                    <span>⏱️ ~<?= $exercice['temps_estime'] ?> min</span>
                                    <span>🏆 <?= $exercice['points'] ?> points</span>
                                </div>
                                <button class="btn btn-exercice" onclick="event.stopPropagation(); openExercice(<?= $exercice['id'] ?>)">
                                    Commencer
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: var(--spacing-xl);">
                    <?php
                    $current_params = $_GET;
                    
                    // Page précédente
                    if ($page > 1):
                        $current_params['page'] = $page - 1;
                        $prev_url = '?' . http_build_query($current_params);
                    ?>
                        <a href="<?= $prev_url ?>" class="btn btn-secondary">← Précédent</a>
                    <?php endif; ?>
                    
                    <!-- Numéros de page -->
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php
                        $current_params['page'] = $i;
                        $page_url = '?' . http_build_query($current_params);
                        ?>
                        <a href="<?= $page_url ?>" 
                           class="btn <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"
                           style="min-width: 2.5rem; text-align: center;">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <!-- Page suivante -->
                    <?php if ($page < $total_pages):
                        $current_params['page'] = $page + 1;
                        $next_url = '?' . http_build_query($current_params);
                    ?>
                        <a href="<?= $next_url ?>" class="btn btn-secondary">Suivant →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
                </div>
            </section>
        </div>

    <!-- Modal pour afficher l'exercice -->
    <div id="exerciceModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px; max-height: 95vh; overflow-y: auto; margin: 20px; background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
            <div class="modal-header" style="padding: var(--spacing-lg); border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h2 id="modalTitre" style="margin: 0; color: #111827; font-size: 1.25rem; font-weight: 700;">Exercice</h2>
                <button onclick="closeModal('exerciceModal')" class="modal-close" style="background: #f3f4f6; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.2rem; color: #6b7280;">✖️</button>
            </div>
            
            <div id="modalContent" class="modal-body" style="padding: 0;">
                <p style="padding: var(--spacing-xl); color: #6b7280; text-align: center;">Chargement de l'exercice...</p>
            </div>
        </div>
    </div>

    <script src="cv-interactions.js"></script>
    <script>
        // Fonctions modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        // Fermer modal en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeModal(e.target.id);
            }
        });

        // Fonction pour ouvrir un exercice
        function openExercice(id) {
            // Afficher un indicateur de chargement
            document.getElementById('modalContent').innerHTML = `
                <div style="text-align: center; padding: 3rem;">
                    <div class="loading-spinner"></div>
                    <p style="margin-top: 1rem; color: var(--text-light);">Chargement de l'exercice...</p>
                </div>
            `;
            openModal('exerciceModal');
            
            fetch(`exercice_detail.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalContent').innerHTML = html;
                    
                    // Initialiser l'éditeur après le chargement
                    const editor = document.getElementById('codeEditor');
                    if (editor) {
                        editor.addEventListener('input', function() {
                            this.style.height = 'auto';
                            this.style.height = Math.max(200, this.scrollHeight) + 'px';
                        });
                        
                        // Reset la visibilité de la solution
                        solutionVisible = false;
                    }
                    
                    // Animation d'apparition du contenu
                    document.getElementById('modalContent').style.opacity = '0';
                    document.getElementById('modalContent').style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        document.getElementById('modalContent').style.transition = 'all 0.3s ease';
                        document.getElementById('modalContent').style.opacity = '1';
                        document.getElementById('modalContent').style.transform = 'translateY(0)';
                    }, 100);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('modalContent').innerHTML = `
                        <div style="text-align: center; padding: 3rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
                            <h3>Erreur de chargement</h3>
                            <p>Impossible de charger l'exercice. Veuillez réessayer.</p>
                        </div>
                    `;
                });
        }
        
        // ===== FONCTIONS POUR LES EXERCICES =====
        let solutionVisible = false;
        
        function toggleSolution() {
            const section = document.getElementById('solutionSection');
            const btn = document.getElementById('solutionBtn');
            
            if (!section || !btn) {
                console.error('Éléments solution introuvables');
                return;
            }
            
            solutionVisible = !solutionVisible;
            
            if (solutionVisible) {
                section.style.display = 'block';
                btn.innerHTML = '🙈 Cacher la solution';
                btn.style.background = 'var(--accent-color)';
                btn.style.borderColor = 'var(--accent-color)';
                btn.style.color = 'white';
            } else {
                section.style.display = 'none';
                btn.innerHTML = '👁️ Voir la solution';
                btn.style.background = 'var(--surface-light)';
                btn.style.borderColor = 'var(--border-color)';
                btn.style.color = 'var(--text-dark)';
            }
        }
        
        function copyCode() {
            const editor = document.getElementById('codeEditor');
            if (editor) {
                editor.select();
                editor.setSelectionRange(0, 99999); // Pour mobile
                
                try {
                    document.execCommand('copy');
                    showNotification('Code copié dans le presse-papiers !', 'success');
                } catch (err) {
                    showNotification('Erreur lors de la copie', 'error');
                }
            }
        }
        
        function copySolution() {
            // Récupérer la solution depuis l'élément dans le DOM
            const solutionElement = document.querySelector('#solutionSection pre code');
            if (solutionElement) {
                const solutionCode = solutionElement.textContent;
                
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(solutionCode).then(() => {
                        showNotification('Solution copiée dans le presse-papiers !', 'success');
                    }).catch(() => {
                        showNotification('Erreur lors de la copie', 'error');
                    });
                } else {
                    // Fallback pour navigateurs plus anciens
                    const textArea = document.createElement('textarea');
                    textArea.value = solutionCode;
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        showNotification('Solution copiée dans le presse-papiers !', 'success');
                    } catch (err) {
                        showNotification('Erreur lors de la copie', 'error');
                    }
                    document.body.removeChild(textArea);
                }
            }
        }
        
        function resetCode() {
            const editor = document.getElementById('codeEditor');
            if (editor) {
                editor.value = '';
                const placeholder = "// Écrivez votre code ici...\n// Prenez le temps de réfléchir à la logique avant de commencer\n\nfunction solution() {\n    // Votre code ici\n}";
                editor.placeholder = placeholder;
                showNotification('Zone de travail réinitialisée', 'info');
            }
        }
        
        function validateSolution() {
            const editor = document.getElementById('codeEditor');
            if (!editor) return;
            
            const code = editor.value.trim();
            
            if (!code) {
                showNotification('Veuillez écrire votre solution avant de valider', 'warning');
                return;
            }
            
            // Simulation de validation
            showNotification('Bonne tentative ! Continuez à vous entraîner 💪', 'success');
        }
        
        function showNotification(message, type) {
            // Supprimer les notifications existantes
            const existingNotifications = document.querySelectorAll('.temp-notification');
            existingNotifications.forEach(n => n.remove());
            
            // Créer une nouvelle notification
            const notification = document.createElement('div');
            notification.className = 'temp-notification';
            
            const icons = {
                'success': '✅',
                'error': '❌', 
                'warning': '⚠️',
                'info': 'ℹ️'
            };
            
            const colors = {
                'success': '#16a34a',
                'error': '#dc2626',
                'warning': '#f59e0b',
                'info': '#1d4ed8'
            };
            
            notification.innerHTML = `${icons[type] || 'ℹ️'} ${message}`;
            
            // Style de la notification
            Object.assign(notification.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: '10000',
                minWidth: '300px',
                background: 'white',
                color: colors[type] || '#1d4ed8',
                padding: '12px 16px',
                borderRadius: '8px',
                border: `2px solid ${colors[type] || '#1d4ed8'}`,
                fontWeight: '600',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                animation: 'slideInRight 0.3s ease-out'
            });
            
            // Ajouter l'animation CSS si elle n'existe pas
            if (!document.querySelector('#notification-styles')) {
                const styles = document.createElement('style');
                styles.id = 'notification-styles';
                styles.textContent = `
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(styles);
            }
            
            document.body.appendChild(notification);
            
            // Suppression automatique
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slideInRight 0.3s ease-in reverse';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
        
        // Effet hover sur les cartes
        document.querySelectorAll('.exercice-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 16px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            });
        });

        // ===== SYSTÈME DE FILTRAGE DYNAMIQUE =====
        
        // État actuel des filtres
        let currentFilters = {
            langage: '<?= $langage_filter ?>',
            difficulte: '<?= $difficulte_filter ?>',
            search: '<?= $search ?>',
            sort: '<?= $sort ?>'
        };
        
        // Éléments DOM
        const searchInput = document.getElementById('searchInput');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const resetFiltersBtn = document.getElementById('resetFilters');
        
        // Initialisation des événements
        document.addEventListener('DOMContentLoaded', function() {
            // Recherche en temps réel
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        currentFilters.search = this.value;
                        updateFiltersAndRedirect();
                    }, 300);
                });
                
                // Toggle du bouton clear
                toggleClearSearchButton();
            }
            
            // Bouton clear search
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    currentFilters.search = '';
                    toggleClearSearchButton();
                    updateFiltersAndRedirect();
                });
            }
            
            // Reset tous les filtres
            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', function() {
                    resetAllFilters();
                });
            }
            
            // Tags de filtres
            document.querySelectorAll('.filter-tag').forEach(tag => {
                tag.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const type = this.dataset.type;
                    const value = this.dataset.value;
                    
                    // Mettre à jour les filtres
                    currentFilters[type] = value;
                    
                    // Redirection avec nouveaux paramètres
                    updateFiltersAndRedirect();
                });
            });
        });
        
        function toggleClearSearchButton() {
            if (clearSearchBtn && searchInput) {
                clearSearchBtn.style.display = searchInput.value ? 'flex' : 'none';
            }
        }
        
        function resetAllFilters() {
            currentFilters = {
                langage: '',
                difficulte: '',
                search: '',
                sort: 'difficulte_langage'
            };
            
            if (searchInput) searchInput.value = '';
            toggleClearSearchButton();
            updateFiltersAndRedirect();
        }
        
        function updateFiltersAndRedirect() {
            const params = new URLSearchParams();
            
            if (currentFilters.langage) params.set('langage', currentFilters.langage);
            if (currentFilters.difficulte) params.set('difficulte', currentFilters.difficulte);
            if (currentFilters.search) params.set('search', currentFilters.search);
            if (currentFilters.sort && currentFilters.sort !== 'difficulte_langage') {
                params.set('sort', currentFilters.sort);
            }
            
            const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.location.href = newURL;
        }
    </script>

</body>
</html>