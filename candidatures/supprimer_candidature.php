<?php
require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID invalide.");
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM candidatures WHERE id = ?");
$stmt->execute([$id]);

header("Location: liste_candidatures.php");
exit;
