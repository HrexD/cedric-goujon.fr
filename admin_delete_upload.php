<?php
require 'auth_helper.php';
header('Content-Type: application/json; charset=utf-8');

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Méthode non autorisée']);
    exit;
}

$file = $_POST['file'] ?? null;
if (!$file) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Paramètre manquant']);
    exit;
}

// Sécuriser le nom de fichier : prendre basename uniquement
$base = basename($file);
$uploads = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $base;

if (!file_exists($uploads)) {
    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>'Fichier introuvable']);
    exit;
}

// Ne pas autoriser la suppression de fichiers en dehors du dossier
if (strpos(realpath($uploads), realpath(__DIR__ . DIRECTORY_SEPARATOR . 'uploads')) !== 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Chemin non autorisé']);
    exit;
}

if (!unlink($uploads)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Impossible de supprimer le fichier']);
    exit;
}

echo json_encode(['success'=>true]);
exit;
