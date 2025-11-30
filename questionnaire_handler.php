<?php
require_once 'config.php';

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: questionnaires.php');
    exit;
}

// Récupération des données
$questionnaire_id = $_POST['questionnaire_id'] ?? null;
$questionnaire_titre = $_POST['questionnaire_titre'] ?? 'Questionnaire';

if (!$questionnaire_id) {
    die('Erreur : ID du questionnaire manquant.');
}

// Collecte des réponses
$reponses = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'question_') === 0) {
        $question_id = str_replace('question_', '', $key);
        $question_id = str_replace('[]', '', $question_id); // Pour les checkboxes
        
        // Gérer les réponses multiples (checkboxes) et uniques (radio)
        if (is_array($value)) {
            $reponses[$question_id] = $value; // Array pour checkboxes
        } else {
            $reponses[$question_id] = [$value]; // Array uniforme
        }
    }
}

if (empty($reponses)) {
    die('Erreur : Aucune réponse trouvée.');
}

try {
    // Récupérer les détails des questions et réponses
    $response_details = [];
    foreach ($reponses as $question_id => $reponse_ids) {
        // Récupérer la question
        $stmt = $pdo->prepare("SELECT libelle FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$question) continue;
        
        $question_responses = [];
        foreach ($reponse_ids as $reponse_id) {
            $stmt = $pdo->prepare("SELECT libelle FROM reponses WHERE id = ?");
            $stmt->execute([$reponse_id]);
            $reponse = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reponse) {
                $question_responses[] = $reponse['libelle'];
            }
        }
        
        if (!empty($question_responses)) {
            $response_details[] = [
                'question' => $question['libelle'],
                'reponse' => implode(', ', $question_responses),
                'question_id' => $question_id,
                'reponse_ids' => $reponse_ids,
                'is_multiple' => count($question_responses) > 1
            ];
        }
    }
    
    // Enregistrer les réponses en base (optionnel pour statistiques)
    $session_id = uniqid('session_', true);
    $date_reponse = date('Y-m-d H:i:s');
    
    // Optionnel : insérer dans une table de sessions de réponses
    /*
    $stmt = $pdo->prepare("INSERT INTO sessions_reponses (session_id, questionnaire_id, date_reponse, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$session_id, $questionnaire_id, $date_reponse, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);
    
    foreach ($response_details as $detail) {
        $stmt = $pdo->prepare("INSERT INTO reponses_utilisateur (session_id, question_id, reponse_id) VALUES (?, ?, ?)");
        $stmt->execute([$session_id, $detail['question_id'], $detail['reponse_id']]);
    }
    */
    
    // Préparation de l'email
    $email_content = generateEmailContent($questionnaire_titre, $response_details, $session_id, $date_reponse);
    
    // Configuration email (à adapter selon votre configuration)
    $to_email = 'contact@cedric-goujon.fr'; 
    $subject = "Nouvelle réponse au questionnaire : " . $questionnaire_titre;
    
    // Headers pour l'email HTML
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Questionnaire Site Cédric <noreply@cedric-goujon.com>',
        'Reply-To: noreply@cedric-goujon.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Envoi de l'email
    $email_sent = mail($to_email, $subject, $email_content, implode("\r\n", $headers));
    
} catch (PDOException $e) {
    $error = "Erreur lors du traitement : " . $e->getMessage();
}

function generateEmailContent($titre, $reponses, $session_id, $date_reponse) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Réponses au questionnaire</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                background-color: #f5f5f5;
            }
            .email-container {
                background-color: white;
                padding: 30px;
                margin: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 25px;
                border-radius: 10px;
                text-align: center;
                margin-bottom: 30px;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
            }
            .meta-info {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 25px;
                border-left: 4px solid #667eea;
            }
            .meta-info h3 {
                margin: 0 0 10px 0;
                color: #667eea;
                font-size: 18px;
            }
            .meta-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                padding: 8px 0;
                border-bottom: 1px solid #e9ecef;
            }
            .meta-item:last-child {
                border-bottom: none;
            }
            .meta-label {
                font-weight: 600;
                color: #495057;
            }
            .meta-value {
                color: #6c757d;
            }
            .responses-section h2 {
                color: #2c3e50;
                border-bottom: 2px solid #667eea;
                padding-bottom: 10px;
                margin-bottom: 25px;
            }
            .question-block {
                background-color: #fff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                margin-bottom: 20px;
                overflow: hidden;
                transition: transform 0.2s ease;
            }
            .question-block:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .question-header {
                background-color: #f8f9fa;
                padding: 15px 20px;
                border-bottom: 1px solid #e9ecef;
            }
            .question-number {
                background-color: #667eea;
                color: white;
                padding: 4px 8px;
                border-radius: 50%;
                font-size: 12px;
                font-weight: bold;
                margin-right: 10px;
            }
            .question-text {
                font-weight: 600;
                color: #2c3e50;
                font-size: 16px;
            }
            .answer-section {
                padding: 20px;
            }
            .answer-text {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 20px;
                border-radius: 25px;
                display: inline-block;
                font-weight: 500;
                position: relative;
                margin-bottom: 8px;
                margin-right: 8px;
            }
            .answer-text::before {
                content: "✓";
                margin-right: 8px;
                font-weight: bold;
            }
            .multiple-answers {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .answer-multiple {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 0.9em;
                font-weight: 500;
            }
            .answer-multiple::before {
                content: "✓";
                margin-right: 6px;
                font-weight: bold;
            }
            .statistics {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                margin-top: 30px;
            }
            .statistics h3 {
                margin: 0 0 15px 0;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            .stat-item {
                background-color: rgba(255,255,255,0.2);
                padding: 15px;
                border-radius: 8px;
            }
            .stat-number {
                font-size: 24px;
                font-weight: bold;
                display: block;
            }
            .stat-label {
                font-size: 14px;
                opacity: 0.9;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding: 20px;
                color: #6c757d;
                border-top: 1px solid #e9ecef;
            }
            .footer p {
                margin: 5px 0;
            }
            @media (max-width: 600px) {
                .email-container {
                    margin: 10px;
                    padding: 20px;
                }
                .header {
                    padding: 20px;
                }
                .question-text {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>📋 Nouvelle Réponse au Questionnaire</h1>
                <p><?php echo htmlspecialchars($titre); ?></p>
            </div>

            <div class="meta-info">
                <h3>ℹ️ Informations de la session</h3>
                <div class="meta-item">
                    <span class="meta-label">ID de session :</span>
                    <span class="meta-value"><?php echo htmlspecialchars($session_id); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date et heure :</span>
                    <span class="meta-value"><?php echo date('d/m/Y à H:i:s', strtotime($date_reponse)); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Nombre de réponses :</span>
                    <span class="meta-value"><?php echo count($reponses); ?> questions</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Type de questionnaire :</span>
                    <span class="meta-value"><?php echo htmlspecialchars($titre); ?></span>
                </div>
            </div>

            <div class="responses-section">
                <h2>📝 Réponses détaillées</h2>
                
                <?php foreach ($reponses as $index => $reponse): ?>
                <div class="question-block">
                    <div class="question-header">
                        <span class="question-number"><?php echo $index + 1; ?></span>
                        <span class="question-text"><?php echo htmlspecialchars($reponse['question']); ?></span>
                    </div>
                    <div class="answer-section">
                        <?php if (is_array($reponse['reponse'])): ?>
                            <div class="multiple-answers">
                                <?php foreach ($reponse['reponse'] as $answer): ?>
                                    <span class="answer-multiple"><?php echo htmlspecialchars($answer); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="answer-text">
                                <?php echo htmlspecialchars($reponse['reponse']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="statistics">
                <h3>📊 Statistiques de cette session</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($reponses); ?></span>
                        <span class="stat-label">Questions répondues</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo ceil(count($reponses) * 0.5); ?></span>
                        <span class="stat-label">Minutes estimées</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Taux de complétion</span>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p><strong>📧 Email généré automatiquement</strong></p>
                <p>Cet email a été généré automatiquement par le système de questionnaire.</p>
                <p><em>Session ID: <?php echo htmlspecialchars($session_id); ?> | <?php echo date('Y-m-d H:i:s'); ?></em></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaire envoyé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="questionnaire-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <main class="main-content">
        <div class="container">
            <div class="success-container">
                <?php if (isset($error)): ?>
                    <div class="error-card">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h1>Erreur lors de l'envoi</h1>
                        <p><?= htmlspecialchars($error) ?></p>
                        <div class="action-buttons">
                            <a href="javascript:history.back()" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                <?php elseif (isset($email_sent) && $email_sent): ?>
                    <div class="success-card">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1>Questionnaire envoyé avec succès !</h1>
                        <p>Merci d'avoir pris le temps de répondre au questionnaire "<strong><?= htmlspecialchars($questionnaire_titre) ?></strong>".</p>
                        <div class="success-details">
                            <div class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <span>Email envoyé avec vos réponses</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span>Traité le <?= date('d/m/Y à H:i') ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-user-secret"></i>
                                <span>Réponses anonymes</span>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <a href="questionnaires.php" class="btn btn-primary">
                                <i class="fas fa-list"></i>
                                Voir d'autres questionnaires
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-home"></i>
                                Retour à l'accueil
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="warning-card">
                        <div class="warning-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h1>Questionnaire traité</h1>
                        <p>Vos réponses ont été enregistrées, mais il y a eu un problème avec l'envoi de l'email.</p>
                        <div class="action-buttons">
                            <a href="questionnaires.php" class="btn btn-primary">
                                <i class="fas fa-list"></i>
                                Retour aux questionnaires
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }

        .success-card, .error-card, .warning-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid #e9ecef;
        }

        .success-card {
            border-color: #28a745;
        }

        .error-card {
            border-color: #dc3545;
        }

        .warning-card {
            border-color: #ffc107;
        }

        .success-icon, .error-icon, .warning-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .success-icon {
            color: #28a745;
        }

        .error-icon {
            color: #dc3545;
        }

        .warning-icon {
            color: #ffc107;
        }

        .success-card h1, .error-card h1, .warning-card h1 {
            margin: 0 0 15px 0;
            color: #2c3e50;
        }

        .success-details {
            margin: 25px 0;
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .detail-item i {
            width: 20px;
            margin-right: 10px;
            color: #667eea;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 20px;
            }
            
            .success-card, .error-card, .warning-card {
                padding: 30px 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>