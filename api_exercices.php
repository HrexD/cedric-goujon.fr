<?php
session_start();
require 'config.php';
require 'auth_helper.php';

// Vérifier l'authentification
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Headers pour l'API JSON
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM exercices WHERE id = ?");
            $stmt->execute([$id]);
            $exercice = $stmt->fetch();
            
            if (!$exercice) {
                throw new Exception('Exercice introuvable');
            }
            
            echo json_encode(['success' => true, 'exercice' => $exercice]);
            break;
            
        case 'list':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $per_page = min(50, max(1, (int)($_GET['per_page'] ?? 10)));
            $offset = ($page - 1) * $per_page;
            
            // Filtres
            $where_conditions = [];
            $params = [];
            
            if (!empty($_GET['langage'])) {
                $where_conditions[] = 'langage = ?';
                $params[] = $_GET['langage'];
            }
            
            if (!empty($_GET['difficulte'])) {
                $where_conditions[] = 'difficulte = ?';
                $params[] = $_GET['difficulte'];
            }
            
            if (!empty($_GET['statut'])) {
                $where_conditions[] = 'statut = ?';
                $params[] = $_GET['statut'];
            }
            
            if (!empty($_GET['search'])) {
                $where_conditions[] = '(titre LIKE ? OR description LIKE ? OR tags LIKE ?)';
                $search_term = '%' . $_GET['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Récupérer les exercices
            $stmt = $pdo->prepare("
                SELECT * FROM exercices 
                {$where_clause} 
                ORDER BY date_creation DESC 
                LIMIT {$per_page} OFFSET {$offset}
            ");
            $stmt->execute($params);
            $exercices = $stmt->fetchAll();
            
            // Compter le total
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM exercices {$where_clause}");
            $count_stmt->execute($params);
            $total = $count_stmt->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'exercices' => $exercices,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per_page,
                    'total' => $total,
                    'total_pages' => ceil($total / $per_page)
                ]
            ]);
            break;
            
        case 'stats':
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN statut = 'actif' THEN 1 END) as actifs,
                    COUNT(CASE WHEN statut = 'inactif' THEN 1 END) as inactifs,
                    COUNT(CASE WHEN statut = 'brouillon' THEN 1 END) as brouillons,
                    COUNT(CASE WHEN difficulte = 'facile' THEN 1 END) as faciles,
                    COUNT(CASE WHEN difficulte = 'moyen' THEN 1 END) as moyens,
                    COUNT(CASE WHEN difficulte = 'difficile' THEN 1 END) as difficiles,
                    AVG(temps_estime) as temps_moyen,
                    AVG(points) as points_moyens
                FROM exercices
            ");
            $stats = $stmt->fetch();
            
            // Statistiques par langage
            $stmt_lang = $pdo->query("
                SELECT langage, COUNT(*) as count 
                FROM exercices 
                WHERE statut = 'actif' 
                GROUP BY langage 
                ORDER BY count DESC
            ");
            $stats_langages = $stmt_lang->fetchAll();
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'langages' => $stats_langages
            ]);
            break;
            
        case 'langages':
            $stmt = $pdo->query("SELECT DISTINCT langage FROM exercices ORDER BY langage");
            $langages = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode(['success' => true, 'langages' => $langages]);
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>