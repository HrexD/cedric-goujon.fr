<?php
require 'config.php';

// Authentification admin
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_username'] = $admin_username;
            $_SESSION['login_time'] = time();
        } else {
            $error = "Identifiants incorrects";
        }
    }
    
    // Afficher le formulaire de connexion si pas connecté
    if (!isset($_SESSION['admin_logged'])) {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>🔐 Administration - Connexion</title>
            <link rel="stylesheet" href="style.css">
            <link rel="stylesheet" href="admin-modern.css">
            <link rel="icon" type="image/x-icon" href="favicon.png">
        </head>
        <body class="admin-page">
            
            <div class="login-container">
                <h1>🔐 Administration</h1>
                <h2>Tableau de bord du site</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">
                    Accès réservé à l'administrateur
                </p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <input type="text" name="username" class="form-input" placeholder="👤 Nom d'utilisateur" required>
                    <input type="password" name="password" class="form-input" placeholder="🔑 Mot de passe" required>
                    <button type="submit" class="btn btn-large" style="width: 100%; margin-top: var(--spacing-lg);">
                        🚪 Se connecter
                    </button>
                </form>
                
                <div style="margin-top: var(--spacing-xl); padding-top: var(--spacing-xl); border-top: 1px solid var(--border-color);">
                    <a href="index" class="btn btn-outline btn-small" style="text-decoration: none;">
                        ← Retour au site
                    </a>
                </div>
            </div>
            
            <script src="admin-modern.js"></script>
        </body>
        </html>
        <?php
        exit;
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin');
    exit;
}

// Récupérer les statistiques
try {
    // Messages de contact
    $stats_messages = [
        'total' => $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn(),
        'unread' => $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn(),
        'today' => $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE DATE(date_envoi) = CURDATE()")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats_messages = ['total' => 0, 'unread' => 0, 'today' => 0];
}

try {
    // Candidatures 
    $stats_candidatures = [
        'total' => $pdo->query("SELECT COUNT(*) FROM candidatures")->fetchColumn(),
        'pending' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE statut = 'en_attente'")->fetchColumn(),
        'recent' => $pdo->query("SELECT COUNT(*) FROM candidatures WHERE DATE(date_candidature) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn()
    ];
    
    // Dernières candidatures
    $recent_candidatures = $pdo->query("
        SELECT entreprise, poste, date_candidature, statut 
        FROM candidatures 
        ORDER BY date_candidature DESC 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $stats_candidatures = ['total' => 0, 'pending' => 0, 'recent' => 0];
    $recent_candidatures = [];
}

try {
    // Projets (si table existe)
    $stats_projets = [
        'total' => $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn(),
        'active' => $pdo->query("SELECT COUNT(*) FROM projets WHERE statut = 'actif'")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats_projets = ['total' => 0, 'active' => 0];
}

// Derniers messages
try {
    $recent_messages = $pdo->query("
        SELECT nom, email, sujet, date_envoi, lu 
        FROM messages_contact 
        ORDER BY date_envoi DESC 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $recent_messages = [];
}

// Informations système
$system_info = [
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Non détecté',
    'mysql_version' => $pdo->query("SELECT VERSION()")->fetchColumn(),
    'disk_space' => function_exists('disk_free_space') ? disk_free_space('.') : null
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🎛️ Administration - Tableau de bord</title>
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
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="user-info">
                <strong>👤 Admin</strong>
                <div style="font-size: 0.8em; opacity: 0.8; margin-top: 0.5rem;">
                    Connecté depuis <?= date('H:i', $_SESSION['login_time'] ?? time()) ?>
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                <li><a href="admin" class="active">📊 Tableau de bord</a></li>
                <li><a href="admin_candidatures.php">💼 Candidatures</a></li>
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
                <h1>🎛️ Tableau de bord</h1>
                <p>Administration du site cedric-goujon.fr</p>
                <div style="margin-top: 1rem; font-size: 0.9em; opacity: 0.8;">
                    Dernière connexion : <?= date('d/m/Y à H:i') ?>
                </div>
            </div>
            
            <!-- Statistiques principales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📧</div>
                    <div class="stat-value"><?= $stats_messages['total'] ?></div>
                    <div class="stat-label">Messages reçus</div>
                    <?php if ($stats_messages['unread'] > 0): ?>
                        <div class="badge badge-danger mt-sm">
                            <?= $stats_messages['unread'] ?> non lu(s)
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💼</div>
                    <div class="stat-value"><?= $stats_candidatures['total'] ?></div>
                    <div class="stat-label">Candidatures</div>
                    <?php if ($stats_candidatures['pending'] > 0): ?>
                        <div class="badge badge-warning mt-sm">
                            <?= $stats_candidatures['pending'] ?> en attente
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🚀</div>
                    <div class="stat-value"><?= $stats_projets['total'] ?></div>
                    <div class="stat-label">Projets</div>
                    <div class="badge badge-success mt-sm">
                        <?= $stats_projets['active'] ?> actif(s)
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value"><?= $stats_candidatures['recent'] ?></div>
                    <div class="stat-label">Cette semaine</div>
                </div>
            </div>
            
            <!-- Messages récents et actions rapides -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-xl); margin-bottom: var(--spacing-xl);">
                <!-- Messages récents -->
                <div class="admin-card">
                    <h2 class="section-title">📧 Messages récents</h2>
                </div>
            </div>
            
            <!-- Activité récente -->
            <div class="recent-section">
                <!-- Messages récents -->
                <div class="recent-card">
                    <h3 style="margin-bottom: 1rem; color: var(--primary);">
                        📧 Messages récents
                        <a href="admin_messages" style="float: right; font-size: 0.8em; color: var(--accent);">Voir tous →</a>
                    </h3>
                    
                    <?php if (empty($recent_messages)): ?>
                        <div class="text-center text-muted" style="padding: var(--spacing-xl);">
                            📭 Aucun message récent
                        </div>
                    <?php else: ?>
                        <div class="admin-table">
                            <tbody>
                                <?php foreach ($recent_messages as $message): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: var(--spacing-md);">
                                            <div class="flex items-center gap-sm">
                                                <span style="font-size: 1.2rem;">
                                                    <?= $message['lu'] ? '✅' : '🔴' ?>
                                                </span>
                                                <div>
                                                    <div class="font-semibold <?= $message['lu'] ? '' : 'text-primary' ?>">
                                                        <?= htmlspecialchars($message['nom']) ?>
                                                    </div>
                                                    <div class="text-sm text-muted">
                                                        <?= htmlspecialchars(substr($message['sujet'], 0, 50)) ?><?= strlen($message['sujet']) > 50 ? '...' : '' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right text-sm text-muted" style="padding: var(--spacing-md);">
                                            <?= date('d/m H:i', strtotime($message['date_envoi'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-lg">
                        <a href="admin_messages" class="btn btn-outline">Voir tous les messages</a>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="admin-card">
                    <h2 class="section-title">⚡ Actions rapides</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                        <a href="upload.php" class="btn btn-primary">
                            📤 Uploader des fichiers
                        </a>
                        <a href="admin_exercices.php" class="btn btn-primary">
                            📚 Gérer les exercices
                        </a>
                        <a href="admin_messages" class="btn btn-secondary">
                            📧 Gérer les messages
                        </a>
                        <a href="admin_candidatures" class="btn btn-secondary">
                            💼 Gérer les candidatures
                        </a>
                        <a href="admin_gallery" class="btn btn-secondary">
                            🖼️ Galerie de fichiers
                        </a>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <h3 class="section-title" style="font-size: var(--font-size-lg);">📊 Infos système</h3>
                    <div style="font-size: var(--font-size-sm); color: var(--text-muted); line-height: 1.8;">
                        <div><strong>PHP:</strong> <?= $system_info['php_version'] ?></div>
                        <div><strong>MySQL:</strong> <?= $system_info['mysql_version'] ?></div>
                        <div><strong>Serveur:</strong> <?= htmlspecialchars($system_info['server']) ?></div>
                        <?php if ($system_info['disk_space']): ?>
                            <div><strong>Espace disque:</strong> <?= round($system_info['disk_space'] / 1024 / 1024 / 1024, 2) ?> GB</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('adminHamburger');
            const sidebar = document.getElementById('adminSidebar');
            
            if (hamburger && sidebar) {
                hamburger.addEventListener('click', function() {
                    hamburger.classList.toggle('active');
                    sidebar.classList.toggle('active');
                });
                
                document.addEventListener('click', function(e) {
                    if (!hamburger.contains(e.target) && !sidebar.contains(e.target)) {
                        hamburger.classList.remove('active');
                        sidebar.classList.remove('active');
                    }
                });
                
                const menuLinks = sidebar.querySelectorAll('a');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        hamburger.classList.remove('active');
                        sidebar.classList.remove('active');
                    });
                });
            }
        });
    </script>
    <script src="admin-modern.js"></script>
</body>
</html>