<?php
require 'config.php';

// Récupération des paramètres
$langage_filter = $_GET['langage'] ?? '';
$niveau_filter = $_GET['niveau'] ?? '';
$search = $_GET['search'] ?? '';

// Construction de la requête avec filtres
$where = ["statut = 'actif'"];
$params = [];

if ($langage_filter) {
    $where[] = "langage = ?";
    $params[] = $langage_filter;
}

if ($niveau_filter) {
    $where[] = "niveau = ?";
    $params[] = $niveau_filter;
}

if ($search) {
    $where[] = "(titre LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(' AND ', $where);

try {
    // Récupérer les cours
    $sql = "SELECT id, titre, description, langage, niveau, duree_estimee, objectifs, date_creation 
            FROM cours 
            WHERE $where_clause 
            ORDER BY langage ASC, ordre_affichage ASC, titre ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cours = $stmt->fetchAll();
    
    // Récupérer les langages disponibles
    $langages_stmt = $pdo->query("SELECT DISTINCT langage FROM cours WHERE statut = 'actif' ORDER BY langage");
    $langages = $langages_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Statistiques
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN niveau = 'debutant' THEN 1 END) as debutant,
            COUNT(CASE WHEN niveau = 'intermediaire' THEN 1 END) as intermediaire,
            COUNT(CASE WHEN niveau = 'avance' THEN 1 END) as avance
        FROM cours WHERE statut = 'actif'
    ");
    $stats = $stats_stmt->fetch();
    
} catch (PDOException $e) {
    $cours = [];
    $langages = [];
    $stats = ['total' => 0, 'debutant' => 0, 'intermediaire' => 0, 'avance' => 0];
}

// Fonction pour obtenir la couleur selon le niveau
function getNiveauColor($niveau) {
    switch ($niveau) {
        case 'debutant': return '#10b981';
        case 'intermediaire': return '#f59e0b';
        case 'avance': return '#ef4444';
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

// Fonction pour obtenir l'icône du niveau
function getNiveauIcon($niveau) {
    switch ($niveau) {
        case 'debutant': return '🟢';
        case 'intermediaire': return '🟡';
        case 'avance': return '🔴';
        default: return '⚫';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Cours de Programmation - Apprendre les bases</title>
    <meta name="description" content="Apprenez les bases des langages de programmation avec nos cours structurés. Préparez-vous pour les exercices pratiques.">
    
    <!-- Styles -->
    <link rel="stylesheet" href="cv-modern.css">
    <link rel="stylesheet" href="cours-styles.css">
    
    <!-- Prism.js pour la coloration syntaxique -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon.png">
</head>

<body class="cv-page"><?php require 'auth_helper.php'; ?>
    <!-- Navigation -->
    <?= generateNavigation('cours') ?>

    <div class="cv-container">
        <!-- En-tête -->
        <section class="cv-hero fade-in">
            <div class="hero-content">
                <h1>📚 Cours de Programmation</h1>
                <p class="hero-subtitle">Apprenez les bases des langages de programmation avec nos cours structurés et préparez-vous pour les exercices pratiques.</p>
            </div>
        </section>

            <!-- Filtres et Recherche Avancés -->
            <section class="filters-section cv-section">
                <h2 class="section-title">
                    <span class="section-icon">🔍</span>
                    Filtrer les cours
                </h2>
                
                <!-- Filtres principaux -->
                <div class="filters-container">
                    <!-- Barre de recherche -->
                    <div class="search-container">
                        <div class="search-input-wrapper">
                            <span class="search-icon">🔍</span>
                            <input type="text" 
                                   id="searchInput" 
                                   class="search-input"
                                   placeholder="Rechercher par titre, description ou langage..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="clear-search" id="clearSearch" style="display: none;">✕</button>
                        </div>
                    </div>

                    <!-- Filtres par tags -->
                    <div class="filter-tags-section">
                        <!-- Langages -->
                        <div class="filter-group">
                            <h3 class="filter-group-title">
                                <span class="filter-icon">💻</span>
                                Langages
                                <span class="filter-count" id="langageCount"></span>
                            </h3>
                            <div class="filter-tags" id="langageTags">
                                <button class="filter-tag active" data-type="langage" data-value="">
                                    Tous <span class="tag-count"></span>
                                </button>
                                <?php foreach ($langages as $lang): ?>
                                    <button class="filter-tag" data-type="langage" data-value="<?= htmlspecialchars($lang) ?>">
                                        <?= getLangageIcon($lang) ?> <?= htmlspecialchars($lang) ?>
                                        <span class="tag-count"></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Niveaux -->
                        <div class="filter-group">
                            <h3 class="filter-group-title">
                                <span class="filter-icon">📊</span>
                                Niveaux
                                <span class="filter-count" id="niveauCount"></span>
                            </h3>
                            <div class="filter-tags" id="niveauTags">
                                <button class="filter-tag active" data-type="niveau" data-value="">
                                    Tous <span class="tag-count"></span>
                                </button>
                                <button class="filter-tag" data-type="niveau" data-value="debutant">
                                    🟢 Débutant <span class="tag-count"></span>
                                </button>
                                <button class="filter-tag" data-type="niveau" data-value="intermediaire">
                                    🟡 Intermédiaire <span class="tag-count"></span>
                                </button>
                                <button class="filter-tag" data-type="niveau" data-value="avance">
                                    🔴 Avancé <span class="tag-count"></span>
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
                                <button class="filter-tag active" data-type="sort" data-value="langage">
                                    📝 Langage
                                </button>
                                <button class="filter-tag" data-type="sort" data-value="niveau">
                                    📊 Niveau
                                </button>
                                <button class="filter-tag" data-type="sort" data-value="titre">
                                    📚 Titre
                                </button>
                                <button class="filter-tag" data-type="sort" data-value="duree">
                                    ⏱️ Durée
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
                                <span id="coursCount"><?= count($cours) ?></span> cours trouvé(s)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ancien formulaire (caché, pour compatibilité) -->
                <form method="GET" class="filters-form" style="display: none;">
                    <div class="form-grid">
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
                            <label for="niveau" class="form-label">
                                <span class="label-icon">📊</span>
                                Niveau
                            </label>
                            <select id="niveau" name="niveau" class="form-select">
                                <option value="">Tous les niveaux</option>
                                <option value="debutant" <?= $niveau_filter === 'debutant' ? 'selected' : '' ?>>🟢 Débutant</option>
                                <option value="intermediaire" <?= $niveau_filter === 'intermediaire' ? 'selected' : '' ?>>🟡 Intermédiaire</option>
                                <option value="avance" <?= $niveau_filter === 'avance' ? 'selected' : '' ?>>🔴 Avancé</option>
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
                                   placeholder="Titre, description..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-filter">
                                <span class="btn-icon">🔍</span>
                                Filtrer
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Statistiques -->
            <section class="stats-section cv-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['total'] ?></span>
                        <span class="stat-label">📚 Cours disponibles</span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['debutant'] ?></span>
                        <span class="stat-label">🟢 Débutant</span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['intermediaire'] ?></span>
                        <span class="stat-label">🟡 Intermédiaire</span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['avance'] ?></span>
                        <span class="stat-label">🔴 Avancé</span>
                    </div>
                </div>
            </section>

            <!-- Liste des cours -->
            <section class="cv-section">
                <div class="cours-grid">
                    <?php if (empty($cours)): ?>
                        <div class="no-results">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                            <h3>Aucun cours trouvé</h3>
                            <p>Essayez de modifier vos filtres de recherche.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cours as $coursItem): ?>
                            <?php 
                            $objectifs = json_decode($coursItem['objectifs'] ?? '[]', true) ?: [];
                            $niveau_color = getNiveauColor($coursItem['niveau']);
                            ?>
                            <div class="cours-card" 
                                 onclick="openCours(<?= $coursItem['id'] ?>)"
                                 data-langage="<?= htmlspecialchars($coursItem['langage']) ?>"
                                 data-niveau="<?= htmlspecialchars($coursItem['niveau']) ?>"
                                 data-titre="<?= htmlspecialchars($coursItem['titre']) ?>"
                                 data-description="<?= htmlspecialchars($coursItem['description']) ?>"
                                 data-duree="<?= $coursItem['duree_estimee'] ?>">
                                
                                <!-- En-tête du cours -->
                                <div class="cours-header">
                                    <div class="cours-langage">
                                        <?= getLangageIcon($coursItem['langage']) ?>
                                        <?= htmlspecialchars($coursItem['langage']) ?>
                                    </div>
                                    <div class="cours-niveau niveau-<?= $coursItem['niveau'] ?>">
                                        <?= getNiveauIcon($coursItem['niveau']) ?>
                                        <?= ucfirst($coursItem['niveau']) ?>
                                    </div>
                                </div>

                                <!-- Contenu du cours -->
                                <div class="cours-content">
                                    <h3 class="cours-title"><?= htmlspecialchars($coursItem['titre']) ?></h3>
                                    <p class="cours-description"><?= htmlspecialchars($coursItem['description']) ?></p>
                                    
                                    <?php if (!empty($objectifs)): ?>
                                        <div class="cours-objectifs">
                                            <h4>🎯 Objectifs :</h4>
                                            <ul>
                                                <?php foreach (array_slice($objectifs, 0, 3) as $objectif): ?>
                                                    <li><?= htmlspecialchars($objectif) ?></li>
                                                <?php endforeach; ?>
                                                <?php if (count($objectifs) > 3): ?>
                                                    <li><em>+ <?= count($objectifs) - 3 ?> autre(s)...</em></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Pied du cours -->
                                <div class="cours-footer">
                                    <div class="cours-meta">
                                        <span class="meta-item">
                                            <span class="meta-icon">⏱️</span>
                                            <?= $coursItem['duree_estimee'] ?> min
                                        </span>
                                        <span class="meta-item">
                                            <span class="meta-icon">📅</span>
                                            <?= date('d/m/Y', strtotime($coursItem['date_creation'])) ?>
                                        </span>
                                    </div>
                                    
                                    <button class="btn-cours">
                                        <span class="btn-icon">📖</span>
                                        Commencer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Lien vers les exercices -->
            <section class="action-section cv-section">
                <div class="action-content">
                    <h2>🚀 Prêt(e) à pratiquer ?</h2>
                    <p>Maintenant que vous avez appris les bases, mettez vos connaissances en pratique avec nos exercices interactifs !</p>
                    <a href="exercices.php" class="action-btn">
                        <span class="btn-icon">💪</span>
                        Commencer les exercices
                    </a>
                </div>
                <div class="action-visual">
                    <div class="practice-icon">🏋️‍♂️</div>
                </div>
            </section>
        </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>

    <script>
        // ===== SYSTÈME DE FILTRAGE DYNAMIQUE =====
        
        // Données des cours (passées depuis PHP)
        const coursData = <?= json_encode($cours) ?>;
        
        // État actuel des filtres
        let currentFilters = {
            langage: '',
            niveau: '',
            search: '',
            sort: 'langage'
        };
        
        // Éléments DOM (seront initialisés après le chargement)
        let searchInput, clearSearchBtn, resetFiltersBtn, coursCountElement;
        let langageTags, niveauTags, sortTags;
        
        // Initialisation au chargement du DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser les éléments DOM
            searchInput = document.getElementById('searchInput');
            clearSearchBtn = document.getElementById('clearSearch');
            resetFiltersBtn = document.getElementById('resetFilters');
            coursCountElement = document.getElementById('coursCount');
            langageTags = document.getElementById('langageTags');
            niveauTags = document.getElementById('niveauTags');
            sortTags = document.getElementById('sortTags');
            
            // Vérifier que tous les éléments existent
            if (!searchInput || !clearSearchBtn || !resetFiltersBtn || !coursCountElement) {
                console.error('Erreur: Certains éléments DOM des filtres sont manquants');
                return;
            }
            
            // Initialiser les filtres
            initializeFilters();
            bindEvents();
            updateCoursCounts();
            filterAndDisplayCours();
        });
        
        function initializeFilters() {
            // Récupérer les filtres depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            currentFilters.langage = urlParams.get('langage') || '';
            currentFilters.niveau = urlParams.get('niveau') || '';
            currentFilters.search = urlParams.get('search') || '';
            
            // Mettre à jour l'interface
            if (searchInput) searchInput.value = currentFilters.search;
            updateActiveFilters();
            toggleClearSearchButton();
        }
        
        function bindEvents() {
            // Recherche en temps réel
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentFilters.search = this.value.trim();
                    toggleClearSearchButton();
                    filterAndDisplayCours();
                    updateURL();
                });
            }
            
            // Bouton clear search
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    if (searchInput) searchInput.value = '';
                    currentFilters.search = '';
                    toggleClearSearchButton();
                    filterAndDisplayCours();
                    updateURL();
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
                    e.stopPropagation();
                    
                    const type = this.dataset.type;
                    const value = this.dataset.value;
                    
                    console.log('🖱️ Tag cliqué:', type, value);
                    
                    if (type === 'sort') {
                        // Gestion spéciale pour le tri
                        console.log('🔄 Changement de tri de', currentFilters.sort, 'vers', value);
                        
                        document.querySelectorAll('[data-type="sort"]').forEach(t => {
                            t.classList.remove('active');
                        });
                        this.classList.add('active');
                        currentFilters.sort = value;
                        
                        console.log('✅ Nouveau tri appliqué:', currentFilters.sort);
                        
                        // Forcer le re-filtrage et tri
                        filterAndDisplayCours();
                        updateURL();
                    } else {
                        // Gestion normale pour les autres filtres
                        console.log(`📝 Changement de filtre ${type} de "${currentFilters[type]}" vers "${value}"`);
                        
                        document.querySelectorAll(`[data-type="${type}"]`).forEach(t => {
                            t.classList.remove('active');
                        });
                        this.classList.add('active');
                        currentFilters[type] = value;
                        
                        filterAndDisplayCours();
                        updateURL();
                    }
                });
            });
        }
        
        function toggleClearSearchButton() {
            if (!clearSearchBtn || !searchInput) return;
            
            if (searchInput.value.trim()) {
                clearSearchBtn.style.display = 'flex';
            } else {
                clearSearchBtn.style.display = 'none';
            }
        }
        
        function updateActiveFilters() {
            // Mettre à jour les tags actifs
            document.querySelectorAll('.filter-tag').forEach(tag => {
                const type = tag.dataset.type;
                const value = tag.dataset.value;
                
                tag.classList.remove('active');
                if (currentFilters[type] === value) {
                    tag.classList.add('active');
                }
            });
            
            // S'assurer qu'un bouton de tri est toujours actif
            const activeSortButton = document.querySelector('[data-type="sort"].active');
            if (!activeSortButton) {
                const defaultSortButton = document.querySelector('[data-type="sort"][data-value="langage"]');
                if (defaultSortButton) {
                    defaultSortButton.classList.add('active');
                }
            }
        }
        
        function filterAndDisplayCours() {
            console.log('Filtrage en cours avec:', currentFilters);
            
            const filteredCours = coursData.filter(cours => {
                // Filtre par langage
                if (currentFilters.langage && cours.langage !== currentFilters.langage) {
                    return false;
                }
                
                // Filtre par niveau
                if (currentFilters.niveau && cours.niveau !== currentFilters.niveau) {
                    return false;
                }
                
                // Filtre par recherche
                if (currentFilters.search) {
                    const searchTerm = currentFilters.search.toLowerCase();
                    const titleMatch = cours.titre.toLowerCase().includes(searchTerm);
                    const descMatch = cours.description.toLowerCase().includes(searchTerm);
                    const langMatch = cours.langage.toLowerCase().includes(searchTerm);
                    
                    if (!titleMatch && !descMatch && !langMatch) {
                        return false;
                    }
                }
                
                return true;
            });
            
            console.log(`${filteredCours.length} cours trouvés sur ${coursData.length}`);
            
            // Trier les cours
            sortCours(filteredCours);
            
            // Afficher/masquer les cours
            displayFilteredCours(filteredCours);
            
            // Mettre à jour les compteurs
            updateCoursCounts(filteredCours);
        }
        
        function sortCours(cours) {
            console.log('🔄 Tri par:', currentFilters.sort);
            console.log('📚 Cours avant tri:', cours.map(c => {
                const value = currentFilters.sort === 'duree' ? 
                    c.duree_estimee : 
                    c[currentFilters.sort];
                return `${c.titre} (${value})`;
            }).slice(0, 5));
            
            cours.sort((a, b) => {
                switch (currentFilters.sort) {
                    case 'titre':
                        const result1 = a.titre.localeCompare(b.titre, 'fr', { numeric: true, caseFirst: 'lower' });
                        console.log(`Tri titre: "${a.titre}" vs "${b.titre}" = ${result1}`);
                        return result1;
                    
                    case 'niveau':
                        const niveauOrder = { 'debutant': 1, 'intermediaire': 2, 'avance': 3 };
                        const niveauA = niveauOrder[a.niveau] || 0;
                        const niveauB = niveauOrder[b.niveau] || 0;
                        console.log(`Tri niveau: ${a.niveau} (${niveauA}) vs ${b.niveau} (${niveauB})`);
                        if (niveauA !== niveauB) {
                            return niveauA - niveauB;
                        }
                        return a.titre.localeCompare(b.titre);
                    
                    case 'duree':
                        // Extraction et conversion de la durée en minutes
                        const extractDuration = (dureeStr) => {
                            if (!dureeStr) return 0;
                            const str = dureeStr.toString().toLowerCase();
                            let total = 0;
                            
                            // Extraction des heures
                            const hoursMatch = str.match(/(\d+)\s*h/);
                            if (hoursMatch) total += parseInt(hoursMatch[1]) * 60;
                            
                            // Extraction des minutes
                            const minutesMatch = str.match(/(\d+)\s*min/);
                            if (minutesMatch) total += parseInt(minutesMatch[1]);
                            
                            // Si aucun format trouvé, essayer d'extraire un nombre
                            if (total === 0) {
                                const numberMatch = str.match(/(\d+)/);
                                if (numberMatch) total = parseInt(numberMatch[1]);
                            }
                            
                            return total;
                        };
                        
                        const durationA = extractDuration(a.duree_estimee);
                        const durationB = extractDuration(b.duree_estimee);
                        
                        console.log(`⏱️ Durées: "${a.titre}" (${a.duree_estimee}) = ${durationA}min vs "${b.titre}" (${b.duree_estimee}) = ${durationB}min`);
                        
                        if (durationA !== durationB) {
                            return durationA - durationB;
                        }
                        return a.titre.localeCompare(b.titre);
                    
                    case 'langage':
                    default:
                        const langageCompare = a.langage.localeCompare(b.langage);
                        console.log(`Tri langage: "${a.langage}" vs "${b.langage}" = ${langageCompare}`);
                        if (langageCompare !== 0) {
                            return langageCompare;
                        }
                        return a.titre.localeCompare(b.titre);
                }
            });
            
            console.log('✅ Cours après tri:', cours.map(c => {
                const value = currentFilters.sort === 'duree' ? 
                    c.duree_estimee : 
                    c[currentFilters.sort];
                return `${c.titre} (${value})`;
            }).slice(0, 5));
        }
        
        function displayFilteredCours(filteredCours) {
            const allCards = document.querySelectorAll('.cours-card');
            const coursGrid = document.querySelector('.cours-grid');
            const filteredIds = filteredCours.map(cours => cours.id);
            
            console.log('IDs des cours filtrés dans l\'ordre:', filteredIds);
            
            // Masquer d'abord toutes les cartes
            allCards.forEach(card => {
                card.style.display = 'none';
                card.classList.remove('visible');
                card.classList.add('hidden');
            });
            
            // Créer un tableau de cartes triées selon l'ordre de filteredCours
            const sortedCards = [];
            
            filteredCours.forEach(cours => {
                const card = Array.from(allCards).find(card => {
                    const onclickAttr = card.getAttribute('onclick');
                    if (onclickAttr) {
                        const match = onclickAttr.match(/openCours\((\d+)\)/);
                        return match && parseInt(match[1]) === cours.id;
                    }
                    return false;
                });
                
                if (card) {
                    card.style.display = 'block';
                    card.classList.remove('hidden');
                    card.classList.add('visible');
                    sortedCards.push(card);
                }
            });
            
            // Réorganiser les cartes dans le DOM selon le tri
            if (coursGrid && sortedCards.length > 0) {
                // Supprimer toutes les cartes du grid
                const noResultsDiv = coursGrid.querySelector('.no-results');
                coursGrid.innerHTML = '';
                
                // Ajouter les cartes dans le bon ordre
                sortedCards.forEach(card => {
                    coursGrid.appendChild(card);
                });
                
                // Si aucun résultat, afficher le message
                if (sortedCards.length === 0 && noResultsDiv) {
                    coursGrid.appendChild(noResultsDiv);
                } else if (sortedCards.length === 0) {
                    coursGrid.innerHTML = `
                        <div class="no-results">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                            <h3>Aucun cours trouvé</h3>
                            <p>Essayez de modifier vos filtres de recherche.</p>
                        </div>
                    `;
                }
            }
            
            console.log(`Affichage de ${sortedCards.length} cours triés`);
        }
        
        function updateCoursCounts(filteredCours = coursData) {
            if (!coursCountElement) return;
            
            // Compter le nombre total de cours filtrés
            coursCountElement.textContent = filteredCours.length;
            
            // Compter par langage
            const langageCounts = {};
            const niveauCounts = {};
            
            filteredCours.forEach(cours => {
                langageCounts[cours.langage] = (langageCounts[cours.langage] || 0) + 1;
                niveauCounts[cours.niveau] = (niveauCounts[cours.niveau] || 0) + 1;
            });
            
            // Mettre à jour les compteurs des tags
            document.querySelectorAll('[data-type="langage"]').forEach(tag => {
                const langage = tag.dataset.value;
                const count = langage ? (langageCounts[langage] || 0) : filteredCours.length;
                const countElement = tag.querySelector('.tag-count');
                if (countElement) {
                    countElement.textContent = `(${count})`;
                }
            });
            
            document.querySelectorAll('[data-type="niveau"]').forEach(tag => {
                const niveau = tag.dataset.value;
                const count = niveau ? (niveauCounts[niveau] || 0) : filteredCours.length;
                const countElement = tag.querySelector('.tag-count');
                if (countElement) {
                    countElement.textContent = `(${count})`;
                }
            });
        }
        
        function resetAllFilters() {
            currentFilters = {
                langage: '',
                niveau: '',
                search: '',
                sort: 'langage'
            };
            
            if (searchInput) searchInput.value = '';
            toggleClearSearchButton();
            updateActiveFilters();
            filterAndDisplayCours();
            updateURL();
        }
        
        function updateURL() {
            const params = new URLSearchParams();
            
            if (currentFilters.langage) params.set('langage', currentFilters.langage);
            if (currentFilters.niveau) params.set('niveau', currentFilters.niveau);
            if (currentFilters.search) params.set('search', currentFilters.search);
            
            const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({}, '', newURL);
        }

        function openCours(id) {
            window.location.href = `cours-detail.php?id=${id}`;
        }

        // Animation des éléments au scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.cours-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>