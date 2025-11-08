// Script principal pour l'interface d'upload moderne
// Gestion des fichiers, compression, progression et upload séquentiel

// === INITIALISATION DES ÉLÉMENTS ===
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

// === VARIABLES GLOBALES ===
let items = []; // {file, id, readyPct, uploadPct, xhr}

// Limite de taille : 512 Mo
const MAX_FILE_SIZE = 512 * 1024 * 1024;

// === GESTION DE L'ÉTAT ===
function saveUploadState() {
  const state = items.map(item => ({
    id: item.id,
    name: item.file.name,
    size: item.file.size,
    readyPct: item.readyPct,
    uploadPct: item.uploadPct,
    completed: item.uploadPct >= 100
  }));
  localStorage.setItem('uploadState', JSON.stringify(state));
}

function restoreUploadState() {
  const saved = localStorage.getItem('uploadState');
  if (saved) {
    try {
      const state = JSON.parse(saved);
      state.forEach(itemState => {
        if (!itemState.completed) {
          showNotification(`Upload en cours restauré: ${itemState.name} (${itemState.uploadPct}%)`);
        }
      });
    } catch (e) {
      console.error('Erreur restauration état:', e);
    }
  }
}

// === NOTIFICATIONS MODERNES ===
function showNotification(message, type = 'info') {
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  
  document.body.appendChild(toast);
  
  // Animer l'entrée
  setTimeout(() => toast.classList.add('show'), 100);
  
  // Retirer automatiquement
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 3000);
}

// === DÉTECTION MOBILE ===
function isMobileDevice() {
  return window.innerWidth <= 768;
}

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

// === GESTION DES ERREURS ===
function showError(message) {
  let errorDiv = document.getElementById('errorMessages');
  if (!errorDiv) {
    errorDiv = document.createElement('div');
    errorDiv.id = 'errorMessages';
    errorDiv.className = 'error-message';
    
    // Vérifier que dropzone et son parent existent
    if (dropzone && dropzone.parentNode) {
      dropzone.parentNode.insertBefore(errorDiv, dropzone);
    } else {
      console.warn('Dropzone ou son parent non trouvé, ajout de l\'erreur au body');
      document.body.appendChild(errorDiv);
    }
  }
  
  errorDiv.innerHTML = message;
  errorDiv.style.display = 'block';
  
  // Masquer automatiquement après 5 secondes
  setTimeout(() => {
    errorDiv.style.display = 'none';
  }, 5000);
}

// === UTILITAIRES ===
function formatBytes(bytes) {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

function escapeHtml(s) {
  return s.replace(/[&<>"']/g, c => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": "&#39;"
  })[c]);
}

// === EVENT LISTENERS ===
// Boutons de sélection
if (selectBtn) selectBtn.addEventListener('click', () => fileInput.click());
if (photoBtn) photoBtn.addEventListener('click', () => photoInput.click());
if (videoBtn) videoBtn.addEventListener('click', () => videoInput.click());
if (photoGalleryBtn) photoGalleryBtn.addEventListener('click', () => photoGalleryInput.click());
if (videoGalleryBtn) videoGalleryBtn.addEventListener('click', () => videoGalleryInput.click());

// Inputs de fichiers
fileInput.addEventListener('change', e => handleFiles(e.target.files));
photoInput.addEventListener('change', e => handleFiles(e.target.files));
videoInput.addEventListener('change', e => handleFiles(e.target.files));
photoGalleryInput.addEventListener('change', e => handleFiles(e.target.files));
videoGalleryInput.addEventListener('change', e => handleFiles(e.target.files));

// Drag & drop avec effets visuels
['dragenter', 'dragover'].forEach(ev => dropzone.addEventListener(ev, e => {
  e.preventDefault();
  dropzone.classList.add('drag-over');
}));

['dragleave', 'drop'].forEach(ev => dropzone.addEventListener(ev, e => {
  e.preventDefault();
  dropzone.classList.remove('drag-over');
}));

dropzone.addEventListener('drop', e => {
  if (e.dataTransfer && e.dataTransfer.files) handleFiles(e.dataTransfer.files);
});

// Redimensionnement
window.addEventListener('resize', initInterface);

// === GESTION DES FICHIERS ===
function handleFiles(fileList) {
  let addedCount = 0;
  
  for (const f of fileList) {
    // Vérification de la taille
    if (f.size > MAX_FILE_SIZE) {
      showError(`Le fichier "${f.name}" est trop volumineux (${formatBytes(f.size)}). Taille maximale autorisée : ${formatBytes(MAX_FILE_SIZE)}.`);
      continue;
    }
    
    const id = Math.random().toString(36).slice(2, 9);
    const item = {
      file: f,
      id,
      readyPct: 0,
      uploadPct: 0,
      xhr: null,
      startTime: null,
      uploadSpeed: 0,
      originalFile: f
    };
    
    items.push(item);
    renderItem(item);
    addedCount++;
    
    // Compression pour les grandes images
    if (f.type.startsWith('image/') && f.size > 5 * 1024 * 1024) {
      compressImageSimple(item);
    } else {
      readFileProgress(item);
    }
  }
  
  // Notification de succès
  if (addedCount > 0) {
    const fileText = addedCount === 1 ? 'fichier ajouté' : 'fichiers ajoutés';
    showNotification(`✅ ${addedCount} ${fileText} avec succès !`, 'success');
  }
}

// === COMPRESSION D'IMAGES ===
async function compressImageSimple(item) {
  try {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = () => {
      canvas.width = img.width;
      canvas.height = img.height;
      ctx.drawImage(img, 0, 0);
      
      // Compression avec qualité réduite
      canvas.toBlob(blob => {
        if (blob && blob.size < item.originalFile.size * 0.8) {
          const compressedFile = new File([blob], item.originalFile.name, {
            type: 'image/jpeg',
            lastModified: item.originalFile.lastModified
          });
          item.file = compressedFile;
        }
        readFileProgress(item);
      }, 'image/jpeg', 0.8);
    };
    
    img.onerror = () => readFileProgress(item);
    img.src = URL.createObjectURL(item.originalFile);
  } catch (err) {
    readFileProgress(item);
  }
}

// === RENDU DE L'INTERFACE ===
function renderItem(item) {
  const f = item.file;
  const div = document.createElement('div');
  div.className = 'file-item new';
  div.id = 'item-' + item.id;

  const thumb = document.createElement('div');
  thumb.className = 'thumb';

  const meta = document.createElement('div');
  meta.className = 'meta';
  
  // Icône selon le type
  const fileIcon = f.type.startsWith('image/') ? '🖼️' : f.type.startsWith('video/') ? '🎥' : '📄';
  
  meta.innerHTML = `
    <div class="file-name">${fileIcon} ${escapeHtml(f.name)}</div>
    <div class="file-details">${f.type || 'Type inconnu'} • ${formatBytes(f.size)}</div>
  `;

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
  actions.style.display = 'flex';
  actions.style.gap = '8px';
  
  const removeBtn = document.createElement('button'); 
  removeBtn.innerHTML = '🗑️ Retirer';
  removeBtn.className = 'secondary';
  removeBtn.addEventListener('click', () => removeItem(item.id));

  const uploadBtn = document.createElement('button'); 
  uploadBtn.innerHTML = '⬆️ Uploader';
  uploadBtn.className = 'primary';
  uploadBtn.addEventListener('click', () => uploadItem(item));

  actions.appendChild(uploadBtn);
  actions.appendChild(removeBtn);

  div.appendChild(thumb);
  div.appendChild(meta);
  meta.appendChild(readiness);
  meta.appendChild(progressBar);
  meta.appendChild(uploadStats);
  div.appendChild(actions);
  filesList.appendChild(div);

  // Prévisualisation
  if (f.type.startsWith('image/')) {
    const img = document.createElement('img');
    img.className = 'thumb';
    img.alt = f.name;
    thumb.replaceWith(img);
    
    const reader = new FileReader();
    reader.onload = e => img.src = e.target.result;
    reader.readAsDataURL(f);
  } else if (f.type.startsWith('video/')) {
    const v = document.createElement('video');
    v.className = 'thumb';
    v.muted = true;
    v.controls = false;
    thumb.replaceWith(v);
    
    const r = new FileReader();
    r.onload = e => v.src = e.target.result;
    r.readAsDataURL(f);
  }
}

// === MISE À JOUR DE L'INTERFACE ===
function updateReadinessUI(item) {
  const el = document.getElementById('item-' + item.id);
  if (!el) return;
  const r = el.querySelector('.readiness .r');
  r.style.width = item.readyPct + '%';
}

function updateUploadUI(item) {
  const el = document.getElementById('item-' + item.id);
  if (!el) return;
  const p = el.querySelector('.progress');
  p.style.width = item.uploadPct + '%';

  saveUploadState();

  // Statistiques d'upload
  const statsEl = el.querySelector('.upload-stats');
  if (statsEl && item.uploadSpeed && item.uploadPct > 0 && item.uploadPct < 100) {
    const speed = formatBytes(item.uploadSpeed) + '/s';
    const eta = item.estimatedTime ? formatTime(item.estimatedTime) : 'Calcul...';
    statsEl.innerHTML = `Vitesse: ${speed} | ETA: ${eta}`;
    statsEl.style.display = 'block';
  }

  // Upload terminé
  if (item.uploadPct >= 100) {
    const readiness = el.querySelector('.readiness');
    const progressBar = el.querySelector('.progress-bar');
    const uploadStats = el.querySelector('.upload-stats');
    
    if (readiness) readiness.style.display = 'none';
    if (progressBar) progressBar.style.display = 'none';
    if (uploadStats) uploadStats.style.display = 'none';

    showNotification(`✓ Upload terminé: ${item.file.name}`, 'success');

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

    // Désactiver les boutons
    const actions = el.querySelectorAll('button');
    actions.forEach(b => {
      b.disabled = true;
      b.style.opacity = '0.6';
    });
  }
}

function removeItem(id) {
  items = items.filter(i => i.id !== id);
  const el = document.getElementById('item-' + id);
  if (el) el.remove();
}

function readFileProgress(item) {
  const reader = new FileReader();
  reader.onprogress = e => {
    if (e.lengthComputable) {
      item.readyPct = Math.round((e.loaded / e.total) * 100);
      updateReadinessUI(item);
    }
  };
  reader.onloadend = e => {
    item.readyPct = 100;
    updateReadinessUI(item);
  };
  
  try {
    reader.readAsArrayBuffer(item.file);
  } catch (err) {
    item.readyPct = 100;
    updateReadinessUI(item);
  }
}

// === UPLOAD DES FICHIERS ===
function uploadItem(item) {
  const form = new FormData();
  form.append('file', item.file);
  const xhr = new XMLHttpRequest();
  item.xhr = xhr;
  item.startTime = Date.now();
  
  xhr.open('POST', 'upload_handler.php');
  xhr.upload.addEventListener('progress', e => {
    if (e.lengthComputable) {
      item.uploadPct = Math.round((e.loaded / e.total) * 100);
      
      // Calcul de la vitesse
      const elapsed = (Date.now() - item.startTime) / 1000;
      if (elapsed > 0) {
        item.uploadSpeed = e.loaded / elapsed;
        const remaining = e.total - e.loaded;
        item.estimatedTime = remaining / item.uploadSpeed;
      }
      
      updateUploadUI(item);
    }
  });
  
  xhr.onreadystatechange = () => {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.success) {
            item.uploadPct = 100;
            updateUploadUI(item);
          }
        } catch (e) {}
      } else {
        alert('Erreur upload: ' + (xhr.statusText || xhr.status));
      }
    }
  };
  
  xhr.send(form);
}

// === UPLOAD SÉQUENTIEL ===
uploadAll.addEventListener('click', () => {
  uploadSequentially();
});

async function uploadSequentially() {
  const pendingItems = items.filter(item => !item.xhr && item.uploadPct < 100);
  if (pendingItems.length === 0) return;

  const globalProgress = document.getElementById('globalProgress');
  const progressText = document.getElementById('progressText');
  const progressBar = document.getElementById('progressBar');
  
  globalProgress.style.display = 'block';
  uploadAll.disabled = true;
  uploadAll.textContent = 'Upload en cours...';

  for (let i = 0; i < pendingItems.length; i++) {
    const item = pendingItems[i];
    const currentFile = i + 1;
    const totalFiles = pendingItems.length;
    
    progressText.textContent = `Upload ${currentFile}/${totalFiles}: ${item.file.name}`;
    
    try {
      await uploadItemSequential(item);
      const globalPct = Math.round((currentFile / totalFiles) * 100);
      progressBar.style.width = globalPct + '%';
    } catch (error) {
      console.error('Erreur upload:', error);
      progressText.textContent = `Erreur sur ${item.file.name}, passage au suivant...`;
      await new Promise(resolve => setTimeout(resolve, 1000));
    }
  }

  progressText.textContent = `✅ Upload terminé ! ${pendingItems.length} fichier(s) traité(s)`;
  setTimeout(() => {
    globalProgress.style.display = 'none';
    uploadAll.disabled = false;
    uploadAll.textContent = '🚀 Uploader tous les fichiers';
  }, 2000);
}

function uploadItemSequential(item) {
  return new Promise((resolve, reject) => {
    const form = new FormData();
    form.append('file', item.file);
    const xhr = new XMLHttpRequest();
    item.xhr = xhr;
    
    xhr.open('POST', 'upload_handler.php');
    xhr.upload.addEventListener('progress', e => {
      if (e.lengthComputable) {
        item.uploadPct = Math.round((e.loaded / e.total) * 100);
        updateUploadUI(item);
      }
    });
    
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          try {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
              item.uploadPct = 100;
              updateUploadUI(item);
              resolve(res);
            } else {
              reject(new Error(res.error || 'Upload failed'));
            }
          } catch (e) {
            reject(new Error('Invalid response'));
          }
        } else {
          reject(new Error(xhr.statusText || xhr.status));
        }
      }
    };
    
    xhr.send(form);
  });
}

// === INITIALISATION ===
// Appeler l'initialisation au chargement
initInterface();