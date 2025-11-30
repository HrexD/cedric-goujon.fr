<?php
require_once 'config.php';
require_once 'auth_helper.php';

// Vérifier si l'utilisateur est admin
if (!isAdminLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit();
}

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID questionnaire invalide']);
    exit();
}

$questionnaire_id = (int)$_GET['id'];

try {
    // Récupérer le questionnaire
    $stmt = $pdo->prepare("SELECT * FROM questionnaire WHERE id = ?");
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$questionnaire) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Questionnaire non trouvé']);
        exit();
    }
    
    // Récupérer les questions et leurs réponses
    $stmt = $pdo->prepare("
        SELECT 
            q.id as question_id,
            q.libelle as question_libelle,
            r.id as reponse_id,
            r.libelle as reponse_libelle
        FROM questions q
        LEFT JOIN reponses r ON q.id = r.question_id
        WHERE q.questionnaire_id = ?
        ORDER BY q.id, r.id
    ");
    $stmt->execute([$questionnaire_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser les données
    $questions = [];
    $current_question = null;
    
    foreach ($results as $row) {
        if ($current_question === null || $current_question['id'] !== $row['question_id']) {
            // Nouvelle question
            if ($current_question !== null) {
                $questions[] = $current_question;
            }
            
            $current_question = [
                'id' => $row['question_id'],
                'libelle' => $row['question_libelle'],
                'reponses' => []
            ];
        }
        
        // Ajouter la réponse si elle existe
        if ($row['reponse_id']) {
            $current_question['reponses'][] = $row['reponse_libelle'];
        }
    }
    
    // Ajouter la dernière question
    if ($current_question !== null) {
        $questions[] = $current_question;
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'questionnaire' => $questionnaire,
        'questions' => $questions
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>