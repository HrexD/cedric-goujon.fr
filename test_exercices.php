<?php
// test_exercices.php - Script de débogage
require 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Exercices</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:20px auto;background:#f5f5f5;padding:20px;}";
echo ".success{color:green;background:#e8f5e9;padding:10px;margin:10px 0;border-radius:5px;}";
echo ".error{color:red;background:#ffebee;padding:10px;margin:10px 0;border-radius:5px;}";
echo ".info{color:blue;background:#e3f2fd;padding:10px;margin:10px 0;border-radius:5px;}</style></head><body>";

echo "<h1>🔍 Test et Débogage du système d'exercices</h1>";

// Test 1: Connexion à la base
try {
    echo "<h2>Test 1: Connexion à la base de données</h2>";
    $pdo->query("SELECT 1");
    echo "<div class='success'>✅ Connexion à la base de données OK</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur de connexion : " . $e->getMessage() . "</div>";
    exit;
}

// Test 2: Existence de la table
try {
    echo "<h2>Test 2: Vérification de la table exercices</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'exercices'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Table 'exercices' existe</div>";
        
        // Compter les exercices
        $count = $pdo->query("SELECT COUNT(*) FROM exercices")->fetchColumn();
        echo "<div class='info'>📚 Nombre d'exercices : $count</div>";
        
        if ($count > 0) {
            // Afficher quelques exercices
            $exercices = $pdo->query("SELECT id, titre, langage, statut FROM exercices LIMIT 3")->fetchAll();
            echo "<div class='success'>Exercices disponibles :</div><ul>";
            foreach ($exercices as $ex) {
                echo "<li>ID {$ex['id']} - {$ex['titre']} ({$ex['langage']}) - Statut: {$ex['statut']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<div class='error'>❌ Table 'exercices' n'existe pas</div>";
        echo "<p><a href='init_db.php'>🔧 Cliquer ici pour créer la table</a></p>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur table : " . $e->getMessage() . "</div>";
}

// Test 3: Test de récupération d'un exercice
try {
    echo "<h2>Test 3: Test de récupération d'exercice</h2>";
    $stmt = $pdo->prepare("SELECT * FROM exercices WHERE statut = 'actif' LIMIT 1");
    $stmt->execute();
    $exercice = $stmt->fetch();
    
    if ($exercice) {
        echo "<div class='success'>✅ Récupération d'exercice OK</div>";
        echo "<div class='info'>Test avec exercice ID {$exercice['id']} : {$exercice['titre']}</div>";
        
        // Test de exercice_detail.php
        $test_id = $exercice['id'];
        echo "<h3>Test de exercice_detail.php</h3>";
        echo "<iframe src='exercice_detail.php?id=$test_id' width='100%' height='300' style='border:1px solid #ccc;'></iframe>";
        
    } else {
        echo "<div class='error'>❌ Aucun exercice actif trouvé</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur récupération : " . $e->getMessage() . "</div>";
}

// Test 4: Test JavaScript
echo "<h2>Test 4: Test des fonctions JavaScript</h2>";
echo "<button onclick='testModal()' style='padding:10px;background:#007cba;color:white;border:none;border-radius:5px;'>Tester le modal</button>";

echo "<div id='testModal' class='modal' style='display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;'>
    <div style='background:white;margin:50px auto;padding:20px;max-width:500px;border-radius:5px;'>
        <h3>Modal de test</h3>
        <p>Si vous voyez ceci, le système de modal fonctionne !</p>
        <button onclick='closeModal(\"testModal\")'>Fermer</button>
    </div>
</div>";

echo "<script src='admin-modern.js'></script>";
echo "<script>
function testModal() {
    if (typeof openModal === 'function') {
        openModal('testModal');
        document.querySelector('.info:last-child').innerHTML = '✅ Fonction openModal trouvée et exécutée';
    } else {
        alert('❌ Fonction openModal non trouvée !');
    }
}
</script>";

echo "<div class='info'>Cliquez sur le bouton pour tester le modal</div>";

echo "<h2>🔗 Liens de test</h2>";
echo "<p><a href='exercices.php'>📚 Page exercices</a></p>";
echo "<p><a href='admin_exercices.php'>⚙️ Administration exercices</a></p>";

echo "</body></html>";
?>