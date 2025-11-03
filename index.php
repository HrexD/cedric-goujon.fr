<?php

require 'config.php';
require 'auth_helper.php';

$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil – Cédric Goujon</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="cv-modern.css">
  <link rel="stylesheet" href="cv-animations.css">
  <link rel="icon" type="image/png" href="favicon.png">
  <meta name="description" content="Cédric Goujon - Développeur Full-Stack avec 2 ans et demi d'expérience, spécialisé en PHP/Symfony et JavaScript/React">
</head>
<body class="cv-page">
  <?= generateNavigation('index') ?>

  <div class="cv-container">
    <!-- Hero Section -->
    <section class="cv-hero fade-in">
      <div class="hero-content">
        <img src="assets/img/moi.jpg" alt="Photo de Cédric Goujon" class="hero-avatar">
        
        <div class="hero-info">
          <h1>Cédric Goujon</h1>
          <p class="hero-subtitle">Développeur Full‑Stack avec 2 ans et demi d'expérience</p>
          
          <div class="hero-details">
            <div class="hero-detail">
              <i class="fas fa-envelope"></i>
              <a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a>
            </div>
            <div class="hero-detail">
              <i class="fas fa-code"></i>
              <span>PHP, JavaScript, HTML5, CSS3</span>
            </div>
            <div class="hero-detail">
              <i class="fas fa-rocket"></i>
              <span>Applications web modernes et performantes</span>
            </div>
          </div>
          
          <div class="hero-links">
            <a href="<?= htmlspecialchars($user['github']) ?>" class="hero-link" target="_blank" rel="noopener">
              <i class="fab fa-github"></i>
              <span>GitHub</span>
            </a>
            <a href="<?= htmlspecialchars($user['linkedin']) ?>" class="hero-link" target="_blank" rel="noopener">
              <i class="fab fa-linkedin"></i>
              <span>LinkedIn</span>
            </a>
          </div>
        </div>
        
        <div class="hero-actions">
          <a href="cv.php" class="btn-download primary">
            <i class="fas fa-user"></i>
            <span>Voir mon CV</span>
          </a>
          <a href="contact.php" class="btn-contact">
            <i class="fas fa-envelope"></i>
            <span>Me contacter</span>
          </a>
          <a href="projets.php" class="btn-share">
            <i class="fas fa-folder-open"></i>
            <span>Mes projets</span>
          </a>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <main class="cv-main single-column">
      <!-- À propos -->
      <section class="cv-section slide-up">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-user section-icon"></i>
            À propos de moi
          </h2>
        </div>
        <div class="section-content">
          <p>Je travaille depuis 2 ans et demi sur des projets web variés, principalement en PHP et JavaScript. J'ai eu l'occasion de participer à toutes les étapes du développement : conception, intégration et mise en production.</p>
          <p>Je suis motivé par les projets où je peux apprendre de nouvelles technologies et améliorer continuellement mes compétences.</p>
          <p>Mon objectif est de créer des applications web utiles, efficaces et agréables à utiliser, tout en restant ouvert aux nouvelles idées et bonnes pratiques.</p>
        </div>
      </section>

      <!-- Compétences -->
      <section class="cv-section slide-up">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-code section-icon"></i>
            Mes compétences
          </h2>
        </div>
        <div class="section-content">
          <div class="skills-grid">
            <div class="skill-category">
              <h4>Développement</h4>
              <div class="skill-tags">
                <span class="skill-tag">PHP</span>
                <span class="skill-tag">JavaScript</span>
                <span class="skill-tag">HTML5</span>
                <span class="skill-tag">CSS3</span>
                <span class="skill-tag">Frameworks récents</span>
              </div>
            </div>
            <div class="skill-category">
              <h4>Spécialisations</h4>
              <div class="skill-tags">
                <span class="skill-tag">Applications web performantes</span>
                <span class="skill-tag">Intégration responsive</span>
                <span class="skill-tag">Optimisation UX/UI</span>
                <span class="skill-tag">Déploiement efficace</span>
              </div>
            </div>
            <div class="skill-category">
              <h4>Outils</h4>
              <div class="skill-tags">
                <span class="skill-tag">Git</span>
                <span class="skill-tag">Gestion de projets</span>
                <span class="skill-tag">Veille technologique</span>
                <span class="skill-tag">Outils modernes</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

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
