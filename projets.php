<?php
require'config.php';
require 'auth_helper.php';
$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
$userid = $user['id'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mes Projets - Cédric Goujon</title>
  
  <!-- Styles modernes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="cv-modern.css">
  <link rel="stylesheet" href="cv-animations.css">
  <link rel="icon" type="image/png" href="favicon.png">
</head>

<body class="cv-page">
  <!-- Navigation -->
  <?= generateNavigation('projets') ?>

  <div class="cv-container">
    <!-- Hero Section -->
    <section class="cv-hero projects-hero fade-in">
      <div class="hero-content">
        <div class="hero-icon">
          <i class="fas fa-folder-open"></i>
        </div>
        
        <div class="hero-info">
          <h1>Mes Projets</h1>
          <p class="hero-subtitle">Découvrez mes réalisations et expérimentations</p>
          
          <div class="project-stats">
            <div class="hero-detail">
              <i class="fas fa-code"></i>
              <span>Projets web modernes</span>
            </div>
            <div class="hero-detail">
              <i class="fas fa-rocket"></i>
              <span>En constante évolution</span>
            </div>
          </div>
        </div>
        
        <div class="hero-actions">
          <a href="<?= htmlspecialchars($user['github']) ?>" class="btn-download primary" target="_blank" rel="noopener">
            <i class="fab fa-github"></i>
            <span>Voir sur GitHub</span>
          </a>
        </div>
      </div>
    </section>
    <!-- Main Content -->
    <main class="cv-main single-column">
      <section class="cv-section slide-up">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fab fa-github section-icon"></i>
            Projets Réalisés
          </h2>
        </div>
        
        <div class="projects-container">
          <div class="project-category">
            <h3 class="category-title">
              <i class="fas fa-star"></i>
              Projets Personnels
            </h3>
            
            <div class="projects-grid">
              <div class="project-card featured">
                <div class="project-header">
                  <div class="project-icon">
                    <i class="fas fa-paw"></i>
                  </div>
                  <div class="project-meta">
                    <h4><a href="https://www.educ-dogs.fr/" target="_blank" rel="noopener">Educ Dog's</a></h4>
                    <span class="project-year">2025</span>
                  </div>
                </div>
                
                <div class="project-content">
                  <p>Site d'éducation canine professionnel créé pour une amie éducatrice. Interface moderne et responsive avec système de contact intégré.</p>
                  
                  <div class="project-technologies">
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">HTML5</span>
                    <span class="tech-tag">CSS3</span>
                    <span class="tech-tag">Responsive</span>
                  </div>
                </div>
                
                <div class="project-actions">
                  <a href="https://www.educ-dogs.fr/" class="project-link" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Voir le site</span>
                  </a>
                </div>
              </div>
              
              <div class="project-card">
                <div class="project-header">
                  <div class="project-icon">
                    <i class="fas fa-user-tie"></i>
                  </div>
                  <div class="project-meta">
                    <h4>Site Portfolio Personnel</h4>
                    <span class="project-year">2024-2025</span>
                  </div>
                </div>
                
                <div class="project-content">
                  <p>Portfolio personnel avec interface d'administration, gestion de candidatures et design moderne. Évolution constante avec nouvelles fonctionnalités.</p>
                  
                  <div class="project-technologies">
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">JavaScript</span>
                    <span class="tech-tag">CSS3</span>
                    <span class="tech-tag">MySQL</span>
                  </div>
                </div>
                
                <div class="project-actions">
                  <a href="index.php" class="project-link">
                    <i class="fas fa-home"></i>
                    <span>Accueil</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
          
          <div class="project-category">
            <h3 class="category-title">
              <i class="fas fa-code-branch"></i>
              En Développement
            </h3>
            
            <div class="projects-grid">
              <div class="project-card upcoming">
                <div class="project-header">
                  <div class="project-icon">
                    <i class="fas fa-flask"></i>
                  </div>
                  <div class="project-meta">
                    <h4>Projets Futurs</h4>
                    <span class="project-year">À venir</span>
                  </div>
                </div>
                
                <div class="project-content">
                  <p>Nouvelles expérimentations et projets en cours de développement. Restez connecté pour découvrir mes prochaines créations !</p>
                  
                  <div class="project-technologies">
                    <span class="tech-tag">Frameworks modernes</span>
                    <span class="tech-tag">APIs</span>
                    <span class="tech-tag">Innovation</span>
                  </div>
                </div>
                
                <div class="project-actions">
                  <a href="<?= htmlspecialchars($user['github']) ?>" class="project-link" target="_blank" rel="noopener">
                    <i class="fab fa-github"></i>
                    <span>Suivre sur GitHub</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Footer moderne -->
  <footer class="cv-footer">
    <div class="footer-content">
      <span>© <script>document.write(new Date().getFullYear())</script> Cédric Goujon. Tous droits réservés.</span>
      <span>Contact: <a href="mailto:<?= htmlspecialchars($user['email']) ?>">contact</a></span>
      <span>Suivez-moi sur <a href="<?= htmlspecialchars($user['github']) ?>">GitHub</a> et <a href="<?= htmlspecialchars($user['linkedin']) ?>">LinkedIn</a></span>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="cv-interactions.js"></script>
</body>
</html>
