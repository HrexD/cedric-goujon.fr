<?php
require 'config.php';

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin');
    exit;
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
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
    <title>Administration - Messages de contact</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body>
    <!-- Bouton thème -->
    <button id="theme-toggle" aria-label="Basculer thème">☀️</button>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>📧 Gestion des Messages</h1>
            <p style="color: var(--text-muted);">Administration des messages de contact</p>
        </div>
        <div>
            <a href="admin" class="btn-small" style="background: var(--text-muted); color: white; margin-right: 1rem;">
                ← Tableau de bord
            </a>
            <a href="?logout=1" class="btn-small btn-delete">Déconnexion</a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="admin-stats">
        <div class="stat-card">
            <h3><?= count($messages) ?></h3>
            <p>Messages total</p>
        </div>
        <div class="stat-card">
            <h3 style="color: var(--accent);"><?= $unread_count ?></h3>
            <p>Non lus</p>
        </div>
        <div class="stat-card">
            <h3><?= count($messages) - $unread_count ?></h3>
            <p>Lus</p>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="filters">
        <span><strong>Filtrer :</strong></span>
        <a href="?filter=all&search=<?= htmlspecialchars($search) ?>" 
           class="btn-small <?= $filter === 'all' ? 'btn-read' : '' ?>" 
           style="background: <?= $filter === 'all' ? 'var(--accent)' : 'var(--text-muted)' ?>">
           Tous (<?= count($messages) ?>)
        </a>
        <a href="?filter=unread&search=<?= htmlspecialchars($search) ?>" 
           class="btn-small <?= $filter === 'unread' ? 'btn-read' : '' ?>"
           style="background: <?= $filter === 'unread' ? 'var(--accent)' : 'var(--text-muted)' ?>">
           Non lus (<?= $unread_count ?>)
        </a>
        <a href="?filter=read&search=<?= htmlspecialchars($search) ?>" 
           class="btn-small <?= $filter === 'read' ? 'btn-read' : '' ?>"
           style="background: <?= $filter === 'read' ? 'var(--accent)' : 'var(--text-muted)' ?>">
           Lus (<?= count($messages) - $unread_count ?>)
        </a>
        
        <form method="GET" style="margin-left: auto; display: flex; gap: 0.5rem;">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="text" name="search" placeholder="Rechercher..." 
                   value="<?= htmlspecialchars($search) ?>" class="search-input">
            <button type="submit" class="btn-small btn-read">🔍</button>
        </form>
    </div>

    <!-- Messages -->
    <?php if (empty($messages)): ?>
        <div class="message-card">
            <div class="message-content" style="text-align: center; color: var(--text-muted);">
                <h3>📭 Aucun message</h3>
                <p>Aucun message ne correspond à vos critères de recherche.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-card <?= !$message['lu'] ? 'message-unread' : '' ?>">
                <div class="message-header">
                    <div>
                        <strong style="color: var(--accent);">
                            <?= !$message['lu'] ? '🔴 ' : '✅ ' ?>
                            <?= htmlspecialchars($message['nom']) ?>
                        </strong>
                        <span style="color: var(--text-muted); margin-left: 1rem;">
                            <?= htmlspecialchars($message['email']) ?>
                        </span>
                        <span style="color: var(--text-muted); margin-left: 1rem; font-size: 0.9em;">
                            📅 <?= date('d/m/Y à H:i', strtotime($message['date_envoi'])) ?>
                        </span>
                    </div>
                    <div class="actions">
                        <a href="?toggle_read=<?= $message['id'] ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" 
                           class="btn-small <?= $message['lu'] ? 'btn-unread' : 'btn-read' ?>">
                            <?= $message['lu'] ? 'Marquer non lu' : 'Marquer lu' ?>
                        </a>
                        <a href="?delete=<?= $message['id'] ?>" 
                           class="btn-small btn-delete" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
                <div class="message-content">
                    <h4 style="margin: 0 0 1rem 0; color: var(--primary);">
                        📝 <?= htmlspecialchars($message['sujet']) ?>
                    </h4>
                    <div style="background: var(--background); padding: 1rem; border-radius: var(--radius); line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($message['message'])) ?>
                    </div>
                    <div style="margin-top: 1rem;">
                        <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: <?= htmlspecialchars($message['sujet']) ?>" 
                           class="cta" style="display: inline-block;">
                            📧 Répondre par email
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="script.js"></script>
    <script src="admin.js"></script>
</body>
</html>