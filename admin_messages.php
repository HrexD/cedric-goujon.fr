<?php
require 'config.php';

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin');
    exit;
}

// Marquer un message comme lu/non lu
if (isset($_GET['toggle_read'])) {
    $id = (int)$_GET['toggle_read'];
    $stmt = $pdo->prepare("UPDATE messages_contact SET lu = !lu WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: admin_messages.php');
    exit;
}

// Supprimer un message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM messages_contact WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: admin_messages.php');
    exit;
}

// Récupérer les messages
try {
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT * FROM messages_contact WHERE 1=1";
    $params = [];
    
    if ($filter === 'unread') {
        $sql .= " AND lu = 0";
    } elseif ($filter === 'read') {
        $sql .= " AND lu = 1";
    }
    
    if (!empty($search)) {
        $sql .= " AND (nom LIKE ? OR email LIKE ? OR sujet LIKE ? OR message LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    $sql .= " ORDER BY date_envoi DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
    
    // Compter les messages non lus
    $unread_count = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn();
    
} catch (PDOException $e) {
    $error = "Erreur : La table des messages n'existe pas encore. Veuillez exécuter le script SQL fourni.";
    $messages = [];
    $unread_count = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📧 Administration - Messages</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-modern.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <button id="theme-toggle" aria-label="Basculer thème" class="theme-toggle">☀️</button>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="user-info">
                <strong>� Admin</strong>
                <div style="font-size: 0.8em; opacity: 0.8; margin-top: 0.5rem;">
                    Interface d'administration
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin">📊 Tableau de bord</a></li>
                    <li><a href="admin_candidatures.php">💼 Candidatures</a></li>
                    <li><a href="admin_messages.php" class="active">📧 Messages</a></li>
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
                <h1>📧 Gestion des Messages</h1>
                <p class="admin-subtitle">Administration des messages de contact</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="notification notification-error">
                    <span class="notification-icon">❌</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📧</div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($messages) ?></div>
                        <div class="stat-label">Messages total</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--warning);">🔔</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--warning);"><?= $unread_count ?></div>
                        <div class="stat-label">Non lus</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--success);">✅</div>
                    <div class="stat-content">
                        <div class="stat-number" style="color: var(--success);"><?= count($messages) - $unread_count ?></div>
                        <div class="stat-label">Lus</div>
                    </div>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <div class="admin-toolbar">
                <div class="filter-group">
                    <span class="filter-label">Filtrer :</span>
                    <a href="?filter=all&search=<?= htmlspecialchars($search) ?>" 
                       class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                       Tous (<?= count($messages) ?>)
                    </a>
                    <a href="?filter=unread&search=<?= htmlspecialchars($search) ?>" 
                       class="filter-btn <?= $filter === 'unread' ? 'active' : '' ?>">
                       Non lus (<?= $unread_count ?>)
                    </a>
                    <a href="?filter=read&search=<?= htmlspecialchars($search) ?>" 
                       class="filter-btn <?= $filter === 'read' ? 'active' : '' ?>">
                       Lus (<?= count($messages) - $unread_count ?>)
                    </a>
                </div>
                
                <div class="toolbar-actions">
                    <form method="GET" class="search-form">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <input type="text" name="search" placeholder="Rechercher..." 
                               value="<?= htmlspecialchars($search) ?>" class="form-input">
                        <button type="submit" class="btn btn-secondary">
                            <span class="btn-icon">🔍</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des messages -->
            <div class="admin-content">
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>Aucun message</h3>
                        <p class="text-muted">Aucun message ne correspond à vos critères.</p>
                    </div>
                <?php else: ?>
                    <div class="data-grid">
                        <?php foreach ($messages as $message): ?>
                            <div class="data-card <?= !$message['lu'] ? 'unread' : '' ?>">
                                <div class="card-header">
                                    <h3>
                                        <?= !$message['lu'] ? '🔔 ' : '📧 ' ?>
                                        <?= htmlspecialchars($message['nom']) ?>
                                    </h3>
                                    <div class="message-date">
                                        <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <p><strong>Email :</strong> 
                                        <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="email-link">
                                            <?= htmlspecialchars($message['email']) ?>
                                        </a>
                                    </p>
                                    <p><strong>Sujet :</strong> <?= htmlspecialchars($message['sujet']) ?></p>
                                    <div class="message-content">
                                        <?= nl2br(htmlspecialchars($message['message'])) ?>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <a href="?toggle_read=<?= $message['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" 
                                       class="btn btn-small <?= $message['lu'] ? 'btn-warning' : 'btn-success' ?>">
                                        <?= $message['lu'] ? '📖 Marquer non lu' : '✅ Marquer lu' ?>
                                    </a>
                                    
                                    <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= urlencode($message['sujet']) ?>" 
                                       class="btn btn-small btn-primary">
                                        📧 Répondre
                                    </a>
                                    
                                    <a href="?delete=<?= $message['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" 
                                       class="btn btn-small btn-danger" 
                                       onclick="return confirm('Supprimer ce message ?')">
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

    <script src="admin-modern.js"></script>
</body>
</html>