<?php
require_once 'config.php';

// Vérifier si un ID de questionnaire est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: questionnaires.php');
    exit;
}

$questionnaire_id = (int)$_GET['id'];

// Récupérer le questionnaire
try {
    $stmt = $pdo->prepare("SELECT * FROM questionnaire WHERE id = ?");
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$questionnaire) {
        header('Location: questionnaires.php');
        exit;
    }
    
    // Récupérer toutes les questions et leurs réponses
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
    <title><?= htmlspecialchars($questionnaire['titre']) ?> - Questionnaire</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="questionnaire-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="questionnaires.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Retour aux questionnaires
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content questionnaire-form">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php else: ?>
                <!-- En-tête du questionnaire -->
                <div class="questionnaire-header">
                    <div class="questionnaire-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="questionnaire-info">
                        <h1 class="questionnaire-title"><?= htmlspecialchars($questionnaire['titre']) ?></h1>
                        <p class="questionnaire-description"><?= htmlspecialchars($questionnaire['description']) ?></p>
                        <div class="questionnaire-meta">
                            <span class="meta-item">
                                <i class="fas fa-question-circle"></i>
                                <?= count($questions) ?> questions
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-clock"></i>
                                Environ <?= ceil(count($questions) * 0.5) ?> minutes
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-user-secret"></i>
                                Réponses anonymes
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Barre de progression -->
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-text">
                        Question <span id="currentQuestion">1</span> sur <?= count($questions) ?>
                    </div>
                </div>

                <!-- Formulaire du questionnaire -->
                <form id="questionnaireForm" action="questionnaire_handler.php" method="POST">
                    <input type="hidden" name="questionnaire_id" value="<?= $questionnaire_id ?>">
                    <input type="hidden" name="questionnaire_titre" value="<?= htmlspecialchars($questionnaire['titre']) ?>">
                    
                    <?php 
                    $question_number = 0;
                    foreach ($questions as $question_id => $question): 
                        $question_number++;
                    ?>
                        <div class="question-card" data-question="<?= $question_number ?>" <?= $question_number === 1 ? 'style="display: block;"' : 'style="display: none;"' ?>>
                            <div class="question-header">
                                <h2 class="question-title">
                                    <span class="question-number"><?= $question_number ?>.</span>
                                    <?= htmlspecialchars($question['libelle']) ?>
                                </h2>
                            </div>
                            
                            <div class="answers-container">
                                <?php if (!empty($question['reponses'])): ?>
                                    <?php 
                                    $nb_reponses = count($question['reponses']);
                                    $input_type = $nb_reponses == 2 ? 'radio' : 'checkbox';
                                    $input_name = $input_type == 'radio' ? "question_{$question['id']}" : "question_{$question['id']}[]";
                                    ?>
                                    <div class="question-type-info">
                                        <small class="text-muted">
                                            <i class="fas fa-<?= $input_type == 'radio' ? 'dot-circle' : 'check-square' ?>"></i>
                                            <?= $input_type == 'radio' ? 'Choix unique' : 'Choix multiples possibles' ?>
                                        </small>
                                    </div>
                                    <?php foreach ($question['reponses'] as $reponse): ?>
                                        <label class="answer-option answer-<?= $input_type ?>">
                                            <input type="<?= $input_type ?>" 
                                                   name="<?= $input_name ?>" 
                                                   value="<?= $reponse['id'] ?>"
                                                   data-libelle="<?= htmlspecialchars($reponse['libelle']) ?>"
                                                   <?= $input_type == 'radio' ? 'required' : '' ?>>
                                            <span class="answer-text"><?= htmlspecialchars($reponse['libelle']) ?></span>
                                            <span class="checkmark checkmark-<?= $input_type ?>"></span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Aucune réponse disponible pour cette question.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Navigation du questionnaire -->
                    <div class="questionnaire-navigation">
                        <button type="button" id="prevBtn" class="btn btn-secondary" style="display: none;">
                            <i class="fas fa-chevron-left"></i>
                            Précédent
                        </button>
                        
                        <button type="button" id="nextBtn" class="btn btn-primary">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        
                        <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer mes réponses
                        </button>
                    </div>
                </form>

                <!-- Informations de confidentialité -->
                <div class="privacy-notice">
                    <div class="privacy-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="privacy-text">
                        <h4>Confidentialité</h4>
                        <p>Vos réponses sont entièrement anonymes. Aucune donnée personnelle n'est collectée. 
                        Les résultats seront utilisés uniquement à des fins statistiques.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de confirmation -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle"></i> Confirmer l'envoi</h3>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir soumettre vos réponses ? Une fois envoyées, elles ne pourront plus être modifiées.</p>
                <div class="response-summary">
                    <h4>Récapitulatif de vos réponses :</h4>
                    <div id="responseSummary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-success" onclick="submitForm()">
                    <i class="fas fa-paper-plane"></i>
                    Confirmer l'envoi
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentQuestionIndex = 0;
        const totalQuestions = <?= count($questions) ?>;
        const questions = document.querySelectorAll('.question-card');

        // Mise à jour de la barre de progression
        function updateProgress() {
            const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;
        }

        // Afficher/masquer les questions
        function showQuestion(index) {
            questions.forEach((question, i) => {
                question.style.display = i === index ? 'block' : 'none';
            });
            
            // Gestion des boutons
            document.getElementById('prevBtn').style.display = index === 0 ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = index === totalQuestions - 1 ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = index === totalQuestions - 1 ? 'inline-block' : 'none';
            
            updateProgress();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Navigation
        document.getElementById('nextBtn').addEventListener('click', function() {
            const currentQuestion = questions[currentQuestionIndex];
            
            // Vérifier le type de question (radio ou checkbox)
            const radioInputs = currentQuestion.querySelectorAll('input[type="radio"]');
            const checkboxInputs = currentQuestion.querySelectorAll('input[type="checkbox"]');
            
            let hasAnswer = false;
            
            if (radioInputs.length > 0) {
                // Question radio (choix unique)
                hasAnswer = currentQuestion.querySelector('input[type="radio"]:checked') !== null;
            } else if (checkboxInputs.length > 0) {
                // Question checkbox (choix multiples) - au moins une réponse
                hasAnswer = currentQuestion.querySelector('input[type="checkbox"]:checked') !== null;
            }
            
            if (!hasAnswer) {
                const questionType = radioInputs.length > 0 ? 'une réponse' : 'au moins une réponse';
                alert(`Veuillez sélectionner ${questionType} avant de continuer.`);
                return;
            }
            
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
            }
        });

        document.getElementById('prevBtn').addEventListener('click', function() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                showQuestion(currentQuestionIndex);
            }
        });

        // Gestion de la soumission
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Vérifier que toutes les questions ont une réponse
            let allAnswered = true;
            const summary = [];
            
            questions.forEach((question, index) => {
                const questionTitle = question.querySelector('.question-title').textContent.trim();
                const radioInputs = question.querySelectorAll('input[type="radio"]');
                const checkboxInputs = question.querySelectorAll('input[type="checkbox"]');
                
                let hasAnswer = false;
                let answerTexts = [];
                
                if (radioInputs.length > 0) {
                    // Question radio
                    const selectedAnswer = question.querySelector('input[type="radio"]:checked');
                    if (selectedAnswer) {
                        hasAnswer = true;
                        answerTexts.push(selectedAnswer.getAttribute('data-libelle'));
                    }
                } else if (checkboxInputs.length > 0) {
                    // Question checkbox
                    const selectedAnswers = question.querySelectorAll('input[type="checkbox"]:checked');
                    if (selectedAnswers.length > 0) {
                        hasAnswer = true;
                        selectedAnswers.forEach(answer => {
                            answerTexts.push(answer.getAttribute('data-libelle'));
                        });
                    }
                }
                
                if (!hasAnswer) {
                    allAnswered = false;
                } else {
                    summary.push({
                        question: questionTitle,
                        answer: answerTexts.join(', ')
                    });
                }
            });
            
            if (!allAnswered) {
                alert('Veuillez répondre à toutes les questions avant de soumettre.');
                return;
            }
            
            // Afficher le modal de confirmation avec le résumé
            showConfirmModal(summary);
        });

        function showConfirmModal(summary) {
            const summaryDiv = document.getElementById('responseSummary');
            summaryDiv.innerHTML = '';
            
            summary.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'summary-item';
                div.innerHTML = `
                    <div class="summary-question">${item.question}</div>
                    <div class="summary-answer">${item.answer}</div>
                `;
                summaryDiv.appendChild(div);
            });
            
            document.getElementById('confirmModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function submitForm() {
            document.getElementById('questionnaireForm').submit();
        }

        // Fermer le modal en cliquant à l'extérieur
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('confirmModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Initialiser la première question
        updateProgress();
    </script>
</body>
</html>