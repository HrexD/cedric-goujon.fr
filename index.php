<?php
require 'config.php';

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
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CV de <?= $user["nom"] ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<button id="theme-toggle">🌙</button>

<header>
  <h1><?= $user["nom"] ?></h1>
  <p>Développeur Fullstack Junior – PHP/Symfony & JavaScript/React</p>
  <p><?= $user["adresse"] ?> · <?= $user["telephone"] ?> · <a href="mailto:<?= $user["email"] ?>"><?= $user["email"] ?></a></p>
  <p><?= $user["permis"] ?> · <?= $user["age"] ?> ans</p>
  <p><a href="<?= $user["github"] ?>">GitHub</a> · <a href="<?= $user["linkedin"] ?>">LinkedIn</a></p>
</header>

<section>
  <h2>Langues</h2>
  <ul>
    <?php foreach ($langues as $l): ?>
      <li><?= ucfirst($l['langue']) ?> : <?= $l['niveau'] ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section>
  <h2>Compétences techniques</h2>
  <?php
    $grouped = ['langage' => [], 'bdd' => [], 'application' => []];
    foreach ($technos as $t) {
      $grouped[$t['type']][] = $t['nom'];
    }
  ?>
  <?php foreach ($grouped as $type => $items): ?>
    <h3><?= ucfirst($type) ?>s</h3>
    <ul>
      <?php foreach ($items as $i): ?>
        <li><?= $i ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>
</section>

<section>
  <h2>Expériences professionnelles</h2>
  <div class="timeline">
    <?php
      $currentId = null;
      $missionsBuffer = [];

      foreach ($experiences as $exp) {
        if ($exp['id'] !== $currentId) {
          if ($currentId !== null) {
            echo '<ul>';
            foreach ($missionsBuffer as $m) {
              $isSub = str_starts_with(trim($m), '-');
              $class = $isSub ? ' class="subpoint"' : '';
              $text = $isSub ? ltrim($m, '- ') : $m;
              echo "<li{$class}>" . htmlspecialchars($text) . "</li>";
            }
            echo '</ul></div>';
          }

          $currentId = $exp['id'];
          $missionsBuffer = [];
          echo "<div class='timeline-item'><h3>{$exp['poste']} — {$exp['entreprise']}</h3><p><em>{$exp['periode']}</em></p>";
        }

        if (!empty($exp['mission'])) {
          $missionsBuffer[] = $exp['mission'];
        }
      }

      if ($currentId !== null) {
        echo '<ul>';
        foreach ($missionsBuffer as $m) {
          $isSub = str_starts_with(trim($m), '-');
          $class = $isSub ? ' class="subpoint"' : '';
          $text = $isSub ? ltrim($m, '- ') : $m;
          echo "<li{$class}>" . htmlspecialchars($text) . "</li>";
        }
        echo '</ul></div>';
      }
    ?>
  </div>
</section>


<section>
  <h2>Formations</h2>
  <div class="timeline">
    <?php
      $currentId = null;
      foreach ($formations as $f) {
        if ($f['id'] !== $currentId) {
          if ($currentId !== null) echo "</ul></div>";
          $currentId = $f['id'];
          echo "<div class='timeline-item'><h3>{$f['diplome']} — {$f['etablissement']}</h3><p><em>{$f['periode']}</em></p><ul>";
        }
        if ($f['detail']) echo "<li>{$f['detail']}</li>";
      }
      if ($currentId !== null) echo "</ul></div>";
    ?>
  </div>
</section>

<section id="github-projects">
  <h2><i class="fab fa-github"></i> Projets GitHub</h2>

  <h3>🛠️ Projets 2025 et après</h3>
  <div id="repos-post" class="repo-grid"></div>

  <h3>🎓 Projets avant 2025</h3>
  <div id="repos-pre" class="repo-grid"></div>
</section>

<section>
  <h2>Soft Skills</h2>
  <ul>
    <?php foreach ($softskills as $s): ?>
      <li><?= $s['skill'] ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section>
  <h2>Centres d'intérêt</h2>
  <ul>
    <?php foreach ($interets as $i): ?>
      <li><?= $i['interet'] ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<script>
  const toggleBtn = document.getElementById("theme-toggle");
  toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("light-theme");
    toggleBtn.textContent = document.body.classList.contains("light-theme") ? "☀️" : "🌙";
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

</body>
</html>
