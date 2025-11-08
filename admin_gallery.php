<?php
require 'auth_helper.php';

// Vérifier que l'admin est connecté
if (!isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$files = array_values(array_filter(scandir($uploadDir), function($f){
    return $f !== '.' && $f !== '..';
}));

// trier par date modification descendante
usort($files, function($a,$b) use ($uploadDir){
    return filemtime($uploadDir . DIRECTORY_SEPARATOR . $b) - filemtime($uploadDir . DIRECTORY_SEPARATOR . $a);
});

function isImage($path){
    $m = mime_content_type($path);
    return strpos($m, 'image/') === 0;
}

function isVideo($path){
    $m = mime_content_type($path);
    return strpos($m, 'video/') === 0;
}

// Calculer les statistiques des fichiers
$totalFiles = count($files);
$imageCount = 0;
$videoCount = 0;
$otherCount = 0;
$totalSize = 0;

foreach ($files as $f) {
    $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $f;
    if (file_exists($fullPath)) {
        $totalSize += filesize($fullPath);
        
        if (@isImage($fullPath)) {
            $imageCount++;
        } elseif (@isVideo($fullPath)) {
            $videoCount++;
        } else {
            $otherCount++;
        }
    }
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 B';
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — Galerie uploads</title>
  <link rel="stylesheet" href="admin-modern.css">
  <link rel="stylesheet" href="gallery-styles.css">
</head>
<body class="admin-page">
  <div class="admin-layout">
    <!-- Hamburger Menu Button (Mobile) -->
    <button class="admin-hamburger" id="adminHamburger" aria-label="Toggle Menu">
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
    </button>
    
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="user-info">
            <strong>👤 Admin</strong>
            <div style="font-size: 0.8em; opacity: 0.8; margin-top: 0.5rem;">
                Interface d'administration
            </div>
        </div>
        
        <nav>
            <ul class="nav-menu">
                <li><a href="admin">📊 Tableau de bord</a></li>
                <li><a href="admin_candidatures.php">💼 Candidatures</a></li>
                <li><a href="admin_messages.php">📧 Messages</a></li>
                <li><a href="admin_projets.php">🚀 Projets</a></li>
                <li><a href="admin_gallery.php" class="active">🖼️ Galerie</a></li>
                <li><a href="admin_utilisateur.php">👤 Utilisateur</a></li>
                <li><a href="admin_systeme.php">⚙️ Système</a></li>
                <li style="margin-top: var(--spacing-xl); border-top: 1px solid var(--border-color); padding-top: var(--spacing-lg);">
                    <a href="index">🌐 Voir le site</a>
                </li>
                <li><a href="?logout=1" style="color: var(--danger-color);">🚪 Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->

    <!-- Main Content -->
    <main class="admin-main">
      <div class="gallery-container">
        <div class="gallery-header">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <h1 class="gallery-title">🖼️ Galerie — fichiers uploadés</h1>
              <p>Liste et gestion des images et vidéos déposées via l'interface.</p>
            </div>
            <div style="display: flex; gap: 1rem;">
              <a href="admin_logs.php" style="background: var(--primary-color); color: white; padding: var(--spacing-sm) var(--spacing-md); border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; transition: all var(--transition-fast);">
                📊 Logs d'upload
              </a>
              <a href="upload.php" style="background: var(--success-color); color: white; padding: var(--spacing-sm) var(--spacing-md); border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; transition: all var(--transition-fast);">
                📤 Uploader des fichiers
              </a>
            </div>
          </div>
        </div>

        <?php if (!empty($files)): ?>
          <div class="gallery-stats">
            <div class="stat-card images">
              <div class="stat-value"><?= $totalFiles ?></div>
              <div class="stat-label">Total fichiers</div>
            </div>
            <div class="stat-card images">
              <div class="stat-value"><?= $imageCount ?></div>
              <div class="stat-label">📷 Images</div>
            </div>
            <div class="stat-card videos">
              <div class="stat-value"><?= $videoCount ?></div>
              <div class="stat-label">🎥 Vidéos</div>
            </div>
            <?php if ($otherCount > 0): ?>
            <div class="stat-card others">
              <div class="stat-value"><?= $otherCount ?></div>
              <div class="stat-label">📄 Autres</div>
            </div>
            <?php endif; ?>
            <div class="stat-card size">
              <div class="stat-value"><?= formatFileSize($totalSize) ?></div>
              <div class="stat-label">💾 Taille totale</div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Filtres et contrôles -->
        <div class="gallery-filters">
          <div class="filter-group">
            <span class="filter-label">🔍 Rechercher :</span>
            <input type="text" id="searchInput" class="filter-input" placeholder="Nom de fichier...">
          </div>
          <div class="filter-group">
            <span class="filter-label">Type :</span>
            <select id="typeFilter" class="filter-select">
              <option value="all">Tous les fichiers</option>
              <option value="image">📷 Images</option>
              <option value="video">🎥 Vidéos</option>
              <option value="other">📄 Autres</option>
            </select>
          </div>
          <div class="filter-group">
            <span class="filter-label">Taille :</span>
            <select id="sizeFilter" class="filter-select">
              <option value="all">Toutes tailles</option>
              <option value="small">< 1 MB</option>
              <option value="medium">1-10 MB</option>
              <option value="large">10-100 MB</option>
              <option value="xlarge">> 100 MB</option>
            </select>
          </div>
          <button id="clearFilters" class="clear-filters">Effacer filtres</button>
          <div style="margin-left:auto;display:flex;gap:var(--spacing-sm);">
            <button id="selectAllBtn" class="filter-btn">Tout sélectionner</button>
            <button id="deselectAllBtn" class="filter-btn">Tout désélectionner</button>
          </div>
        </div>

        <section>
          <?php if (empty($files)): ?>
            <div style="padding:2rem;color:var(--gray-500);text-align:center;">
              <div style="font-size:3rem;margin-bottom:1rem;">📁</div>
              <div>Aucun fichier dans le dossier <code>uploads/</code></div>
            </div>
          <?php else: ?>
            <div class="gallery-grid">
              <?php foreach ($files as $f):
                $full = $uploadDir . DIRECTORY_SEPARATOR . $f;
                $url = 'uploads/' . rawurlencode($f);
                $isImg = @isImage($full);
                $isVid = @isVideo($full);
                $fileType = $isImg ? 'image' : ($isVid ? 'video' : 'other');
              ?>
              <div class="gallery-card" data-filename="<?= htmlspecialchars($f) ?>" 
                   data-type="<?= $fileType ?>"
                   data-size="<?= filesize($full) ?>"
                   data-date="<?= filemtime($full) ?>">
                
                <input type="checkbox" class="card-checkbox" data-filename="<?= htmlspecialchars($f) ?>">
                
                <div class="card-media">
                  <?php if ($isImg): ?>
                    <img src="<?= $url ?>" class="card-thumbnail" alt="<?= htmlspecialchars($f) ?>">
                  <?php elseif ($isVid): ?>
                    <video src="<?= $url ?>" class="card-thumbnail" muted></video>
                  <?php else: ?>
                    <div class="card-thumbnail" style="display:flex;align-items:center;justify-content:center;color:var(--gray-600);font-size:3rem;">📄</div>
                  <?php endif; ?>
                  
                  <div class="card-overlay"></div>
                  
                  <div class="card-actions">
                    <button class="action-btn view" data-action="view" title="Visualiser">👁️</button>
                    <button class="action-btn download" data-action="download" title="Télécharger">📥</button>
                    <button class="action-btn delete" data-action="delete" title="Supprimer">🗑️</button>
                  </div>
                </div>
                
                <div class="card-content">
                  <h3 class="card-title" title="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></h3>
                  <div class="card-meta">
                    <span class="file-type-badge <?= $fileType ?>"><?= strtoupper($fileType) ?></span>
                    <span><?= formatFileSize(filesize($full)) ?> • <?= date('d/m H:i', filemtime($full)) ?></span>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </main>
  </div>

  <!-- Contrôles de sélection multiple -->
  <div id="selectionControls" class="selection-controls">
    <span class="selection-counter">0 fichier(s) sélectionné(s)</span>
    <div class="bulk-actions">
      <button onclick="bulkDownload()" class="bulk-btn download">📥 Télécharger sélection</button>
      <button onclick="bulkDelete()" class="bulk-btn delete">🗑️ Supprimer sélection</button>
      <button onclick="clearSelection()" class="bulk-btn cancel">Annuler</button>
    </div>
  </div>

  <script src="gallery-script.js"></script>
  <script src="admin-modern.js"></script>
</body>
</html>
