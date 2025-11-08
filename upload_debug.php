<?php
// Debug helper pour les uploads
// Affiche les valeurs php.ini pertinentes et les dernières lignes du journal d'upload

// Protection simple : accessible seulement en local (si le site est en local)
if (php_sapi_name() !== 'cli' && ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1' && ($_SERVER['REMOTE_ADDR'] ?? '') !== '::1') {
    // Vous pouvez commenter la ligne suivante si vous voulez accéder depuis un réseau
    // exit('Accès restreint (debug local).');
}

function lastLines($file, $lines = 20) {
    if (!is_readable($file)) return [];
    $fp = fopen($file, 'r');
    if (!$fp) return [];

    $pos = -1;
    $currentLine = '';
    $result = [];
    fseek($fp, $pos, SEEK_END);
    $char = fgetc($fp);
    while ($lines > 0 && $char !== false) {
        if ($char === "\n") {
            $result[] = strrev($currentLine);
            $currentLine = '';
            $lines--;
        } else {
            $currentLine .= $char;
        }
        $pos--;
        if (@fseek($fp, $pos, SEEK_END) === -1) break;
        $char = fgetc($fp);
    }
    if ($currentLine !== '' && $lines > 0) $result[] = strrev($currentLine);
    fclose($fp);
    return array_reverse($result);
}

$uploadLog = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'upload_log.csv';

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Debug upload</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:18px} pre{background:#f7f7f7;padding:12px;border-radius:6px}</style>
</head>
<body>
  <h1>Diagnostics uploads</h1>
  <h2>Paramètres PHP</h2>
  <table>
    <tr><td><strong>upload_max_filesize</strong></td><td><?= htmlspecialchars(ini_get('upload_max_filesize')) ?></td></tr>
    <tr><td><strong>post_max_size</strong></td><td><?= htmlspecialchars(ini_get('post_max_size')) ?></td></tr>
    <tr><td><strong>memory_limit</strong></td><td><?= htmlspecialchars(ini_get('memory_limit')) ?></td></tr>
    <tr><td><strong>max_execution_time</strong></td><td><?= htmlspecialchars(ini_get('max_execution_time')) ?></td></tr>
  </table>

  <h2 style="margin-top:18px">Derniers uploads (log)</h2>
  <?php if (!file_exists($uploadLog)): ?>
    <div>Aucun log trouvé dans <code>logs/upload_log.csv</code></div>
  <?php else: ?>
    <pre><?php
      $lines = lastLines($uploadLog, 30);
      if (empty($lines)) echo "(log vide)";
      else echo implode("\n", $lines);
    ?></pre>
  <?php endif; ?>

  <h2 style="margin-top:18px">Conseils rapides</h2>
  <ul>
    <li>Si votre vidéo est plus grande que <code>upload_max_filesize</code> ou <code>post_max_size</code>, l'upload échouera. Augmentez ces valeurs dans <code>php.ini</code> ou réduisez la taille du fichier.</li>
    <li>Vérifiez l'onglet Réseau (Network) dans les outils développeur du navigateur pour voir la réponse JSON de <code>upload_handler.php</code> (champ <code>error</code> si échec).</li>
    <li>Consultez ce log : il contient une ligne par upload réussi (timestamp, ip, nom original, nom stocké, taille).</li>
  </ul>
</body>
</html>
