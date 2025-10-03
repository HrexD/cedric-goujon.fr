<?php
require 'config.php';

// Simple authentification pour l'API
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

try {
    // Compter les messages non lus
    $unread_count = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn();
    $total_count = $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn();
    
    // Dernier message reçu
    $last_message = $pdo->query("SELECT nom, date_envoi FROM messages_contact ORDER BY date_envoi DESC LIMIT 1")->fetch();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'unread_count' => (int)$unread_count,
        'total_count' => (int)$total_count,
        'last_message' => $last_message ? [
            'nom' => $last_message['nom'],
            'date' => $last_message['date_envoi']
        ] : null
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Table des messages non trouvée']);
}
?>