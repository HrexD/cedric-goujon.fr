<?php
// upload_handler.php
// Reçoit un fichier (field 'file') et l'enregistre dans uploads/ avec un nom unique.

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Méthode non autorisée', 405);

    if (!isset($_FILES['file'])) throw new Exception('Aucun fichier reçu', 400);

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur upload ('.$file['error'].')', 500);
    }

    // Déterminer une limite cohérente avec php.ini et une limite raisonnable côté application
    $toBytes = function(string $s){
        // Convertit des valeurs php.ini comme '2M', '512K' en octets
        if (is_numeric($s)) return (int)$s;
        $unit = strtoupper(substr($s, -1));
        $value = (int)$s;
        switch ($unit) {
            case 'G': return $value * 1024 * 1024 * 1024;
            case 'M': return $value * 1024 * 1024;
            case 'K': return $value * 1024;
            default: return (int)$s;
        }
    };

    $iniUpload = @ini_get('upload_max_filesize') ?: '2M';
    $iniPost = @ini_get('post_max_size') ?: '8M';
    $maxFromIni = min($toBytes($iniUpload), $toBytes($iniPost));

    // Plafonner côté application (ex : 512 MB) pour éviter abus
    $appLimit = 5 * 1024 * 1024 * 1024;
    $maxBytes = min($maxFromIni, $appLimit);

    if ($file['size'] > $maxBytes) throw new Exception('Fichier trop volumineux', 413);

    // créer dossier uploads si besoin
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // nom sécurisé
    $origName = basename($file['name']);
    $origName = preg_replace('/[^A-Za-z0-9._-]/', '_', $origName);
    $target = $uploadDir . DIRECTORY_SEPARATOR . uniqid() . '_' . $origName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new Exception('Impossible de déplacer le fichier', 500);
    }

    // Journaliser l'upload (simple CSV) pour audit / debug : timestamp, ip, origName, storedName, size
    try {
        $logDir = $uploadDir;
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'upload_log.csv';
        $fp = fopen($logFile, 'a');
        if ($fp) {
            fputcsv($fp, [date('c'), $_SERVER['REMOTE_ADDR'] ?? 'unknown', $file['name'], basename($target), $file['size']]);
            fclose($fp);
        }
    } catch (Exception $e) {
        // Ne pas empêcher la réussite si le log échoue
    }

    $relPath = 'uploads/' . basename($target);
    echo json_encode(['success' => true, 'path' => $relPath]);
    exit;

} catch (Exception $e) {
    http_response_code($e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
