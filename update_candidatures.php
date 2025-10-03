<?php
/**
 * Script de mise à jour de la structure de la table candidatures
 * Exécute les modifications nécessaires pour la nouvelle version
 */

require 'config.php';

echo "🔄 Mise à jour de la structure de la table candidatures...\n\n";

try {
    // Vérifier si les nouvelles colonnes existent déjà
    $result = $pdo->query("SHOW COLUMNS FROM candidatures LIKE 'nom'");
    if ($result->rowCount() == 0) {
        echo "➕ Ajout des nouvelles colonnes...\n";
        
        // Ajouter les nouveaux champs
        $pdo->exec("ALTER TABLE candidatures 
            ADD COLUMN nom VARCHAR(100),
            ADD COLUMN prenom VARCHAR(100),
            ADD COLUMN email VARCHAR(255),
            ADD COLUMN statut ENUM('en_attente', 'acceptee', 'refusee', 'entretien') DEFAULT 'en_attente',
            ADD COLUMN notes TEXT,
            ADD COLUMN telephone VARCHAR(20),
            ADD COLUMN cv_fichier VARCHAR(255),
            ADD COLUMN lettre_motivation TEXT");
        
        echo "✅ Nouvelles colonnes ajoutées avec succès\n";
    } else {
        echo "ℹ️ Les nouvelles colonnes existent déjà\n";
    }
    
    // Mettre à jour les enregistrements existants
    echo "🔧 Mise à jour des enregistrements existants...\n";
    $updated = $pdo->exec("UPDATE candidatures SET statut = 'en_attente' WHERE statut IS NULL");
    echo "✅ $updated enregistrements mis à jour\n";
    
    // Ajouter les index pour les performances
    echo "🔍 Ajout des index pour les performances...\n";
    try {
        $pdo->exec("CREATE INDEX idx_candidatures_email ON candidatures(email)");
        echo "✅ Index sur email créé\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️ Index sur email existe déjà\n";
        } else {
            throw $e;
        }
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_candidatures_statut ON candidatures(statut)");
        echo "✅ Index sur statut créé\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️ Index sur statut existe déjà\n";
        } else {
            throw $e;
        }
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_candidatures_date ON candidatures(date_candidature)");
        echo "✅ Index sur date_candidature créé\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️ Index sur date_candidature existe déjà\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n🎉 Mise à jour terminée avec succès !\n";
    echo "📋 La structure de la table candidatures est maintenant à jour.\n";
    echo "🔗 Vous pouvez maintenant accéder au module candidatures depuis la navigation.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la mise à jour : " . $e->getMessage() . "\n";
    exit(1);
}
?>