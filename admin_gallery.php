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
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    .gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
    .card{border:1px solid #e6e6e6;padding:8px;border-radius:8px;background:#fff;position:relative;overflow:hidden}
    .thumb{width:100%;height:140px;object-fit:cover;border-radius:6px;background:#f5f5f5}
    .meta{margin-top:8px;display:flex;justify-content:space-between;align-items:center}
    .file-name{font-size:0.9em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

    /* Croix de suppression flottante en haut à droite, visible uniquement au survol */
    .delete-x{
      position:absolute;top:8px;right:8px;width:32px;height:32px;border-radius:50%;
      background:rgba(0,0,0,0.6);color:#fff;border:none;display:flex;align-items:center;justify-content:center;
      opacity:0;transform:scale(0.95);transition:opacity .12s ease, transform .12s ease;cursor:pointer;font-size:18px;line-height:1;
    }
    .card:hover .delete-x{opacity:1;transform:scale(1)}

    /* bouton alternatif (non utilisé) kept for compatibility */
    .btn-red{background:#dc3545;color:white;padding:6px 8px;border-radius:6px;border:none;cursor:pointer}
  </style>
</head>
<body class="admin-page">
  <div class="admin-layout">
    <?php // sidebar similaire à admin.php - reproduction minimale ?>
    <aside class="admin-sidebar">
      <div class="user-info"><strong>👤 Admin</strong></div>
      <nav>
        <ul class="nav-menu">
          <li><a href="admin">Tableau de bord</a></li>
          <li><a href="admin_messages">Messages</a></li>
          <li><a href="admin_candidatures">Candidatures</a></li>
          <li><a href="admin_projets">Projets</a></li>
          <li><a href="admin_utilisateur">Profil</a></li>
          <li><a href="admin_systeme">Système</a></li>
          <li><a href="admin_gallery">Galerie uploads</a></li>
          <li style="margin-top: 2rem; border-top: 1px solid var(--background); padding-top: 1rem;"><a href="index">Voir le site</a></li>
          <li><a href="?logout=1" style="color: #dc3545;">Déconnexion</a></li>
        </ul>
      </nav>
    </aside>

    <main class="admin-main">
      <div class="admin-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h1>🖼️ Galerie — fichiers uploadés</h1>
            <p>Liste et gestion des images et vidéos déposées via l'interface.</p>
          </div>
          <a href="upload.php" style="background: #28a745; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 500;">
            📤 Uploader des fichiers
          </a>
        </div>
      </div>

      <?php if (!empty($files)): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
          <div style="text-align: center; padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid #007bff;">
            <div style="font-size: 2em; font-weight: bold; color: #007bff;"><?= $totalFiles ?></div>
            <div style="font-size: 0.9em; color: #666;">Total fichiers</div>
          </div>
          <div style="text-align: center; padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid #28a745;">
            <div style="font-size: 2em; font-weight: bold; color: #28a745;"><?= $imageCount ?></div>
            <div style="font-size: 0.9em; color: #666;">📷 Images</div>
          </div>
          <div style="text-align: center; padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid #dc3545;">
            <div style="font-size: 2em; font-weight: bold; color: #dc3545;"><?= $videoCount ?></div>
            <div style="font-size: 0.9em; color: #666;">🎥 Vidéos</div>
          </div>
          <?php if ($otherCount > 0): ?>
          <div style="text-align: center; padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid #ffc107;">
            <div style="font-size: 2em; font-weight: bold; color: #ffc107;"><?= $otherCount ?></div>
            <div style="font-size: 0.9em; color: #666;">📄 Autres</div>
          </div>
          <?php endif; ?>
          <div style="text-align: center; padding: 1rem; background: white; border-radius: 6px; border-left: 4px solid #6c757d;">
            <div style="font-size: 1.2em; font-weight: bold; color: #6c757d;"><?= formatFileSize($totalSize) ?></div>
            <div style="font-size: 0.9em; color: #666;">💾 Taille totale</div>
          </div>
        </div>
      <?php endif; ?>

      <section>
        <?php if (empty($files)): ?>
          <div style="padding:2rem;color:var(--text-muted);">Aucun fichier dans le dossier <code>uploads/</code>.</div>
        <?php else: ?>
          <div class="gallery-grid">
            <?php foreach ($files as $f):
              $full = $uploadDir . DIRECTORY_SEPARATOR . $f;
              $url = 'uploads/' . rawurlencode($f);
              $isImg = @isImage($full);
              $isVid = @isVideo($full);
            ?>
            <div class="card" data-filename="<?= htmlspecialchars($f) ?>">
              <?php if ($isImg): ?>
                <img src="<?= $url ?>" class="thumb" alt="<?= htmlspecialchars($f) ?>">
              <?php elseif ($isVid): ?>
                <video src="<?= $url ?>" class="thumb" controls muted></video>
              <?php else: ?>
                <div class="thumb" style="display:flex;align-items:center;justify-content:center;color:#666">Fichier</div>
              <?php endif; ?>

              <button class="delete-x" data-action="delete" title="Supprimer">×</button>
              <div class="meta">
                <div style="flex:1">
                  <div class="file-name" title="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars($f) ?></div>
                  <div style="font-size:0.8em;color:var(--text-muted);"><?= round(filesize($full)/1024,2) ?> KB • <?= date('d/m H:i', filemtime($full)) ?></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script>
    document.addEventListener('click', async (e)=>{
      const btn = e.target.closest('button[data-action="delete"]');
      if(!btn) return;
      const card = btn.closest('.card');
      const filename = card.getAttribute('data-filename');
      if (!confirm('Supprimer "'+filename+'" ?')) return;

      try{
        const form = new FormData(); form.append('file', filename);
        const res = await fetch('admin_delete_upload.php', {method:'POST', body: form});
        const data = await res.json();
        if(data.success){
          card.remove();
        } else {
          alert('Erreur: '+(data.error||'Erreur inconnue'));
        }
      } catch(err){
        alert('Erreur réseau');
      }
    });
  </script>
</body>
</html>
