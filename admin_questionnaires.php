<?php
require_once 'config.php';
require_once 'auth_helper.php';

// Vérifier si l'utilisateur est admin
if (!isAdminLoggedIn()) {
    header('Location: admin.php');
    exit();
}

// Variables pour les messages
$message = '';
$message_type = '';

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_questionnaire') {
        try {
            $titre = trim($_POST['questionnaire_titre']);
            $description = trim($_POST['questionnaire_description']);
            
            if (empty($titre)) {
                throw new Exception('Le titre du questionnaire est obligatoire.');
            }
            
            // Créer le questionnaire
            $stmt = $pdo->prepare("INSERT INTO questionnaire (titre, description, date_creation) VALUES (?, ?, NOW())");
            $stmt->execute([$titre, $description]);
            $questionnaire_id = $pdo->lastInsertId();
            
            // Traiter les questions
            $questions_data = json_decode($_POST['questions_data'], true);
            
            if (empty($questions_data)) {
                throw new Exception('Au moins une question est requise.');
            }
            
            foreach ($questions_data as $question_data) {
                if (empty(trim($question_data['libelle']))) continue;
                
                // Créer la question
                $stmt = $pdo->prepare("INSERT INTO questions (questionnaire_id, libelle) VALUES (?, ?)");
                $stmt->execute([$questionnaire_id, trim($question_data['libelle'])]);
                $question_id = $pdo->lastInsertId();
                
                // Créer les réponses
                if (!empty($question_data['reponses'])) {
                    foreach ($question_data['reponses'] as $reponse) {
                        if (empty(trim($reponse))) continue;
                        
                        $stmt = $pdo->prepare("INSERT INTO reponses (question_id, libelle) VALUES (?, ?)");
                        $stmt->execute([$question_id, trim($reponse)]);
                    }
                }
            }
            
            $message = "Questionnaire créé avec succès ! ID: $questionnaire_id";
            $message_type = 'success';
            
        } catch (Exception $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    elseif ($_POST['action'] === 'update_questionnaire') {
        try {
            $questionnaire_id = (int)$_POST['questionnaire_id'];
            $titre = trim($_POST['questionnaire_titre']);
            $description = trim($_POST['questionnaire_description']);
            
            if (empty($titre)) {
                throw new Exception('Le titre du questionnaire est obligatoire.');
            }
            
            // Mettre à jour le questionnaire
            $stmt = $pdo->prepare("UPDATE questionnaire SET titre = ?, description = ? WHERE id = ?");
            $stmt->execute([$titre, $description, $questionnaire_id]);
            
            // Supprimer les anciennes questions et réponses
            $stmt = $pdo->prepare("DELETE r FROM reponses r INNER JOIN questions q ON r.question_id = q.id WHERE q.questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            $stmt = $pdo->prepare("DELETE FROM questions WHERE questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            // Ajouter les nouvelles questions
            $questions_data = json_decode($_POST['questions_data'], true);
            
            if (empty($questions_data)) {
                throw new Exception('Au moins une question est requise.');
            }
            
            foreach ($questions_data as $question_data) {
                if (empty(trim($question_data['libelle']))) continue;
                
                // Créer la question
                $stmt = $pdo->prepare("INSERT INTO questions (questionnaire_id, libelle) VALUES (?, ?)");
                $stmt->execute([$questionnaire_id, trim($question_data['libelle'])]);
                $question_id = $pdo->lastInsertId();
                
                // Créer les réponses
                if (!empty($question_data['reponses'])) {
                    foreach ($question_data['reponses'] as $reponse) {
                        if (empty(trim($reponse))) continue;
                        
                        $stmt = $pdo->prepare("INSERT INTO reponses (question_id, libelle) VALUES (?, ?)");
                        $stmt->execute([$question_id, trim($reponse)]);
                    }
                }
            }
            
            $message = "Questionnaire mis à jour avec succès !";
            $message_type = 'success';
            
        } catch (Exception $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    elseif ($_POST['action'] === 'delete_questionnaire') {
        try {
            $questionnaire_id = (int)$_POST['questionnaire_id'];
            
            // Supprimer les réponses associées
            $stmt = $pdo->prepare("DELETE r FROM reponses r INNER JOIN questions q ON r.question_id = q.id WHERE q.questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            // Supprimer les questions associées
            $stmt = $pdo->prepare("DELETE FROM questions WHERE questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            // Supprimer le questionnaire
            $stmt = $pdo->prepare("DELETE FROM questionnaire WHERE id = ?");
            $stmt->execute([$questionnaire_id]);
            
            $message = "Questionnaire supprimé avec succès !";
            $message_type = 'success';
            
        } catch (Exception $e) {
            $message = "Erreur lors de la suppression : " . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    elseif ($_POST['action'] === 'update_questionnaire') {
        try {
            $questionnaire_id = (int)$_POST['questionnaire_id'];
            $titre = trim($_POST['questionnaire_titre']);
            $description = trim($_POST['questionnaire_description']);
            
            if (empty($titre)) {
                throw new Exception('Le titre du questionnaire est obligatoire.');
            }
            
            // Mettre à jour le questionnaire
            $stmt = $pdo->prepare("UPDATE questionnaire SET titre = ?, description = ? WHERE id = ?");
            $stmt->execute([$titre, $description, $questionnaire_id]);
            
            // Supprimer les anciennes questions et réponses
            $stmt = $pdo->prepare("DELETE r FROM reponses r INNER JOIN questions q ON r.question_id = q.id WHERE q.questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            $stmt = $pdo->prepare("DELETE FROM questions WHERE questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            // Ajouter les nouvelles questions
            $questions_data = json_decode($_POST['questions_data'], true);
            
            if (empty($questions_data)) {
                throw new Exception('Au moins une question est requise.');
            }
            
            foreach ($questions_data as $question_data) {
                if (empty(trim($question_data['libelle']))) continue;
                
                // Créer la question
                $stmt = $pdo->prepare("INSERT INTO questions (questionnaire_id, libelle) VALUES (?, ?)");
                $stmt->execute([$questionnaire_id, trim($question_data['libelle'])]);
                $question_id = $pdo->lastInsertId();
                
                // Créer les réponses
                if (!empty($question_data['reponses'])) {
                    foreach ($question_data['reponses'] as $reponse) {
                        if (empty(trim($reponse))) continue;
                        
                        $stmt = $pdo->prepare("INSERT INTO reponses (question_id, libelle) VALUES (?, ?)");
                        $stmt->execute([$question_id, trim($reponse)]);
                    }
                }
            }
            
            $message = "Questionnaire mis à jour avec succès !";
            $message_type = 'success';
            
        } catch (Exception $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    elseif ($_POST['action'] === 'delete_questionnaire') {
        try {
            $questionnaire_id = (int)$_POST['questionnaire_id'];
            
            // Supprimer les réponses associées
            $stmt = $pdo->prepare("DELETE r FROM reponses r INNER JOIN questions q ON r.question_id = q.id WHERE q.questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            // Supprimer les questions associées
            $stmt = $pdo->prepare("DELETE FROM questions WHERE questionnaire_id = ?");
            $stmt->execute([$questionnaire_id]);
            
            // Supprimer le questionnaire
            $stmt = $pdo->prepare("DELETE FROM questionnaire WHERE id = ?");
            $stmt->execute([$questionnaire_id]);
            
            $message = "Questionnaire supprimé avec succès !";
            $message_type = 'success';
            
        } catch (Exception $e) {
            $message = "Erreur lors de la suppression : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Récupérer la liste des questionnaires existants
try {
    $stmt = $pdo->query("SELECT q.*, COUNT(questions.id) as nb_questions 
                         FROM questionnaire q 
                         LEFT JOIN questions ON q.id = questions.questionnaire_id 
                         GROUP BY q.id 
                         ORDER BY q.date_creation DESC");
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $questionnaires = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Créer un questionnaire</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="questionnaire-styles.css">
    <link rel="stylesheet" href="admin-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="candidatures/index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Admin
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="questionnaires.php">Voir les questionnaires</a></li>
                <li><a href="index.php">Site public</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content admin-content">
        <div class="container">
            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- En-tête -->
            <div class="admin-header">
                <h1 id="pageTitle"><i class="fas fa-plus-circle"></i> Créer un nouveau questionnaire</h1>
                <p id="pageDescription">Créez facilement des questionnaires interactifs avec questions à choix multiples.</p>
                <div class="header-actions">
                    <button type="button" id="newQuestionnaireBtn" class="btn btn-secondary" onclick="resetForm()" style="display: none;">
                        <i class="fas fa-plus"></i>
                        Nouveau questionnaire
                    </button>
                </div>
            </div>

            <!-- Formulaire de création/modification -->
            <div class="admin-form-container">
                <form id="questionnaireForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="create_questionnaire">
                    <input type="hidden" name="questionnaire_id" id="questionnaireId">
                    <input type="hidden" name="questions_data" id="questionsData">

                    <!-- Informations générales -->
                    <div class="form-section">
                        <h2><i class="fas fa-info-circle"></i> Informations générales</h2>
                        
                        <div class="form-group">
                            <label for="questionnaire_titre">Titre du questionnaire *</label>
                            <input type="text" 
                                   id="questionnaire_titre" 
                                   name="questionnaire_titre" 
                                   placeholder="Ex: Questionnaire de satisfaction client"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="questionnaire_description">Description</label>
                            <textarea id="questionnaire_description" 
                                      name="questionnaire_description" 
                                      rows="3"
                                      placeholder="Description optionnelle du questionnaire..."></textarea>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="form-section">
                        <h2><i class="fas fa-question-circle"></i> Questions</h2>
                        
                        <div id="questionsContainer">
                            <!-- Les questions seront ajoutées ici dynamiquement -->
                        </div>

                        <button type="button" id="addQuestionBtn" class="btn btn-secondary">
                            <i class="fas fa-plus"></i>
                            Ajouter une question
                        </button>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="form-actions">
                        <button type="button" id="previewBtn" class="btn btn-info">
                            <i class="fas fa-eye"></i>
                            Aperçu
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-save"></i>
                            Créer le questionnaire
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn" onclick="resetForm()" style="display: none;">
                            <i class="fas fa-times"></i>
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

            <!-- Liste des questionnaires existants -->
            <div class="existing-questionnaires">
                <h2><i class="fas fa-list"></i> Questionnaires existants</h2>
                
                <?php if (empty($questionnaires)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucun questionnaire créé pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="questionnaires-grid">
                        <?php foreach ($questionnaires as $questionnaire): ?>
                            <div class="questionnaire-card-admin">
                                <div class="card-header">
                                    <h3><?= htmlspecialchars($questionnaire['titre']) ?></h3>
                                    <div class="card-meta">
                                        <span><i class="fas fa-question-circle"></i> <?= $questionnaire['nb_questions'] ?> questions</span>
                                        <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($questionnaire['date_creation'])) ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($questionnaire['description']): ?>
                                    <p class="card-description"><?= htmlspecialchars($questionnaire['description']) ?></p>
                                <?php endif; ?>
                                
                                <div class="card-actions">
                                    <a href="questionnaire_reponse.php?id=<?= $questionnaire['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play"></i>
                                        Tester
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="editQuestionnaire(<?= $questionnaire['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                        Modifier
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteQuestionnaire(<?= $questionnaire['id'] ?>, '<?= htmlspecialchars($questionnaire['titre']) ?>')">
                                        <i class="fas fa-trash"></i>
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal d'aperçu -->
    <div id="previewModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> Aperçu du questionnaire</h3>
                <button type="button" class="close-modal" onclick="closePreviewModal()">&times;</button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Contenu de l'aperçu -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePreviewModal()">
                    <i class="fas fa-times"></i>
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <script>
        let questionCounter = 0;
        
        // Ajouter une nouvelle question
        document.getElementById('addQuestionBtn').addEventListener('click', function() {
            addQuestion();
        });

        function addQuestion(questionText = '', answers = ['']) {
            questionCounter++;
            
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-item';
            questionDiv.dataset.questionId = questionCounter;
            
            questionDiv.innerHTML = `
                <div class="question-header">
                    <h4>Question ${questionCounter}</h4>
                    <button type="button" class="btn-remove-question" onclick="removeQuestion(${questionCounter})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="form-group">
                    <label>Texte de la question *</label>
                    <input type="text" 
                           class="question-text" 
                           placeholder="Saisissez votre question..."
                           value="${questionText}"
                           required>
                </div>
                
                <div class="answers-section">
                    <label>Réponses possibles *</label>
                    <div class="answers-container" id="answers_${questionCounter}">
                        <!-- Les réponses seront ajoutées ici -->
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary add-answer" onclick="addAnswer(${questionCounter})">
                        <i class="fas fa-plus"></i>
                        Ajouter une réponse
                    </button>
                </div>
            `;
            
            document.getElementById('questionsContainer').appendChild(questionDiv);
            
            // Ajouter les réponses
            if (answers.length === 0) answers = [''];
            answers.forEach(answer => addAnswer(questionCounter, answer));
        }

        function addAnswer(questionId, answerText = '') {
            const answersContainer = document.getElementById(`answers_${questionId}`);
            const answerIndex = answersContainer.children.length;
            
            const answerDiv = document.createElement('div');
            answerDiv.className = 'answer-item';
            
            answerDiv.innerHTML = `
                <input type="text" 
                       class="answer-text" 
                       placeholder="Réponse ${answerIndex + 1}"
                       value="${answerText}"
                       required>
                <button type="button" class="btn-remove-answer" onclick="removeAnswer(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            answersContainer.appendChild(answerDiv);
        }

        function removeQuestion(questionId) {
            const questionDiv = document.querySelector(`[data-question-id="${questionId}"]`);
            if (questionDiv) {
                questionDiv.remove();
                updateQuestionNumbers();
            }
        }

        function removeAnswer(button) {
            button.parentElement.remove();
        }

        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-item');
            questions.forEach((question, index) => {
                const header = question.querySelector('.question-header h4');
                header.textContent = `Question ${index + 1}`;
            });
        }

        // Aperçu du questionnaire
        document.getElementById('previewBtn').addEventListener('click', function() {
            const questionsData = collectQuestionsData();
            if (questionsData.length === 0) {
                alert('Veuillez ajouter au moins une question avant de prévisualiser.');
                return;
            }
            
            showPreview(questionsData);
        });

        function showPreview(questionsData) {
            const titre = document.getElementById('questionnaire_titre').value || 'Questionnaire sans titre';
            const description = document.getElementById('questionnaire_description').value || 'Aucune description';
            
            let previewHTML = `
                <div class="preview-container">
                    <div class="preview-header">
                        <h2>${titre}</h2>
                        <p>${description}</p>
                        <div class="preview-meta">
                            <span><i class="fas fa-question-circle"></i> ${questionsData.length} questions</span>
                            <span><i class="fas fa-clock"></i> Environ ${Math.ceil(questionsData.length * 0.5)} minutes</span>
                        </div>
                    </div>
                    
                    <div class="preview-questions">
            `;
            
            questionsData.forEach((question, index) => {
                previewHTML += `
                    <div class="preview-question">
                        <h4>${index + 1}. ${question.libelle}</h4>
                        <div class="preview-answers">
                            ${question.reponses.map(reponse => `
                                <div class="preview-answer">
                                    <input type="radio" disabled> ${reponse}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });
            
            previewHTML += `
                    </div>
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewHTML;
            document.getElementById('previewModal').style.display = 'block';
        }

        function closePreviewModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        // Collecte des données des questions
        function collectQuestionsData() {
            const questions = [];
            const questionItems = document.querySelectorAll('.question-item');
            
            questionItems.forEach(item => {
                const questionText = item.querySelector('.question-text').value.trim();
                if (!questionText) return;
                
                const answers = [];
                const answerInputs = item.querySelectorAll('.answer-text');
                
                answerInputs.forEach(input => {
                    const answerText = input.value.trim();
                    if (answerText) {
                        answers.push(answerText);
                    }
                });
                
                if (answers.length >= 2) {
                    questions.push({
                        libelle: questionText,
                        reponses: answers
                    });
                }
            });
            
            return questions;
        }

        // Soumission du formulaire
        document.getElementById('questionnaireForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const questionsData = collectQuestionsData();
            
            if (questionsData.length === 0) {
                alert('Veuillez ajouter au moins une question avec au moins 2 réponses.');
                return;
            }
            
            // Vérifier que chaque question a au moins 2 réponses
            const invalidQuestions = questionsData.filter(q => q.reponses.length < 2);
            if (invalidQuestions.length > 0) {
                alert('Chaque question doit avoir au moins 2 réponses possibles.');
                return;
            }
            
            document.getElementById('questionsData').value = JSON.stringify(questionsData);
            this.submit();
        });

        // Fonctions de gestion des questionnaires existants
        function editQuestionnaire(questionnaireId) {
            // Charger les données du questionnaire
            fetch(`get_questionnaire.php?id=${questionnaireId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateEditForm(data.questionnaire, data.questions);
                    } else {
                        alert('Erreur lors du chargement du questionnaire : ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement du questionnaire');
                });
        }
        
        function populateEditForm(questionnaire, questions) {
            // Changer le mode du formulaire
            document.getElementById('formAction').value = 'update_questionnaire';
            document.getElementById('questionnaireId').value = questionnaire.id;
            
            // Mettre à jour l'interface
            document.getElementById('pageTitle').innerHTML = '<i class="fas fa-edit"></i> Modifier le questionnaire';
            document.getElementById('pageDescription').textContent = 'Modifiez le questionnaire et ses questions.';
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Mettre à jour';
            document.getElementById('newQuestionnaireBtn').style.display = 'inline-block';
            document.getElementById('cancelBtn').style.display = 'inline-block';
            
            // Remplir les champs
            document.getElementById('questionnaire_titre').value = questionnaire.titre;
            document.getElementById('questionnaire_description').value = questionnaire.description || '';
            
            // Vider les questions existantes
            document.getElementById('questionsContainer').innerHTML = '';
            questionCounter = 0;
            
            // Ajouter les questions existantes
            questions.forEach(question => {
                addQuestion(question.libelle, question.reponses);
            });
            
            // Défiler vers le formulaire
            document.querySelector('.admin-form-container').scrollIntoView({ behavior: 'smooth' });
        }
        
        function resetForm() {
            // Remettre en mode création
            document.getElementById('formAction').value = 'create_questionnaire';
            document.getElementById('questionnaireId').value = '';
            
            // Mettre à jour l'interface
            document.getElementById('pageTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Créer un nouveau questionnaire';
            document.getElementById('pageDescription').textContent = 'Créez facilement des questionnaires interactifs avec questions à choix multiples.';
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Créer le questionnaire';
            document.getElementById('newQuestionnaireBtn').style.display = 'none';
            document.getElementById('cancelBtn').style.display = 'none';
            
            // Vider le formulaire
            document.getElementById('questionnaireForm').reset();
            document.getElementById('questionsContainer').innerHTML = '';
            questionCounter = 0;
        }

        function deleteQuestionnaire(questionnaireId, titre) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le questionnaire "${titre}" ?\n\nCette action est irréversible et supprimera également toutes les questions et réponses associées.`)) {
                // Créer un formulaire pour envoyer la requête de suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_questionnaire';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'questionnaire_id';
                idInput.value = questionnaireId;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fermer le modal en cliquant à l'extérieur
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target === modal) {
                closePreviewModal();
            }
        });

        // Ajouter la première question par défaut
        addQuestion();
    </script>
</body>
</html>