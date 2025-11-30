<?php
require_once 'config.php';
require_once 'auth_helper.php';

// Gestion des filtres
$langage_filter = $_GET['langage'] ?? '';
$niveau_filter = $_GET['niveau'] ?? '';

// Fonction pour obtenir l'icône du langage
function getLangageIcon($langage) {
    $icons = [
        "SQL" => "🗃️",
        "HTML" => "📄",
        "CSS" => "🎨", 
        "JavaScript" => "🟨",
        "Python" => "🐍",
        "PHP" => "🐘",
        "Java" => "☕",
        "C++" => "⚡",
        "C#" => "🔵",
        "Web" => "🌐"
    ];
    return $icons[$langage] ?? "💻";
}

// Récupération des cours avec filtres
$query = "SELECT * FROM cours WHERE statut = 'actif'";
$params = [];

// Filtre par langage
if ($langage_filter) {
    $query .= " AND langage = ?";
    $params[] = $langage_filter;
}

// Filtre par niveau
if ($niveau_filter) {
    $query .= " AND niveau = ?";
    $params[] = $niveau_filter;
}

$query .= " ORDER BY ordre_affichage ASC, titre ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cours_list = $stmt->fetchAll();

// Récupérer tous les cours pour les filtres
$stmt_all = $pdo->query("SELECT DISTINCT langage, niveau FROM cours WHERE statut = 'actif' ORDER BY langage ASC");
$tous_cours = $stmt_all->fetchAll();

// Extraire les langages et niveaux uniques
$langages_uniques = [];
$niveaux = [];
foreach ($tous_cours as $cours) {
    // Filtrer les valeurs vides pour les langages
    if (!empty($cours['langage']) && !in_array($cours['langage'], $langages_uniques)) {
        $langages_uniques[] = $cours['langage'];
    }
    
    // Filtrer les valeurs vides pour les niveaux
    if (!empty($cours['niveau']) && !in_array($cours['niveau'], $niveaux)) {
        $niveaux[] = $cours['niveau'];
    }
}
sort($langages_uniques);

// Trier les niveaux par ordre de difficulté
$ordre_niveaux = ['debutant', 'intermediaire', 'avance'];
$niveaux_ordonnes = [];
foreach ($ordre_niveaux as $niveau_ordre) {
    if (in_array($niveau_ordre, $niveaux)) {
        $niveaux_ordonnes[] = $niveau_ordre;
    }
}
$niveaux = $niveaux_ordonnes;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cours de Programmation - Cédric Goujon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="cv-modern.css">
    <link rel="stylesheet" href="cv-animations.css">
    <link rel="stylesheet" href="exercices-styles.css">
    <link rel="stylesheet" href="cours-styles.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body class="cv-page">
    <?= generateNavigation('cours') ?>

    <div class="cv-container">
        <!-- Hero Section -->
        <section class="cv-hero fade-in">
            <div class="hero-content">
                <h1><i class="fas fa-graduation-cap"></i> Cours de Programmation</h1>
                <p class="hero-subtitle">Apprenez la programmation avec des cours structurés et progressifs</p>
            </div>
        </section>
   <div class="learning-notice">
                <div class="notice-content">
                    <i class="fas fa-lightbulb"></i>
                    <div class="notice-text">
                        <h3>💡 Conseil important</h3>
                        <p>Il est fortement recommandé de suivre ces cours <strong>avant</strong> de faire les exercices pratiques. Les cours vous donnent les bases théoriques nécessaires pour réussir les exercices.</p>
                    </div>
                    <a href="exercices.php" class="notice-btn">
                        <i class="fas fa-dumbbell"></i>
                        Exercices pratiques
                    </a>
                </div>
            </div>
        <section class="cv-section">
            <!-- Statistiques -->
            <div class="stats-container fade-in">
                <div class="stat-item">
                    <i class="fas fa-book"></i>
                    <span class="stat-number"><?= count($cours_list) ?></span>
                    <span class="stat-label">Cours disponibles</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-filter"></i>
                    <span class="stat-number"><?= count($langages_uniques) ?></span>
                    <span class="stat-label">Technologies</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-chart-line"></i>
                    <span class="stat-number"><?= count($niveaux) ?></span>
                    <span class="stat-label">Niveaux</span>
                </div>
            </div>
            
            <!-- Filtres par boutons -->
            <div class="filters-section">
                <div class="filters-grid">
                    <!-- Langages/Technologies -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">
                            <span class="filter-icon">🖥️</span>
                            Technologies
                        </h3>
                        <div class="filter-tags">
                            <button class="filter-tag <?= empty($langage_filter) ? 'active' : '' ?>" data-type="langage" data-value="">
                                💻 Toutes
                            </button>
                            <?php foreach ($langages_uniques as $lang): ?>
                            <button class="filter-tag <?= $langage_filter === $lang ? 'active' : '' ?>" data-type="langage" data-value="<?= htmlspecialchars($lang) ?>">
                                <?= getLangageIcon($lang) ?> <?= htmlspecialchars($lang) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Niveaux -->
                    <div class="filter-group">
                        <h3 class="filter-group-title">
                            <span class="filter-icon">🎯</span>
                            Niveau
                        </h3>
                        <div class="filter-tags">
                            <button class="filter-tag <?= empty($niveau_filter) ? 'active' : '' ?>" data-type="niveau" data-value="">
                                🎯 Tous
                            </button>
                            <?php foreach ($niveaux as $niveau): ?>
                            <button class="filter-tag filter-niveau-<?= $niveau ?> <?= $niveau_filter === $niveau ? 'active' : '' ?>" data-type="niveau" data-value="<?= htmlspecialchars($niveau) ?>">
                                <?php
                                $niveau_icon = '';
                                $niveau_label = '';
                                switch($niveau) {
                                    case 'debutant': 
                                        $niveau_icon = '🟢'; 
                                        $niveau_label = 'Débutant';
                                        break;
                                    case 'intermediaire': 
                                        $niveau_icon = '🟡'; 
                                        $niveau_label = 'Intermédiaire';
                                        break;
                                    case 'avance': 
                                        $niveau_icon = '🔴'; 
                                        $niveau_label = 'Avancé';
                                        break;
                                    default: 
                                        $niveau_icon = '⚪'; 
                                        $niveau_label = ucfirst($niveau);
                                        break;
                                }
                                ?>
                                <?= $niveau_icon ?> <?= $niveau_label ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grille des cours -->
            <h2 class="section-title">📚 Catalogue des cours</h2>
            
            <!-- Message important sur les prérequis -->
         
            <div class="exercices-grid">
                <?php if (empty($cours_list)): ?>
                    <div class="no-results">
                        <h3>Aucun cours disponible</h3>
                        <p>Les cours seront bientôt disponibles.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($cours_list as $cours): ?>
                        <div class="exercice-card" onclick="openCours(<?= $cours['id'] ?>)">
                            <div class="exercice-header">
                                <h3 class="exercice-title">
                                    <?= getLangageIcon($cours['langage']) ?>
                                    <?= htmlspecialchars($cours['titre']) ?>
                                </h3>
                                <div class="exercice-meta">
                                    <span class="badge badge-langage">
                                        <?= htmlspecialchars($cours['langage']) ?>
                                    </span>
                                    <span class="badge badge-difficulte <?= $cours['niveau'] ?>">
                                        <?= ucfirst($cours['niveau']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="exercice-body">
                                <p class="exercice-description">
                                    <?= htmlspecialchars($cours['description']) ?>
                                </p>
                            </div>
                            
                            <div class="exercice-footer">
                                <!-- <div class="exercice-stats">
                                    <span>📅 <?= date("d/m/Y", strtotime($cours['date_creation'])) ?></span>
                                </div> -->
                                <button class="btn-exercice">Voir le cours</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Modal pour afficher les détails du cours -->
    <div id="coursModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Cours</h2>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>

    <script>
        function openCours(id) {
            document.getElementById("modalTitle").textContent = "Chargement...";
            document.getElementById("modalBody").innerHTML = "<p>🔄 Chargement...</p>";
            document.getElementById("coursModal").style.display = "flex";
            
            fetch(`cours_detail.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById("modalBody").innerHTML = html;
                    document.getElementById("modalTitle").textContent = "Contenu du cours";
                })
                .catch(error => {
                    document.getElementById("modalBody").innerHTML = "<p>❌ Erreur de chargement.</p>";
                });
        }

        function closeModal() {
            document.getElementById("coursModal").style.display = "none";
        }

        document.getElementById("coursModal").addEventListener("click", function(e) {
            if (e.target === this) closeModal();
        });

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.exercice-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
            
            // Gestion des filtres par boutons
            const filterButtons = document.querySelectorAll('.filter-tag');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.type;
                    const value = this.dataset.value;
                    
                    // Créer la nouvelle URL avec les filtres
                    const url = new URL(window.location);
                    
                    if (value) {
                        url.searchParams.set(type, value);
                    } else {
                        url.searchParams.delete(type);
                    }
                    
                    // Rediriger vers la nouvelle URL
                    window.location = url;
                });
            });
        });
    </script>

    <style>
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin: 2rem 0;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            text-align: center;
        }

        .stat-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-item i {
            font-size: 2rem;
            opacity: 0.9;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .section-title {
            text-align: center;
            color: var(--primary-color);
            margin: 2rem 0;
            font-size: 2rem;
        }

        .exercice-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .exercice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-weight: 600;
        }

        .badge-langage {
            background: #4299e1;
            color: white;
        }

        .badge-difficulte.debutant {
            background: #48bb78;
            color: white;
        }

        .badge-difficulte.intermediaire {
            background: #ed8936;
            color: white;
        }

        .badge-difficulte.avance {
            background: #e53e3e;
            color: white;
        }

        /* Styles pour la modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 90vw;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: #2c5282;
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 2rem;
            overflow-y: auto;
            max-height: 70vh;
        }

        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .modal-content {
                max-width: 95vw;
                max-height: 95vh;
            }
            
            .modal-body {
                padding: 1rem;
            }
        }
        
        /* Styles pour les filtres par boutons */
        .filters-section {
            margin: 2rem 0;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .filter-group {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .filter-group-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 1rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .filter-icon {
            font-size: 1.2rem;
        }
        
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .filter-tag {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .filter-tag:hover {
            border-color: #4299e1;
            color: #4299e1;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.2);
        }
        
        .filter-tag.active {
            background: #4299e1;
            border-color: #4299e1;
            color: white;
        }
        
        .filter-tag.active:hover {
            background: #3182ce;
            border-color: #3182ce;
        }
        
        /* Styles spécifiques pour les niveaux */
        .filter-niveau-debutant {
            border-color: #48bb78;
            color: #38a169;
        }
        
        .filter-niveau-debutant:hover {
            border-color: #38a169;
            color: #38a169;
            background: rgba(72, 187, 120, 0.1);
        }
        
        .filter-niveau-debutant.active {
            background: #48bb78;
            border-color: #48bb78;
            color: white;
        }
        
        .filter-niveau-intermediaire {
            border-color: #ed8936;
            color: #dd6b20;
        }
        
        .filter-niveau-intermediaire:hover {
            border-color: #dd6b20;
            color: #dd6b20;
            background: rgba(237, 137, 54, 0.1);
        }
        
        .filter-niveau-intermediaire.active {
            background: #ed8936;
            border-color: #ed8936;
            color: white;
        }
        
        .filter-niveau-avance {
            border-color: #e53e3e;
            color: #c53030;
        }
        
        .filter-niveau-avance:hover {
            border-color: #c53030;
            color: #c53030;
            background: rgba(229, 62, 62, 0.1);
        }
        
        .filter-niveau-avance.active {
            background: #e53e3e;
            border-color: #e53e3e;
            color: white;
        }
        
        /* Message important sur l'apprentissage */
        .learning-notice {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            color: white;
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        }
        
        .notice-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .notice-content > i {
            font-size: 2.5rem;
            color: rgba(255, 255, 255, 0.9);
            flex-shrink: 0;
        }
        
        .notice-text {
            flex: 1;
        }
        
        .notice-text h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .notice-text p {
            margin: 0;
            line-height: 1.6;
            opacity: 0.95;
        }
        
        .notice-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        .notice-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .filter-tags {
                justify-content: center;
            }
            
            .notice-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .notice-content > i {
                font-size: 2rem;
            }
        }
    </style>

</body>
</html>
