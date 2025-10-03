<?php
require 'config.php';

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin');
    exit;
}

$success = '';
$error = '';

// Actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $entreprise = sanitize_input($_POST['entreprise'] ?? '');
            $poste = sanitize_input($_POST['poste'] ?? '');
            $type_contrat = sanitize_input($_POST['type_contrat'] ?? '');
            $localisation = sanitize_input($_POST['localisation'] ?? '');
            $salaire = sanitize_input($_POST['salaire'] ?? '');
            $date_candidature = $_POST['date_candidature'] ?? date('Y-m-d');
            $statut = $_POST['statut'] ?? 'en_attente';
            $source = sanitize_input($_POST['source'] ?? '');
            $notes = sanitize_input($_POST['notes'] ?? '');
            
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
            $id = (int)$_POST['id'];
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
    $id = (int)$_GET['delete'];
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
$sort = $_GET['sort'] ?? 'date_candidature_desc';

try {
    $sql = "SELECT * FROM candidatures WHERE 1=1";
    $params = [];
    
    // Filtres par statut
    if ($filter !== 'all') {
        $sql .= " AND statut = ?";
        $params[] = $filter;
    }
    
    // Recherche
    if (!empty($search)) {
        $sql .= " AND (entreprise LIKE ? OR poste LIKE ? OR localisation LIKE ? OR notes LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
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
        'total' => $pdo->query("SELECT COUNT(*) FROM candidatures")->fetchColumn(),
        'en_attente' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'en_attente'")->fetchColumn(),
        'accepte' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'accepte'")->fetchColumn(),
        'refuse' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'refuse'")->fetchColumn(),
        'entretien' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'entretien'")->fetchColumn()
    ];
    
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
    $candidatures = [];
    $stats = ['total' => 0, 'en_attente' => 0, 'accepte' => 0, 'refuse' => 0, 'entretien' => 0];
}

// Statuts disponibles avec leurs couleurs
$statuts = [
    'en_attente' => ['label' => 'En attente', 'color' => '#ffc107'],
    'entretien' => ['label' => 'Entretien', 'color' => '#17a2b8'],
    'accepte' => ['label' => 'Accepté', 'color' => '#28a745'],
    'refuse' => ['label' => 'Refusé', 'color' => '#dc3545'],
    'relance' => ['label' => 'Relance', 'color' => '#fd7e14']
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>💼 Administration - Candidatures</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
    
    <div class="message-header">
        <div>
            <h1>💼 Gestion des Candidatures</h1>
            <p style="color: var(--text-muted);">Administration des candidatures d'emploi</p>
        </div>
        <div>
            <a href="admin" class="btn-small" style="background: var(--text-muted); color: white; margin-right: 1rem;">
                ← Tableau de bord
            </a>
            <button onclick="openModal('addModal')" class="cta">+ Nouvelle candidature</button>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Statistiques -->
    <div class="stats-candidatures">
        <div class="stat-card">
            <span class="stat-number"><?= $stats['total'] ?></span>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #ffc107;"><?= $stats['en_attente'] ?></span>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #17a2b8;"><?= $stats['entretien'] ?></span>
            <div class="stat-label">Entretiens</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #28a745;"><?= $stats['accepte'] ?></span>
            <div class="stat-label">Acceptées</div>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #dc3545;"><?= $stats['refuse'] ?></span>
            <div class="stat-label">Refusées</div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters-candidatures">
        <span><strong>Filtrer :</strong></span>
        <a href="?filter=all&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
           class="btn-small" style="background: <?= $filter === 'all' ? 'var(--accent)' : 'var(--text-muted)' ?>">
           Toutes (<?= $stats['total'] ?>)
        </a>
        <a href="?filter=en_attente&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
           class="btn-small" style="background: <?= $filter === 'en_attente' ? '#ffc107' : 'var(--text-muted)' ?>">
           En attente (<?= $stats['en_attente'] ?>)
        </a>
        <a href="?filter=entretien&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
           class="btn-small" style="background: <?= $filter === 'entretien' ? '#17a2b8' : 'var(--text-muted)' ?>">
           Entretiens (<?= $stats['entretien'] ?>)
        </a>
        
        <div style="margin-left: auto; display: flex; gap: 0.5rem;">
            <select onchange="window.location.href='?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&sort=' + this.value" 
                    style="padding: 0.5rem; border-radius: var(--radius);">
                <option value="date_candidature_desc" <?= $sort === 'date_candidature_desc' ? 'selected' : '' ?>>
                    Date (récent)
                </option>
                <option value="date_candidature_asc" <?= $sort === 'date_candidature_asc' ? 'selected' : '' ?>>
                    Date (ancien)
                </option>
                <option value="entreprise" <?= $sort === 'entreprise' ? 'selected' : '' ?>>
                    Entreprise A-Z
                </option>
                <option value="statut" <?= $sort === 'statut' ? 'selected' : '' ?>>
                    Statut
                </option>
            </select>
            
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="text" name="search" placeholder="Rechercher..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                <button type="submit" class="btn-small btn-read">🔍</button>
            </form>
        </div>
    </div>
    
    <!-- Liste des candidatures -->
    <?php if (empty($candidatures)): ?>
        <div class="candidature-card">
            <div class="candidature-content" style="text-align: center; padding: 3rem;">
                <h3>📭 Aucune candidature</h3>
                <p style="color: var(--text-muted);">Aucune candidature ne correspond à vos critères.</p>
                <button onclick="openModal('addModal')" class="cta" style="margin-top: 1rem;">
                    + Ajouter la première candidature
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($candidatures as $candidature): ?>
            <div class="candidature-card">
                <div class="candidature-header">
                    <div>
                        <h3 style="margin: 0; color: var(--primary);">
                            🏢 <?= htmlspecialchars($candidature['entreprise']) ?>
                        </h3>
                        <div style="color: var(--text-muted); margin-top: 0.3rem;">
                            📝 <?= htmlspecialchars($candidature['poste']) ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span class="status-badge" style="background-color: <?= $statuts[$candidature['statut']]['color'] ?>">
                            <?= $statuts[$candidature['statut']]['label'] ?>
                        </span>
                        <div style="font-size: 0.8em; color: var(--text-muted); margin-top: 0.5rem;">
                            📅 <?= date('d/m/Y', strtotime($candidature['date_candidature'])) ?>
                        </div>
                    </div>
                </div>
                <div class="candidature-content">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <?php if ($candidature['type_contrat']): ?>
                            <div><strong>Type :</strong> <?= htmlspecialchars($candidature['type_contrat']) ?></div>
                        <?php endif; ?>
                        <?php if ($candidature['localisation']): ?>
                            <div><strong>Localisation :</strong> 📍 <?= htmlspecialchars($candidature['localisation']) ?></div>
                        <?php endif; ?>
                        <?php if ($candidature['salaire']): ?>
                            <div><strong>Salaire :</strong> 💰 <?= htmlspecialchars($candidature['salaire']) ?></div>
                        <?php endif; ?>
                        <?php if ($candidature['source']): ?>
                            <div><strong>Source :</strong> <?= htmlspecialchars($candidature['source']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($candidature['notes']): ?>
                        <div style="background: var(--background); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem;">
                            <strong>Notes :</strong><br>
                            <?= nl2br(htmlspecialchars($candidature['notes'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="quick-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id" value="<?= $candidature['id'] ?>">
                            <select name="statut" onchange="this.form.submit()" class="btn-small">
                                <?php foreach ($statuts as $key => $statut): ?>
                                    <option value="<?= $key ?>" <?= $candidature['statut'] === $key ? 'selected' : '' ?>>
                                        <?= $statut['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        
                        <button onclick="editCandidature(<?= htmlspecialchars(json_encode($candidature)) ?>)" 
                                class="btn-small" style="background: var(--secondary);">
                            ✏️ Modifier
                        </button>
                        
                        <a href="?delete=<?= $candidature['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>" 
                           class="btn-small" style="background: #dc3545;" 
                           onclick="return confirm('Supprimer cette candidature ?')">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Modal Ajouter candidature -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>➕ Nouvelle candidature</h2>
                <button onclick="closeModal('addModal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">✖️</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>🏢 Entreprise *</label>
                        <input type="text" name="entreprise" required>
                    </div>
                    <div class="form-group">
                        <label>📝 Poste *</label>
                        <input type="text" name="poste" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>💼 Type de contrat</label>
                        <select name="type_contrat">
                            <option value="">Sélectionner...</option>
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="Stage">Stage</option>
                            <option value="Freelance">Freelance</option>
                            <option value="Alternance">Alternance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>📍 Localisation</label>
                        <input type="text" name="localisation" placeholder="Paris, Lyon, Remote...">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>💰 Salaire</label>
                        <input type="text" name="salaire" placeholder="45k€, 500€/jour...">
                    </div>
                    <div class="form-group">
                        <label>📅 Date candidature</label>
                        <input type="date" name="date_candidature" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>📊 Statut</label>
                        <select name="statut">
                            <?php foreach ($statuts as $key => $statut): ?>
                                <option value="<?= $key ?>" <?= $key === 'en_attente' ? 'selected' : '' ?>>
                                    <?= $statut['label'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>🔗 Source</label>
                    <input type="text" name="source" placeholder="LinkedIn, Indeed, site entreprise...">
                </div>
                
                <div class="form-group">
                    <label>📝 Notes</label>
                    <textarea name="notes" rows="4" placeholder="Remarques, contacts, étapes..."></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('addModal')" class="btn-small" style="background: var(--text-muted);">
                        Annuler
                    </button>
                    <button type="submit" class="cta">✅ Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Modifier candidature -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>✏️ Modifier candidature</h2>
                <button onclick="closeModal('editModal')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">✖️</button>
            </div>
            
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <!-- Le contenu sera généré par JavaScript -->
                <div id="editFormContent"></div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('editModal')" class="btn-small" style="background: var(--text-muted);">
                        Annuler
                    </button>
                    <button type="submit" class="cta">✅ Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script src="script.js"></script>
    <script src="admin.js"></script>
</body>
</html>