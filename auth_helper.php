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
    
    // Déterminer si on est dans un sous-dossier (candidatures)
    $isInCandidaturesFolder = strpos($currentPage, 'candidatures/') === 0;
    
    // Définir les liens de navigation selon la position
    if ($isInCandidaturesFolder) {
        // Navigation depuis le dossier candidatures
        $navItems = [
            '../index' => 'Accueil',
            '../cv' => 'Mon CV', 
            '../projets' => 'Mes Projets',
            '../exercices' => 'Exercices',
            '../contact' => 'Contact'
        ];
        
        // Ajouter candidatures si admin connecté
        if ($isAdmin) {
            $navItems['index'] = 'Candidatures';
        }
        
        // Admin
        $navItems['../admin'] = 'Admin';
    } else {
        // Navigation depuis la racine
        $navItems = [
            'index' => 'Accueil',
            'cv' => 'Mon CV', 
            'projets' => 'Mes Projets',
            'exercices' => 'Exercices',
            'contact' => 'Contact'
        ];
        
        // Ajouter candidatures si admin connecté
        if ($isAdmin) {
            $navItems['candidatures/index'] = 'Candidatures';
        }
        
        // Admin
        $navItems['admin'] = 'Admin';
    }
    
    $nav = '<nav>' . PHP_EOL;
    
    // Bouton hamburger pour mobile
    $nav .= '    <button class="nav-toggle" onclick="toggleNav()" aria-label="Menu">' . PHP_EOL;
    $nav .= '        <span></span>' . PHP_EOL;
    $nav .= '        <span></span>' . PHP_EOL;
    $nav .= '        <span></span>' . PHP_EOL;
    $nav .= '    </button>' . PHP_EOL;
    
    // Container des liens de navigation
    $nav .= '    <div class="nav-links">' . PHP_EOL;
    
    $items = [];
    
    foreach ($navItems as $path => $label) {
        // Déterminer si c'est la page active
        $isActive = false;
        if ($isInCandidaturesFolder) {
            $isActive = ($currentPage === 'candidatures/index' && $path === 'index') ||
                       ($currentPage === $path);
        } else {
            $isActive = ($currentPage === $path);
        }
        
        $activeAttr = $isActive ? ' id="active"' : '';
        
        // Déterminer la classe CSS
        $class = '';
        if (strpos($path, 'admin') !== false) {
            $class = ' class="admin-link"';
        } elseif ($label === 'Candidatures') {
            $class = ' class="candidatures-link"';
        }
        
        $items[] = "        <a href=\"{$path}\"{$activeAttr}{$class}>{$label}</a>";
    }
    
    $nav .= implode(PHP_EOL, $items) . PHP_EOL;
    $nav .= '    </div>' . PHP_EOL;
    $nav .= '</nav>' . PHP_EOL;
    
    // Ajouter le script JavaScript pour le menu mobile
    $nav .= '<script>' . PHP_EOL;
    $nav .= 'function toggleNav() {' . PHP_EOL;
    $nav .= '    const navLinks = document.querySelector(\'.nav-links\');' . PHP_EOL;
    $nav .= '    const navToggle = document.querySelector(\'.nav-toggle\');' . PHP_EOL;
    $nav .= '    navLinks.classList.toggle(\'active\');' . PHP_EOL;
    $nav .= '    navToggle.classList.toggle(\'active\');' . PHP_EOL;
    $nav .= '}' . PHP_EOL;
    $nav .= '' . PHP_EOL;
    $nav .= '// Fermer le menu si on clique en dehors' . PHP_EOL;
    $nav .= 'document.addEventListener(\'click\', function(event) {' . PHP_EOL;
    $nav .= '    const nav = document.querySelector(\'nav\');' . PHP_EOL;
    $nav .= '    const navLinks = document.querySelector(\'.nav-links\');' . PHP_EOL;
    $nav .= '    const navToggle = document.querySelector(\'.nav-toggle\');' . PHP_EOL;
    $nav .= '    ' . PHP_EOL;
    $nav .= '    if (!nav.contains(event.target) && navLinks.classList.contains(\'active\')) {' . PHP_EOL;
    $nav .= '        navLinks.classList.remove(\'active\');' . PHP_EOL;
    $nav .= '        navToggle.classList.remove(\'active\');' . PHP_EOL;
    $nav .= '    }' . PHP_EOL;
    $nav .= '});' . PHP_EOL;
    $nav .= '' . PHP_EOL;
    $nav .= '// Fermer le menu si on clique sur un lien' . PHP_EOL;
    $nav .= 'document.querySelectorAll(\'.nav-links a\').forEach(link => {' . PHP_EOL;
    $nav .= '    link.addEventListener(\'click\', function() {' . PHP_EOL;
    $nav .= '        const navLinks = document.querySelector(\'.nav-links\');' . PHP_EOL;
    $nav .= '        const navToggle = document.querySelector(\'.nav-toggle\');' . PHP_EOL;
    $nav .= '        navLinks.classList.remove(\'active\');' . PHP_EOL;
    $nav .= '        navToggle.classList.remove(\'active\');' . PHP_EOL;
    $nav .= '    });' . PHP_EOL;
    $nav .= '});' . PHP_EOL;
    $nav .= '</script>' . PHP_EOL;
    
    return $nav;
}
?>