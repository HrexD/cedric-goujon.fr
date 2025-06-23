<?php
$data = json_decode(file_get_contents("data.json"), true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>CV de <?= $data["nom"] ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<button id="theme-toggle" title="Changer le thème">🌙 Mode sombre</button>

<header>
  <h1><i class="fas fa-user-circle"></i> <?= $data["nom"] ?></h1>
  <p><strong>Développeur Fullstack Junior – PHP/Symfony & JavaScript/React</strong></p>
  <p><i class="fas fa-location-dot"></i> <?= $data["adresse"] ?></p>
  <p>
    <i class="fas fa-phone"></i> <?= $data["telephone"] ?> · 
    <i class="fas fa-envelope"></i> <a href="mailto:<?= $data["email"] ?>"><?= $data["email"] ?></a>
  </p>
  <p>
    <i class="fas fa-car"></i> <?= $data["permis"] ?> · 
    <i class="fas fa-cake-candles"></i> <?= $data["age"] ?> ans
  </p>
  <p>
    <i class="fab fa-github"></i> <a href="<?= $data["github"] ?>">GitHub</a> · 
    <i class="fab fa-linkedin"></i> <a href="<?= $data["linkedin"] ?>">LinkedIn</a>
  </p>
</header>

<section>
  <h2><i class="fas fa-cogs"></i> Langages connus</h2>
  <ul>
    <?php foreach ($data["competences"] as $c): ?>
      <li><?= $c ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section>
  <h2><i class="fas fa-briefcase"></i> Expériences Professionnelles</h2>
  <?php foreach ($data["experiences"] as $exp): ?>
    <div class="experience">
      <h3><?= $exp["poste"] ?> — <?= $exp["entreprise"] ?></h3>
      <p><em><i class="fas fa-calendar-alt"></i> <?= $exp["periode"] ?></em></p>
      <ul>
        <?php foreach ($exp["missions"] as $m): ?>
          <li><?= $m ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</section>

<section>
  <h2><i class="fas fa-graduation-cap"></i> Formations</h2>
  <?php foreach ($data["formations"] as $f): ?>
    <div class="formation">
      <h3><?= $f["diplome"] ?> — <?= $f["etablissement"] ?></h3>
      <p><em><i class="fas fa-calendar-alt"></i> <?= $f["periode"] ?></em></p>
      <ul>
        <?php foreach ($f["details"] as $d): ?>
          <li><?= $d ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</section>

<section id="github-projects">
  <h2><i class="fab fa-github"></i> Projets GitHub</h2>

  <h3>🛠️ Projets Perso / Pro</h3>
  <div id="repo-perso" class="repo-grid"></div>

  <h3>🎓 Projets de Cours</h3>
  <div id="repo-cours" class="repo-grid"></div>
</section>

<script>
  const username = "HrexD";
  const persoContainer = document.getElementById("repo-perso");
  const coursContainer = document.getElementById("repo-cours");

  fetch(`https://api.github.com/users/${username}/repos`)
    .then(res => res.json())
    .then(repos => {
      repos
        .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
        .forEach(repo => {
          const year = new Date(repo.created_at).getFullYear();
          const isCourse = year < 2025;

          const card = document.createElement("div");
          card.className = "repo-card";
          card.innerHTML = `
            <h4><a href="${repo.html_url}" target="_blank">${repo.name}</a></h4>
            <p>${repo.description || "Aucune description fournie."}</p>
            <a class="repo-link" href="${repo.html_url}" target="_blank">Voir sur GitHub</a>
          `;

          (isCourse ? coursContainer : persoContainer).appendChild(card);
        });
    });
</script>




<section>
  <h2><i class="fas fa-language"></i> Langues</h2>
  <ul>
    <li><i class="fas fa-flag-usa"></i> Anglais : <?= $data["langues"]["anglais"] ?></li>
    <li><i class="fas fa-flag"></i> Allemand : <?= $data["langues"]["allemand"] ?></li>
  </ul>
</section>

<section>
  <h2><i class="fas fa-heart"></i> Centres d’intérêts</h2>
  <ul>
    <?php foreach ($data["interets"] as $i): ?>
      <li><?= $i ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<script>
  const toggleBtn = document.getElementById("theme-toggle");
  const body = document.body;

  function updateLabel() {
    toggleBtn.textContent = body.classList.contains("light-theme") ? "☀️ Mode clair" : "🌙 Mode sombre";
  }

  toggleBtn.addEventListener("click", () => {
    body.classList.toggle("light-theme");
    updateLabel();
  });

  updateLabel();
</script>

</body>
</html>
