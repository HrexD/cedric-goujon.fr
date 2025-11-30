<?php
// Test simple sans base de données
$githubUrl = "https://github.com/HrexD";

echo "Test de récupération GitHub pour: $githubUrl\n\n";

// Fonction pour récupérer les repositories GitHub
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
        
        echo "Tentative de connexion à l'API...\n";
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response !== false) {
            $repos = json_decode($response, true);
            
            if (is_array($repos)) {
                echo "✅ Nombre de repositories trouvés: " . count($repos) . "\n\n";
                
                // Filtrer les repositories pour exclure les forks
                $filteredRepos = array_filter($repos, function($repo) {
                    return !$repo['fork'] && !$repo['archived'];
                });
                
                echo "Après filtrage (sans forks/archivés): " . count($filteredRepos) . "\n\n";
                
                // Afficher les repositories
                foreach (array_slice($filteredRepos, 0, 5) as $index => $repo) {
                    echo ($index + 1) . ". " . $repo['name'] . "\n";
                    echo "   Description: " . ($repo['description'] ?? 'Aucune description') . "\n";
                    echo "   Langage: " . ($repo['language'] ?? 'Non spécifié') . "\n";
                    echo "   URL: " . $repo['html_url'] . "\n";
                    echo "   Étoiles: " . $repo['stargazers_count'] . "\n";
                    echo "   Forks: " . $repo['forks_count'] . "\n";
                    echo "   Mis à jour: " . date('d/m/Y', strtotime($repo['updated_at'])) . "\n\n";
                }
                
                return $filteredRepos;
            } else {
                echo "❌ Erreur: Réponse API non valide\n";
                echo "Réponse: " . substr($response, 0, 200) . "...\n";
            }
        } else {
            echo "❌ Erreur: Impossible de récupérer les données de l'API GitHub\n";
            $error = error_get_last();
            if ($error) {
                echo "Dernière erreur: " . $error['message'] . "\n";
            }
        }
    } else {
        echo "❌ Erreur: URL GitHub invalide\n";
    }
    
    return [];
}

// Test
$repos = getGitHubRepos($githubUrl);

if (count($repos) > 0) {
    echo "\n🎉 Test réussi ! " . count($repos) . " repositories récupérés.\n";
} else {
    echo "\n❌ Aucun repository récupéré.\n";
}
?>