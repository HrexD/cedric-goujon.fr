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
  <p>Voici la page d'accueil de mon site personnel./p>
</section>
<section id="github-projects">
  <h2><i class="fab fa-github"></i> Projets GitHub</h2>

  <h3>🛠️ Projets 2025 et après</h3>
  <div id="repos-post" class="repo-grid"></div>

  <h3>🎓 Projets avant 2025</h3>
  <div id="repos-pre" class="repo-grid"></div>
</section>

</body>
<script>
 const toggleBtn = document.getElementById("theme-toggle");
toggleBtn.addEventListener("click", () => {
  document.documentElement.classList.toggle("light-theme");
  toggleBtn.textContent = document.documentElement.classList.contains("light-theme") ? "☀️" : "🌙";
  });
  
  const username = "HrexD";
  const postContainer = document.getElementById("repos-post");
  const preContainer = document.getElementById("repos-pre");

  fetch(`https://api.github.com/users/${username}/repos`)
    .then(res => res.json())
    .then(repos => {
      repos
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .forEach(repo => {
          const year = new Date(repo.created_at).getFullYear();
          const isPost2025 = year >= 2025;

          const card = document.createElement("div");
          card.className = "repo-card";
          card.innerHTML = `
            <h4><a href="${repo.html_url}" target="_blank">${repo.name}</a></h4>
            <p>${repo.description || "Aucune description fournie."}</p>
            <p><strong>Créé en :</strong> ${year}</p>
          <!--  <a class="repo-link" href="${repo.html_url}" target="_blank">Voir sur GitHub</a> -->
          `;

          (isPost2025 ? postContainer : preContainer).appendChild(card);
        });
    });
</script>
</html>