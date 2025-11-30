<?php
// Charger les données depuis data.json si la base n'est pas accessible
try {
    require'config.php';
    require 'auth_helper.php';
    $user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
    $userid = $user['id'];
} catch (Exception $e) {
    // Fallback vers data.json
    $dataJson = file_get_contents('data.json');
    $data = json_decode($dataJson, true);
    $user = [
        'id' => 1,
        'github' => $data['github'] ?? '',
        'nom' => $data['nom'] ?? ''
    ];
    $userid = 1;
}

// Fonction pour récupérer les repositories GitHub
function getGitHubRepos($githubUrl) {
    // Extraire le nom d'utilisateur de l'URL GitHub
    if (preg_match('/github\.com\/([^\/?]+)/', $githubUrl, $matches)) {
        $username = $matches[1];
        
        // Cache simple pour éviter trop de requêtes API
        $cacheFile = sys_get_temp_dir() . "/github_repos_{$username}.json";
        $cacheTime = 3600; // 1 heure
        
        // Vérifier si le cache est valide
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
            $cachedData = file_get_contents($cacheFile);
            return json_decode($cachedData, true);
        }
        
        $apiUrl = "https://api.github.com/users/{$username}/repos?sort=updated&direction=desc&per_page=20";
        
        // Configuration du contexte pour l'API GitHub
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Portfolio-Website/1.0',
                    'Accept: application/vnd.github.v3+json'
                ],
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response !== false) {
            // Filtrer les repositories pour exclure les forks et garder seulement les plus intéressants
            $repos = json_decode($response, true);
            
            if (is_array($repos)) {
                // Filtrer et trier les repositories
                $filteredRepos = array_filter($repos, function($repo) {
                    return !$repo['fork'] && !$repo['archived']; // Exclure les forks et archives
                });
                
                // Trier par dernière mise à jour
                usort($filteredRepos, function($a, $b) {
                    return strtotime($b['updated_at']) - strtotime($a['updated_at']);
                });
                
                // Limiter à 12 repositories
                $filteredRepos = array_slice($filteredRepos, 0, 12);
                
                // Mettre en cache
                file_put_contents($cacheFile, json_encode($filteredRepos));
                
                return $filteredRepos;
            }
        }
    }
    
    return [];
}

// Récupérer les repositories
$githubRepos = [];
if (!empty($user['github'])) {
    $githubRepos = getGitHubRepos($user['github']);
}
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
  <link rel="stylesheet" href="github-repos.css">
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
            <?php if (!empty($githubRepos)): ?>
            <div class="hero-detail">
              <i class="fab fa-github"></i>
              <span><?= count($githubRepos) ?> repositories publics</span>
            </div>
            <?php endif; ?>
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
          
          <?php if (!empty($githubRepos)): ?>
          <div class="project-category">
            <h3 class="category-title">
              <i class="fab fa-github"></i>
              Repositories GitHub
              <span class="category-count"><?= count($githubRepos) ?> projets</span>
            </h3>
            
            <div class="projects-grid github-repos">
              <?php foreach ($githubRepos as $repo): ?>
                <div class="project-card github-repo" data-repo-id="<?= $repo['id'] ?>">
                  <div class="project-header">
                    <div class="project-icon">
                      <i class="fab fa-git-alt"></i>
                    </div>
                    <div class="project-meta">
                      <h4>
                        <a href="<?= htmlspecialchars($repo['html_url']) ?>" target="_blank" rel="noopener">
                          <?= htmlspecialchars($repo['name']) ?>
                        </a>
                      </h4>
                      <span class="project-year"><?= date('Y', strtotime($repo['created_at'])) ?></span>
                      <?php if (!$repo['private']): ?>
                        <span class="visibility-badge public">Public</span>
                      <?php else: ?>
                        <span class="visibility-badge private">Privé</span>
                      <?php endif; ?>
                    </div>
                    <div class="repo-stats">
                      <?php if ($repo['stargazers_count'] > 0): ?>
                        <span class="stat">
                          <i class="fas fa-star"></i>
                          <?= $repo['stargazers_count'] ?>
                        </span>
                      <?php endif; ?>
                      <?php if ($repo['forks_count'] > 0): ?>
                        <span class="stat">
                          <i class="fas fa-code-branch"></i>
                          <?= $repo['forks_count'] ?>
                        </span>
                      <?php endif; ?>
                      <span class="stat">
                        <i class="fas fa-file-code"></i>
                        <?= number_format($repo['size']) ?>kb
                      </span>
                    </div>
                  </div>
                  
                  <div class="project-content">
                    <p><?= $repo['description'] ? htmlspecialchars($repo['description']) : 'Aucune description disponible.' ?></p>
                    
                    <div class="project-technologies">
                      <?php if ($repo['language']): ?>
                        <span class="tech-tag primary-lang"><?= htmlspecialchars($repo['language']) ?></span>
                      <?php endif; ?>
                      <?php if (isset($repo['topics']) && !empty($repo['topics'])): ?>
                        <?php foreach (array_slice($repo['topics'], 0, 3) as $topic): ?>
                          <span class="tech-tag topic"><?= htmlspecialchars($topic) ?></span>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                    
                    <div class="repo-dates">
                      <span class="date-info">
                        <i class="fas fa-calendar-alt"></i>
                        Créé le <?= date('d/m/Y', strtotime($repo['created_at'])) ?>
                      </span>
                      <span class="date-info">
                        <i class="fas fa-clock"></i>
                        Mis à jour <?= date('d/m/Y', strtotime($repo['updated_at'])) ?>
                      </span>
                    </div>
                  </div>
                  
                  <div class="project-actions">
                    <a href="<?= htmlspecialchars($repo['html_url']) ?>" class="project-link" target="_blank" rel="noopener">
                      <i class="fab fa-github"></i>
                      <span>Voir le code</span>
                    </a>
                    <?php if ($repo['homepage']): ?>
                      <a href="<?= htmlspecialchars($repo['homepage']) ?>" class="project-link demo" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Demo</span>
                      </a>
                    <?php endif; ?>
                    <!-- <?php if ($repo['has_downloads']): ?>
                      <a href="<?= htmlspecialchars($repo['html_url']) ?>/releases" class="project-link download" target="_blank" rel="noopener">
                        <i class="fas fa-download"></i>
                        <span>Télécharger</span>
                      </a>
                    <?php endif; ?> -->
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php elseif (isset($user['github']) && !empty($user['github'])): ?>
          <div class="project-category">
            <h3 class="category-title">
              <i class="fab fa-github"></i>
              Repositories GitHub
            </h3>
            
            <div class="github-error">
              <i class="fas fa-exclamation-triangle"></i>
              <p><strong>Impossible de charger les repositories GitHub</strong></p>
              <p>Veuillez réessayer plus tard ou consultez directement <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank">mon profil GitHub</a>.</p>
            </div>
          </div>
          <?php endif; ?>
          
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
