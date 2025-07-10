<?php
require'config.php';
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
      <nav>
    <a href="index">Accueil</a> |
    <a href="cv"> Mon CV</a> |
    <a id="active" href="projets"> Mes Projets</a>
</nav>
  <button id="theme-toggle">☀️</button>

  <header>
    <h1>Voici la liste de mes projets</h1>
    <p>Ce n'est qu'un début mais j'aime m'aventurer dans des projets challengeants</p>
  </header>
 
  <section id="github-projects">
    <h2><i class="fab fa-github"></i> Projets GitHub</h2>

    <h3>🛠️ Projets 2025 et après</h3>
    <div id="repos-post" class="repo-grid"></div>

    <h3>🎓 Projets avant 2025</h3>
    <div id="repos-pre" class="repo-grid"></div>
  </section>

    <script src="script.js"></script>
    

<footer>
  <div class="footer-content">
    <span>© <script>document.write(new Date().getFullYear())</script> Cédric Goujon. Tous droits réservés.</span>
    <span>Contact: <a href="mailto:<?= $user["email"] ?>">email</a></span>
    <span>Suivez-nous sur <a href="<?= $user["github"]?>">GitHub</a> et <a href="<?= $user["linkedin"]?>">LinkedIn</a></span>
    <span>Créé avec passion par <?= $user["nom"] ?></span>
    <span>Version: 1.0.0</span>
    <span>Dernière mise à jour: <script>document.write(new Date().toLocaleDateString('fr-FR'))</script></span>
  </div>
</footer>

   
</body>

</html>
