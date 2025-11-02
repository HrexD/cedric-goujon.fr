<?php
// Page d'upload multi-fichiers avec prévisualisation et barres de progression
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>✨ Uploader — photos & vidéos</title>
  <link rel="stylesheet" href="upload-styles.css">
  <link rel="stylesheet" href="upload-animations.css">
</head>
<body>
  <div class="container">
    <h1>✨ Déposer des photos & vidéos</h1>

    <div class="dropzone" id="dropzone">
      <div class="dropzone-text">
        🎯 Glissez-déposez vos fichiers ici, ou
      </div>
      <div id="desktopButtons" style="display:inline;">
        <button id="selectBtn" class="primary">📁 Sélectionner des fichiers</button>
      </div>
      <div id="mobileButtons" style="display:none;">
        <!-- <button id="photoBtn"> 🖼️Prendre Photo</button> -->
        <button id="photoGalleryBtn">📷 Choisir Photo</button>
        <!-- <button id="videoBtn"> 📁Prendre Vidéo</button> -->
        <button id="videoGalleryBtn">🎥 Choisir Vidéo</button>
      </div>
      <input id="fileInput" type="file" multiple accept="image/*,video/*" style="display:none">
      <input id="photoInput" type="file" accept="image/*" capture="environment" style="display:none">
      <input id="videoInput" type="file" accept="video/*" capture="environment" style="display:none">
      <input id="photoGalleryInput" type="file" accept="image/*" multiple style="display:none">
      <input id="videoGalleryInput" type="file" accept="video/*" multiple style="display:none">
      <p style="font-size:0.9em;color:var(--text-secondary);margin-top:16px;line-height:1.4;">
        💡 <strong>Aperçu automatique</strong> → Vérification (prêt) → Upload avec progression en temps réel
      </p>
    </div>

    <div class="files-list" id="filesList"></div>

    <div style="margin-top:24px;text-align:center;">
      <button id="uploadAll" class="primary" style="font-size:1.1em;padding:16px 32px;">
        🚀 Uploader tous les fichiers
      </button>
      <div id="globalProgress" class="global-progress" style="display:none;">
        <div class="progress-text" id="progressText">
          ⏳ Upload en cours...
        </div>
        <div class="global-progress-bar">
          <div id="progressBar" class="global-progress-fill"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="upload-script.js"></script>
</body>
</html>
