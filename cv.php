<?php
require 'config.php';
require 'auth_helper.php';

$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
$userid = $user['id'];

$experiences = $pdo->query("
  SELECT ep.*, me.mission
  FROM experiences_pro ep
  LEFT JOIN missions_experience me ON ep.id = me.experience_id
  WHERE ep.utilisateur_id = $userid
  ORDER BY ep.id, me.id
")->fetchAll();

$formations = $pdo->query("
  SELECT f.*, df.detail
  FROM formations f
  LEFT JOIN details_formation df ON f.id = df.formation_id
  WHERE f.utilisateur_id = $userid
  ORDER BY f.id, df.id
")->fetchAll();

$langues = $pdo->query("SELECT langue, niveau FROM langues WHERE utilisateur_id = $userid")->fetchAll();
$softskills = $pdo->query("SELECT skill FROM soft_skills WHERE utilisateur_id = $userid")->fetchAll();
$interets = $pdo->query("SELECT interet FROM interets WHERE utilisateur_id = $userid")->fetchAll();
$technos = $pdo->query("SELECT nom, type FROM technologies WHERE utilisateur_id = $userid")->fetchAll();

// Grouper les technologies par type
$groupedTechnos = ['langage' => [], 'bdd' => [], 'application' => []];
foreach ($technos as $t) {
    $groupedTechnos[$t['type']][] = $t['nom'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV de <?= $user["nom"] ?> - Développeur Fullstack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <link rel="stylesheet" href="cv-modern.css">
    <link rel="stylesheet" href="cv-animations.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <meta name="description" content="CV de <?= $user["nom"] ?> - Développeur Fullstack Junior spécialisé en PHP/Symfony et JavaScript/React">
</head>
<body class="cv-page">
    <?= generateNavigation('cv') ?>

    <div class="cv-container">
        <!-- Hero Section -->
        <section class="cv-hero fade-in">
            <div class="hero-content">
                <img src="assets/img/moi.jpg" alt="Photo de <?= $user["nom"] ?>" class="hero-avatar">
                
                <div class="hero-info">
                    <h1><?= htmlspecialchars($user["nom"]) ?></h1>
                    <p class="hero-subtitle">Développeur Fullstack Junior – PHP/Symfony & JavaScript/React</p>
                    
                    <div class="hero-details">
                        <div class="hero-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($user["adresse"]) ?></span>
                        </div>
                        <div class="hero-detail">
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($user["telephone"]) ?></span>
                        </div>
                        <div class="hero-detail">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?= htmlspecialchars($user["email_pro"]) ?>"><?= htmlspecialchars($user["email_pro"]) ?></a>
                        </div>
                        <div class="hero-detail">
                            <i class="fas fa-car"></i>
                            <span><?= htmlspecialchars($user["permis"]) ?></span>
                        </div>
                        <div class="hero-detail">
                            <i class="fas fa-birthday-cake"></i>
                            <span><?= htmlspecialchars($user["age"]) ?> ans</span>
                        </div>
                    </div>
                    
                    <div class="hero-links">
                        <a href="<?= htmlspecialchars($user["github"]) ?>" class="hero-link" target="_blank" rel="noopener">
                            <i class="fab fa-github"></i>
                            <span>GitHub</span>
                        </a>
                        <a href="<?= htmlspecialchars($user["linkedin"]) ?>" class="hero-link" target="_blank" rel="noopener">
                            <i class="fab fa-linkedin"></i>
                            <span>LinkedIn</span>
                        </a>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="download.php" class="btn-download primary">
                        <i class="fas fa-file-pdf"></i>
                        <span>Télécharger CV</span>
                    </a>
                    <a href="contact.php" class="btn-contact">
                        <i class="fas fa-envelope"></i>
                        <span>Me contacter</span>
                    </a>
                    <button class="btn-share" onclick="shareCV()">
                        <i class="fas fa-share-alt"></i>
                        <span>Partager</span>
                    </button>
                </div>
            </div>
        </section>
        <!-- Main Content -->
        <main class="cv-main">
            <!-- Sidebar -->
            <aside class="cv-sidebar">
                <!-- Compétences Techniques -->
                <section class="cv-section slide-up">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-code section-icon"></i>
                            Compétences techniques
                        </h2>
                    </div>
                    <div class="section-content">
                        <div class="skills-grid">
                            <?php foreach ($groupedTechnos as $type => $items): ?>
                                <?php if (!empty($items)): ?>
                                    <div class="skill-category">
                                        <h4><?= ucfirst($type) ?>s</h4>
                                        <div class="skill-tags">
                                            <?php foreach ($items as $item): ?>
                                                <span class="skill-tag"><?= htmlspecialchars($item) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <!-- Soft Skills -->
                <section class="cv-section slide-up">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-lightbulb section-icon"></i>
                            Soft Skills
                        </h2>
                    </div>
                    <div class="section-content">
                        <div class="skill-tags">
                            <?php foreach ($softskills as $skill): ?>
                                <span class="skill-tag"><?= htmlspecialchars($skill['skill']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <!-- Langues -->
                <section class="cv-section slide-up">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-globe section-icon"></i>
                            Langues
                        </h2>
                    </div>
                    <div class="section-content">
                        <ul class="info-list">
                            <?php foreach ($langues as $langue): ?>
                                <li class="info-item">
                                    <i class="fas fa-comment info-icon"></i>
                                    <span class="info-text"><?= htmlspecialchars(ucfirst($langue['langue'])) ?></span>
                                    <span class="info-level"><?= htmlspecialchars($langue['niveau']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>

                <!-- Centres d'intérêt -->
                <section class="cv-section slide-up">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-heart section-icon"></i>
                            Centres d'intérêt
                        </h2>
                    </div>
                    <div class="section-content">
                        <ul class="info-list">
                            <?php foreach ($interets as $interet): ?>
                                <li class="info-item">
                                    <i class="fas fa-star info-icon"></i>
                                    <span class="info-text"><?= htmlspecialchars($interet['interet']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            </aside>
            <!-- Content Principal -->
            <div class="cv-content">
                <!-- Expériences Professionnelles -->
                <section class="cv-section slide-up">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-briefcase section-icon"></i>
                            Expériences professionnelles
                        </h2>
                    </div>
                    <div class="section-content">
                        <div class="timeline">
                            <?php
                            $currentId = null;
                            $missionsBuffer = [];

                            foreach ($experiences as $exp) {
                                if ($exp['id'] !== $currentId) {
                                    // Afficher l'expérience précédente si elle existe
                                    if ($currentId !== null) {
                                        if (!empty($missionsBuffer)) {
                                            echo '<ul class="timeline-missions">';
                                            foreach ($missionsBuffer as $mission) {
                                                $isSub = str_starts_with(trim($mission), '-');
                                                $class = $isSub ? ' subpoint' : '';
                                                $text = $isSub ? ltrim($mission, '- ') : $mission;
                                                echo '<li class="timeline-mission' . $class . '">' . htmlspecialchars($text) . '</li>';
                                            }
                                            echo '</ul>';
                                        }
                                        echo '</div></div>';
                                    }

                                    // Commencer une nouvelle expérience
                                    $currentId = $exp['id'];
                                    $missionsBuffer = [];
                                    ?>
                                    <div class="timeline-item">
                                        <div class="timeline-header">
                                            <h3 class="timeline-title">
                                                <?= htmlspecialchars($exp['poste']) ?> — 
                                                <span class="timeline-company"><?= htmlspecialchars($exp['entreprise']) ?></span>
                                            </h3>
                                            <div class="timeline-period">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><?= htmlspecialchars($exp['periode']) ?></span>
                                            </div>
                                        </div>
                                        <div class="timeline-content">
                                    <?php
                                }

                                // Collecter les missions
                                if (!empty($exp['mission'])) {
                                    $missionsBuffer[] = $exp['mission'];
                                }
                            }

                            // Afficher la dernière expérience
                            if ($currentId !== null) {
                                if (!empty($missionsBuffer)) {
                                    echo '<ul class="timeline-missions">';
                                    foreach ($missionsBuffer as $mission) {
                                        $isSub = str_starts_with(trim($mission), '-');
                                        $class = $isSub ? ' subpoint' : '';
                                        $text = $isSub ? ltrim($mission, '- ') : $mission;
                                        echo '<li class="timeline-mission' . $class . '">' . htmlspecialchars($text) . '</li>';
                                    }
                                    echo '</ul>';
                                }
                                echo '</div></div>';
                            }
                            ?>
                        </div>
                    </div>
                </section>

                <!-- Formations -->
                <section class="cv-section slide-up">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-graduation-cap section-icon"></i>
                            Formations
                        </h2>
                    </div>
                    <div class="section-content">
                        <div class="timeline">
                            <?php
                            $currentId = null;
                            $detailsBuffer = [];

                            foreach ($formations as $formation) {
                                if ($formation['id'] !== $currentId) {
                                    // Afficher la formation précédente si elle existe
                                    if ($currentId !== null) {
                                        if (!empty($detailsBuffer)) {
                                            echo '<ul class="timeline-missions">';
                                            foreach ($detailsBuffer as $detail) {
                                                echo '<li class="timeline-mission">' . htmlspecialchars($detail) . '</li>';
                                            }
                                            echo '</ul>';
                                        }
                                        echo '</div></div>';
                                    }

                                    // Commencer une nouvelle formation
                                    $currentId = $formation['id'];
                                    $detailsBuffer = [];
                                    ?>
                                    <div class="timeline-item">
                                        <div class="timeline-header">
                                            <h3 class="timeline-title">
                                                <?= htmlspecialchars($formation['diplome']) ?> — 
                                                <span class="timeline-company"><?= htmlspecialchars($formation['etablissement']) ?></span>
                                            </h3>
                                            <div class="timeline-period">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><?= htmlspecialchars($formation['periode']) ?></span>
                                            </div>
                                        </div>
                                        <div class="timeline-content">
                                    <?php
                                }

                                // Collecter les détails
                                if (!empty($formation['detail'])) {
                                    $detailsBuffer[] = $formation['detail'];
                                }
                            }

                            // Afficher la dernière formation
                            if ($currentId !== null) {
                                if (!empty($detailsBuffer)) {
                                    echo '<ul class="timeline-missions">';
                                    foreach ($detailsBuffer as $detail) {
                                        echo '<li class="timeline-mission">' . htmlspecialchars($detail) . '</li>';
                                    }
                                    echo '</ul>';
                                }
                                echo '</div></div>';
                            }
                            ?>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="script.js"></script>
    <script src="cv-interactions.js"></script>
    <script>
        // Animation d'apparition progressive
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observer toutes les sections
            document.querySelectorAll('.cv-section').forEach(section => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(30px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });

            // Animation des skill tags au hover
            document.querySelectorAll('.skill-tag').forEach(tag => {
                tag.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05) rotate(2deg)';
                });
                
                tag.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0deg)';
                });
            });

            // Smooth scroll pour les ancres
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Fonction d'impression optimisée
        function optimizedPrint() {
            // Masquer temporairement les éléments non nécessaires pour l'impression
            const elementsToHide = document.querySelectorAll('nav, #theme-toggle, .hero-actions');
            elementsToHide.forEach(el => el.style.display = 'none');
            
            // Lancer l'impression
            window.print();
            
            // Restaurer l'affichage après impression
            setTimeout(() => {
                elementsToHide.forEach(el => el.style.display = '');
            }, 100);
        }
    </script>

    <footer>
        <div class="footer-content">
            <span>© <script>document.write(new Date().getFullYear())</script> <?= htmlspecialchars($user["nom"]) ?>. Tous droits réservés.</span>
            <span>Contact: <a href="mailto:<?= htmlspecialchars($user["email_contact"] ?? $user["email"]) ?>">contact</a></span>
            <span>Suivez-moi sur <a href="<?= htmlspecialchars($user["github"]) ?>" target="_blank" rel="noopener">GitHub</a> et <a href="<?= htmlspecialchars($user["linkedin"]) ?>" target="_blank" rel="noopener">LinkedIn</a></span>
        </div>
    </footer>
</body>
</html>
