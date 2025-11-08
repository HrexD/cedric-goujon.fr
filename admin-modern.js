// =======================
// ADMIN MANAGEMENT SYSTEM - MODERNE
// =======================

class AdminManager {
  constructor() {
    this.theme = localStorage.getItem('admin-theme') || 'light';
    this.notifications = [];
    this.init();
  }

  init() {
    this.setupTheme();
    this.setupNotifications();
    this.setupForms();
    this.setupTables();
    this.setupAnimations();
    this.setupKeyboardShortcuts();
  }

  // =======================
  // GESTION DU THÈME
  // =======================

  setupTheme() {
    document.documentElement.setAttribute('data-theme', this.theme);
    
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', () => this.toggleTheme());
      this.updateThemeIcon(themeToggle);
    }
  }

  toggleTheme() {
    this.theme = this.theme === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', this.theme);
    localStorage.setItem('admin-theme', this.theme);
    
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
      this.updateThemeIcon(themeToggle);
    }
    
    this.showNotification(
      `Thème ${this.theme === 'dark' ? 'sombre' : 'clair'} activé`,
      'success'
    );
  }

  updateThemeIcon(button) {
    button.textContent = this.theme === 'dark' ? '☀️' : '🌙';
    button.setAttribute('aria-label', `Passer au thème ${this.theme === 'dark' ? 'clair' : 'sombre'}`);
  }

  // =======================
  // SYSTÈME DE NOTIFICATIONS
  // =======================

  setupNotifications() {
    // Créer le conteneur s'il n'existe pas
    if (!document.querySelector('.notifications-container')) {
      const container = document.createElement('div');
      container.className = 'notifications-container';
      container.style.cssText = `
        position: fixed;
        top: var(--spacing-xl);
        right: var(--spacing-xl);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
        max-width: 400px;
      `;
      document.body.appendChild(container);
    }
  }

  showNotification(message, type = 'info', duration = 4000) {
    const container = document.querySelector('.notifications-container');
    if (!container) return;

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
      success: '✅',
      error: '❌',
      warning: '⚠️',
      info: 'ℹ️'
    };

    notification.style.cssText = `
      background: var(--surface);
      padding: var(--spacing-lg);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-lg);
      border-left: 4px solid var(--${type === 'error' ? 'danger' : type}-color);
      transform: translateX(450px);
      transition: transform var(--transition-normal);
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      font-weight: 500;
      cursor: pointer;
    `;

    notification.innerHTML = `
      <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
      <span style="flex: 1;">${message}</span>
      <button style="background: none; border: none; font-size: 1.2rem; cursor: pointer; opacity: 0.7;">×</button>
    `;

    container.appendChild(notification);

    // Animation d'entrée
    setTimeout(() => {
      notification.style.transform = 'translateX(0)';
    }, 100);

    // Fermeture manuelle
    const closeBtn = notification.querySelector('button');
    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      this.removeNotification(notification);
    });

    // Fermeture au clic
    notification.addEventListener('click', () => {
      this.removeNotification(notification);
    });

    // Fermeture automatique
    setTimeout(() => {
      this.removeNotification(notification);
    }, duration);

    this.notifications.push(notification);
  }

  removeNotification(notification) {
    notification.style.transform = 'translateX(450px)';
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
      this.notifications = this.notifications.filter(n => n !== notification);
    }, 300);
  }

  // =======================
  // AMÉLIORATION DES FORMULAIRES
  // =======================

  setupForms() {
    // Validation en temps réel
    const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
    inputs.forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => this.clearFieldError(input));
    });

    // Sauvegarde automatique des formulaires
    const forms = document.querySelectorAll('form[data-autosave]');
    forms.forEach(form => this.setupAutoSave(form));

    // Confirmation avant soumission
    const dangerousForms = document.querySelectorAll('form[data-confirm]');
    dangerousForms.forEach(form => {
      form.addEventListener('submit', (e) => {
        const message = form.dataset.confirm || 'Êtes-vous sûr de vouloir continuer ?';
        if (!confirm(message)) {
          e.preventDefault();
        }
      });
    });
  }

  validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let error = null;

    // Validation basique
    if (field.required && !value) {
      error = 'Ce champ est obligatoire';
    } else if (type === 'email' && value && !this.isValidEmail(value)) {
      error = 'Adresse email invalide';
    } else if (type === 'url' && value && !this.isValidUrl(value)) {
      error = 'URL invalide';
    }

    this.showFieldError(field, error);
    return !error;
  }

  showFieldError(field, error) {
    this.clearFieldError(field);
    
    if (error) {
      field.style.borderColor = 'var(--danger-color)';
      
      const errorDiv = document.createElement('div');
      errorDiv.className = 'field-error';
      errorDiv.style.cssText = `
        color: var(--danger-color);
        font-size: var(--font-size-sm);
        margin-top: var(--spacing-xs);
      `;
      errorDiv.textContent = error;
      
      field.parentNode.appendChild(errorDiv);
    }
  }

  clearFieldError(field) {
    field.style.borderColor = '';
    const error = field.parentNode.querySelector('.field-error');
    if (error) {
      error.remove();
    }
  }

  isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  isValidUrl(url) {
    try {
      new URL(url);
      return true;
    } catch {
      return false;
    }
  }

  setupAutoSave(form) {
    const autosaveKey = `autosave_${form.id || 'form'}`;
    
    // Charger les données sauvegardées
    const saved = localStorage.getItem(autosaveKey);
    if (saved) {
      try {
        const data = JSON.parse(saved);
        Object.keys(data).forEach(name => {
          const field = form.querySelector(`[name="${name}"]`);
          if (field) field.value = data[name];
        });
        this.showNotification('Données du formulaire restaurées', 'info');
      } catch (e) {
        console.warn('Erreur lors de la restauration:', e);
      }
    }

    // Sauvegarder lors des changements
    let saveTimeout;
    form.addEventListener('input', () => {
      clearTimeout(saveTimeout);
      saveTimeout = setTimeout(() => {
        const data = new FormData(form);
        const obj = Object.fromEntries(data.entries());
        localStorage.setItem(autosaveKey, JSON.stringify(obj));
      }, 1000);
    });

    // Nettoyer après soumission
    form.addEventListener('submit', () => {
      localStorage.removeItem(autosaveKey);
    });
  }

  // =======================
  // AMÉLIORATION DES TABLEAUX
  // =======================

  setupTables() {
    // Éviter le setup multiple
    if (this.tablesSetup) return;
    this.tablesSetup = true;
    
    const tables = document.querySelectorAll('.admin-table');
    tables.forEach(table => {
      this.makeTableResponsive(table);
      this.addTableSorting(table);
      this.addTableFiltering(table);
    });
  }

  makeTableResponsive(table) {
    // Vérifier si la table n'est pas déjà dans un wrapper responsive
    if (table.parentNode && table.parentNode.style.overflowX === 'auto') {
      return; // Déjà wrappé
    }
    
    if (!table.parentNode) {
      console.warn('Table sans parent trouvée, ignorée');
      return;
    }
    
    const wrapper = document.createElement('div');
    wrapper.style.cssText = `
      overflow-x: auto;
      border-radius: var(--border-radius-lg);
      box-shadow: var(--shadow-sm);
    `;
    table.parentNode.insertBefore(wrapper, table);
    wrapper.appendChild(table);
  }

  addTableSorting(table) {
    const headers = table.querySelectorAll('th[data-sortable]');
    headers.forEach(header => {
      header.style.cursor = 'pointer';
      header.style.userSelect = 'none';
      header.style.position = 'relative';
      
      header.innerHTML += ' <span style="opacity: 0.5;">↕️</span>';
      
      header.addEventListener('click', () => {
        this.sortTable(table, header);
      });
    });
  }

  sortTable(table, header) {
    const column = Array.from(header.parentNode.children).indexOf(header);
    const rows = Array.from(table.tbody.rows);
    const ascending = !header.dataset.sorted || header.dataset.sorted === 'desc';
    
    rows.sort((a, b) => {
      const aVal = a.cells[column].textContent.trim();
      const bVal = b.cells[column].textContent.trim();
      
      // Détection numérique
      const aNum = parseFloat(aVal);
      const bNum = parseFloat(bVal);
      
      if (!isNaN(aNum) && !isNaN(bNum)) {
        return ascending ? aNum - bNum : bNum - aNum;
      }
      
      return ascending ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });

    rows.forEach(row => table.tbody.appendChild(row));
    
    // Mettre à jour les indicateurs
    table.querySelectorAll('th').forEach(th => {
      th.dataset.sorted = '';
      th.querySelector('span').textContent = '↕️';
    });
    
    header.dataset.sorted = ascending ? 'asc' : 'desc';
    header.querySelector('span').textContent = ascending ? '↑' : '↓';
  }

  addTableFiltering(table) {
    if (table.dataset.filterable === 'false') return;

    // Vérifier si le filtre n'existe pas déjà
    const wrapper = table.closest('div');
    if (!wrapper) {
      console.warn('Wrapper non trouvé pour la table, impossible d\'ajouter le filtre');
      return;
    }
    
    // Vérifier si un input de filtre existe déjà
    const existingFilter = wrapper.querySelector('input[placeholder*="Filtrer"]');
    if (existingFilter) {
      return; // Filtre déjà présent
    }

    const filterInput = document.createElement('input');
    filterInput.type = 'text';
    filterInput.placeholder = '🔍 Filtrer le tableau...';
    filterInput.className = 'form-input';
    filterInput.style.marginBottom = 'var(--spacing-md)';
    
    // Vérifier que la table est bien un enfant du wrapper
    if (wrapper.contains(table)) {
      wrapper.insertBefore(filterInput, table);
    } else {
      console.warn('Table non trouvée dans le wrapper, impossible d\'ajouter le filtre');
      return;
    }
    
    let filterTimeout;
    filterInput.addEventListener('input', () => {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(() => {
        this.filterTable(table, filterInput.value);
      }, 300);
    });
  }

  filterTable(table, query) {
    const rows = table.tbody.rows;
    const lowerQuery = query.toLowerCase();
    
    Array.from(rows).forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(lowerQuery) ? '' : 'none';
    });
  }

  // =======================
  // ANIMATIONS ET EFFETS
  // =======================

  setupAnimations() {
    // Animation au chargement
    const cards = document.querySelectorAll('.admin-card, .stat-card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        card.style.transition = 'all 0.5s ease-out';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 100);
    });

    // Effet de survol sur les liens de navigation
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
      link.addEventListener('mouseenter', () => {
        link.style.transform = 'translateX(4px)';
      });
      
      link.addEventListener('mouseleave', () => {
        if (!link.classList.contains('active')) {
          link.style.transform = 'translateX(0)';
        }
      });
    });
  }

  // =======================
  // RACCOURCIS CLAVIER
  // =======================

  setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      // Ctrl/Cmd + K : Focus sur la recherche
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"], input[placeholder*="recherch"], input[placeholder*="filtr"]');
        if (searchInput) {
          searchInput.focus();
          searchInput.select();
        }
      }

      // Ctrl/Cmd + S : Sauvegarder le formulaire
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        const form = document.querySelector('form');
        if (form) {
          e.preventDefault();
          this.showNotification('💾 Sauvegarde...', 'info', 1000);
          form.submit();
        }
      }

      // Échap : Fermer les modales/notifications
      if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => modal.classList.remove('show'));
        
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => this.removeNotification(notification));
      }
    });
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
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // =======================
  // API HELPERS
  // =======================

  async apiRequest(url, options = {}) {
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    };

    try {
      const response = await fetch(url, { ...defaultOptions, ...options });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return await response.json();
      }
      
      return await response.text();
    } catch (error) {
      this.showNotification(`Erreur réseau: ${error.message}`, 'error');
      throw error;
    }
  }

  // =======================
  // EXPORT/IMPORT
  // =======================

  exportTableAsCSV(table, filename = 'export.csv') {
    const rows = [];
    const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent.trim());
    rows.push(headers.join(','));

    Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
      const cells = Array.from(row.cells).map(cell => {
        return '"' + cell.textContent.trim().replace(/"/g, '""') + '"';
      });
      rows.push(cells.join(','));
    });

    const csv = rows.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', filename);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
}

// =======================
// FONCTIONS GLOBALES
// =======================

// Initialiser l'admin manager
let adminManager = null;

document.addEventListener('DOMContentLoaded', () => {
  adminManager = new AdminManager();
  
  // Notification de bienvenue supprimée
});

// Fonctions exposées globalement
window.showNotification = (message, type, duration) => {
  if (adminManager) {
    adminManager.showNotification(message, type, duration);
  }
};

window.exportTable = (table, filename) => {
  if (adminManager) {
    adminManager.exportTableAsCSV(table, filename);
  }
};

// Fonctions modales
window.openModal = (modalId) => {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Animation d'apparition
    modal.style.opacity = '0';
    setTimeout(() => {
      modal.style.opacity = '1';
    }, 10);
  }
};

window.closeModal = (modalId) => {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.opacity = '0';
    setTimeout(() => {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }, 200);
  }
};

// Fermer modal en cliquant à l'extérieur
window.addEventListener('click', (event) => {
  if (event.target.classList.contains('modal')) {
    const modalId = event.target.id;
    if (modalId) {
      closeModal(modalId);
    }
  }
});

// Fermer modal avec Escape
window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    const openModals = document.querySelectorAll('.modal[style*="flex"]');
    openModals.forEach(modal => {
      closeModal(modal.id);
    });
  }
});

// Export pour utilisation externe
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AdminManager;
}

// =======================
// MENU HAMBURGER - Mobile Navigation
// =======================
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, searching for hamburger elements...');
  
  const hamburger = document.getElementById('adminHamburger');
  const sidebar = document.getElementById('adminSidebar');

  console.log('Hamburger found:', !!hamburger);
  console.log('Sidebar found:', !!sidebar);

  if (hamburger && sidebar) {
    console.log('Both elements found, setting up event listeners...');

    hamburger.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      console.log('Hamburger clicked!');
      
      const isOpen = sidebar.classList.contains('admin-sidebar-open');
      console.log('Sidebar currently open:', isOpen);
      
      if (isOpen) {
        sidebar.classList.remove('admin-sidebar-open');
        hamburger.classList.remove('hamburger-active');
        console.log('Sidebar closed');
      } else {
        sidebar.classList.add('admin-sidebar-open');
        hamburger.classList.add('hamburger-active');
        console.log('Sidebar opened');
      }
    });

    // Fermer la sidebar quand on clique en dehors
    document.addEventListener('click', function(e) {
      if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
        sidebar.classList.remove('admin-sidebar-open');
        hamburger.classList.remove('hamburger-active');
      }
    });

    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        sidebar.classList.remove('admin-sidebar-open');
        hamburger.classList.remove('hamburger-active');
      }
    });
  } else {
    console.error('Hamburger menu elements not found!');
    console.log('Available elements with ID:', 
      Array.from(document.querySelectorAll('[id]')).map(el => el.id)
    );
  }
});

// Initialiser AdminManager quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
  new AdminManager();
});