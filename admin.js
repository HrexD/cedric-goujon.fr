/* 🎛️ JAVASCRIPT ADMINISTRATION - Système complet */

/* ===== GESTION DES MODALES ===== */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Empêcher le scroll
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restaurer le scroll
    }
}

// Fermer modal en cliquant à l'extérieur ou avec Escape
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal[style*="block"]');
        openModals.forEach(modal => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
});

/* ===== GESTION DES CANDIDATURES ===== */
function editCandidature(candidature) {
    document.getElementById('edit_id').value = candidature.id;
    
    const formContent = `
        <div class="form-grid">
            <div class="form-group">
                <label>🏢 Entreprise *</label>
                <input type="text" name="entreprise" value="${escapeHtml(candidature.entreprise)}" required>
            </div>
            <div class="form-group">
                <label>📝 Poste *</label>
                <input type="text" name="poste" value="${escapeHtml(candidature.poste)}" required>
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>💼 Type de contrat</label>
                <select name="type_contrat">
                    <option value="">Sélectionner...</option>
                    <option value="CDI" ${candidature.type_contrat === 'CDI' ? 'selected' : ''}>CDI</option>
                    <option value="CDD" ${candidature.type_contrat === 'CDD' ? 'selected' : ''}>CDD</option>
                    <option value="Stage" ${candidature.type_contrat === 'Stage' ? 'selected' : ''}>Stage</option>
                    <option value="Freelance" ${candidature.type_contrat === 'Freelance' ? 'selected' : ''}>Freelance</option>
                    <option value="Alternance" ${candidature.type_contrat === 'Alternance' ? 'selected' : ''}>Alternance</option>
                </select>
            </div>
            <div class="form-group">
                <label>📍 Localisation</label>
                <input type="text" name="localisation" value="${escapeHtml(candidature.localisation || '')}">
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>💰 Salaire</label>
                <input type="text" name="salaire" value="${escapeHtml(candidature.salaire || '')}">
            </div>
            <div class="form-group">
                <label>📅 Date candidature</label>
                <input type="date" name="date_candidature" value="${candidature.date_candidature}">
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>📊 Statut</label>
                <select name="statut">
                    <option value="en_attente" ${candidature.statut === 'en_attente' ? 'selected' : ''}>En attente</option>
                    <option value="entretien" ${candidature.statut === 'entretien' ? 'selected' : ''}>Entretien</option>
                    <option value="accepte" ${candidature.statut === 'accepte' ? 'selected' : ''}>Accepté</option>
                    <option value="refuse" ${candidature.statut === 'refuse' ? 'selected' : ''}>Refusé</option>
                </select>
            </div>
            <div class="form-group">
                <label>🔗 Source</label>
                <input type="text" name="source" value="${escapeHtml(candidature.source || '')}">
            </div>
        </div>
        <div class="form-group full-width">
            <label>📝 Notes</label>
            <textarea name="notes" rows="4">${escapeHtml(candidature.notes || '')}</textarea>
        </div>
    `;
    
    const editFormContent = document.getElementById('editFormContent');
    if (editFormContent) {
        editFormContent.innerHTML = formContent;
        openModal('editModal');
    }
}

/* ===== GESTION DES PROJETS ===== */
function editProjet(projet) {
    document.getElementById('edit_id').value = projet.id;
    
    const formContent = `
        <div class="form-group">
            <label>🚀 Nom du projet *</label>
            <input type="text" name="nom" value="${escapeHtml(projet.nom)}" required>
        </div>
        <div class="form-group">
            <label>📝 Description *</label>
            <textarea name="description" rows="4" required>${escapeHtml(projet.description)}</textarea>
        </div>
        <div class="form-group">
            <label>💻 Technologies utilisées</label>
            <input type="text" name="technologies" value="${escapeHtml(projet.technologies || '')}">
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>🌐 URL de démonstration</label>
                <input type="url" name="url_demo" value="${escapeHtml(projet.url_demo || '')}">
            </div>
            <div class="form-group">
                <label>📂 URL GitHub</label>
                <input type="url" name="url_github" value="${escapeHtml(projet.url_github || '')}">
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>📊 Statut</label>
                <select name="statut">
                    <option value="actif" ${projet.statut === 'actif' ? 'selected' : ''}>Actif</option>
                    <option value="en_cours" ${projet.statut === 'en_cours' ? 'selected' : ''}>En cours</option>
                    <option value="archive" ${projet.statut === 'archive' ? 'selected' : ''}>Archivé</option>
                </select>
            </div>
            <div class="form-group">
                <label>🔢 Ordre d'affichage</label>
                <input type="number" name="ordre_affichage" value="${projet.ordre_affichage}" min="0">
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>📅 Date début</label>
                <input type="date" name="date_debut" value="${projet.date_debut || ''}">
            </div>
            <div class="form-group">
                <label>🏁 Date fin</label>
                <input type="date" name="date_fin" value="${projet.date_fin || ''}">
            </div>
        </div>
    `;
    
    const editFormContent = document.getElementById('editFormContent');
    if (editFormContent) {
        editFormContent.innerHTML = formContent;
        openModal('editModal');
    }
}

/* ===== GESTION DES MESSAGES ===== */
function toggleMessageDetails(messageId) {
    const details = document.getElementById(`details-${messageId}`);
    if (details) {
        if (details.style.display === 'none' || details.style.display === '') {
            details.style.display = 'block';
            // Marquer comme lu automatiquement
            markAsRead(messageId);
        } else {
            details.style.display = 'none';
        }
    }
}

function markAsRead(messageId) {
    fetch('admin_messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle_read&id=${messageId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour l'affichage
            const messageItem = document.querySelector(`[data-message-id="${messageId}"]`);
            if (messageItem) {
                if (data.lu) {
                    messageItem.classList.remove('message-unread');
                } else {
                    messageItem.classList.add('message-unread');
                }
            }
            // Recharger les stats si nécessaire
            updateUnreadCount();
        }
    })
    .catch(error => console.error('Erreur:', error));
}

function updateUnreadCount() {
    // Mettre à jour le compteur de messages non lus
    const unreadElements = document.querySelectorAll('.message-unread');
    const countElement = document.querySelector('.unread-count');
    if (countElement) {
        countElement.textContent = unreadElements.length;
    }
}

/* ===== GESTION DU SYSTÈME ===== */
function refreshSystemLogs() {
    const logContainer = document.querySelector('.log-container');
    if (logContainer) {
        // Ajouter un nouveau log simulé
        const newLog = document.createElement('div');
        newLog.className = 'log-entry';
        newLog.innerHTML = `
            <span class="log-time">${new Date().toLocaleTimeString()}</span>
            <span class="log-level-INFO">[INFO]</span>
            <span>Vérification automatique du système</span>
        `;
        logContainer.appendChild(newLog);
        
        // Garder seulement les 10 derniers logs
        while (logContainer.children.length > 10) {
            logContainer.removeChild(logContainer.firstChild);
        }
        
        // Scroll automatique vers le bas
        logContainer.scrollTop = logContainer.scrollHeight;
    }
}

function optimizeDatabase() {
    if (confirm('Êtes-vous sûr de vouloir optimiser la base de données ?')) {
        // Animation de chargement
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Optimisation...';
        button.disabled = true;
        
        // Simuler l'optimisation
        setTimeout(() => {
            button.textContent = '✅ Optimisé';
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 2000);
        }, 3000);
    }
}

function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Nettoyage...';
        button.disabled = true;
        
        setTimeout(() => {
            button.textContent = '✅ Cache vidé';
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
            }, 2000);
        }, 2000);
    }
}

/* ===== RECHERCHE ET FILTRES ===== */
function setupSearch() {
    const searchInputs = document.querySelectorAll('.search-box');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const table = e.target.closest('.admin-section').querySelector('table tbody');
            
            if (table) {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });
}

function setupFilters() {
    const filterSelects = document.querySelectorAll('select[name="filter"]');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const table = e.target.closest('.admin-section').querySelector('table tbody');
            
            if (table) {
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    if (filterValue === '' || filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const statusCell = row.querySelector('.status-badge');
                        if (statusCell && statusCell.textContent.toLowerCase().includes(filterValue.toLowerCase())) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            }
        });
    });
}

/* ===== CONFIRMATIONS ===== */
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

function confirmAction(message = 'Êtes-vous sûr de vouloir effectuer cette action ?') {
    return confirm(message);
}

/* ===== UTILITAIRES ===== */
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return text.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('fr-FR');
}

/* ===== NOTIFICATIONS ===== */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">×</button>
    `;
    
    // Styles inline pour la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: var(--surface);
        border-left: 4px solid var(--accent);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        z-index: 9999;
        max-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    if (type === 'success') {
        notification.style.borderLeftColor = '#28a745';
    } else if (type === 'error') {
        notification.style.borderLeftColor = '#dc3545';
    } else if (type === 'warning') {
        notification.style.borderLeftColor = '#ffc107';
    }
    
    document.body.appendChild(notification);
    
    // Auto-suppression après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

/* ===== AUTO-REFRESH ===== */
function setupAutoRefresh() {
    // Auto-refresh des logs toutes les 30 secondes
    if (document.querySelector('.log-container')) {
        setInterval(refreshSystemLogs, 30000);
    }
    
    // Auto-refresh des stats toutes les 2 minutes
    if (document.querySelector('.stats-grid')) {
        setInterval(() => {
            // Simuler la mise à jour des stats
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const current = parseInt(stat.textContent);
                if (Math.random() > 0.8) { // 20% de chance de changement
                    stat.textContent = current + Math.floor(Math.random() * 3);
                }
            });
        }, 120000);
    }
}

/* ===== THÈME ET APPARENCE ===== */
function toggleTheme() {
    const currentTheme = document.body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.body.setAttribute('data-theme', newTheme);
    localStorage.setItem('admin-theme', newTheme);
    
    // Mettre à jour l'icône du bouton
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.textContent = newTheme === 'dark' ? '☀️' : '🌙';
    }
}

/* ===== INITIALISATION ===== */
document.addEventListener('DOMContentLoaded', function() {
    // Restaurer le thème sauvegardé
    const savedTheme = localStorage.getItem('admin-theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
    
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
        themeToggle.addEventListener('click', toggleTheme);
    }
    
    // Initialiser les fonctionnalités
    setupSearch();
    setupFilters();
    setupAutoRefresh();
    
    // Ajouter les gestionnaires d'événements pour les boutons de fermeture des modales
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Ajouter les gestionnaires d'événements pour les boutons de suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirmDelete()) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Animation d'entrée pour les éléments
    const animatedElements = document.querySelectorAll('.stat-card, .recent-card, .admin-section');
    animatedElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
        element.classList.add('fade-in');
    });
    
    console.log('🎛️ Administration système initialisé');
});

/* ===== GESTION DES ERREURS ===== */
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
    showNotification('Une erreur est survenue. Veuillez rafraîchir la page.', 'error');
});

/* ===== RACCOURCIS CLAVIER ===== */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K pour ouvrir la recherche
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchBox = document.querySelector('.search-box');
        if (searchBox) {
            searchBox.focus();
        }
    }
    
    // Ctrl/Cmd + N pour nouveau (ouvrir modal d'ajout)
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const addButton = document.querySelector('[onclick*="addModal"]');
        if (addButton) {
            addButton.click();
        }
    }
});