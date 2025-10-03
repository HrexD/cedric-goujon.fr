<?php
// auth_helper.php
// Fonctions d'aide pour l'authentification

/**
 * Vérifie si l'utilisateur est connecté en tant qu'admin
 */
function isAdminLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

/**
 * Génère la navigation dynamique en fonction du statut admin
 * @param string $currentPage La page active actuelle
 * @return string Le HTML de la navigation
 */
function generateNavigation(string $currentPage = ''): string {
    $isAdmin = isAdminLoggedIn();
    
    $navItems = [
        'index' => 'Accueil',
        'cv' => 'Mon CV', 
        'projets' => 'Mes Projets',
        'contact' => 'Contact'
    ];
    
    // Ajouter candidatures si admin connecté
    if ($isAdmin) {
        $navItems['candidatures/index'] = 'Candidatures';
    }
    
    // Toujours ajouter admin à la fin
    $navItems['admin'] = 'Admin';
    
    $nav = '<nav>' . PHP_EOL;
    $items = [];
    
    foreach ($navItems as $page => $label) {
        $isActive = ($currentPage === $page) ? ' id="active"' : '';
        $class = ($page === 'admin') ? ' class="admin-link"' : '';
        $class = ($page === 'candidatures/index') ? ' class="candidatures-link"' : $class;
        
        $items[] = "    <a href=\"{$page}\"{$isActive}{$class}>{$label}</a>";
    }
    
    $nav .= implode(' |' . PHP_EOL, $items) . PHP_EOL;
    $nav .= '</nav>';
    
    return $nav;
}
?>