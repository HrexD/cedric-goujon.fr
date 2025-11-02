<?php
// Script de téléchargement pour CV
$file = 'assets/CV_CGO_FS.pdf';
$download_name = 'CV_Cedric_Goujon.pdf';

// Vérifier si le fichier existe
if (!file_exists($file)) {
    http_response_code(404);
    die('Fichier introuvable');
}

// Forcer le téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $download_name . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Envoyer le fichier
readfile($file);
exit();
?>