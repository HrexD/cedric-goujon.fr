<?php
require_once 'config.php';

// Test spécifique pour l'affichage des réponses
$questionnaire_id = 1; // ID du questionnaire de test

try {
    // Récupérer le questionnaire
    $stmt = $pdo->prepare("SELECT * FROM questionnaire WHERE id = ?");
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer toutes les questions et leurs réponses avec debug
    $stmt = $pdo->prepare("
        SELECT q.id as question_id, q.libelle as question_libelle,
               r.id as reponse_id, r.libelle as reponse_libelle
        FROM questions q
        LEFT JOIN reponses r ON q.id = r.question_id
        WHERE q.questionnaire_id = ?
        ORDER BY q.id, r.id
    ");
    $stmt->execute([$questionnaire_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser les données
    $questions = [];
    foreach ($results as $row) {
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
    
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération du questionnaire : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Affichage Questionnaire</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="questionnaire-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .test-container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .debug-info { background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .test-question { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Test d'affichage du questionnaire</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            
            <div class="debug-info">
                <h3>📊 Informations de debug</h3>
                <p><strong>Questionnaire:</strong> <?= htmlspecialchars($questionnaire['titre']) ?></p>
                <p><strong>Nombre de questions organisées:</strong> <?= count($questions) ?></p>
                <p><strong>Résultats bruts de la DB:</strong> <?= count($results) ?> lignes</p>
            </div>
            
            <h2>🔍 Affichage des questions avec réponses</h2>
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="test-question">
                    <div class="debug-info">
                        <strong>Question ID:</strong> <?= $question['id'] ?> | 
                        <strong>Index:</strong> <?= $index ?> | 
                        <strong>Nb réponses:</strong> <?= count($question['reponses']) ?>
                    </div>
                    
                    <h3><?= ($index + 1) ?>. <?= htmlspecialchars($question['libelle']) ?></h3>
                    
                    <div class="answers-container">
                        <?php if (!empty($question['reponses'])): ?>
                            <div style="color: green; margin-bottom: 10px;">
                                ✅ <?= count($question['reponses']) ?> réponse(s) trouvée(s)
                            </div>
                            <?php foreach ($question['reponses'] as $reponse): ?>
                                <label class="answer-option">
                                    <input type="radio" 
                                           name="question_<?= $question['id'] ?>" 
                                           value="<?= $reponse['id'] ?>"
                                           data-libelle="<?= htmlspecialchars($reponse['libelle']) ?>">
                                    <span class="answer-text"><?= htmlspecialchars($reponse['libelle']) ?></span>
                                    <span class="checkmark"></span>
                                    <small style="color: #6c757d; margin-left: 10px;">(ID: <?= $reponse['id'] ?>)</small>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                ❌ Aucune réponse disponible pour cette question.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="debug-info">
                <h3>🔧 Actions de test</h3>
                <p>
                    <a href="questionnaire_reponse.php?id=<?= $questionnaire_id ?>" class="btn btn-primary">
                        🎯 Tester la vraie page
                    </a>
                    <a href="diagnostic_questionnaires.php" class="btn btn-info">
                        🔍 Diagnostic complet
                    </a>
                    <a href="questionnaires.php" class="btn btn-secondary">
                        ← Retour aux questionnaires
                    </a>
                </p>
            </div>
            
        <?php endif; ?>
    </div>

    <script>
        console.log('Test page loaded');
        console.log('Questions found:', <?= count($questions) ?>);
        
        // Test des événements sur les réponses
        document.querySelectorAll('.answer-option input').forEach(input => {
            input.addEventListener('change', function() {
                console.log('Réponse sélectionnée:', this.value, 'Label:', this.getAttribute('data-libelle'));
            });
        });
    </script>
</body>
</html>