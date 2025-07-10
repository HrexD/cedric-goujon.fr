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
  <link rel="icon" href="favicon.png" type="image/png">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
  <nav aria-label="Navigation principale">
    <a href="index.php" id="active">Accueil</a>
    <a href="cv.php">Mon CV</a>
    <a href="projets.php">Mes Projets</a>
  </nav>
  <header>
    <h1>Bonjour, je suis <strong>Cédric Goujon</strong></h1>
    <p>Développeur Full‑Stack basé à Paris, diplômé de H3 Hitema.</p>
    <p>Actuellement en poste chez Free, je recherche de nouvelles opportunités en développement web.</p>
  </header>
  <main>
    <section class="about">
      <h2>Ce que j’ai</h2>
      <ul>
        <li>📘 Diplômé H3 Hitema</li>
        <li>🛠️ Expérience chez Free (opérateur télécom)</li>
        <li>💻 Compétences : HTML, CSS, JavaScript, PHP, SQL, frameworks front/back</li>
      </ul>
    </section>
    <section class="looking">
      <h2>Ce que je cherche</h2>
      <p>Je souhaite rejoindre une équipe innovante en tant que <strong>développeur Full‑Stack</strong>, idéalement sur des projets web modernes, en présentiel ou télétravail depuis Paris.</p>
    </section>
    <section class="contact">
      <h2>Contact</h2>
      <p>Email : <a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></p>
    </section>
  </main>
  <footer>
    <p>&copy; <?= date('Y') ?> Cédric Goujon. Tous droits réservés.</p>
  </footer>
  <script src="script.js"></script>
</body>
</html>
