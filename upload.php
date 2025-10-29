<?php
// Page d'upload multi-fichiers avec prévisualisation et barres de progression
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Uploader — photos & vidéos</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:20px;}
    .dropzone{border:2px dashed #ccc;padding:20px;border-radius:8px;text-align:center}
    .files-list{margin-top:16px}
    .file-item{display:flex;gap:12px;align-items:center;padding:8px;border:1px solid #eee;border-radius:6px;margin-bottom:8px}
    .thumb{width:80px;height:60px;object-fit:cover;border-radius:4px;background:#f5f5f5}
    .meta{flex:1}
    .progress-bar{height:8px;background:#eee;border-radius:4px;overflow:hidden;margin-top:6px}
    .progress{height:100%;background:#4caf50;width:0%}
    .readiness{height:6px;background:#ddd;border-radius:3px;overflow:hidden;margin-top:6px}
    .readiness .r{height:100%;background:#2196F3;width:0%}
    .actions{display:flex;gap:8px}
    button{padding:6px 10px;border-radius:4px;border:1px solid #bbb;background:#fff;cursor:pointer}
    button.primary{background:#0078d4;color:#fff;border-color:#0063b1}
    .error-message{background:#fee;color:#c33;padding:8px;border-radius:4px;margin-top:6px;font-size:0.9em}
    .upload-stats{font-size:0.8em;color:#666;margin-top:4px}
    #mobileButtons button{margin:4px;padding:12px 16px;font-size:1em;border-radius:8px;font-weight:500}
    #mobileButtons button:active{transform:scale(0.98);transition:transform 0.1s;}
    #photoBtn, #videoBtn{background:#007bff;color:white;border-color:#0056b3;}
    #photoGalleryBtn, #videoGalleryBtn{background:#28a745;color:white;border-color:#1e7e34;}
    @media (max-width: 768px) {
      #mobileButtons{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:8px 0;}
      #mobileButtons button{margin:0;padding:12px 8px;font-size:0.95em;width:100%;}
      .dropzone{padding:16px;font-size:1.1em;}
      body{margin:12px;}
    }
  </style>
</head>
<body>
  <h1>Déposer des photos & vidéos</h1>

  <div class="dropzone" id="dropzone">
    Glisser-déposer des fichiers ici, ou
    <div id="desktopButtons" style="display:inline;">
      <button id="selectBtn">Sélectionner des fichiers</button>
    </div>
    <div id="mobileButtons" style="display:none;">
      <!-- <button id="photoBtn">📷 Prendre une Photo</button> -->
      <button id="photoGalleryBtn">🖼️ Choisir Photo</button>
      <!-- <button id="videoBtn">🎥 Prendre une Vidéo</button> -->
      <button id="videoGalleryBtn">📁 Choisir Vidéo</button>
    </div>
    <input id="fileInput" type="file" multiple accept="image/*,video/*" style="display:none">
    <input id="photoInput" type="file" accept="image/*" capture="environment" style="display:none">
    <input id="videoInput" type="file" accept="video/*" capture="environment" style="display:none">
    <input id="photoGalleryInput" type="file" accept="image/*" multiple style="display:none">
    <input id="videoGalleryInput" type="file" accept="video/*" multiple style="display:none">
    <p style="font-size:0.9em;color:#666;margin-top:8px">Aperçu, vérification (prêt) avant upload, puis envoi avec progression.</p>
  </div>

  <div class="files-list" id="filesList"></div>

  <div style="margin-top:12px">
    <button id="uploadAll" class="primary">Uploader tous les fichiers (séquentiel)</button>
    <div id="globalProgress" style="display:none; margin-top:8px;">
      <div style="font-size:0.9em; color:#666; margin-bottom:4px;">
        <span id="progressText">Upload en cours...</span>
      </div>
      <div style="height:6px; background:#eee; border-radius:3px; overflow:hidden;">
        <div id="progressBar" style="height:100%; background:#4caf50; width:0%; transition:width 0.3s;"></div>
      </div>
    </div>
  </div>

  <script>
    const fileInput = document.getElementById('fileInput');
    const photoInput = document.getElementById('photoInput');
    const videoInput = document.getElementById('videoInput');
    const photoGalleryInput = document.getElementById('photoGalleryInput');
    const videoGalleryInput = document.getElementById('videoGalleryInput');
    const selectBtn = document.getElementById('selectBtn');
    const photoBtn = document.getElementById('photoBtn');
    const videoBtn = document.getElementById('videoBtn');
    const photoGalleryBtn = document.getElementById('photoGalleryBtn');
    const videoGalleryBtn = document.getElementById('videoGalleryBtn');
    const dropzone = document.getElementById('dropzone');
    const filesList = document.getElementById('filesList');
    const uploadAll = document.getElementById('uploadAll');

    let items = []; // {file, id, readyPct, uploadPct, xhr}
    
    // Limite de taille : 512 Mo
    const MAX_FILE_SIZE = 512 * 1024 * 1024; // 512 Mo en bytes

    // Détection mobile
    function isMobileDevice() {
      return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
             window.innerWidth <= 768;
    }

    // Initialisation de l'interface selon le device
    function initInterface() {
      if (isMobileDevice()) {
        document.getElementById('desktopButtons').style.display = 'none';
        document.getElementById('mobileButtons').style.display = 'block';
        
        // Modifier le texte pour mobile
        dropzone.querySelector('p').textContent = 'Prenez des photos/vidéos ou sélectionnez depuis votre galerie';
      } else {
        document.getElementById('desktopButtons').style.display = 'inline';
        document.getElementById('mobileButtons').style.display = 'none';
      }
    }

    // Appeler l'initialisation
    initInterface();

    // Redimensionnement de fenêtre (pour les tablettes qui changent d'orientation)
    window.addEventListener('resize', initInterface);

    function showError(message) {
      // Créer ou mettre à jour l'élément d'erreur
      let errorDiv = document.getElementById('errorMessages');
      if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'errorMessages';
        errorDiv.className = 'error-message';
        dropzone.parentNode.insertBefore(errorDiv, dropzone);
      }
      
      errorDiv.innerHTML = message;
      errorDiv.style.display = 'block';
      
      // Masquer automatiquement après 5 secondes
      setTimeout(() => {
        errorDiv.style.display = 'none';
      }, 5000);
    }

    function formatBytes(bytes){
      if(bytes===0) return '0 B';
      const k=1024; const sizes=['B','KB','MB','GB'];
      const i=Math.floor(Math.log(bytes)/Math.log(k));
      return parseFloat((bytes/Math.pow(k,i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatTime(seconds) {
      if (seconds < 60) {
        return Math.round(seconds) + 's';
      } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.round(seconds % 60);
        return minutes + 'm ' + secs + 's';
      } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return hours + 'h ' + minutes + 'm';
      }
    }

    // Event listeners pour les boutons
    if (selectBtn) selectBtn.addEventListener('click',()=>fileInput.click());
    if (photoBtn) photoBtn.addEventListener('click',()=>photoInput.click());
    if (videoBtn) videoBtn.addEventListener('click',()=>videoInput.click());
    if (photoGalleryBtn) photoGalleryBtn.addEventListener('click',()=>photoGalleryInput.click());
    if (videoGalleryBtn) videoGalleryBtn.addEventListener('click',()=>videoGalleryInput.click());
    
    fileInput.addEventListener('change', e => handleFiles(e.target.files));
    photoInput.addEventListener('change', e => handleFiles(e.target.files));
    videoInput.addEventListener('change', e => handleFiles(e.target.files));
    photoGalleryInput.addEventListener('change', e => handleFiles(e.target.files));
    videoGalleryInput.addEventListener('change', e => handleFiles(e.target.files));

    ['dragenter','dragover'].forEach(ev=>dropzone.addEventListener(ev,e=>{e.preventDefault();dropzone.style.borderColor='#888'}));
    ['dragleave','drop'].forEach(ev=>dropzone.addEventListener(ev,e=>{e.preventDefault();dropzone.style.borderColor='#ccc'}));
    dropzone.addEventListener('drop', e => {
      if(e.dataTransfer && e.dataTransfer.files) handleFiles(e.dataTransfer.files);
    });

    function handleFiles(fileList){
      for(const f of fileList){
        // Vérification de la taille du fichier
        if(f.size > MAX_FILE_SIZE) {
          showError(`Le fichier "${f.name}" est trop volumineux (${formatBytes(f.size)}). Taille maximale autorisée : ${formatBytes(MAX_FILE_SIZE)}.`);
          continue;
        }
        
        const id = Math.random().toString(36).slice(2,9);
        const item = {file: f, id, readyPct:0, uploadPct:0, xhr:null, startTime:null, uploadSpeed:0};
        items.push(item);
        renderItem(item);
        readFileProgress(item);
      }
    }

    function renderItem(item){
      const f = item.file;
      const div = document.createElement('div');
      div.className = 'file-item';
      div.id = 'item-'+item.id;

      const thumb = document.createElement('div');
      thumb.className = 'thumb';

      const meta = document.createElement('div');
      meta.className = 'meta';
      meta.innerHTML = `<strong>${escapeHtml(f.name)}</strong><br><small>${f.type || 'n/a'} — ${formatBytes(f.size)}</small>`;

      const readiness = document.createElement('div');
      readiness.className = 'readiness';
      readiness.innerHTML = '<div class="r" style="width:0%"></div>';

      const progressBar = document.createElement('div');
      progressBar.className = 'progress-bar';
      progressBar.innerHTML = '<div class="progress" style="width:0%"></div>';

      const uploadStats = document.createElement('div');
      uploadStats.className = 'upload-stats';
      uploadStats.style.display = 'none';

      const actions = document.createElement('div');
      actions.className = 'actions';
      const removeBtn = document.createElement('button'); removeBtn.textContent='Retirer';
      removeBtn.addEventListener('click',()=>{ removeItem(item.id); });

      const uploadBtn = document.createElement('button'); uploadBtn.textContent='Uploader';
      uploadBtn.addEventListener('click',()=>{ uploadItem(item); });

      actions.appendChild(uploadBtn);
      actions.appendChild(removeBtn);

      div.appendChild(thumb);
      div.appendChild(meta);
      meta.appendChild(readiness);
      meta.appendChild(progressBar);
      meta.appendChild(uploadStats);
      div.appendChild(actions);
      filesList.appendChild(div);

      // preview
      if(f.type.startsWith('image/')){
        const img = document.createElement('img'); img.className='thumb'; img.alt=f.name;
        thumb.replaceWith(img);
        // small preview via FileReader after load
        const reader = new FileReader();
        reader.onload = e => img.src = e.target.result;
        reader.readAsDataURL(f);
      } else if(f.type.startsWith('video/')){
        const v = document.createElement('video'); v.className='thumb'; v.muted=true; v.controls=false; v.src='';
        thumb.replaceWith(v);
        const r = new FileReader();
        r.onload = e => v.src = e.target.result;
        r.readAsDataURL(f);
      }
    }

    function updateReadinessUI(item){
      const el = document.getElementById('item-'+item.id);
      if(!el) return;
      const r = el.querySelector('.readiness .r');
      r.style.width = item.readyPct + '%';
    }

    function updateUploadUI(item){
      const el = document.getElementById('item-'+item.id);
      if(!el) return;
      const p = el.querySelector('.progress');
      p.style.width = item.uploadPct + '%';

      // Afficher les statistiques d'upload
      const statsEl = el.querySelector('.upload-stats');
      if(statsEl && item.uploadSpeed && item.uploadPct > 0 && item.uploadPct < 100) {
        const speed = formatBytes(item.uploadSpeed) + '/s';
        const eta = item.estimatedTime ? formatTime(item.estimatedTime) : 'Calcul...';
        statsEl.innerHTML = `Vitesse: ${speed} | ETA: ${eta}`;
        statsEl.style.display = 'block';
      }

      // If upload reached 100%, hide readiness and progress bars and show confirmation
      if (item.uploadPct >= 100) {
        const readiness = el.querySelector('.readiness');
        const progressBar = el.querySelector('.progress-bar');
        const uploadStats = el.querySelector('.upload-stats');
        if (readiness) readiness.style.display = 'none';
        if (progressBar) progressBar.style.display = 'none';
        if (uploadStats) uploadStats.style.display = 'none';

        // show confirmation badge if not already present
        if (!el.querySelector('.upload-confirm')) {
          const meta = el.querySelector('.meta');
          const badge = document.createElement('div');
          badge.className = 'upload-confirm';
          badge.style.marginTop = '8px';
          badge.style.color = '#2e7d32';
          badge.style.fontWeight = '600';
          badge.textContent = '✓ Upload terminé';
          meta.appendChild(badge);
        }

        // disable action buttons
        const actions = el.querySelectorAll('button');
        actions.forEach(b=>{ b.disabled = true; b.style.opacity = '0.6'; });
      }
    }

    function removeItem(id){
      items = items.filter(i=>i.id!==id);
      const el = document.getElementById('item-'+id);
      if(el) el.remove();
    }

    function readFileProgress(item){
      const reader = new FileReader();
      reader.onprogress = e => {
        if(e.lengthComputable){
          item.readyPct = Math.round((e.loaded / e.total) * 100);
          updateReadinessUI(item);
        }
      };
      reader.onloadend = e => {
        item.readyPct = 100; updateReadinessUI(item);
      };
      // We don't need the data here; just trigger read to track readiness
      try{ reader.readAsArrayBuffer(item.file); }catch(err){ item.readyPct=100; updateReadinessUI(item);}    
    }

    function uploadItem(item){
      const form = new FormData();
      form.append('file', item.file);
      const xhr = new XMLHttpRequest();
      item.xhr = xhr;
      item.startTime = Date.now(); // Enregistrer le temps de début
      
      xhr.open('POST','upload_handler.php');
      xhr.upload.addEventListener('progress', e => {
        if(e.lengthComputable){
          item.uploadPct = Math.round((e.loaded/e.total)*100);
          
          // Calculer la vitesse d'upload
          const elapsed = (Date.now() - item.startTime) / 1000; // en secondes
          if(elapsed > 0) {
            item.uploadSpeed = e.loaded / elapsed; // bytes par seconde
            
            // Estimer le temps restant
            const remaining = e.total - e.loaded;
            item.estimatedTime = remaining / item.uploadSpeed; // en secondes
          }
          
          updateUploadUI(item);
        }
      });
      xhr.onreadystatechange = ()=>{
        if(xhr.readyState===4){
          if(xhr.status===200){
            try{ const res = JSON.parse(xhr.responseText); if(res.success){ item.uploadPct=100; updateUploadUI(item); }}catch(e){}
          } else {
            alert('Erreur upload: '+(xhr.statusText||xhr.status));
          }
        }
      };
      xhr.send(form);
    }

    uploadAll.addEventListener('click', ()=>{
      // Upload files sequentially to maximize bandwidth
      uploadSequentially();
    });

    // Function to upload files one by one
    async function uploadSequentially() {
      const pendingItems = items.filter(item => !item.xhr && item.uploadPct < 100);
      if (pendingItems.length === 0) return;

      // Show global progress
      const globalProgress = document.getElementById('globalProgress');
      const progressText = document.getElementById('progressText');
      const progressBar = document.getElementById('progressBar');
      
      globalProgress.style.display = 'block';

      // Disable the upload button during sequential upload
      uploadAll.disabled = true;
      uploadAll.textContent = 'Upload en cours...';

      for (let i = 0; i < pendingItems.length; i++) {
        const item = pendingItems[i];
        const currentFile = i + 1;
        const totalFiles = pendingItems.length;
        
        progressText.textContent = `Upload ${currentFile}/${totalFiles}: ${item.file.name}`;
        
        try {
          await uploadItemSequential(item);
          // Update global progress
          const globalPct = Math.round(((currentFile) / totalFiles) * 100);
          progressBar.style.width = globalPct + '%';
        } catch (error) {
          console.error('Erreur upload:', error);
          progressText.textContent = `Erreur sur ${item.file.name}, passage au suivant...`;
          await new Promise(resolve => setTimeout(resolve, 1000)); // Brief pause
          // Continue with next file even if one fails
        }
      }

      // All done
      progressText.textContent = `✅ Upload terminé ! ${pendingItems.length} fichier(s) traité(s)`;
      setTimeout(() => {
        globalProgress.style.display = 'none';
        uploadAll.disabled = false;
        uploadAll.textContent = 'Uploader tous les fichiers (séquentiel)';
      }, 2000);
    }

    // Promise-based upload function for sequential processing
    function uploadItemSequential(item) {
      return new Promise((resolve, reject) => {
        const form = new FormData();
        form.append('file', item.file);
        const xhr = new XMLHttpRequest();
        item.xhr = xhr;
        
        xhr.open('POST','upload_handler.php');
        xhr.upload.addEventListener('progress', e => {
          if(e.lengthComputable){ 
            item.uploadPct = Math.round((e.loaded/e.total)*100); 
            updateUploadUI(item); 
          }
        });
        
        xhr.onreadystatechange = ()=>{
          if(xhr.readyState===4){
            if(xhr.status===200){
              try{ 
                const res = JSON.parse(xhr.responseText); 
                if(res.success){ 
                  item.uploadPct=100; 
                  updateUploadUI(item); 
                  resolve(res);
                } else {
                  reject(new Error(res.error || 'Upload failed'));
                }
              }catch(e){
                reject(new Error('Invalid response'));
              }
            } else {
              reject(new Error(xhr.statusText||xhr.status));
            }
          }
        };
        
        xhr.send(form);
      });
    }

    // helper
    function escapeHtml(s){ return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]); }
  </script>
</body>
</html>
