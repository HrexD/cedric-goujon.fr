<?php
require_once 'config.php';

echo "<h1>🔍 Diagnostic du système de questionnaires</h1>";

try {
    // Vérifier les tables
    $tables_to_check = ['questionnaire', 'questions', 'reponses'];
    echo "<h2>📊 Vérification des tables</h2>";
    
    foreach ($tables_to_check as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<div style='padding: 10px; margin: 5px; background: " . ($count > 0 ? '#d4edda' : '#f8d7da') . "; border-radius: 5px;'>";
        echo "Table <strong>$table</strong>: $count enregistrements";
        echo "</div>";
    }
    
    // Vérifier les relations
    echo "<h2>🔗 Vérification des relations</h2>";
    
    $stmt = $pdo->query("
        SELECT q.id as questionnaire_id, q.titre, COUNT(qu.id) as nb_questions
        FROM questionnaire q
        LEFT JOIN questions qu ON q.id = qu.questionnaire_id
        GROUP BY q.id
    ");
    $questionnaires_with_questions = $stmt->fetchAll();
    
    foreach ($questionnaires_with_questions as $qwq) {
        echo "<div style='padding: 10px; margin: 5px; background: #e7f3ff; border-radius: 5px;'>";
        echo "Questionnaire: <strong>" . htmlspecialchars($qwq['titre']) . "</strong> - " . $qwq['nb_questions'] . " questions";
        echo "</div>";
    }
    
    // Vérifier les réponses pour chaque question
    echo "<h2>📝 Vérification des réponses par question</h2>";
    
    $stmt = $pdo->query("
        SELECT qu.id as question_id, qu.libelle, questionnaire.titre, COUNT(r.id) as nb_reponses
        FROM questions qu
        JOIN questionnaire ON qu.questionnaire_id = questionnaire.id
        LEFT JOIN reponses r ON qu.id = r.question_id
        GROUP BY qu.id
        ORDER BY questionnaire.id, qu.id
    ");
    $questions_with_responses = $stmt->fetchAll();
    
    foreach ($questions_with_responses as $qwr) {
        $color = $qwr['nb_reponses'] >= 2 ? '#d4edda' : ($qwr['nb_reponses'] > 0 ? '#fff3cd' : '#f8d7da');
        echo "<div style='padding: 10px; margin: 5px; background: $color; border-radius: 5px;'>";
        echo "Question (ID: " . $qwr['question_id'] . "): <strong>" . htmlspecialchars(substr($qwr['libelle'], 0, 50)) . "...</strong>";
        echo "<br>Questionnaire: " . htmlspecialchars($qwr['titre']);
        echo "<br>Réponses: " . $qwr['nb_reponses'];
        echo "</div>";
    }
    
    // Test de la requête utilisée dans questionnaire_reponse.php
    echo "<h2>🧪 Test de la requête principale</h2>";
    
    if (!empty($questionnaires_with_questions)) {
        $test_questionnaire_id = $questionnaires_with_questions[0]['questionnaire_id'];
        
        $stmt = $pdo->prepare("
            SELECT q.id as question_id, q.libelle as question_libelle,
                   r.id as reponse_id, r.libelle as reponse_libelle
            FROM questions q
            LEFT JOIN reponses r ON q.id = r.question_id
            WHERE q.questionnaire_id = ?
            ORDER BY q.id, r.id
        ");
        $stmt->execute([$test_questionnaire_id]);
        $test_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Test avec questionnaire ID: $test_questionnaire_id</strong><br>";
        echo "Résultats trouvés: " . count($test_results) . "<br>";
        
        // Organiser comme dans le code principal
        $questions = [];
        foreach ($test_results as $row) {
            $question_id = $row['question_id'];
            if (!isset($questions[$question_id])) {
                $questions[$question_id] = [
                    'id' => $question_id,
                    'libelle' => $row['question_libelle'],
                    'reponses' => []
                ];
            }
            if ($row['reponse_id']) {
                $questions[$question_id]['reponses'][] = [
                    'id' => $row['reponse_id'],
                    'libelle' => $row['reponse_libelle']
                ];
            }
        }
        
        foreach ($questions as $question) {
            echo "<div style='margin: 10px 0; padding: 10px; background: white; border-left: 3px solid #007bff;'>";
            echo "<strong>Q:</strong> " . htmlspecialchars($question['libelle']) . "<br>";
            echo "<strong>Réponses:</strong> " . count($question['reponses']) . "<br>";
            foreach ($question['reponses'] as $reponse) {
                echo "- " . htmlspecialchars($reponse['libelle']) . "<br>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='padding: 15px; background: #f8d7da; color: #721c24; border-radius: 5px;'>";
    echo "<strong>Erreur de base de données:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr><p><a href='questionnaires.php' style='color: #007bff;'>← Retour aux questionnaires</a></p>";
?>