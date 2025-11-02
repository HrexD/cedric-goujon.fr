<?php
require 'auth_helper.php';

// Vérifier que l'admin est connecté
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

if (empty($_POST['files']) || !is_array($_POST['files'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Aucun fichier spécifié']);
    exit;
}

$files = $_POST['files'];
$validFiles = [];

// Vérifier que tous les fichiers existent
foreach ($files as $filename) {
    $filePath = $uploadDir . DIRECTORY_SEPARATOR . basename($filename);
    if (file_exists($filePath) && is_file($filePath)) {
        $validFiles[] = [
            'name' => $filename,
            'path' => $filePath
        ];
    }
}

if (empty($validFiles)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Aucun fichier valide trouvé']);
    exit;
}

// Créer le fichier ZIP
$zipFilename = 'gallery_selection_' . date('Y-m-d_H-i-s') . '.zip';
$zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFilename;

$zip = new ZipArchive();
$result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($result !== TRUE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Impossible de créer le fichier ZIP']);
    exit;
}

// Ajouter les fichiers au ZIP
foreach ($validFiles as $file) {
    $zip->addFile($file['path'], $file['name']);
}

$zip->close();

// Vérifier que le ZIP a été créé
if (!file_exists($zipPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la création du ZIP']);
    exit;
}

// Envoyer le fichier ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
header('Content-Length: ' . filesize($zipPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Lire et envoyer le fichier
readfile($zipPath);

// Nettoyer le fichier temporaire
unlink($zipPath);

exit;
?>