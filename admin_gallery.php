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
    
    /* Filtres */
    .filters{display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
    .filter-group{display:flex;gap:8px;align-items:center}
    .filter-select{padding:8px 12px;border:1px solid #ddd;border-radius:6px;background:white}
    .search-input{padding:8px 12px;border:1px solid #ddd;border-radius:6px;min-width:200px}
    .filter-btn{padding:8px 16px;border:1px solid #007bff;background:white;color:#007bff;border-radius:6px;cursor:pointer;transition:all 0.2s}
    .filter-btn.active{background:#007bff;color:white}
    .clear-filters{background:#6c757d;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer}
    
    /* Sélection multiple */
    .selection-controls{display:none;position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:white;padding:12px 20px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);border:1px solid #ddd;z-index:1000}
    .selection-counter{font-weight:600;color:#007bff;margin-right:12px}
    .bulk-actions{display:flex;gap:8px}
    .card.selected{border-color:#007bff;box-shadow:0 0 0 2px rgba(0,123,255,0.25)}
    .card-checkbox{position:absolute;top:8px;left:8px;width:20px;height:20px;cursor:pointer}
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

      <!-- Filtres et contrôles -->
      <div class="filters">
        <div class="filter-group">
          <label>🔍 Rechercher :</label>
          <input type="text" id="searchInput" class="search-input" placeholder="Nom de fichier...">
        </div>
        <div class="filter-group">
          <label>Type :</label>
          <select id="typeFilter" class="filter-select">
            <option value="all">Tous les fichiers</option>
            <option value="image">📷 Images</option>
            <option value="video">🎥 Vidéos</option>
            <option value="other">📄 Autres</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Taille :</label>
          <select id="sizeFilter" class="filter-select">
            <option value="all">Toutes tailles</option>
            <option value="small">< 1 MB</option>
            <option value="medium">1-10 MB</option>
            <option value="large">10-100 MB</option>
            <option value="xlarge">> 100 MB</option>
          </select>
        </div>
        <button id="clearFilters" class="clear-filters">Effacer filtres</button>
        <div style="margin-left:auto;display:flex;gap:8px;">
          <button id="selectAllBtn" class="filter-btn">Tout sélectionner</button>
          <button id="deselectAllBtn" class="filter-btn">Tout désélectionner</button>
        </div>
      </div>

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
            <div class="card" data-filename="<?= htmlspecialchars($f) ?>" 
                 data-type="<?= $isImg ? 'image' : ($isVid ? 'video' : 'other') ?>"
                 data-size="<?= filesize($full) ?>"
                 data-date="<?= filemtime($full) ?>">
              <input type="checkbox" class="card-checkbox" data-filename="<?= htmlspecialchars($f) ?>">
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
                  <div style="font-size:0.8em;color:var(--text-muted);"><?= formatFileSize(filesize($full)) ?> • <?= date('d/m H:i', filemtime($full)) ?></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <!-- Contrôles de sélection multiple -->
  <div id="selectionControls" class="selection-controls">
    <span class="selection-counter">0 fichier(s) sélectionné(s)</span>
    <div class="bulk-actions">
      <button onclick="bulkDelete()" style="background:#dc3545;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer">🗑️ Supprimer sélection</button>
      <button onclick="clearSelection()" style="background:#6c757d;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer">Annuler</button>
    </div>
  </div>

  <script>
    let selectedFiles = new Set();

    // Filtres
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const sizeFilter = document.getElementById('sizeFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');

    function filterCards() {
      const searchTerm = searchInput.value.toLowerCase();
      const typeValue = typeFilter.value;
      const sizeValue = sizeFilter.value;
      const cards = document.querySelectorAll('.card');

      cards.forEach(card => {
        const filename = card.dataset.filename.toLowerCase();
        const type = card.dataset.type;
        const size = parseInt(card.dataset.size);

        // Filtre recherche
        const matchesSearch = filename.includes(searchTerm);

        // Filtre type
        const matchesType = typeValue === 'all' || type === typeValue;

        // Filtre taille
        let matchesSize = true;
        if (sizeValue === 'small') matchesSize = size < 1024 * 1024;
        else if (sizeValue === 'medium') matchesSize = size >= 1024 * 1024 && size < 10 * 1024 * 1024;
        else if (sizeValue === 'large') matchesSize = size >= 10 * 1024 * 1024 && size < 100 * 1024 * 1024;
        else if (sizeValue === 'xlarge') matchesSize = size >= 100 * 1024 * 1024;

        card.style.display = (matchesSearch && matchesType && matchesSize) ? 'block' : 'none';
      });
    }

    // Event listeners pour les filtres (simplifiés avec debounce)
    let filterTimeout;
    searchInput.addEventListener('input', () => {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(filterCards, 300); // Délai pour éviter les appels excessifs
    });
    typeFilter.addEventListener('change', filterCards);
    sizeFilter.addEventListener('change', filterCards);
    clearFiltersBtn.addEventListener('click', () => {
      searchInput.value = '';
      typeFilter.value = 'all';
      sizeFilter.value = 'all';
      filterCards();
    });

    // Sélection multiple
    function updateSelectionUI() {
      const selectionControls = document.getElementById('selectionControls');
      const counter = selectionControls.querySelector('.selection-counter');
      const count = selectedFiles.size;
      
      counter.textContent = `${count} fichier(s) sélectionné(s)`;
      selectionControls.style.display = count > 0 ? 'block' : 'none';
    }

    function toggleCardSelection(filename) {
      const card = document.querySelector(`[data-filename="${CSS.escape(filename)}"]`);
      if (selectedFiles.has(filename)) {
        selectedFiles.delete(filename);
        card.classList.remove('selected');
        card.querySelector('.card-checkbox').checked = false;
      } else {
        selectedFiles.add(filename);
        card.classList.add('selected');
        card.querySelector('.card-checkbox').checked = true;
      }
      updateSelectionUI();
    }

    function selectAll() {
      const visibleCards = document.querySelectorAll('.card[style*="block"], .card:not([style*="none"])');
      visibleCards.forEach(card => {
        if (card.style.display !== 'none') {
          const filename = card.dataset.filename;
          selectedFiles.add(filename);
          card.classList.add('selected');
          card.querySelector('.card-checkbox').checked = true;
        }
      });
      updateSelectionUI();
    }

    function clearSelection() {
      selectedFiles.clear();
      document.querySelectorAll('.card').forEach(card => {
        card.classList.remove('selected');
        card.querySelector('.card-checkbox').checked = false;
      });
      updateSelectionUI();
    }

    async function bulkDelete() {
      if (selectedFiles.size === 0) return;
      
      if (!confirm(`Supprimer ${selectedFiles.size} fichier(s) sélectionné(s) ?`)) return;

      const promises = Array.from(selectedFiles).map(async filename => {
        const form = new FormData();
        form.append('file', filename);
        const res = await fetch('admin_delete_upload.php', {method: 'POST', body: form});
        const data = await res.json();
        if (data.success) {
          const card = document.querySelector(`[data-filename="${CSS.escape(filename)}"]`);
          if (card) card.remove();
          selectedFiles.delete(filename);
        }
        return data;
      });

      try {
        await Promise.all(promises);
        updateSelectionUI();
      } catch (err) {
        alert('Erreur lors de la suppression groupée');
      }
    }

    selectAllBtn.addEventListener('click', selectAll);
    deselectAllBtn.addEventListener('click', clearSelection);

    // Event listeners pour checkboxes et suppression individuelle
    document.addEventListener('click', async (e) => {
      // Checkbox selection
      if (e.target.classList.contains('card-checkbox')) {
        e.stopPropagation();
        const filename = e.target.dataset.filename;
        toggleCardSelection(filename);
        return;
      }

      // Suppression individuelle
      const btn = e.target.closest('button[data-action="delete"]');
      if (!btn) return;
      
      const card = btn.closest('.card');
      const filename = card.getAttribute('data-filename');
      if (!confirm('Supprimer "' + filename + '" ?')) return;

      try {
        const form = new FormData();
        form.append('file', filename);
        const res = await fetch('admin_delete_upload.php', {method: 'POST', body: form});
        const data = await res.json();
        if (data.success) {
          card.remove();
          selectedFiles.delete(filename);
          updateSelectionUI();
        } else {
          alert('Erreur: ' + (data.error || 'Erreur inconnue'));
        }
      } catch (err) {
        alert('Erreur réseau');
      }
    });
  </script>
</body>
</html>
