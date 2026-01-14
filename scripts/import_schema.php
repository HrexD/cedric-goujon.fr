<?php
// scripts/import_schema.php
// Script pour importer le schéma de la base de données

require_once __DIR__ . '/../config.php';

echo "🔧 Import du schéma Calendar Manager...\n";

// Lire le fichier SQL
$sql_file = __DIR__ . '/../projet/calendar-manager/database/schema.sql';

if (!file_exists($sql_file)) {
    die("❌ Fichier schema.sql non trouvé : $sql_file\n");
}

$sql_content = file_get_contents($sql_file);

// Diviser les requêtes
$queries = array_filter(
    array_map('trim', explode(';', $sql_content)),
    function($query) {
        return !empty($query) && !preg_match('/^--/', $query);
    }
);

$success = 0;
$errors = 0;

foreach ($queries as $query) {
    if (empty($query)) continue;
    
    try {
        $pdo->exec($query);
        $success++;
        echo "✅ Requête exécutée avec succès\n";
    } catch (PDOException $e) {
        $errors++;
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        echo "📝 Requête: " . substr($query, 0, 100) . "...\n";
    }
}

echo "\n📊 Résultats:\n";
echo "   ✅ Succès: $success\n";
echo "   ❌ Erreurs: $errors\n";

if ($errors === 0) {
    echo "🎉 Import terminé avec succès!\n";
} else {
    echo "⚠️  Import terminé avec des erreurs.\n";
}

// Vérifier les tables créées
try {
    $stmt = $pdo->query("SHOW TABLES LIKE '%events%' OR SHOW TABLES LIKE '%tasks%' OR SHOW TABLES LIKE '%todos%' OR SHOW TABLES LIKE '%appointments%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📋 Tables Calendar Manager détectées:\n";
    foreach ($tables as $table) {
        echo "   📁 $table\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Impossible de vérifier les tables: " . $e->getMessage() . "\n";
}
?>