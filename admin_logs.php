<?php
require 'config.php';
require 'auth_helper.php';

// Vérifier si l'utilisateur est admin
if (!isAdminLoggedIn()) {
    header('Location: admin.php');
    exit();
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

function parseLogFile($logFile) {
    if (!file_exists($logFile)) return [];
    
    $logs = [];
    if (($handle = fopen($logFile, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($data) >= 5) {
                $logs[] = [
                    'timestamp' => $data[0],
                    'ip' => $data[1],
                    'original_name' => $data[2],
                    'stored_name' => $data[3],
                    'size' => intval($data[4]),
                    'user_agent' => $data[5] ?? 'Non disponible'
                ];
            }
        }
        fclose($handle);
    }
    
    // Trier par timestamp décroissant (plus récent en premier)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $logs;
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function getFileType($filename) {
    $ext = getFileExtension($filename);
    $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $videoExts = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
    
    if (in_array($ext, $imageExts)) return 'Image';
    if (in_array($ext, $videoExts)) return 'Vidéo';
    return 'Autre';
}

function getBrowserName($userAgent) {
    if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) return 'Chrome';
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) return 'Safari';
    if (strpos($userAgent, 'Edg') !== false) return 'Edge';
    if (strpos($userAgent, 'Opera') !== false) return 'Opera';
    if (strpos($userAgent, 'Postman') !== false) return 'Postman';
    return 'Autre';
}

function getOSName($userAgent) {
    if (strpos($userAgent, 'Windows') !== false) return 'Windows';
    if (strpos($userAgent, 'Mac') !== false) return 'macOS';
    if (strpos($userAgent, 'Linux') !== false) return 'Linux';
    if (strpos($userAgent, 'Android') !== false) return 'Android';
    if (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) return 'iOS';
    return 'Autre';
}

// Récupérer les logs
$logFile = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'upload_log.csv';
$logs = parseLogFile($logFile);

// Statistiques
$totalUploads = count($logs);
$totalSize = array_sum(array_column($logs, 'size'));
$uniqueIPs = count(array_unique(array_column($logs, 'ip')));

// Statistiques par type de fichier
$typeStats = [];
$browserStats = [];
$osStats = [];

foreach ($logs as $log) {
    // Stats par type
    $type = getFileType($log['original_name']);
    if (!isset($typeStats[$type])) {
        $typeStats[$type] = ['count' => 0, 'size' => 0];
    }
    $typeStats[$type]['count']++;
    $typeStats[$type]['size'] += $log['size'];
    
    // Stats par navigateur
    $browser = getBrowserName($log['user_agent']);
    if (!isset($browserStats[$browser])) {
        $browserStats[$browser] = 0;
    }
    $browserStats[$browser]++;
    
    // Stats par OS
    $os = getOSName($log['user_agent']);
    if (!isset($osStats[$os])) {
        $osStats[$os] = 0;
    }
    $osStats[$os]++;
}

// Trier les stats par popularité
arsort($browserStats);
arsort($osStats);

// Filtrage
$filterType = $_GET['type'] ?? '';
$filterIP = $_GET['ip'] ?? '';
$filterDate = $_GET['date'] ?? '';

$filteredLogs = $logs;
if ($filterType) {
    $filteredLogs = array_filter($filteredLogs, function($log) use ($filterType) {
        return getFileType($log['original_name']) === $filterType;
    });
}
if ($filterIP) {
    $filteredLogs = array_filter($filteredLogs, function($log) use ($filterIP) {
        return $log['ip'] === $filterIP;
    });
}
if ($filterDate) {
    $filteredLogs = array_filter($filteredLogs, function($log) use ($filterDate) {
        return date('Y-m-d', strtotime($log['timestamp'])) === $filterDate;
    });
}

$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Logs d'Upload - Administration</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-modern.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body>
    <!-- Navigation -->
    <?= generateNavigation('admin_logs') ?>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
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
                    <li><a href="admin_systeme.php">⚙️ Système</a></li>
                    <li><a href="admin_logs.php" class="active">📊 Logs</a></li>
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
                <h1>📊 Logs d'Upload</h1>
                <p class="admin-subtitle">Analyse et suivi des fichiers uploadés</p>
                <div class="admin-actions">
                    <a href="admin.php" class="btn btn-secondary">
                        ⬅️ Retour au Dashboard
                    </a>
                    <a href="upload_debug.php" class="btn btn-outline" target="_blank">
                        🔧 Debug Upload
                    </a>
                </div>
            </div>

        <div class="admin-content">
            <!-- Statistiques globales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Uploads</h3>
                    <div class="stat-value"><?= number_format($totalUploads) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Taille Totale</h3>
                    <div class="stat-value"><?= formatBytes($totalSize) ?></div>
                </div>
                <div class="stat-card">
                    <h3>IPs Uniques</h3>
                    <div class="stat-value"><?= $uniqueIPs ?></div>
                </div>
                <div class="stat-card">
                    <h3>Taille Moyenne</h3>
                    <div class="stat-value"><?= $totalUploads > 0 ? formatBytes($totalSize / $totalUploads) : '0 B' ?></div>
                </div>
            </div>

            <!-- Statistiques par type -->
            <?php if (!empty($typeStats)): ?>
            <div class="stats-grid">
                <?php foreach ($typeStats as $type => $stats): ?>
                <div class="stat-card">
                    <h3><?= $type ?>s</h3>
                    <div class="stat-value"><?= $stats['count'] ?></div>
                    <div class="stat-desc"><?= formatBytes($stats['size']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Statistiques navigateurs et OS -->
            <?php if (!empty($browserStats) || !empty($osStats)): ?>
            <div class="stats-grid">
                <!-- Top navigateurs -->
                <div class="stat-card">
                    <h3>🌐 Navigateurs populaires</h3>
                    <div class="stat-details">
                        <?php 
                        $topBrowsers = array_slice($browserStats, 0, 3, true);
                        foreach ($topBrowsers as $browser => $count): ?>
                            <div>
                                <span><?= $browser ?></span>
                                <strong><?= $count ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Top OS -->
                <div class="stat-card">
                    <h3>💻 Systèmes populaires</h3>
                    <div class="stat-details">
                        <?php 
                        $topOS = array_slice($osStats, 0, 3, true);
                        foreach ($topOS as $os => $count): ?>
                            <div>
                                <span><?= $os ?></span>
                                <strong><?= $count ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Navigateur le plus utilisé -->
                <?php if (!empty($browserStats)): 
                    $topBrowser = array_key_first($browserStats);
                    $topBrowserCount = $browserStats[$topBrowser];
                ?>
                <div class="stat-card">
                    <h3>🏆 Navigateur #1</h3>
                    <div class="stat-value" style="font-size: 1.5rem;"><?= $topBrowser ?></div>
                    <div class="stat-desc"><?= $topBrowserCount ?> uploads</div>
                </div>
                <?php endif; ?>

                <!-- OS le plus utilisé -->
                <?php if (!empty($osStats)): 
                    $topOSName = array_key_first($osStats);
                    $topOSCount = $osStats[$topOSName];
                ?>
                <div class="stat-card">
                    <h3>🖥️ OS #1</h3>
                    <div class="stat-value" style="font-size: 1.5rem;"><?= $topOSName ?></div>
                    <div class="stat-desc"><?= $topOSCount ?> uploads</div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Filtres -->
            <form method="GET" class="form-group">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="type">Type de fichier</label>
                        <select name="type" id="type" class="form-input">
                            <option value="">Tous les types</option>
                            <option value="Image" <?= $filterType === 'Image' ? 'selected' : '' ?>>Images</option>
                            <option value="Vidéo" <?= $filterType === 'Vidéo' ? 'selected' : '' ?>>Vidéos</option>
                            <option value="Autre" <?= $filterType === 'Autre' ? 'selected' : '' ?>>Autres</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="ip">Adresse IP</label>
                        <select name="ip" id="ip" class="form-input">
                            <option value="">Toutes les IPs</option>
                            <?php foreach (array_unique(array_column($logs, 'ip')) as $ip): ?>
                            <option value="<?= htmlspecialchars($ip) ?>" <?= $filterIP === $ip ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ip) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" class="form-input" value="<?= htmlspecialchars($filterDate) ?>">
                    </div>
                    <div class="form-field" style="display: flex; gap: 0.5rem; align-items: end;">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="?" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Tableau des logs -->
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date/Heure</th>
                            <th>Type</th>
                            <th>IP</th>
                            <th>Navigateur</th>
                            <th>Nom original</th>
                            <th>Nom stocké</th>
                            <th>Taille</th>
                            <th>Ext.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredLogs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted); font-style: italic;">
                                <?php if (empty($logs)): ?>
                                    Aucun log d'upload trouvé.
                                <?php else: ?>
                                    Aucun résultat ne correspond aux filtres appliqués.
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($filteredLogs as $log): ?>
                            <tr>
                                <td style="font-family: monospace; font-size: 0.8rem;">
                                    <?= date('d/m/Y H:i', strtotime($log['timestamp'])) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower(getFileType($log['original_name'])) === 'image' ? 'success' : (strtolower(getFileType($log['original_name'])) === 'vidéo' ? 'warning' : 'secondary') ?>">
                                        <?= getFileType($log['original_name']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['ip']) ?></td>
                                <td title="<?= htmlspecialchars($log['user_agent']) ?>">
                                    <div>
                                        <strong><?= getBrowserName($log['user_agent']) ?></strong><br>
                                        <small><?= getOSName($log['user_agent']) ?></small>
                                    </div>
                                </td>
                                <td title="<?= htmlspecialchars($log['original_name']) ?>">
                                    <?= htmlspecialchars($log['original_name']) ?>
                                </td>
                                <td title="<?= htmlspecialchars($log['stored_name']) ?>">
                                    <?= htmlspecialchars($log['stored_name']) ?>
                                </td>
                                <td><?= formatBytes($log['size']) ?></td>
                                <td><?= strtoupper(getFileExtension($log['original_name'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 2rem; color: var(--text-muted);">
                <small>
                    Affichage de <?= count($filteredLogs) ?> résultat(s) sur <?= $totalUploads ?> upload(s) total(aux)
                    <?php if (file_exists($logFile)): ?>
                        • Dernière mise à jour: <?= date('d/m/Y H:i', filemtime($logFile)) ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>

    <script src="admin-modern.js"></script>
    <script src="script.js"></script>
</body>
</html>