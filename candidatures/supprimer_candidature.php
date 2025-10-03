<?php
require_once '../config.php';
require_once '../auth_helper.php';

// Vérifier si l'utilisateur est admin
if (!isAdminLoggedIn()) {
    header('Location: ../admin.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=ID invalide');
    exit();
}

$id = (int)$_GET['id'];

// Vérifier que la candidature existe
$stmt = $pdo->prepare("SELECT nom, prenom FROM candidatures WHERE id = ?");
$stmt->execute([$id]);
$candidature = $stmt->fetch();

if (!$candidature) {
    header('Location: index.php?error=Candidature introuvable');
    exit();
}

// Supprimer la candidature
try {
    $stmt = $pdo->prepare("DELETE FROM candidatures WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: index.php?success=Candidature de ' . urlencode($candidature['prenom'] . ' ' . $candidature['nom']) . ' supprimée avec succès');
} catch (PDOException $e) {
    header('Location: index.php?error=Erreur lors de la suppression : ' . urlencode($e->getMessage()));
}
exit();
?>
