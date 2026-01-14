<?php
    require 'config.php';

    // Vérifier l'authentification admin
    session_start();
    if (! isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
        header('Location: admin');
        exit;
    }

    $success = '';
    $error   = '';

    // Actions CRUD
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                $entreprise       = sanitize_input($_POST['entreprise'] ?? '');
                $poste            = sanitize_input($_POST['poste'] ?? '');
                $type_contrat     = sanitize_input($_POST['type_contrat'] ?? '');
                $localisation     = sanitize_input($_POST['localisation'] ?? '');
                $salaire          = sanitize_input($_POST['salaire'] ?? '');
                $date_candidature = $_POST['date_candidature'] ?? date('Y-m-d');
                $statut           = $_POST['statut'] ?? 'en_attente';
                $source           = sanitize_input($_POST['source'] ?? '');
                $notes            = sanitize_input($_POST['notes'] ?? '');

                if ($entreprise && $poste) {
                    try {
                        $stmt = $pdo->prepare("
                        INSERT INTO candidatures (entreprise, poste, type_contrat, localisation, salaire, date_candidature, statut, source, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                        $stmt->execute([$entreprise, $poste, $type_contrat, $localisation, $salaire, $date_candidature, $statut, $source, $notes]);
                        $success = "Candidature ajoutée avec succès !";
                    } catch (PDOException $e) {
                        $error = "Erreur lors de l'ajout : " . $e->getMessage();
                    }
                } else {
                    $error = "L'entreprise et le poste sont obligatoires.";
                }
                break;

            case 'update_status':
                $id     = (int) $_POST['id'];
                $statut = $_POST['statut'];

                try {
                    $stmt = $pdo->prepare("UPDATE candidatures SET statut = ? WHERE id = ?");
                    $stmt->execute([$statut, $id]);
                    $success = "Statut mis à jour !";
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                }
                break;
        }
    }

    // Suppression
    if (isset($_GET['delete'])) {
        $id = (int) $_GET['delete'];
        try {
            $stmt = $pdo->prepare("DELETE FROM candidatures WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Candidature supprimée !";
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }

    // Récupération des candidatures avec filtres
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $sort   = $_GET['sort'] ?? 'date_candidature_desc';

    try {
        $sql    = "SELECT * FROM candidatures WHERE 1=1";
        $params = [];

        // Filtres par statut
        if ($filter !== 'all') {
            $sql .= " AND statut = ?";
            $params[] = $filter;
        }

        // Recherche
        if (! empty($search)) {
            $sql .= " AND (entreprise LIKE ? OR poste LIKE ? OR localisation LIKE ? OR notes LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params      = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        // Tri
        switch ($sort) {
            case 'date_candidature_asc':
                $sql .= " ORDER BY date_candidature ASC";
                break;
            case 'entreprise':
                $sql .= " ORDER BY entreprise ASC";
                break;
            case 'statut':
                $sql .= " ORDER BY statut ASC";
                break;
            default:
                $sql .= " ORDER BY date_candidature DESC";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $candidatures = $stmt->fetchAll();

        // Statistiques
        $stats = [
            'total'      => $pdo->query("SELECT COUNT(*) FROM candidatures")->fetchColumn(),
            'en_attente' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'en_attente'")->fetchColumn(),
            'accepte'    => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'accepte'")->fetchColumn(),
            'refuse'     => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'refuse'")->fetchColumn(),
            'entretien'  => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'entretien'")->fetchColumn(),
        ];

    } catch (PDOException $e) {
        $error        = "Erreur de base de données : " . $e->getMessage();
        $candidatures = [];
        $stats        = ['total' => 0, 'en_attente' => 0, 'accepte' => 0, 'refuse' => 0, 'entretien' => 0];
    }

    // Statuts disponibles avec leurs couleurs
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'color' => '#ffc107'],
        'entretien'  => ['label' => 'Entretien', 'color' => '#17a2b8'],
        'accepte'    => ['label' => 'Accepté', 'color' => '#28a745'],
        'refuse'     => ['label' => 'Refusé', 'color' => '#dc3545'],
        'relance'    => ['label' => 'Relance', 'color' => '#fd7e14'],
    ];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>💼 Administration - Candidatures</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-modern.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <div class="admin-layout">
        <!-- Hamburger Menu Button (Mobile) -->
        <button class="admin-hamburger" id="adminHamburger" aria-label="Toggle Menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>

        <!-- Sidebar Navigation -->
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
                    <li><a href="admin_candidatures.php" class="active">💼 Candidatures</a></li>
                    <li><a href="admin_messages.php">📧 Messages</a></li>
                    <li><a href="admin_projets.php">🚀 Projets</a></li>
                    <li><a href="admin_gallery.php">🖼️ Galerie</a></li>
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
        <main class="admin-main">
            <div class="admin-header">
                <h1>💼 Gestion des Candidatures</h1>
                <p class="admin-subtitle">Administration des candidatures d'emploi</p>
                <div class="header-actions">
                    <button onclick="openModal('addModal')" class="btn btn-primary">
                        <span class="btn-icon">➕</span>
                        Nouvelle candidature
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($success): ?>
                <div class="notification notification-success">
                    <span class="notification-icon">✅</span>
                    <?php echo htmlspecialchars($success)?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="notification notification-error">
                    <span class="notification-icon">❌</span>
                    <?php echo htmlspecialchars($error)?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total']?></div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--warning);">⏳</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--warning);"><?php echo $stats['en_attente']?></div>
                        <div class="stat-label">En attente</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--info);">🎯</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--info);"><?php echo $stats['entretien']?></div>
                        <div class="stat-label">Entretiens</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success);">✅</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--success);"><?php echo $stats['accepte']?></div>
                        <div class="stat-label">Acceptées</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--danger);">❌</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--danger);"><?php echo $stats['refuse']?></div>
                        <div class="stat-label">Refusées</div>
                    </div>
                </div>
            </div>

            <!-- Filtres et actions -->
            <div class="admin-toolbar">
                <div class="filter-group">
                    <span class="filter-label">Filtrer :</span>
                    <a href="?filter=all&search=<?php echo urlencode($search)?>&sort=<?php echo $sort?>"
                       class="filter-btn <?php echo $filter === 'all' ? 'active' : ''?>">
                       Toutes (<?php echo $stats['total']?>)
                    </a>
                    <a href="?filter=en_attente&search=<?php echo urlencode($search)?>&sort=<?php echo $sort?>"
                       class="filter-btn <?php echo $filter === 'en_attente' ? 'active' : ''?>">
                       En attente (<?php echo $stats['en_attente']?>)
                    </a>
                    <a href="?filter=entretien&search=<?php echo urlencode($search)?>&sort=<?php echo $sort?>"
                       class="filter-btn <?php echo $filter === 'entretien' ? 'active' : ''?>">
                       Entretiens (<?php echo $stats['entretien']?>)
                    </a>
                </div>

                <div class="toolbar-actions">
                    <select onchange="window.location.href='?filter=<?php echo $filter?>&search=<?php echo urlencode($search)?>&sort=' + this.value"
                            class="form-select">
                        <option value="date_candidature_desc" <?php echo $sort === 'date_candidature_desc' ? 'selected' : ''?>>
                            Date (récent)
                        </option>
                        <option value="date_candidature_asc" <?php echo $sort === 'date_candidature_asc' ? 'selected' : ''?>>
                            Date (ancien)
                        </option>
                        <option value="entreprise" <?php echo $sort === 'entreprise' ? 'selected' : ''?>>
                            Entreprise A-Z
                        </option>
                        <option value="statut" <?php echo $sort === 'statut' ? 'selected' : ''?>>
                            Statut
                        </option>
                    </select>

                    <form method="GET" class="search-form">
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter)?>">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort)?>">
                        <input type="text" name="search" placeholder="Rechercher..."
                               value="<?php echo htmlspecialchars($search)?>" class="form-input">
                        <button type="submit" class="btn btn-secondary">
                            <span class="btn-icon">🔍</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des candidatures -->
            <div class="admin-content">
                <?php if (empty($candidatures)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>Aucune candidature</h3>
                        <p class="text-muted">Aucune candidature ne correspond à vos critères.</p>
                        <button onclick="openModal('addModal')" class="btn btn-primary">
                            <span class="btn-icon">➕</span>
                            Ajouter la première candidature
                        </button>
                    </div>
                <?php else: ?>
                    <div class="data-grid">
                        <?php foreach ($candidatures as $candidature): ?>
                            <div class="data-card">
                                <div class="card-header">
                                    <h3>🏢 <?php echo htmlspecialchars($candidature['entreprise'])?></h3>
                                    <div class="status-badge status-<?php echo $candidature['statut']?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $candidature['statut']))?>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <p><strong>Poste :</strong> <?php echo htmlspecialchars($candidature['poste'])?></p>
                                    <p><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($candidature['date_candidature']))?></p>
                                    <?php if ($candidature['localisation']): ?>
                                        <p><strong>Lieu :</strong> <?php echo htmlspecialchars($candidature['localisation'])?></p>
                                    <?php endif; ?>
                                    <?php if ($candidature['salaire']): ?>
                                        <p><strong>Salaire :</strong> <?php echo htmlspecialchars($candidature['salaire'])?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?php echo $candidature['id']?>">
                                        <select name="statut" onchange="this.form.submit()" class="form-select">
                                            <?php foreach ($statuts as $key => $statut): ?>
                                                <option value="<?php echo $key?>" <?php echo $candidature['statut'] === $key ? 'selected' : ''?>>
                                                    <?php echo $statut['label']?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>

                                    <a href="?delete=<?php echo $candidature['id']?>&filter=<?php echo $filter?>&search=<?php echo urlencode($search)?>&sort=<?php echo $sort?>"
                                       class="btn btn-small btn-danger"
                                       onclick="return confirm('Supprimer cette candidature ?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Ajouter candidature -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Nouvelle candidature</h2>
                <button onclick="closeModal('addModal')" class="modal-close">✖️</button>
            </div>

            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="entreprise" class="form-label">
                            <span class="label-icon">🏢</span>
                            Entreprise *
                        </label>
                        <input type="text"
                               id="entreprise"
                               name="entreprise"
                               class="form-input"
                               required
                               placeholder="Nom de l'entreprise"
                               autocomplete="organization">
                    </div>

                    <div class="form-group">
                        <label for="poste" class="form-label">
                            <span class="label-icon">💼</span>
                            Poste *
                        </label>
                        <input type="text"
                               id="poste"
                               name="poste"
                               class="form-input"
                               required
                               placeholder="Intitulé du poste"
                               autocomplete="job-title">
                    </div>

                    <div class="form-group">
                        <label for="type_contrat" class="form-label">
                            <span class="label-icon">📄</span>
                            Type de contrat
                        </label>
                        <select id="type_contrat" name="type_contrat" class="form-select">
                            <option value="">-- Sélectionnez le type --</option>
                            <option value="CDI">CDI - Contrat à Durée Indéterminée</option>
                            <option value="CDD">CDD - Contrat à Durée Déterminée</option>
                            <option value="Stage">Stage</option>
                            <option value="Freelance">Freelance / Mission</option>
                            <option value="Alternance">Alternance / Apprentissage</option>
                            <option value="Interim">Intérim</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="localisation" class="form-label">
                            <span class="label-icon">📍</span>
                            Localisation
                        </label>
                        <input type="text"
                               id="localisation"
                               name="localisation"
                               class="form-input"
                               placeholder="Paris, Lyon, Remote..."
                               autocomplete="address-level2">
                    </div>

                    <div class="form-group">
                        <label for="salaire" class="form-label">
                            <span class="label-icon">💰</span>
                            Salaire proposé
                        </label>
                        <input type="text"
                               id="salaire"
                               name="salaire"
                               class="form-input"
                               placeholder="45k€, 2500€/mois, À négocier...">
                        <small class="form-help">Exemple: 45k€, 2500€/mois, 500€/jour</small>
                    </div>

                    <div class="form-group">
                        <label for="date_candidature" class="form-label">
                            <span class="label-icon">📅</span>
                            Date de candidature
                        </label>
                        <input type="date"
                               id="date_candidature"
                               name="date_candidature"
                               class="form-input"
                               value="<?php echo date('Y-m-d')?>"
                               max="<?php echo date('Y-m-d')?>">
                    </div>

                    <div class="form-group">
                        <label for="statut" class="form-label">
                            <span class="label-icon">📊</span>
                            Statut actuel
                        </label>
                        <select id="statut" name="statut" class="form-select">
                            <?php foreach ($statuts as $key => $statut): ?>
                                <option value="<?php echo $key?>"
                                        <?php echo $key === 'en_attente' ? 'selected' : ''?>
                                        data-color="<?php echo $statut['color']?>">
                                    <?php echo $statut['label']?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="source" class="form-label">
                            <span class="label-icon">🌐</span>
                            Source / Canal
                        </label>
                        <input type="text"
                               id="source"
                               name="source"
                               class="form-input"
                               placeholder="LinkedIn, Indeed, Site entreprise..."
                               list="sources-datalist">
                        <datalist id="sources-datalist">
                            <option value="LinkedIn">
                            <option value="Indeed">
                            <option value="Pôle Emploi">
                            <option value="Site entreprise">
                            <option value="Recommandation">
                            <option value="Candidature spontanée">
                            <option value="Agence de recrutement">
                            <option value="Job board">
                        </datalist>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="notes" class="form-label">
                            <span class="label-icon">📝</span>
                            Notes et commentaires
                        </label>
                        <textarea id="notes"
                                  name="notes"
                                  class="form-textarea"
                                  rows="4"
                                  placeholder="Commentaires, informations supplémentaires, contacts, prochaines étapes..."></textarea>
                        <small class="form-help">Ajoutez ici toutes les informations utiles sur cette candidature</small>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeModal('addModal')" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-icon">✅</span>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin-modern.js"></script>
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';

            // Focus sur le premier champ du formulaire
            setTimeout(() => {
                const firstInput = document.querySelector(`#${modalId} .form-input`);
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';

            // Réinitialiser le formulaire
            const form = document.querySelector(`#${modalId} form`);
            if (form) {
                form.reset();
                // Remettre la date du jour
                const dateInput = form.querySelector('input[type="date"]');
                if (dateInput) {
                    dateInput.value = new Date().toISOString().split('T')[0];
                }
                // Nettoyer les classes de validation
                form.querySelectorAll('.form-group').forEach(group => {
                    group.classList.remove('success', 'error');
                });
            }
        }

        // Validation en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#addModal form');
            if (!form) return;

            // Validation des champs requis
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('blur', validateField);
                field.addEventListener('input', clearErrors);
            });

            // Validation du formulaire avant soumission
            form.addEventListener('submit', function(e) {
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!validateField({ target: field })) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    showFormError('Veuillez corriger les erreurs avant de soumettre.');
                }
            });

            // Validation de l'email (si ajouté plus tard)
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                field.addEventListener('blur', validateEmail);
            });
        });

        function validateField(e) {
            const field = e.target;
            const formGroup = field.closest('.form-group');
            const value = field.value.trim();

            // Nettoyer les erreurs précédentes
            formGroup.classList.remove('success', 'error');
            removeFieldError(formGroup);

            // Vérifier si le champ est requis et vide
            if (field.hasAttribute('required') && !value) {
                showFieldError(formGroup, 'Ce champ est obligatoire');
                return false;
            }

            // Validations spécifiques par type de champ
            if (value) {
                switch (field.type) {
                    case 'date':
                        if (new Date(value) > new Date()) {
                            showFieldError(formGroup, 'La date ne peut pas être dans le futur');
                            return false;
                        }
                        break;
                }

                // Validations par nom de champ
                switch (field.name) {
                    case 'entreprise':
                        if (value.length < 2) {
                            showFieldError(formGroup, 'Le nom de l\'entreprise doit contenir au moins 2 caractères');
                            return false;
                        }
                        break;
                    case 'poste':
                        if (value.length < 2) {
                            showFieldError(formGroup, 'L\'intitulé du poste doit contenir au moins 2 caractères');
                            return false;
                        }
                        break;
                }

                // Si tout est OK, marquer comme succès
                formGroup.classList.add('success');
            }

            return true;
        }

        function validateEmail(e) {
            const field = e.target;
            const formGroup = field.closest('.form-group');
            const email = field.value.trim();

            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showFieldError(formGroup, 'Veuillez saisir une adresse email valide');
                return false;
            }

            if (email) {
                formGroup.classList.add('success');
            }
            return true;
        }

        function showFieldError(formGroup, message) {
            formGroup.classList.add('error');

            let errorDiv = formGroup.querySelector('.form-error');
            if (!errorDiv) {
                errorDiv = document.createElement('span');
                errorDiv.className = 'form-error';
                formGroup.appendChild(errorDiv);
            }
            errorDiv.textContent = message;
        }

        function removeFieldError(formGroup) {
            const errorDiv = formGroup.querySelector('.form-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        function clearErrors(e) {
            const formGroup = e.target.closest('.form-group');
            formGroup.classList.remove('error');
            removeFieldError(formGroup);
        }

        function showFormError(message) {
            // Créer ou mettre à jour la notification d'erreur
            let notification = document.querySelector('.notification-form-error');
            if (!notification) {
                notification = document.createElement('div');
                notification.className = 'notification notification-error notification-form-error';
                notification.innerHTML = `<span class="notification-icon">❌</span><span></span>`;

                const form = document.querySelector('#addModal form');
                form.insertBefore(notification, form.firstChild);
            }

            notification.querySelector('span:last-child').textContent = message;

            // Faire disparaître automatiquement après 5 secondes
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Fermer modal en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target.id);
            }
        }

        // Fermer modal avec échap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.style.display === 'block') {
                        closeModal(modal.id);
                    }
                });
            }
        });

        // Auto-complétion intelligente pour la source
        document.addEventListener('DOMContentLoaded', function() {
            const sourceInput = document.getElementById('source');
            if (sourceInput) {
                sourceInput.addEventListener('input', function(e) {
                    const value = e.target.value.toLowerCase();

                    // Auto-complétion basée sur les premiers caractères
                    const suggestions = {
                        'lin': 'LinkedIn',
                        'ind': 'Indeed',
                        'pol': 'Pôle Emploi',
                        'sit': 'Site entreprise',
                        'rec': 'Recommandation',
                        'can': 'Candidature spontanée',
                        'age': 'Agence de recrutement'
                    };

                    for (const [key, suggestion] of Object.entries(suggestions)) {
                        if (value.startsWith(key) && value.length >= 3 && value !== suggestion.toLowerCase()) {
                            e.target.value = suggestion;
                            e.target.setSelectionRange(key.length, suggestion.length);
                            break;
                        }
                    }
                });
            }
        });
    </script>
    <script src="admin-modern.js"></script>
</body>
</html>