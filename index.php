<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Accueil - Mon site perso</title>
  <link rel="stylesheet" href="style.css" />
  
</head>
<body><button id="theme-toggle">🌙</button>


<header>
  <h1>Bienvenue sur mon site personnel</h1>
  <p>Découvrez mon univers et mon parcours professionnel.</p>
</header>

<nav>
  <a href="index.php">Accueil</a> |
  <a href="cv.php">Mon CV</a>
</nav>

<section>
  <h2>À propos</h2>
  <p>Voici la page d'accueil de mon site personnel. Utilisez la navigation pour accéder à mon CV et découvrir mon expérience.</p>
</section>

</body>
<script>
 const toggleBtn = document.getElementById("theme-toggle");
toggleBtn.addEventListener("click", () => {
  document.documentElement.classList.toggle("light-theme");
  toggleBtn.textContent = document.documentElement.classList.contains("light-theme") ? "☀️" : "🌙";
  });
</script>
</html>