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
            <link rel="stylesheet" href="admin.css">
            <link rel="icon" type="image/x-icon" href="favicon.png">
        </head>
        <body>
            <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
            
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
                    <input type="text" name="username" placeholder="👤 Nom d'utilisateur" required>
                    <input type="password" name="password" placeholder="🔑 Mot de passe" required>
                    <button type="submit" class="cta" style="width: 100%; margin-top: 1rem;">
                        🚪 Se connecter
                    </button>
                </form>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--text-muted);">
                    <a href="index" class="btn-small" style="color: var(--text-muted);">
                        ← Retour au site
                    </a>
                </div>
            </div>
            
            <script src="script.js"></script>
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
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
    
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="user-info">
                <strong>👤 Cédric</strong>
                <div style="font-size: 0.8em; color: var(--text-muted); margin-top: 0.5rem;">
                    Connecté depuis <?= date('H:i', $_SESSION['login_time']) ?>
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="admin"><span class="icon">🎛️</span> Tableau de bord</a></li>
                    <li><a href="admin_messages"><span class="icon">📧</span> Messages</a></li>
                    <li><a href="admin_candidatures"><span class="icon">💼</span> Candidatures</a></li>
                    <li><a href="admin_projets"><span class="icon">🚀</span> Projets</a></li>
                    <li><a href="admin_gallery"><span class="icon">🖼️</span> Galerie</a></li>
                    <li><a href="admin_utilisateur"><span class="icon">👤</span> Profil</a></li>
                    <li><a href="admin_systeme"><span class="icon">⚙️</span> Système</a></li>
                    <li style="margin-top: 2rem; border-top: 1px solid var(--background); padding-top: 1rem;">
                        <a href="index" style="color: var(--text-muted);"><span class="icon">🌐</span> Voir le site</a>
                    </li>
                    <li><a href="?logout=1" style="color: #dc3545;"><span class="icon">🚪</span> Déconnexion</a></li>
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
                    <span class="stat-number"><?= $stats_messages['total'] ?></span>
                    <div class="stat-label">Messages reçus</div>
                    <?php if ($stats_messages['unread'] > 0): ?>
                        <div style="color: var(--accent); margin-top: 0.5rem; font-weight: bold;">
                            <?= $stats_messages['unread'] ?> non lu(s)
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <span class="stat-number"><?= $stats_candidatures['total'] ?></span>
                    <div class="stat-label">Candidatures</div>
                    <?php if ($stats_candidatures['pending'] > 0): ?>
                        <div style="color: var(--accent); margin-top: 0.5rem; font-weight: bold;">
                            <?= $stats_candidatures['pending'] ?> en attente
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stat-card">
                    <span class="stat-number"><?= $stats_projets['total'] ?></span>
                    <div class="stat-label">Projets</div>
                    <div style="color: var(--secondary); margin-top: 0.5rem;">
                        <?= $stats_projets['active'] ?> actif(s)
                    </div>
                </div>
                
                <div class="stat-card">
                    <span class="stat-number"><?= $stats_messages['today'] ?></span>
                    <div class="stat-label">Messages aujourd'hui</div>
                    <div style="color: var(--secondary); margin-top: 0.5rem;">
                        <?= $stats_candidatures['recent'] ?> candidatures (7j)
                    </div>
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
                        <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            📭 Aucun message
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $message): ?>
                            <div class="recent-item">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <strong style="color: <?= $message['lu'] ? 'var(--text)' : 'var(--accent)' ?>;">
                                            <?= $message['lu'] ? '✅' : '🔴' ?> <?= htmlspecialchars($message['nom']) ?>
                                        </strong>
                                        <div style="font-size: 0.9em; color: var(--text-muted); margin-top: 0.2rem;">
                                            <?= htmlspecialchars(substr($message['sujet'], 0, 40)) ?><?= strlen($message['sujet']) > 40 ? '...' : '' ?>
                                        </div>
                                    </div>
                                    <div style="font-size: 0.8em; color: var(--text-muted);">
                                        <?= date('d/m H:i', strtotime($message['date_envoi'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Candidatures récentes -->
                <div class="recent-card">
                    <h3 style="margin-bottom: 1rem; color: var(--primary);">
                        💼 Candidatures récentes
                        <a href="admin_candidatures" style="float: right; font-size: 0.8em; color: var(--accent);">Voir toutes →</a>
                    </h3>
                    
                    <?php if (empty($recent_candidatures)): ?>
                        <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            📋 Aucune candidature
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_candidatures as $candidature): ?>
                            <div class="recent-item">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <strong><?= htmlspecialchars($candidature['entreprise']) ?></strong>
                                        <div style="font-size: 0.9em; color: var(--text-muted); margin-top: 0.2rem;">
                                            <?= htmlspecialchars($candidature['poste']) ?>
                                        </div>
                                        <div style="font-size: 0.8em; margin-top: 0.3rem;">
                                            <span style="background: var(--accent); color: white; padding: 0.2rem 0.5rem; border-radius: 10px;">
                                                <?= ucfirst(str_replace('_', ' ', $candidature['statut'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div style="font-size: 0.8em; color: var(--text-muted);">
                                        <?= date('d/m/Y', strtotime($candidature['date_candidature'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informations système -->
            <div class="recent-card" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem; color: var(--primary);">⚙️ Informations système</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div><strong>PHP :</strong> <?= $system_info['php_version'] ?></div>
                    <div><strong>MySQL :</strong> <?= $system_info['mysql_version'] ?></div>
                    <div><strong>Serveur :</strong> <?= htmlspecialchars($system_info['server']) ?></div>
                    <?php if ($system_info['disk_space']): ?>
                        <div><strong>Espace libre :</strong> <?= round($system_info['disk_space'] / 1024 / 1024 / 1024, 2) ?> GB</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="script.js"></script>
    <script src="admin.js"></script>
    <script src="message_widget.js"></script>
</body>
</html>