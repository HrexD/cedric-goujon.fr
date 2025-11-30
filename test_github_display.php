<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Test GitHub Repositories</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="github-repos.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0;
      padding: 20px;
      min-height: 100vh;
    }
    
    .test-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .test-header {
      text-align: center;
      color: white;
      margin-bottom: 40px;
    }
    
    .test-header h1 {
      margin: 0;
      font-size: 2.5em;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .test-header p {
      margin: 10px 0;
      opacity: 0.9;
    }
    
    .github-status {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
      color: white;
    }
    
    .status-success { border-left: 4px solid #28a745; }
    .status-error { border-left: 4px solid #dc3545; }
    .status-warning { border-left: 4px solid #ffc107; }
  </style>
</head>
<body>
  <div class="test-container">
    <div class="test-header">
      <h1><i class="fab fa-github"></i> Test GitHub API</h1>
      <p>Démonstration de l'intégration des repositories GitHub</p>
    </div>

    <?php
    // URL GitHub configurée
    $githubUrl = "https://github.com/HrexD";
    
    // Fonction pour récupérer les repositories GitHub
    function getGitHubRepos($githubUrl) {
        if (preg_match('/github\.com\/([^\/?]+)/', $githubUrl, $matches)) {
            $username = $matches[1];
            
            $apiUrl = "https://api.github.com/users/{$username}/repos?sort=updated&direction=desc&per_page=20";
            
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
                $repos = json_decode($response, true);
                
                if (is_array($repos)) {
                    $filteredRepos = array_filter($repos, function($repo) {
                        return !$repo['fork'] && !$repo['archived'];
                    });
                    
                    usort($filteredRepos, function($a, $b) {
                        return strtotime($b['updated_at']) - strtotime($a['updated_at']);
                    });
                    
                    return array_slice($filteredRepos, 0, 12);
                }
            }
        }
        
        return [];
    }
    
    // Récupérer les repositories
    $githubRepos = getGitHubRepos($githubUrl);
    ?>

    <!-- Status Section -->
    <div class="github-status <?= !empty($githubRepos) ? 'status-success' : 'status-error' ?>">
      <h3><i class="fas fa-<?= !empty($githubRepos) ? 'check-circle' : 'exclamation-triangle' ?>"></i> 
          Status de l'API GitHub</h3>
      <?php if (!empty($githubRepos)): ?>
        <p><strong>✅ Connexion réussie !</strong></p>
        <p>📊 <?= count($githubRepos) ?> repositories publics récupérés</p>
        <p>🔗 Utilisateur: <strong>HrexD</strong></p>
        <p>🕒 Dernière mise à jour: <?= date('d/m/Y H:i:s') ?></p>
      <?php else: ?>
        <p><strong>❌ Erreur de connexion</strong></p>
        <p>Impossible de récupérer les repositories GitHub.</p>
        <p>Vérifiez votre connexion internet et réessayez.</p>
      <?php endif; ?>
    </div>

    <!-- Repositories Section -->
    <?php if (!empty($githubRepos)): ?>
    <div class="project-category">
      <h2 style="color: white; text-align: center; margin-bottom: 30px;">
        <i class="fab fa-github"></i> Mes Repositories GitHub
        <span style="opacity: 0.7; font-size: 0.8em;">(<?= count($githubRepos) ?> projets)</span>
      </h2>
      
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
                <span class="visibility-badge public">Public</span>
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
              <button class="project-link github-link" data-url="<?= htmlspecialchars($repo['html_url']) ?>">
                <i class="fab fa-github"></i>
                <span>Voir le code</span>
              </button>
              <?php if ($repo['homepage']): ?>
                <button class="project-link demo github-link" data-url="<?= htmlspecialchars($repo['homepage']) ?>">
                  <i class="fas fa-external-link-alt"></i>
                  <span>Demo</span>
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 40px;">
      <a href="projets.php" style="color: white; text-decoration: none; padding: 12px 24px; background: rgba(255,255,255,0.2); border-radius: 25px; backdrop-filter: blur(10px);">
        <i class="fas fa-arrow-left"></i> Retour à la page projets
      </a>
    </div>
  </div>

  <script>
    // Debug et correction pour les boutons GitHub
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page chargée - initialisation des boutons GitHub');
        
        // Gestionnaire global pour les boutons GitHub
        document.addEventListener('click', function(e) {
            // Vérifier si l'élément cliqué est un bouton GitHub ou ses enfants
            const button = e.target.closest('.github-link');
            if (button && button.dataset.url) {
                e.preventDefault();
                e.stopPropagation();
                
                const url = button.dataset.url;
                console.log('Clic sur bouton GitHub détecté:', url);
                
                // Ouvrir dans un nouvel onglet
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        });
        
        // Styles supplémentaires pour les boutons
        const style = document.createElement('style');
        style.textContent = `
            .github-link {
                border: none !important;
                background: white !important;
                cursor: pointer !important;
                display: inline-flex !important;
                align-items: center !important;
                text-decoration: none !important;
            }
            
            .github-link:hover {
                background: #f6f8fa !important;
                transform: translateY(-1px) !important;
            }
            
            .github-link.demo {
                background: linear-gradient(135deg, #28a745, #34ce57) !important;
                color: white !important;
            }
            
            .github-link.demo:hover {
                background: linear-gradient(135deg, #218838, #28a745) !important;
            }
        `;
        document.head.appendChild(style);
        
        // Test de cliquabilité
        console.log('Configuration terminée. Test de cliquabilité...');
    });
  </script>
</body>
</html>