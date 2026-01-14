<?php
$baseDir = __DIR__;

function projectFolders(string $baseDir): array {
    $entries = glob($baseDir . '/*', GLOB_ONLYDIR) ?: [];

    $candidates = array_filter(array_map('basename', $entries), function (string $name): bool {
        if ($name === 'assets') {
            return false;
        }
        return $name !== '' && $name[0] !== '.';
    });

    $projects = [];
    foreach ($candidates as $folder) {
        $htmlIndex = $baseDir . '/' . $folder . '/index.html';
        $phpIndex = $baseDir . '/' . $folder . '/index.php';
        if (file_exists($phpIndex) || file_exists($htmlIndex)) {
            $target = $folder . '/' . (file_exists($phpIndex) ? 'index.php' : 'index.html');
            $projects[] = [
                'name' => ucfirst(str_replace(['-', '_'], ' ', $folder)),
                'url'  => $target,
            ];
        }
    }

    usort($projects, fn($a, $b) => strcmp($a['name'], $b['name']));
    return $projects;
}

$projects = projectFolders($baseDir);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorateur des projets</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
    <header class="hero">
        <p class="eyebrow">Sélectionnez un dossier</p>
        <h1>Explorateur des projets</h1>
        <p class="subtitle">Chaque carte mène vers l'index du dossier correspondant.</p>
    </header>

    <?php if (!empty($projects)): ?>
        <div class="project-grid">
            <?php foreach ($projects as $project): ?>
                <a class="project-card" href="<?php echo htmlspecialchars($project['url'], ENT_QUOTES); ?>">
                    <span class="project-name"><?php echo htmlspecialchars($project['name'], ENT_QUOTES); ?></span>
                    <span class="project-arrow" aria-hidden="true">→</span>
                    <span class="sr-only">Ouvrir <?php echo htmlspecialchars($project['name'], ENT_QUOTES); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Aucun dossier de projet avec un index détecté.</p>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
