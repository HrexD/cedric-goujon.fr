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
  <title>Accueil - Mon site perso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
 <link rel="icon" type="image/png" href="favicon.png">
  <link rel="stylesheet" href="style.css" />
   
  

</head>

<body>
  <?= generateNavigation('projets') ?>
  <button id="theme-toggle">☀️</button>

  <header>
    <h1>Voici la liste de mes projets</h1>
    <p>Ce n'est qu'un début mais j'aime m'aventurer dans des projets challengeants</p>
  </header>
 
  <section id="github-projects">
    <h2><i class="fab fa-github"></i> Projets GitHub</h2>

    <h3>🛠️ Projets personnels</h3>
    <div id="repos-post" class="repo-grid">
      <div class="repo-card wordpress">
      <h4><a href="https://www.educ-dogs.fr/">Educ Dog's</a></h4>
        <p>Site d'éducation canine pour une amie</p>
        <p><strong>Crée en :</strong> 2025</p>
        <div id="badges-educdogsfr" class="badges">
        <span class="lang-badge">PHP</span>
        <span class="lang-badge">HTML</span>
        <span class="lang-badge">CSS</span>
        </div>
      </div>
    </div>

    <!-- <h3>🎓 Projets de cours</h3> -->
    <!-- <div id="repos-pre" class="repo-grid"></div> -->
  </section>

    <script src="script.js"></script>
    

<footer>
  <div class="footer-content">
    <span>© <script>document.write(new Date().getFullYear())</script> Cédric Goujon. Tous droits réservés.</span>
    <span>Contact: <a href="mailto:<?= $user["email_contact"] ?>">contact</a></span>
    <span>Suivez-moi sur <a href="<?= $user["github"]?>">GitHub</a> et <a href="<?= $user["linkedin"]?>">LinkedIn</a></span>
  </div>
</footer>

   
</body>

</html>
