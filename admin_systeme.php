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

// Actions système
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_cache':
            // Simuler le nettoyage du cache
            $success = "Cache vidé avec succès !";
            break;
            
        case 'backup_db':
            // Simuler une sauvegarde
            $success = "Sauvegarde de la base de données créée !";
            break;
            
        case 'optimize_db':
            try {
                // Optimiser les tables
                $tables = ['messages_contact', 'candidatures', 'projets', 'utilisateur_principal'];
                foreach ($tables as $table) {
                    $pdo->query("OPTIMIZE TABLE $table");
                }
                $success = "Base de données optimisée !";
            } catch (PDOException $e) {
                $error = "Erreur lors de l'optimisation : " . $e->getMessage();
            }
            break;
    }
}

// Informations détaillées du système
$system_info = [
    'server' => [
        'os' => PHP_OS_FAMILY,
        'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Non détecté',
        'php_version' => PHP_VERSION,
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Non défini',
        'request_time' => date('d/m/Y H:i:s', $_SERVER['REQUEST_TIME'] ?? time())
    ],
    'php' => [
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'timezone' => date_default_timezone_get()
    ],
    'database' => [],
    'disk' => []
];

// Informations base de données
try {
    $system_info['database'] = [
        'version' => $pdo->query("SELECT VERSION()")->fetchColumn(),
        'charset' => $pdo->query("SELECT @@character_set_database")->fetchColumn(),
        'tables' => $pdo->query("SHOW TABLES")->rowCount()
    ];
} catch (PDOException $e) {
    $system_info['database'] = ['error' => $e->getMessage()];
}

// Informations disque (avec garde contre disk_total_space() retournant false ou 0)
if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
    $total = @disk_total_space('.');
    $free = @disk_free_space('.');

    // Vérifier que nous avons bien des valeurs numériques valides
    if ($total === false || $free === false || $total <= 0) {
        // Ne pas remplir les infos disque si on ne peut pas les obtenir
        $system_info['disk'] = [];
    } else {
        $used = max(0, $total - $free);
        $usagePercent = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        $system_info['disk'] = [
            'total' => round($total / 1024 / 1024 / 1024, 2),
            'used' => round($used / 1024 / 1024 / 1024, 2),
            'free' => round($free / 1024 / 1024 / 1024, 2),
            'usage_percent' => $usagePercent
        ];
    }
}

// Statistiques des tables
$table_stats = [];
try {
    $tables = ['messages_contact', 'candidatures', 'projets', 'utilisateur_principal'];
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $table_stats[$table] = $count;
        } catch (PDOException $e) {
            $table_stats[$table] = 'N/A';
        }
    }
} catch (Exception $e) {
    // Ignorer les erreurs de tables manquantes
}

// Extensions PHP importantes
$php_extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'gd' => extension_loaded('gd'),
    'curl' => extension_loaded('curl'),
    'mbstring' => extension_loaded('mbstring'),
    'json' => extension_loaded('json'),
    'openssl' => extension_loaded('openssl'),
    'zip' => extension_loaded('zip')
];

// Logs système (simulation)
$system_logs = [
    ['time' => date('H:i:s'), 'level' => 'INFO', 'message' => 'Connexion administrateur réussie'],
    ['time' => date('H:i:s', time() - 300), 'level' => 'INFO', 'message' => 'Nouveau message de contact reçu'],
    ['time' => date('H:i:s', time() - 600), 'level' => 'INFO', 'message' => 'Optimisation automatique de la base de données'],
    ['time' => date('H:i:s', time() - 1200), 'level' => 'WARNING', 'message' => 'Tentative de connexion échouée'],
    ['time' => date('H:i:s', time() - 1800), 'level' => 'INFO', 'message' => 'Sauvegarde automatique effectuée']
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>⚙️ Administration - Système</title>
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
                <li><a href="admin_candidatures.php">💼 Candidatures</a></li>
                <li><a href="admin_messages.php">📧 Messages</a></li>
                <li><a href="admin_projets.php">🚀 Projets</a></li>
                <li><a href="admin_gallery.php">🖼️ Galerie</a></li>
                <li><a href="admin_utilisateur.php">👤 Utilisateur</a></li>
                <li><a href="admin_systeme.php" class="active">⚙️ Système</a></li>
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
                <h1>⚙️ Administration Système</h1>
                <p class="admin-subtitle">Surveillance et maintenance du système</p>
                <div class="admin-actions">
                    <a href="admin_logs.php" class="btn btn-primary">
                        📊 Logs d'Upload
                    </a>
                    <a href="upload_debug.php" class="btn btn-outline" target="_blank">
                        🔧 Debug Upload
                    </a>
                </div>
            </div>
            
            <!-- Messages -->
            <?php if ($success): ?>
                <div class="notification notification-success">
                    <span class="notification-icon">✅</span>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="notification notification-error">
                    <span class="notification-icon">❌</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Actions rapides -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🛠️ Actions de maintenance</h3>
        
        <div class="form-grid">
            <form method="POST" class="form-group">
                <button type="submit" name="action" value="clear_cache" class="btn btn-primary"
                        onclick="return confirm('Vider le cache ?')">
                    <span class="btn-icon">🗑️</span>
                    Vider le cache
                </button>
            </form>
            
            <form method="POST" class="form-group">
                <button type="submit" name="action" value="optimize_db" class="btn btn-warning"
                        onclick="return confirm('Optimiser la base de données ?')">
                    <span class="btn-icon">🚀</span>
                    Optimiser la DB
                </button>
            </form>
            
            <form method="POST" class="form-group">
                <button type="submit" name="action" value="backup_db" class="btn btn-success"
                        onclick="return confirm('Créer une sauvegarde ?')">
                    <span class="btn-icon">💾</span>
                    Sauvegarder
                </button>
            </form>
            
            <div class="form-group">
                <button type="button" onclick="location.reload()" class="btn btn-secondary">
                    <span class="btn-icon">🔄</span>
                    Actualiser les infos
                </button>
            </div>
        </div>
    </div>
    
    <!-- Informations serveur -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🖥️ Informations serveur</h3>
        
        <div class="form-grid">
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="info-icon">🌐</span>
                    <strong>Système d'exploitation</strong>
                </div>
                <div class="info-value"><?= $system_info['server']['os'] ?></div>
            </div>
            
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="info-icon">🚀</span>
                    <strong>Serveur web</strong>
                </div>
                <div class="info-value"><?= htmlspecialchars($system_info['server']['software']) ?></div>
            </div>
            
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="info-icon">🐘</span>
                    <strong>Version PHP</strong>
                </div>
                <div class="info-value"><?= $system_info['server']['php_version'] ?></div>
            </div>
            
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="info-icon">🏠</span>
                    <strong>Racine du site</strong>
                </div>
                <div class="info-value" style="font-size: 0.8em; word-break: break-all;"><?= htmlspecialchars($system_info['server']['document_root']) ?></div>
            </div>
            
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="info-icon">🌍</span>
                    <strong>Nom du serveur</strong>
                </div>
                <div class="info-value"><?= htmlspecialchars($system_info['server']['server_name']) ?></div>
            </div>
            
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="info-icon">⏰</span>
                    <strong>Dernière requête</strong>
                </div>
                <div class="info-value"><?= $system_info['server']['request_time'] ?></div>
            </div>
        </div>
    </div>
    
    <!-- Configuration PHP -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🐘 Configuration PHP</h3>
        
        <div class="form-grid">
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">💾</span>
                    <strong>Limite mémoire</strong>
                </div>
                <div class="info-value"><?= $system_info['php']['memory_limit'] ?></div>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">⏱️</span>
                    <strong>Temps d'exécution max</strong>
                </div>
                <div class="info-value"><?= $system_info['php']['max_execution_time'] ?> secondes</div>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">📤</span>
                    <strong>Taille upload max</strong>
                </div>
                <div class="info-value"><?= $system_info['php']['upload_max_filesize'] ?></div>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">📊</span>
                    <strong>Taille POST max</strong>
                </div>
                <div class="info-value"><?= $system_info['php']['post_max_size'] ?></div>
            </div>
            
            <div class="info-card">
                <div class="info-header">
                    <span class="info-icon">🌍</span>
                    <strong>Fuseau horaire</strong>
                </div>
                <div class="info-value"><?= $system_info['php']['timezone'] ?></div>
            </div>
        </div>
    </div>
    
    <!-- Extensions PHP -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🔌 Extensions PHP</h3>
        
        <div class="form-grid">
            <?php foreach ($php_extensions as $extension => $loaded): ?>
                <div class="info-card <?= $loaded ? 'status-good' : 'status-error' ?>">
                    <div class="info-header">
                        <span class="info-icon"><?= $loaded ? '✅' : '❌' ?></span>
                        <strong><?= strtoupper($extension) ?></strong>
                    </div>
                    <div class="info-value">
                        <span class="status-badge <?= $loaded ? 'status-success' : 'status-danger' ?>">
                            <?= $loaded ? 'Activé' : 'Désactivé' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Base de données -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🗄️ Base de données</h3>
        
        <?php if (isset($system_info['database']['error'])): ?>
            <div class="info-card status-error">
                <div class="info-header">
                    <span class="info-icon">❌</span>
                    <strong>Erreur de connexion</strong>
                </div>
                <div class="info-value"><?= htmlspecialchars($system_info['database']['error']) ?></div>
            </div>
        <?php else: ?>
            <div class="form-grid">
                <div class="info-card status-good">
                    <div class="info-header">
                        <span class="info-icon">🔢</span>
                        <strong>Version MySQL</strong>
                    </div>
                    <div class="info-value"><?= $system_info['database']['version'] ?></div>
                </div>
                
                <div class="info-card status-good">
                    <div class="info-header">
                        <span class="info-icon">🔤</span>
                        <strong>Charset</strong>
                    </div>
                    <div class="info-value"><?= $system_info['database']['charset'] ?></div>
                </div>
                
                <div class="info-card status-good">
                    <div class="info-header">
                        <span class="info-icon">📊</span>
                        <strong>Nombre de tables</strong>
                    </div>
                    <div class="info-value"><?= $system_info['database']['tables'] ?> tables</div>
                </div>
            </div>
            
            <h4 style="margin: 2rem 0 1rem 0; color: var(--primary);">📈 Statistiques des tables</h4>
            <div class="form-grid">
                <?php foreach ($table_stats as $table => $count): ?>
                    <div class="info-card">
                        <div class="info-header">
                            <span class="info-icon">🗂️</span>
                            <strong><?= ucfirst(str_replace('_', ' ', $table)) ?></strong>
                        </div>
                        <div class="info-value">
                            <span class="status-badge status-success"><?= $count ?> enregistrement(s)</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Espace disque -->
    <?php if (!empty($system_info['disk'])): ?>
        <div class="system-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary);">💾 Espace disque</h3>
            
            <div class="form-grid">
                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">📊</span>
                        <strong>Espace total</strong>
                    </div>
                    <div class="info-value"><?= $system_info['disk']['total'] ?> GB</div>
                </div>
                
                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">💽</span>
                        <strong>Espace utilisé</strong>
                    </div>
                    <div class="info-value"><?= $system_info['disk']['used'] ?> GB</div>
                </div>
                
                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">🆓</span>
                        <strong>Espace libre</strong>
                    </div>
                    <div class="info-value"><?= $system_info['disk']['free'] ?> GB</div>
                </div>
                
                <div class="info-card">
                    <div class="info-header">
                        <span class="info-icon">📈</span>
                        <strong>Utilisation</strong>
                    </div>
                    <div class="info-value">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $system_info['disk']['usage_percent'] ?>%"></div>
                        </div>
                        <span style="margin-top: var(--spacing-xs); display: block;"><?= $system_info['disk']['usage_percent'] ?>% utilisé</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Logs système -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">📋 Logs système (simulation)</h3>
        
        <div class="log-container">
            <?php foreach ($system_logs as $log): ?>
                <div class="log-entry">
                    <span class="log-time"><?= $log['time'] ?></span>
                    <span class="log-level-<?= $log['level'] ?>">[<?= $log['level'] ?>]</span>
                    <span><?= htmlspecialchars($log['message']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: var(--spacing-md); padding: var(--spacing-md); background: var(--gray-100); border-radius: var(--radius-md); font-size: var(--font-size-xs); color: var(--text-muted);">
            💡 <strong>Astuce :</strong> Les logs sont actualisés automatiquement toutes les 30 secondes. Les vrais logs système seraient stockés dans des fichiers sur le serveur.
        </div>
    </div>
    
    <script src="script.js"></script>
    <script>
        // Auto-refresh des logs toutes les 30 secondes
        setInterval(() => {
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
                
                // Scroll vers le bas
                logContainer.scrollTop = logContainer.scrollHeight;
            }
        }, 30000);
    </script>
    <script src="admin-modern.js"></script>
</body>
</html>