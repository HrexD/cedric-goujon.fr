<?php
require 'config.php';
$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Accueil – Cédric Goujon</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body>
  <!-- Bouton thème -->
  <button id="theme-toggle" aria-label="Basculer thème">☀️</button>

  <!-- Navigation -->
  <nav>
    <a id="active" href="index">Accueil</a> |
    <a  href="cv">Mon CV</a> |
    <a href="projets">Mes Projets</a>
</nav>

  <!-- Hero section -->
  <header id="hero">
    <h1>Bonjour, je suis <strong>Cédric Goujon</strong></h1>
    <p>Développeur Full‑Stack avec 2 ans et demi d’expérience, je crée des applications web simples, performantes et agréables à utiliser.</p>
    <p>Passionné par le web moderne, je combine PHP, JavaScript et frameworks récents pour concrétiser vos idées.</p>
    <a href="#contact" class="cta">Travaillons ensemble</a>
  </header>

  <!-- Biographie en deux colonnes -->
  <section id="biography" class="index-biography">
    <div class="bio-container">
      <div class="bio-image">
        <img src="assets/img/moi.jpg" alt="Portrait de Cédric">
      </div>
      <div class="bio-text">
        <h2>À propos de moi</h2>
        <p>Je travaille depuis 2 ans et demi sur des projets web variés, principalement en PHP et JavaScript. J’ai eu l’occasion de participer à toutes les étapes du développement : conception, intégration et mise en production.</p>
        <p>Je suis motivé par les projets où je peux apprendre de nouvelles technologies et améliorer continuellement mes compétences.</p>
        <p>Mon objectif est de créer des applications web utiles, efficaces et agréables à utiliser, tout en restant ouvert aux nouvelles idées et bonnes pratiques.</p>
      </div>
    </div>
  </section>

  <!-- Compétences -->
  <section id="skills" class="index-interests">
    <h2>Compétences</h2>
    <ul>
      <li>💻 Développement Full‑Stack : PHP, JavaScript, HTML5, CSS3, frameworks récents</li>
      <li>⚡ Création d’applications web performantes et fiables</li>
      <li>🛠 Intégration responsive et optimisation UX/UI</li>
      <li>🔧 Git, gestion de projets, déploiement simple et efficace</li>
      <li>🌐 Veille technologique pour rester à jour sur les outils modernes</li>
    </ul>
  </section>

  <!-- Contact -->
  <section id="contact" class="index-contact">
    <h2>Contactez-moi</h2>
    <p>📧 <a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></p>
    <p>🔗 <a href="<?= htmlspecialchars($user['linkedin']) ?>">LinkedIn</a> | <a href="<?= htmlspecialchars($user['github']) ?>">GitHub</a></p>
    <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="cta">Envoyez-moi un message</a>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <span>© <script>document.write(new Date().getFullYear())</script> Cédric Goujon. Tous droits réservés.</span>
      <span>Contact: <a href="mailto:<?= htmlspecialchars($user['email']) ?>">contact</a></span>
      <span>Suivez-moi sur <a href="<?= htmlspecialchars($user['github']) ?>">GitHub</a> et <a href="<?= htmlspecialchars($user['linkedin']) ?>">LinkedIn</a></span>
    </div>
  </footer>

  <!-- Script thème -->
     <script src="script.js"></script>

</body>
</html>
