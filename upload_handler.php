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

    // Nom du fichier original
    $origName = basename($file['name']);

    // Validation MIME type et extension pour sécurité
    $allowedMimeTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
        'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm', 'video/mkv'
    ];
    
    $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp',
        'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'
    ];

    // Vérifier MIME type (avec fallbacks multiples)
    $mimeType = '';
    
    // 1. Essayer mime_content_type
    if (function_exists('mime_content_type')) {
        $mimeType = @mime_content_type($file['tmp_name']);
    }
    
    // 2. Essayer finfo si disponible
    if (!$mimeType && function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = @finfo_file($finfo, $file['tmp_name']);
            @finfo_close($finfo);
        }
    }
    
    // 3. Fallback : utiliser le type fourni par le navigateur
    if (!$mimeType) {
        $mimeType = $file['type'] ?? '';
    }
    
    // 4. Si toujours pas de MIME, deviner depuis l'extension
    if (!$mimeType) {
        $pathInfo = pathinfo($origName);
        $ext = strtolower($pathInfo['extension'] ?? '');
        $mimeMap = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'bmp' => 'image/bmp',
            'mp4' => 'video/mp4', 'avi' => 'video/avi', 'mov' => 'video/mov'
        ];
        $mimeType = $mimeMap[$ext] ?? 'application/octet-stream';
    }
    
    // Validation plus souple
    if ($mimeType && !in_array($mimeType, $allowedMimeTypes)) {
        // Vérifier si c'est un format image/vidéo commun même si MIME différent
        $isImage = strpos($mimeType, 'image/') === 0;
        $isVideo = strpos($mimeType, 'video/') === 0;
        
        if (!$isImage && !$isVideo) {
            throw new Exception('Type de fichier non autorisé: ' . $mimeType, 415);
        }
    }

    // Vérifier extension
    $pathInfo = pathinfo($origName);
    $extension = strtolower($pathInfo['extension'] ?? '');
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Extension de fichier non autorisée: ' . $extension, 415);
    }

    // créer dossier uploads si besoin
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // nom sécurisé avec nettoyage
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
