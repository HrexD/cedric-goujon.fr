<?php
session_start();
require 'config.php';
require 'auth_helper.php';

// Vérifier l'authentification
if (!isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Variables pour les filtres et pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$filter_langage = $_GET['langage'] ?? '';
$filter_difficulte = $_GET['difficulte'] ?? '';
$filter_statut = $_GET['statut'] ?? '';
$search = $_GET['search'] ?? '';

// Messages
$message = '';
$message_type = '';

// Traitement des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create') {
            $stmt = $pdo->prepare("
                INSERT INTO exercices (titre, description, langage, difficulte, enonce, solution, tags, temps_estime, points, auteur, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $tags = !empty($_POST['tags']) ? json_encode(array_map('trim', explode(',', $_POST['tags']))) : '[]';
            
            $stmt->execute([
                $_POST['titre'],
                $_POST['description'],
                $_POST['langage'],
                $_POST['difficulte'],
                $_POST['enonce'],
                $_POST['solution'],
                $tags,
                (int)$_POST['temps_estime'],
                (int)$_POST['points'],
                $_POST['auteur'],
                $_POST['statut']
            ]);
            
            $message = 'Exercice créé avec succès !';
            $message_type = 'success';
            
        } elseif ($action === 'update') {
            $stmt = $pdo->prepare("
                UPDATE exercices SET 
                    titre = ?, description = ?, langage = ?, difficulte = ?, 
                    enonce = ?, solution = ?, tags = ?, temps_estime = ?, 
                    points = ?, auteur = ?, statut = ?
                WHERE id = ?
            ");
            
            $tags = !empty($_POST['tags']) ? json_encode(array_map('trim', explode(',', $_POST['tags']))) : '[]';
            
            $stmt->execute([
                $_POST['titre'],
                $_POST['description'],
                $_POST['langage'],
                $_POST['difficulte'],
                $_POST['enonce'],
                $_POST['solution'],
                $tags,
                (int)$_POST['temps_estime'],
                (int)$_POST['points'],
                $_POST['auteur'],
                $_POST['statut'],
                (int)$_POST['id']
            ]);
            
            $message = 'Exercice mis à jour avec succès !';
            $message_type = 'success';
            
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM exercices WHERE id = ?");
            $stmt->execute([(int)$_POST['id']]);
            
            $message = 'Exercice supprimé avec succès !';
            $message_type = 'success';
        }
        
    } catch (PDOException $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Construction de la requête avec filtres
$where_conditions = [];
$params = [];

if ($filter_langage) {
    $where_conditions[] = 'langage = ?';
    $params[] = $filter_langage;
}

if ($filter_difficulte) {
    $where_conditions[] = 'difficulte = ?';
    $params[] = $filter_difficulte;
}

if ($filter_statut) {
    $where_conditions[] = 'statut = ?';
    $params[] = $filter_statut;
}

if ($search) {
    $where_conditions[] = '(titre LIKE ? OR description LIKE ? OR tags LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Récupération des exercices
$stmt = $pdo->prepare("
    SELECT * FROM exercices 
    {$where_clause} 
    ORDER BY date_creation DESC 
    LIMIT {$per_page} OFFSET {$offset}
");
$stmt->execute($params);
$exercices = $stmt->fetchAll();

// Compte total pour la pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM exercices {$where_clause}");
$count_stmt->execute($params);
$total_exercices = $count_stmt->fetchColumn();
$total_pages = ceil($total_exercices / $per_page);

// Récupération des statistiques
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN statut = 'actif' THEN 1 END) as actifs,
        COUNT(CASE WHEN difficulte = 'facile' THEN 1 END) as faciles,
        COUNT(CASE WHEN difficulte = 'moyen' THEN 1 END) as moyens,
        COUNT(CASE WHEN difficulte = 'difficile' THEN 1 END) as difficiles
    FROM exercices
");
$stats = $stats_stmt->fetch();

// Récupération des langages uniques
$langages_stmt = $pdo->query("SELECT DISTINCT langage FROM exercices ORDER BY langage");
$langages = $langages_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Exercices</title>
    <link rel="stylesheet" href="admin-modern.css">
    <style>
        .exercices-grid {
            display: grid;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .exercice-card {
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            transition: all 0.2s ease;
        }
        
        .exercice-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }
        
        .exercice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-sm);
        }
        
        .exercice-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: var(--spacing-sm);
            margin: var(--spacing-sm) 0;
            padding: var(--spacing-sm);
            background: var(--gray-50);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
        }
        
        .exercice-actions {
            display: flex;
            gap: var(--spacing-xs);
            margin-top: var(--spacing-sm);
        }
        
        .modal-form {
            max-width: 800px;
            width: 100%;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-md);
        }
        
        .form-row-full {
            grid-column: 1 / -1;
        }
        
        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-actif { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .status-inactif { background: rgba(107, 114, 128, 0.1); color: var(--text-muted); }
        .status-brouillon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        
        .difficulte-facile { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .difficulte-moyen { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .difficulte-difficile { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>
                <span class="admin-icon">📚</span>
                Administration des Exercices
            </h1>
            <div class="admin-nav">
                <a href="admin.php" class="btn btn-secondary">← Retour</a>
                <button onclick="openModal('createModal')" class="btn btn-primary">
                    ➕ Nouvel exercice
                </button>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="notification notification-<?= $message_type ?>">
                <span class="notification-icon"><?= $message_type === 'success' ? '✅' : '❌' ?></span>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total exercices</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['actifs'] ?></div>
                <div class="stat-label">Exercices actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['faciles'] ?></div>
                <div class="stat-label">Niveau facile</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['moyens'] ?></div>
                <div class="stat-label">Niveau moyen</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['difficiles'] ?></div>
                <div class="stat-label">Niveau difficile</div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="form-group">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="🔍 Rechercher par titre, description ou tags..." class="form-input">
                </div>
                
                <div class="form-group">
                    <select name="langage" class="form-select">
                        <option value="">Tous les langages</option>
                        <?php foreach ($langages as $langage): ?>
                            <option value="<?= htmlspecialchars($langage) ?>" <?= $filter_langage === $langage ? 'selected' : '' ?>>
                                <?= htmlspecialchars($langage) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="difficulte" class="form-select">
                        <option value="">Toutes difficultés</option>
                        <option value="facile" <?= $filter_difficulte === 'facile' ? 'selected' : '' ?>>Facile</option>
                        <option value="moyen" <?= $filter_difficulte === 'moyen' ? 'selected' : '' ?>>Moyen</option>
                        <option value="difficile" <?= $filter_difficulte === 'difficile' ? 'selected' : '' ?>>Difficile</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?= $filter_statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= $filter_statut === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        <option value="brouillon" <?= $filter_statut === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="admin_exercices.php" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Liste des exercices -->
        <div class="exercices-grid">
            <?php if (empty($exercices)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3>Aucun exercice trouvé</h3>
                    <p>Commencez par créer votre premier exercice !</p>
                    <button onclick="openModal('createModal')" class="btn btn-primary">
                        ➕ Créer un exercice
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($exercices as $exercice): ?>
                    <?php $tags = json_decode($exercice['tags'] ?? '[]', true) ?: []; ?>
                    <div class="exercice-card">
                        <div class="exercice-header">
                            <div>
                                <h3 style="margin: 0; color: var(--text);"><?= htmlspecialchars($exercice['titre']) ?></h3>
                                <span class="status-badge status-<?= $exercice['statut'] ?>"><?= ucfirst($exercice['statut']) ?></span>
                            </div>
                            <span class="status-badge difficulte-<?= $exercice['difficulte'] ?>">
                                <?= ucfirst($exercice['difficulte']) ?>
                            </span>
                        </div>
                        
                        <p style="color: var(--text-muted); margin: var(--spacing-sm) 0; line-height: 1.4;">
                            <?= htmlspecialchars(substr($exercice['description'], 0, 100)) ?><?= strlen($exercice['description']) > 100 ? '...' : '' ?>
                        </p>
                        
                        <div class="exercice-meta">
                            <div>
                                <strong>Langage:</strong> <?= htmlspecialchars($exercice['langage']) ?>
                            </div>
                            <div>
                                <strong>Temps:</strong> <?= $exercice['temps_estime'] ?> min
                            </div>
                            <div>
                                <strong>Points:</strong> <?= $exercice['points'] ?>
                            </div>
                            <div>
                                <strong>Auteur:</strong> <?= htmlspecialchars($exercice['auteur']) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($tags)): ?>
                            <div style="margin: var(--spacing-sm) 0;">
                                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                    <span class="status-badge" style="background: var(--primary-light); color: var(--primary-color);">
                                        #<?= htmlspecialchars($tag) ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($tags) > 3): ?>
                                    <span class="status-badge" style="background: var(--gray-100); color: var(--text-muted);">
                                        +<?= count($tags) - 3 ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="exercice-actions">
                            <button onclick="editExercice(<?= $exercice['id'] ?>)" class="btn btn-secondary">
                                ✏️ Modifier
                            </button>
                            <button onclick="viewExercice(<?= $exercice['id'] ?>)" class="btn btn-primary">
                                👁️ Voir
                            </button>
                            <button onclick="deleteExercice(<?= $exercice['id'] ?>)" class="btn btn-danger">
                                🗑️ Supprimer
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>&<?= http_build_query($_GET) ?>" class="btn btn-secondary">← Précédent</a>
                <?php endif; ?>
                
                <span class="page-info">
                    Page <?= $page ?> sur <?= $total_pages ?> 
                    (<?= $total_exercices ?> exercices au total)
                </span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?>&<?= http_build_query($_GET) ?>" class="btn btn-secondary">Suivant →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Créer/Modifier -->
    <div id="createModal" class="modal">
        <div class="modal-content modal-form">
            <div class="modal-header">
                <h2 id="modalTitle">Nouvel Exercice</h2>
                <button onclick="closeModal('createModal')" class="close-btn">&times;</button>
            </div>
            
            <form id="exerciceForm" method="POST">
                <input type="hidden" id="exerciceId" name="id">
                <input type="hidden" id="formAction" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="titre">Titre *</label>
                        <input type="text" id="titre" name="titre" required class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label for="auteur">Auteur *</label>
                        <input type="text" id="auteur" name="auteur" required class="form-input">
                    </div>
                </div>
                
                <div class="form-group form-row-full">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="langage">Langage *</label>
                        <select id="langage" name="langage" required class="form-select">
                            <option value="">Sélectionner...</option>
                            <option value="PHP">PHP</option>
                            <option value="JavaScript">JavaScript</option>
                            <option value="Python">Python</option>
                            <option value="Java">Java</option>
                            <option value="C++">C++</option>
                            <option value="C#">C#</option>
                            <option value="CSS">CSS</option>
                            <option value="HTML">HTML</option>
                            <option value="SQL">SQL</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulte">Difficulté *</label>
                        <select id="difficulte" name="difficulte" required class="form-select">
                            <option value="">Sélectionner...</option>
                            <option value="facile">Facile</option>
                            <option value="moyen">Moyen</option>
                            <option value="difficile">Difficile</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="temps_estime">Temps estimé (minutes) *</label>
                        <input type="number" id="temps_estime" name="temps_estime" required class="form-input" min="1" max="180">
                    </div>
                    
                    <div class="form-group">
                        <label for="points">Points *</label>
                        <input type="number" id="points" name="points" required class="form-input" min="1" max="1000">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tags">Tags (séparés par des virgules)</label>
                        <input type="text" id="tags" name="tags" class="form-input" placeholder="ex: boucle, algorithme, logique">
                    </div>
                    
                    <div class="form-group">
                        <label for="statut">Statut *</label>
                        <select id="statut" name="statut" required class="form-select">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="brouillon">Brouillon</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="enonce">Énoncé *</label>
                    <textarea id="enonce" name="enonce" required class="form-textarea" rows="6" 
                              placeholder="Décrivez clairement l'exercice, les consignes et les exemples..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="solution">Solution *</label>
                    <textarea id="solution" name="solution" required class="form-textarea" rows="8" 
                              placeholder="Écrivez la solution complète de l'exercice..." style="font-family: var(--font-mono);"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeModal('createModal')" class="btn btn-secondary">Annuler</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Créer l'exercice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Voir l'exercice -->
    <div id="viewModal" class="modal">
        <div class="modal-content modal-form">
            <div class="modal-header">
                <h2>Aperçu de l'exercice</h2>
                <button onclick="closeModal('viewModal')" class="close-btn">&times;</button>
            </div>
            
            <div id="exercicePreview"></div>
            
            <div class="form-actions" style="margin-top: var(--spacing-lg);">
                <button onclick="closeModal('viewModal')" class="btn btn-secondary">Fermer</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'createModal') {
                resetForm();
            }
        }

        function resetForm() {
            document.getElementById('exerciceForm').reset();
            document.getElementById('exerciceId').value = '';
            document.getElementById('formAction').value = 'create';
            document.getElementById('modalTitle').textContent = 'Nouvel Exercice';
            document.getElementById('submitBtn').textContent = 'Créer l\'exercice';
        }

        function editExercice(id) {
            fetch(`api_exercices.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const exercice = data.exercice;
                        const tags = JSON.parse(exercice.tags || '[]');
                        
                        document.getElementById('exerciceId').value = exercice.id;
                        document.getElementById('formAction').value = 'update';
                        document.getElementById('modalTitle').textContent = 'Modifier l\'Exercice';
                        document.getElementById('submitBtn').textContent = 'Mettre à jour';
                        
                        document.getElementById('titre').value = exercice.titre;
                        document.getElementById('description').value = exercice.description;
                        document.getElementById('langage').value = exercice.langage;
                        document.getElementById('difficulte').value = exercice.difficulte;
                        document.getElementById('enonce').value = exercice.enonce;
                        document.getElementById('solution').value = exercice.solution;
                        document.getElementById('tags').value = tags.join(', ');
                        document.getElementById('temps_estime').value = exercice.temps_estime;
                        document.getElementById('points').value = exercice.points;
                        document.getElementById('auteur').value = exercice.auteur;
                        document.getElementById('statut').value = exercice.statut;
                        
                        openModal('createModal');
                    } else {
                        alert('Erreur lors du chargement de l\'exercice');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement de l\'exercice');
                });
        }

        function viewExercice(id) {
            fetch(`exercice_detail.php?id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('exercicePreview').innerHTML = html;
                    openModal('viewModal');
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement de l\'exercice');
                });
        }

        function deleteExercice(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet exercice ?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Fermer les modals en cliquant à l'extérieur
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>