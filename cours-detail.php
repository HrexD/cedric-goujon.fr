<?php
require 'config.php';

$cours_id = (int)($_GET['id'] ?? 0);

if (!$cours_id) {
    header('Location: cours.php');
    exit;
}

try {
    // Récupérer le cours
    $sql = "SELECT * FROM cours WHERE id = ? AND statut = 'actif'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cours_id]);
    $cours = $stmt->fetch();
    
    if (!$cours) {
        header('Location: cours.php');
        exit;
    }
    
    // Récupérer les sections du cours
    $sections_sql = "SELECT * FROM cours_sections WHERE cours_id = ? ORDER BY ordre_section ASC";
    $sections_stmt = $pdo->prepare($sections_sql);
    $sections_stmt->execute([$cours_id]);
    $sections = $sections_stmt->fetchAll();
    
    // Récupérer d'autres cours du même langage
    $autres_sql = "SELECT id, titre, niveau, duree_estimee FROM cours 
                   WHERE langage = ? AND id != ? AND statut = 'actif' 
                   ORDER BY ordre_affichage ASC, titre ASC LIMIT 3";
    $autres_stmt = $pdo->prepare($autres_sql);
    $autres_stmt->execute([$cours['langage'], $cours_id]);
    $autres_cours = $autres_stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: cours.php');
    exit;
}

// Fonctions utilitaires (comme dans cours.php)
function getNiveauColor($niveau) {
    switch ($niveau) {
        case 'debutant': return '#10b981';
        case 'intermediaire': return '#f59e0b';
        case 'avance': return '#ef4444';
        default: return '#6b7280';
    }
}

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

function getNiveauIcon($niveau) {
    switch ($niveau) {
        case 'debutant': return '🟢';
        case 'intermediaire': return '🟡';
        case 'avance': return '🔴';
        default: return '⚫';
    }
}

$objectifs = json_decode($cours['objectifs'] ?? '[]', true) ?: [];
$prerequis = json_decode($cours['prerequis'] ?? '[]', true) ?: [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cours['titre']) ?> - Cours <?= htmlspecialchars($cours['langage']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($cours['description']) ?>">
    
    <!-- Styles -->
    <link rel="stylesheet" href="cv-modern.css">
    <link rel="stylesheet" href="cours-styles.css">
    <link rel="stylesheet" href="cours-detail-styles.css">
    
    <!-- Prism.js pour la coloration syntaxique -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon.png">
</head>

<body class="cv-page"><?php require 'auth_helper.php'; ?>
    <!-- Navigation -->
    <?= generateNavigation('cours') ?>

    <div class="cv-container">
        
            <!-- Navigation du cours -->
            <section class="cours-nav">
                <a href="cours.php" class="back-btn">
                    <span class="btn-icon">←</span>
                    Retour aux cours
                </a>
                
                <div class="cours-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%" id="progress-fill"></div>
                    </div>
                    <span class="progress-text" id="progress-text">0%</span>
                </div>
            </section>

            <!-- En-tête du cours -->
            <section class="cv-hero cours-hero">
                <div class="cours-hero-content">
                    <div class="cours-meta-header">
                        <span class="cours-langage-badge">
                            <?= getLangageIcon($cours['langage']) ?>
                            <?= htmlspecialchars($cours['langage']) ?>
                        </span>
                        <span class="cours-niveau-badge niveau-<?= $cours['niveau'] ?>">
                            <?= getNiveauIcon($cours['niveau']) ?>
                            <?= ucfirst($cours['niveau']) ?>
                        </span>
                        <span class="cours-duree">
                            ⏱️ <?= $cours['duree_estimee'] ?> min
                        </span>
                    </div>
                    
                    <h1 class="cours-title hero-title"><?= htmlspecialchars($cours['titre']) ?></h1>
                    <p class="cours-description hero-subtitle"><?= htmlspecialchars($cours['description']) ?></p>
                </div>
            </section>
                    
            <?php if (!empty($objectifs)): ?>
                <section class="cv-section">
                    <div class="cours-objectifs">
                        <h3>🎯 Objectifs du cours</h3>
                        <ul>
                            <?php foreach ($objectifs as $objectif): ?>
                                <li><?= htmlspecialchars($objectif) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>
                        
            <?php if (!empty($prerequis)): ?>
                <section class="cv-section">
                    <div class="cours-objectifs">
                        <h3>📋 Prérequis</h3>
                        <ul>
                            <?php foreach ($prerequis as $prerequis_item): ?>
                                <li><?= htmlspecialchars($prerequis_item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Table des matières -->
            <?php if (!empty($sections)): ?>
                <section class="cv-section">
                    <h2 class="section-title">
                        <span class="section-icon">📋</span>
                        Table des matières
                    </h2>
                    <div class="table-matieres">
                        <?php foreach ($sections as $index => $section): ?>
                            <a href="#section-<?= $section['id'] ?>" class="matiere-item" data-section="<?= $index + 1 ?>">
                                <div class="matiere-numero"><?= $index + 1 ?></div>
                                <div class="matiere-content">
                                    <div class="matiere-title"><?= htmlspecialchars($section['titre']) ?></div>
                                    <div class="matiere-type"><?= ucfirst($section['type_section']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Contenu du cours -->
            <section class="cv-section cours-content-section">
                <h2 class="section-title">
                    <span class="section-icon">📖</span>
                    Contenu du cours
                </h2>
                
                <div class="cours-content-main">
                    <article class="cours-article">
                        <?= $cours['contenu'] ?>
                    </article>
                    
                    <!-- Sections supplémentaires -->
                    <?php if (!empty($sections)): ?>
                        <?php foreach ($sections as $index => $section): ?>
                            <section class="cours-section" id="section-<?= $section['id'] ?>" data-section="<?= $index + 1 ?>">
                                <div class="section-header">
                                    <h3 class="section-title-detail">
                                        <span class="section-number"><?= $index + 1 ?></span>
                                        <?= htmlspecialchars($section['titre']) ?>
                                    </h3>
                                    <span class="section-type section-type-<?= $section['type_section'] ?>">
                                        <?php
                                        $icons = [
                                            'theorie' => '📚',
                                            'exemple' => '💡',
                                            'exercice' => '✏️',
                                            'resume' => '📝'
                                        ];
                                        echo $icons[$section['type_section']] ?? '📄';
                                        ?>
                                        <?= ucfirst($section['type_section']) ?>
                                    </span>
                                </div>
                                
                                <div class="section-content">
                                    <?= $section['contenu'] ?>
                                    
                                    <?php if ($section['code_exemple']): ?>
                                        <div class="code-example">
                                            <h4>💻 Code d'exemple :</h4>
                                            <pre class="cours-code-block"><code class="language-<?= strtolower($cours['langage']) ?>"><?= htmlspecialchars($section['code_exemple']) ?></code></pre>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="section-footer">
                                    <button class="btn-section-complete" onclick="markSectionComplete(<?= $index + 1 ?>)">
                                        ✓ Marquer comme terminé
                                    </button>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Actions -->
            <section class="cv-section cours-actions">
                <div class="actions-grid">
                    <button class="btn btn-primary btn-large" onclick="markCourseComplete()">
                        <span class="btn-icon">🏆</span>
                        Terminer le cours
                    </button>
                    
                    <a href="exercices.php?langage=<?= urlencode($cours['langage']) ?>" class="btn btn-secondary btn-large">
                        <span class="btn-icon">💪</span>
                        Exercices <?= htmlspecialchars($cours['langage']) ?>
                    </a>
                </div>
            </section>

            <!-- Autres cours -->
            <?php if (!empty($autres_cours)): ?>
                <section class="cv-section">
                    <h2 class="section-title">
                        <span class="section-icon">🔍</span>
                        Autres cours de <?= htmlspecialchars($cours['langage']) ?>
                    </h2>
                    
                    <div class="autres-cours">
                        <?php foreach ($autres_cours as $autre): ?>
                            <a href="cours-detail.php?id=<?= $autre['id'] ?>" class="autre-cours-item">
                                <div class="autre-cours-content">
                                    <h4><?= htmlspecialchars($autre['titre']) ?></h4>
                                    <div class="autre-cours-meta">
                                        <span><?= getNiveauIcon($autre['niveau']) ?> <?= ucfirst($autre['niveau']) ?></span>
                                        <span>⏱️ <?= $autre['duree_estimee'] ?> min</span>
                                    </div>
                                </div>
                                <div class="autre-cours-arrow">→</div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>

    <script>
        let completedSections = new Set();
        const totalSections = <?= count($sections) + 1 ?>; // +1 pour le contenu principal

        function markSectionComplete(sectionNumber) {
            completedSections.add(sectionNumber);
            updateProgress();
            
            // Marquer visuellement la section
            const button = event.target;
            button.textContent = '✅ Terminé';
            button.disabled = true;
            button.style.background = '#10b981';
            
            // Animation
            const section = button.closest('.cours-section');
            section.style.opacity = '0.7';
            
            // Notification
            showNotification('Section terminée ! 🎉', 'success');
        }

        function markCourseComplete() {
            // Marquer toutes les sections comme complètes
            for (let i = 1; i <= totalSections; i++) {
                completedSections.add(i);
            }
            updateProgress();
            
            // Notification de fin
            showNotification('Félicitations ! Cours terminé ! 🏆', 'success');
            
            // Redirection après quelques secondes
            setTimeout(() => {
                window.location.href = 'exercices.php?langage=<?= urlencode($cours['langage']) ?>';
            }, 2000);
        }

        function updateProgress() {
            const progress = (completedSections.size / totalSections) * 100;
            document.getElementById('progress-fill').style.width = progress + '%';
            document.getElementById('progress-text').textContent = Math.round(progress) + '%';
        }

        function showNotification(message, type = 'info') {
            // Créer une notification
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : '#3b82f6'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 1000;
                animation: slideInRight 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            // Retirer après 3 secondes
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Animation CSS pour les notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Navigation fluide vers les sections
        document.querySelectorAll('a[href^="#section-"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Marquer le contenu principal comme lu au scroll
        let mainContentSeen = false;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !mainContentSeen) {
                    mainContentSeen = true;
                    completedSections.add(0); // Section 0 = contenu principal
                    updateProgress();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.querySelector('.cours-article');
            if (mainContent) {
                observer.observe(mainContent);
            }
        });
    </script>
</body>
</html>