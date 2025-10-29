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
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
    
    <div class="message-header">
        <div>
            <h1>⚙️ Administration Système</h1>
            <p style="color: var(--text-muted);">Surveillance et maintenance du système</p>
        </div>
        <div>
            <a href="admin" class="btn-small" style="background: var(--text-muted); color: white;">
                ← Tableau de bord
            </a>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Actions rapides -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🛠️ Actions de maintenance</h3>
        
        <form method="POST" class="action-buttons">
            <button type="submit" name="action" value="clear_cache" class="action-btn btn-primary"
                    onclick="return confirm('Vider le cache ?')">
                🗑️ Vider le cache
            </button>
            <button type="submit" name="action" value="optimize_db" class="action-btn btn-warning"
                    onclick="return confirm('Optimiser la base de données ?')">
                🚀 Optimiser la DB
            </button>
            <button type="submit" name="action" value="backup_db" class="action-btn btn-success"
                    onclick="return confirm('Créer une sauvegarde ?')">
                💾 Sauvegarder
            </button>
            <button type="button" onclick="location.reload()" class="action-btn btn-primary">
                🔄 Actualiser les infos
            </button>
        </form>
    </div>
    
    <!-- Informations serveur -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🖥️ Informations serveur</h3>
        
        <div class="info-grid">
            <div class="info-item status-good">
                <strong>🌐 Système d'exploitation</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['server']['os'] ?></span>
            </div>
            <div class="info-item status-good">
                <strong>🚀 Serveur web</strong><br>
                <span style="color: var(--text-muted);"><?= htmlspecialchars($system_info['server']['software']) ?></span>
            </div>
            <div class="info-item status-good">
                <strong>🐘 Version PHP</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['server']['php_version'] ?></span>
            </div>
            <div class="info-item status-good">
                <strong>🏠 Racine du site</strong><br>
                <span style="color: var(--text-muted); font-size: 0.8em;"><?= htmlspecialchars($system_info['server']['document_root']) ?></span>
            </div>
            <div class="info-item status-good">
                <strong>🌍 Nom du serveur</strong><br>
                <span style="color: var(--text-muted);"><?= htmlspecialchars($system_info['server']['server_name']) ?></span>
            </div>
            <div class="info-item status-good">
                <strong>⏰ Dernière requête</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['server']['request_time'] ?></span>
            </div>
        </div>
    </div>
    
    <!-- Configuration PHP -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🐘 Configuration PHP</h3>
        
        <div class="info-grid">
            <div class="info-item">
                <strong>💾 Limite mémoire</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['php']['memory_limit'] ?></span>
            </div>
            <div class="info-item">
                <strong>⏱️ Temps d'exécution max</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['php']['max_execution_time'] ?> secondes</span>
            </div>
            <div class="info-item">
                <strong>📤 Taille upload max</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['php']['upload_max_filesize'] ?></span>
            </div>
            <div class="info-item">
                <strong>📊 Taille POST max</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['php']['post_max_size'] ?></span>
            </div>
            <div class="info-item">
                <strong>🌍 Fuseau horaire</strong><br>
                <span style="color: var(--text-muted);"><?= $system_info['php']['timezone'] ?></span>
            </div>
        </div>
    </div>
    
    <!-- Extensions PHP -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🔌 Extensions PHP</h3>
        
        <div class="extension-grid">
            <?php foreach ($php_extensions as $extension => $loaded): ?>
                <div class="extension-item <?= $loaded ? 'extension-active' : 'extension-inactive' ?>">
                    <strong><?= strtoupper($extension) ?></strong><br>
                    <span><?= $loaded ? '✅ Activé' : '❌ Désactivé' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Base de données -->
    <div class="system-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">🗄️ Base de données</h3>
        
        <?php if (isset($system_info['database']['error'])): ?>
            <div class="info-item status-error">
                <strong>❌ Erreur de connexion</strong><br>
                <span style="color: var(--text-muted);"><?= htmlspecialchars($system_info['database']['error']) ?></span>
            </div>
        <?php else: ?>
            <div class="info-grid">
                <div class="info-item status-good">
                    <strong>🔢 Version MySQL</strong><br>
                    <span style="color: var(--text-muted);"><?= $system_info['database']['version'] ?></span>
                </div>
                <div class="info-item status-good">
                    <strong>🔤 Charset</strong><br>
                    <span style="color: var(--text-muted);"><?= $system_info['database']['charset'] ?></span>
                </div>
                <div class="info-item status-good">
                    <strong>📊 Nombre de tables</strong><br>
                    <span style="color: var(--text-muted);"><?= $system_info['database']['tables'] ?> tables</span>
                </div>
            </div>
            
            <h4 style="margin: 2rem 0 1rem 0;">📈 Statistiques des tables</h4>
            <div class="info-grid">
                <?php foreach ($table_stats as $table => $count): ?>
                    <div class="info-item">
                        <strong><?= ucfirst(str_replace('_', ' ', $table)) ?></strong><br>
                        <span style="color: var(--text-muted);"><?= $count ?> enregistrement(s)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Espace disque -->
    <?php if (!empty($system_info['disk'])): ?>
        <div class="system-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary);">💾 Espace disque</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <strong>📊 Espace total</strong><br>
                    <span style="color: var(--text-muted);"><?= $system_info['disk']['total'] ?> GB</span>
                </div>
                <div class="info-item">
                    <strong>💽 Espace utilisé</strong><br>
                    <span style="color: var(--text-muted);"><?= $system_info['disk']['used'] ?> GB</span>
                </div>
                <div class="info-item">
                    <strong>🆓 Espace libre</strong><br>
                    <span style="color: var(--text-muted);"><?= $system_info['disk']['free'] ?> GB</span>
                </div>
                <div class="info-item">
                    <strong>📈 Utilisation</strong><br>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $system_info['disk']['usage_percent'] ?>%"></div>
                    </div>
                    <span style="color: var(--text-muted);"><?= $system_info['disk']['usage_percent'] ?>% utilisé</span>
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
    <script src="script.js"></script>
    <script src="admin.js"></script>
</body>
</html>