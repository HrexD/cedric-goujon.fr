// =======================
// GALLERY MANAGEMENT SYSTEM
// =======================

class GalleryManager {
  constructor() {
    this.selectedFiles = new Set();
    this.currentViewFile = null;
    this.allFiles = [];
    this.filteredFiles = [];
    this.currentFilter = { search: '', type: 'all', size: 'all' };
    
    this.init();
  }

  init() {
    this.bindElements();
    this.bindEvents();
    this.loadFiles();
    this.initModal();
  }

  bindElements() {
    // Filtres
    this.searchInput = document.getElementById('searchInput');
    this.typeFilter = document.getElementById('typeFilter');
    this.sizeFilter = document.getElementById('sizeFilter');
    this.clearFiltersBtn = document.getElementById('clearFilters');
    this.selectAllBtn = document.getElementById('selectAllBtn');
    this.deselectAllBtn = document.getElementById('deselectAllBtn');
    
    // Grille et cartes
    this.galleryGrid = document.querySelector('.gallery-grid');
    
    // Contrôles de sélection
    this.selectionControls = document.getElementById('selectionControls');
    this.selectionCounter = this.selectionControls?.querySelector('.selection-counter');
    
    // Modal
    this.modal = document.getElementById('viewModal');
    this.modalContent = this.modal?.querySelector('.modal-media');
    this.modalTitle = this.modal?.querySelector('.modal-title');
    this.modalInfo = this.modal?.querySelector('.modal-info');
    this.modalClose = this.modal?.querySelector('.modal-close');
    this.modalPrev = this.modal?.querySelector('.modal-nav.prev');
    this.modalNext = this.modal?.querySelector('.modal-nav.next');
  }

  bindEvents() {
    // Filtres avec debounce
    let filterTimeout;
    this.searchInput?.addEventListener('input', () => {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(() => this.applyFilters(), 300);
    });
    
    this.typeFilter?.addEventListener('change', () => this.applyFilters());
    this.sizeFilter?.addEventListener('change', () => this.applyFilters());
    this.clearFiltersBtn?.addEventListener('click', () => this.clearFilters());
    
    // Sélection
    this.selectAllBtn?.addEventListener('click', () => this.selectAll());
    this.deselectAllBtn?.addEventListener('click', () => this.clearSelection());
    
    // Événements délégués sur la grille
    this.galleryGrid?.addEventListener('click', (e) => this.handleGridClick(e));
    
    // Modal
    this.modalClose?.addEventListener('click', () => this.closeModal());
    this.modalPrev?.addEventListener('click', () => this.navigateModal(-1));
    this.modalNext?.addEventListener('click', () => this.navigateModal(1));
    
    // Fermer modal avec Échap
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.modal?.classList.contains('show')) {
        this.closeModal();
      }
      if (this.modal?.classList.contains('show')) {
        if (e.key === 'ArrowLeft') this.navigateModal(-1);
        if (e.key === 'ArrowRight') this.navigateModal(1);
      }
    });
    
    // Fermer modal en cliquant sur l'arrière-plan
    this.modal?.addEventListener('click', (e) => {
      if (e.target === this.modal) this.closeModal();
    });
  }

  loadFiles() {
    // Récupérer tous les fichiers depuis les cartes existantes
    const cards = document.querySelectorAll('.gallery-card');
    this.allFiles = Array.from(cards).map(card => ({
      filename: card.dataset.filename,
      type: card.dataset.type,
      size: parseInt(card.dataset.size),
      date: parseInt(card.dataset.date),
      element: card
    }));
    this.filteredFiles = [...this.allFiles];
  }

  handleGridClick(e) {
    const card = e.target.closest('.gallery-card');
    if (!card) return;

    // Checkbox
    if (e.target.classList.contains('card-checkbox')) {
      e.stopPropagation();
      this.toggleSelection(card.dataset.filename);
      return;
    }

    // Boutons d'action
    const actionBtn = e.target.closest('.action-btn');
    if (actionBtn) {
      e.stopPropagation();
      const action = actionBtn.dataset.action;
      const filename = card.dataset.filename;
      
      switch (action) {
        case 'view':
          this.openModal(filename);
          break;
        case 'download':
          this.downloadFile(filename);
          break;
        case 'delete':
          this.deleteFile(filename);
          break;
      }
      return;
    }

    // Clic sur la carte = ouvrir en modal
    this.openModal(card.dataset.filename);
  }

  // =======================
  // GESTION DES FILTRES
  // =======================

  applyFilters() {
    const search = this.searchInput?.value.toLowerCase() || '';
    const type = this.typeFilter?.value || 'all';
    const size = this.sizeFilter?.value || 'all';
    
    this.currentFilter = { search, type, size };
    
    this.filteredFiles = this.allFiles.filter(file => {
      // Filtre recherche
      const matchesSearch = file.filename.toLowerCase().includes(search);
      
      // Filtre type
      const matchesType = type === 'all' || file.type === type;
      
      // Filtre taille
      let matchesSize = true;
      if (size === 'small') matchesSize = file.size < 1024 * 1024;
      else if (size === 'medium') matchesSize = file.size >= 1024 * 1024 && file.size < 10 * 1024 * 1024;
      else if (size === 'large') matchesSize = file.size >= 10 * 1024 * 1024 && file.size < 100 * 1024 * 1024;
      else if (size === 'xlarge') matchesSize = file.size >= 100 * 1024 * 1024;
      
      return matchesSearch && matchesType && matchesSize;
    });

    this.updateGridDisplay();
  }

  updateGridDisplay() {
    this.allFiles.forEach(file => {
      const isVisible = this.filteredFiles.includes(file);
      file.element.style.display = isVisible ? 'block' : 'none';
    });
  }

  clearFilters() {
    if (this.searchInput) this.searchInput.value = '';
    if (this.typeFilter) this.typeFilter.value = 'all';
    if (this.sizeFilter) this.sizeFilter.value = 'all';
    this.applyFilters();
  }

  // =======================
  // GESTION DE LA SELECTION
  // =======================

  toggleSelection(filename) {
    const card = document.querySelector(`[data-filename="${CSS.escape(filename)}"]`);
    const checkbox = card?.querySelector('.card-checkbox');
    
    if (this.selectedFiles.has(filename)) {
      this.selectedFiles.delete(filename);
      card?.classList.remove('selected');
      if (checkbox) checkbox.checked = false;
    } else {
      this.selectedFiles.add(filename);
      card?.classList.add('selected');
      if (checkbox) checkbox.checked = true;
    }
    
    this.updateSelectionUI();
  }

  selectAll() {
    this.filteredFiles.forEach(file => {
      if (file.element.style.display !== 'none') {
        this.selectedFiles.add(file.filename);
        file.element.classList.add('selected');
        const checkbox = file.element.querySelector('.card-checkbox');
        if (checkbox) checkbox.checked = true;
      }
    });
    this.updateSelectionUI();
  }

  clearSelection() {
    this.selectedFiles.clear();
    document.querySelectorAll('.gallery-card').forEach(card => {
      card.classList.remove('selected');
      const checkbox = card.querySelector('.card-checkbox');
      if (checkbox) checkbox.checked = false;
    });
    this.updateSelectionUI();
  }

  updateSelectionUI() {
    const count = this.selectedFiles.size;
    
    if (this.selectionCounter) {
      this.selectionCounter.textContent = `${count} fichier(s) sélectionné(s)`;
    }
    
    if (this.selectionControls) {
      if (count > 0) {
        this.selectionControls.classList.add('show');
      } else {
        this.selectionControls.classList.remove('show');
      }
    }
  }

  // =======================
  // MODAL DE VISIONNAGE
  // =======================

  initModal() {
    if (!this.modal) {
      this.createModal();
    }
  }

  createModal() {
    const modalHTML = `
      <div id="viewModal" class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title">Visualisation</h3>
            <button class="modal-close">&times;</button>
          </div>
          <div class="modal-media">
            <!-- Le contenu sera injecté dynamiquement -->
          </div>
          <button class="modal-nav prev">&lsaquo;</button>
          <button class="modal-nav next">&rsaquo;</button>
          <div class="modal-footer">
            <div class="modal-info">
              <!-- Informations du fichier -->
            </div>
            <div class="modal-actions">
              <button class="modal-btn download" onclick="gallery.downloadCurrentFile()">
                📥 Télécharger
              </button>
              <button class="modal-btn delete" onclick="gallery.deleteCurrentFile()">
                🗑️ Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    this.bindElements();
    this.bindEvents();
  }

  openModal(filename) {
    const file = this.allFiles.find(f => f.filename === filename);
    if (!file) return;
    
    this.currentViewFile = file;
    
    // Mettre à jour le titre
    if (this.modalTitle) {
      this.modalTitle.textContent = filename;
    }
    
    // Créer le contenu média
    const mediaURL = 'uploads/' + encodeURIComponent(filename);
    let mediaElement = '';
    
    if (file.type === 'image') {
      mediaElement = `<img src="${mediaURL}" alt="${filename}" />`;
    } else if (file.type === 'video') {
      mediaElement = `<video src="${mediaURL}" controls></video>`;
    } else {
      mediaElement = `
        <div style="padding: 2rem; text-align: center; color: white;">
          <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
          <div>Aperçu non disponible pour ce type de fichier</div>
          <div style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.7;">${filename}</div>
        </div>
      `;
    }
    
    if (this.modalContent) {
      this.modalContent.innerHTML = mediaElement;
    }
    
    // Mettre à jour les informations
    if (this.modalInfo) {
      this.modalInfo.textContent = `${this.formatFileSize(file.size)} • ${this.formatDate(file.date)}`;
    }
    
    // Afficher la modal
    if (this.modal) {
      this.modal.classList.add('show');
      document.body.style.overflow = 'hidden';
    }
  }

  closeModal() {
    if (this.modal) {
      this.modal.classList.remove('show');
      document.body.style.overflow = '';
    }
    this.currentViewFile = null;
  }

  navigateModal(direction) {
    if (!this.currentViewFile) return;
    
    const currentIndex = this.filteredFiles.findIndex(f => f.filename === this.currentViewFile.filename);
    if (currentIndex === -1) return;
    
    let newIndex = currentIndex + direction;
    if (newIndex < 0) newIndex = this.filteredFiles.length - 1;
    if (newIndex >= this.filteredFiles.length) newIndex = 0;
    
    const newFile = this.filteredFiles[newIndex];
    this.openModal(newFile.filename);
  }

  downloadCurrentFile() {
    if (this.currentViewFile) {
      this.downloadFile(this.currentViewFile.filename);
    }
  }

  deleteCurrentFile() {
    if (this.currentViewFile) {
      this.deleteFile(this.currentViewFile.filename);
      this.closeModal();
    }
  }

  // =======================
  // ACTIONS SUR LES FICHIERS
  // =======================

  async downloadFile(filename) {
    try {
      const url = 'uploads/' + encodeURIComponent(filename);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      this.showToast(`📥 Téléchargement de "${filename}" démarré`, 'success');
    } catch (error) {
      this.showToast('❌ Erreur lors du téléchargement', 'error');
    }
  }

  async downloadSelected() {
    if (this.selectedFiles.size === 0) {
      this.showToast('Aucun fichier sélectionné', 'warning');
      return;
    }

    if (this.selectedFiles.size === 1) {
      const filename = Array.from(this.selectedFiles)[0];
      await this.downloadFile(filename);
      return;
    }

    // Téléchargement multiple - créer un ZIP côté serveur
    try {
      this.showToast(`📦 Préparation du ZIP avec ${this.selectedFiles.size} fichiers...`, 'info');
      
      const formData = new FormData();
      this.selectedFiles.forEach(filename => {
        formData.append('files[]', filename);
      });
      
      const response = await fetch('admin_download_zip.php', {
        method: 'POST',
        body: formData
      });
      
      if (response.ok) {
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `gallery_selection_${Date.now()}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        
        this.showToast(`📥 ZIP avec ${this.selectedFiles.size} fichiers téléchargé`, 'success');
      } else {
        throw new Error('Erreur serveur');
      }
    } catch (error) {
      this.showToast('❌ Erreur lors de la création du ZIP', 'error');
      
      // Fallback: télécharger un par un
      for (const filename of this.selectedFiles) {
        await this.downloadFile(filename);
        await new Promise(resolve => setTimeout(resolve, 500)); // Pause entre téléchargements
      }
    }
  }

  async deleteFile(filename) {
    if (!confirm(`Supprimer "${filename}" ?`)) return;
    
    try {
      const formData = new FormData();
      formData.append('file', filename);
      
      const response = await fetch('admin_delete_upload.php', {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Supprimer de la DOM
        const card = document.querySelector(`[data-filename="${CSS.escape(filename)}"]`);
        if (card) {
          card.style.transition = 'all 0.3s ease-out';
          card.style.transform = 'scale(0)';
          card.style.opacity = '0';
          setTimeout(() => card.remove(), 300);
        }
        
        // Supprimer des données
        this.allFiles = this.allFiles.filter(f => f.filename !== filename);
        this.filteredFiles = this.filteredFiles.filter(f => f.filename !== filename);
        this.selectedFiles.delete(filename);
        this.updateSelectionUI();
        
        this.showToast(`🗑️ "${filename}" supprimé`, 'success');
      } else {
        throw new Error(data.error || 'Erreur inconnue');
      }
    } catch (error) {
      this.showToast(`❌ Erreur: ${error.message}`, 'error');
    }
  }

  async bulkDelete() {
    if (this.selectedFiles.size === 0) return;
    
    if (!confirm(`Supprimer ${this.selectedFiles.size} fichier(s) sélectionné(s) ?`)) return;
    
    const promises = Array.from(this.selectedFiles).map(async filename => {
      const formData = new FormData();
      formData.append('file', filename);
      
      try {
        const response = await fetch('admin_delete_upload.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();
        
        if (data.success) {
          const card = document.querySelector(`[data-filename="${CSS.escape(filename)}"]`);
          if (card) {
            card.style.transition = 'all 0.3s ease-out';
            card.style.transform = 'scale(0)';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
          }
          this.selectedFiles.delete(filename);
        }
        
        return { filename, success: data.success, error: data.error };
      } catch (error) {
        return { filename, success: false, error: error.message };
      }
    });
    
    try {
      const results = await Promise.all(promises);
      const successful = results.filter(r => r.success).length;
      const failed = results.filter(r => !r.success).length;
      
      // Mettre à jour les données
      this.allFiles = this.allFiles.filter(f => !this.selectedFiles.has(f.filename));
      this.filteredFiles = this.filteredFiles.filter(f => !this.selectedFiles.has(f.filename));
      this.selectedFiles.clear();
      this.updateSelectionUI();
      
      if (failed === 0) {
        this.showToast(`🗑️ ${successful} fichier(s) supprimé(s)`, 'success');
      } else {
        this.showToast(`⚠️ ${successful} supprimé(s), ${failed} échec(s)`, 'warning');
      }
    } catch (error) {
      this.showToast('❌ Erreur lors de la suppression groupée', 'error');
    }
  }

  // =======================
  // NOTIFICATIONS
  // =======================

  showToast(message, type = 'info', duration = 4000) {
    // Créer le conteneur s'il n'existe pas
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    
    // Icônes par type
    const icons = {
      success: '✅',
      error: '❌',
      warning: '⚠️',
      info: 'ℹ️'
    };
    
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <span class="toast-message">${message}</span>
      <button class="toast-close">&times;</button>
    `;
    
    // Ajouter au conteneur
    container.appendChild(toast);
    
    // Animer l'entrée
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Fermeture manuelle
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => this.removeToast(toast));
    
    // Fermeture automatique
    setTimeout(() => this.removeToast(toast), duration);
  }

  removeToast(toast) {
    toast.classList.remove('show');
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }

  // =======================
  // UTILITAIRES
  // =======================

  formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  formatDate(timestamp) {
    return new Date(timestamp * 1000).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
}

// =======================
// FONCTIONS GLOBALES POUR COMPATIBILITÉ
// =======================

// Initialiser le gestionnaire de galerie
let gallery = null;

document.addEventListener('DOMContentLoaded', () => {
  gallery = new GalleryManager();
});

// Fonctions exposées pour les boutons
function bulkDelete() {
  gallery?.bulkDelete();
}

function bulkDownload() {
  gallery?.downloadSelected();
}

function clearSelection() {
  gallery?.clearSelection();
}

// Export pour utilisation externe
if (typeof module !== 'undefined' && module.exports) {
  module.exports = GalleryManager;
}