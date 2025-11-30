<?php
require 'config.php';

$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
echo "GitHub URL: " . ($user['github'] ?? 'Non défini') . "\n";

// Fonction de test pour récupérer les repositories GitHub
function getGitHubRepos($githubUrl) {
    // Extraire le nom d'utilisateur de l'URL GitHub
    if (preg_match('/github\.com\/([^\/?]+)/', $githubUrl, $matches)) {
        $username = $matches[1];
        echo "Nom d'utilisateur GitHub trouvé: $username\n";
        
        $apiUrl = "https://api.github.com/users/{$username}/repos?sort=updated&direction=desc&per_page=10";
        echo "URL API: $apiUrl\n";
        
        // Configuration du contexte pour l'API GitHub
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Portfolio-Website/1.0',
                    'Accept: application/vnd.github.v3+json'
                ],
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response !== false) {
            $repos = json_decode($response, true);
            
            if (is_array($repos)) {
                echo "Nombre de repositories trouvés: " . count($repos) . "\n";
                
                // Filtrer les repositories pour exclure les forks
                $filteredRepos = array_filter($repos, function($repo) {
                    return !$repo['fork'] && !$repo['archived'];
                });
                
                echo "Après filtrage: " . count($filteredRepos) . "\n";
                
                // Afficher les premiers repositories
                foreach (array_slice($filteredRepos, 0, 3) as $repo) {
                    echo "- " . $repo['name'] . " (" . $repo['html_url'] . ")\n";
                }
                
                return $filteredRepos;
            } else {
                echo "Erreur: Réponse API non valide\n";
                echo "Réponse: " . $response . "\n";
            }
        } else {
            echo "Erreur: Impossible de récupérer les données de l'API GitHub\n";
            echo "Dernière erreur: " . error_get_last()['message'] ?? 'Inconnue' . "\n";
        }
    } else {
        echo "Erreur: URL GitHub invalide\n";
    }
    
    return [];
}

// Test
if (!empty($user['github'])) {
    echo "\nTest de récupération des repositories...\n";
    $repos = getGitHubRepos($user['github']);
} else {
    echo "Aucune URL GitHub configurée!\n";
}
?>