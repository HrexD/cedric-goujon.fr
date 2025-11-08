<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    echo '<div class="notification notification-error">Exercice introuvable</div>';
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM exercices WHERE id = ? AND statut = 'actif'");
    $stmt->execute([$id]);
    $exercice = $stmt->fetch();
    
    if (!$exercice) {
        echo '<div class="notification notification-error">Exercice introuvable</div>';
        exit;
    }
    
    $tags = json_decode($exercice['tags'] ?? '[]', true) ?: [];
    
    // Fonction pour obtenir la couleur selon la difficulté
    function getDifficulteColor($difficulte) {
        switch ($difficulte) {
            case 'facile': return '#10b981';
            case 'moyen': return '#f59e0b';
            case 'difficile': return '#ef4444';
            default: return '#6b7280';
        }
    }
    
    // Fonction pour obtenir l'icône du langage
    function getLangageIcon($langage) {
        switch (strtolower($langage)) {
            case 'php': return '🐘';
            case 'javascript': return '🟨';
            case 'python': return '🐍';
            case 'java': return '☕';
            case 'c++': return '⚡';
            case 'c#': return '🔵';
            case 'css': return '🎨';
            case 'html': return '📝';
            case 'sql': return '🗄️';
            default: return '💻';
        }
    }
    
} catch (PDOException $e) {
    echo '<div class="notification notification-error">Erreur de base de données</div>';
    exit;
}
?>

<div class="exercice-detail" style="background: white; padding: var(--spacing-xl); border-radius: var(--radius-lg); color: #1a1a1a; line-height: 1.6; max-height: 80vh; overflow-y: auto;">
    <!-- En-tête -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-md); border-bottom: 2px solid #e5e7eb;">
        <div>
            <span class="langage-badge" style="background: #dbeafe; color: #1d4ed8; padding: 0.5rem 1rem; border-radius: var(--radius-full); font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem; display: inline-block;">
                <?= getLangageIcon($exercice['langage']) ?> <?= htmlspecialchars($exercice['langage']) ?>
            </span>
            <h3 style="margin: 0; color: #111827; font-size: 1.5rem; font-weight: 700;">
                <?= htmlspecialchars($exercice['titre']) ?>
            </h3>
        </div>
        <span class="difficulte-badge" style="background: <?= getDifficulteColor($exercice['difficulte']) ?>; color: white; padding: 0.5rem 1rem; border-radius: var(--radius-full); font-size: 0.9rem; font-weight: 600;">
            <?= ucfirst($exercice['difficulte']) ?>
        </span>
    </div>
    
    <!-- Description -->
    <div style="margin-bottom: var(--spacing-lg);">
        <p style="color: #4b5563; font-size: 1rem; line-height: 1.7; margin: 0;">
            <?= htmlspecialchars($exercice['description']) ?>
        </p>
    </div>
    
    <!-- Métadonnées -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: var(--spacing-md); margin-bottom: var(--spacing-xl); padding: var(--spacing-md); background: #f8fafc; border-radius: var(--radius-md); border: 1px solid #e5e7eb;">
        <div style="text-align: center;">
            <div style="font-size: 1.2rem;">⏱️</div>
            <strong style="color: #111827;"><?= $exercice['temps_estime'] ?> min</strong>
            <div style="font-size: 0.8rem; color: #6b7280;">Temps estimé</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 1.2rem;">🏆</div>
            <strong style="color: #111827;"><?= $exercice['points'] ?> points</strong>
            <div style="font-size: 0.8rem; color: #6b7280;">Récompense</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 1.2rem;">👤</div>
            <strong style="color: #111827;"><?= htmlspecialchars($exercice['auteur']) ?></strong>
            <div style="font-size: 0.8rem; color: #6b7280;">Auteur</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 1.2rem;">📅</div>
            <strong style="color: #111827;"><?= date('d/m/Y', strtotime($exercice['date_creation'])) ?></strong>
            <div style="font-size: 0.8rem; color: #6b7280;">Créé le</div>
        </div>
    </div>
    
    <!-- Tags -->
    <?php if (!empty($tags)): ?>
        <div style="margin-bottom: var(--spacing-xl);">
            <h4 style="margin-bottom: var(--spacing-sm); color: #1d4ed8; font-weight: 600;">🏷️ Tags</h4>
            <div>
                <?php foreach ($tags as $tag): ?>
                    <span style="background: #dbeafe; color: #1d4ed8; padding: 0.4rem 0.8rem; border-radius: var(--radius-full); font-size: 0.85rem; margin: 0 0.5rem 0.5rem 0; display: inline-block; font-weight: 500; border: 1px solid #bfdbfe;">
                        #<?= htmlspecialchars($tag) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Énoncé -->
    <div style="margin-bottom: var(--spacing-xl);">
        <h4 style="margin-bottom: var(--spacing-md); color: #1d4ed8; display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
            📋 Énoncé de l'exercice
        </h4>
        <div style="background: #f8fafc; padding: var(--spacing-lg); border-radius: var(--radius-md); border-left: 4px solid #1d4ed8; line-height: 1.8; color: #374151; border: 1px solid #e5e7eb;">
            <?= nl2br(htmlspecialchars($exercice['enonce'])) ?>
        </div>
    </div>
    
    <!-- Zone de travail -->
    <div style="margin-bottom: var(--spacing-xl);">
        <h4 style="margin-bottom: var(--spacing-md); color: var(--primary-color); display: flex; align-items: center; gap: 0.5rem;">
            💻 Zone de travail
            <button onclick="toggleSolution()" id="solutionBtn" class="btn btn-secondary" style="margin-left: auto;">
                👁️ Voir la solution
            </button>
        </h4>
        
        <div style="background: #1a1a1a; color: #e5e5e5; padding: var(--spacing-lg); border-radius: var(--radius-md); font-family: 'Courier New', Consolas, monospace; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-sm); border-bottom: 1px solid #404040;">
                <span style="color: #888; font-size: 0.9rem; font-weight: 600;"><?= htmlspecialchars($exercice['langage']) ?> - Votre solution</span>
                <button onclick="copyCode()" class="btn btn-secondary" style="font-size: 0.8rem; background: #404040; color: white;">
                    📋 Copier
                </button>
            </div>
            
            <textarea id="codeEditor" 
                      placeholder="// Écrivez votre code ici...&#10;// Prenez le temps de réfléchir à la logique avant de commencer&#10;&#10;function solution() {&#10;    // Votre code ici&#10;}"
                      style="width: 100%; min-height: 200px; background: transparent; border: none; color: #e5e5e5; font-family: 'Courier New', Consolas, monospace; font-size: 0.9rem; line-height: 1.6; resize: vertical; outline: none; padding: 0;"></textarea>
        </div>
    </div>
    
    <!-- Solution (cachée par défaut) -->
    <div id="solutionSection" style="display: none; margin-bottom: var(--spacing-xl);">
        <h4 style="margin-bottom: var(--spacing-md); color: #16a34a; display: flex; align-items: center; gap: 0.5rem;">
            ✅ Solution proposée
            <span style="font-size: 0.8rem; background: rgba(34, 197, 94, 0.15); color: #16a34a; padding: 0.4rem 0.8rem; border-radius: var(--radius-sm); border: 1px solid rgba(34, 197, 94, 0.3);">
                ⚠️ Attention: Essayez d'abord par vous-même !
            </span>
        </h4>
        
        <div style="background: #0f1419; color: #e6e6e6; padding: var(--spacing-lg); border-radius: var(--radius-md); font-family: 'Courier New', Consolas, monospace; border: 2px solid #16a34a; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-sm); border-bottom: 1px solid #404040;">
                <span style="color: #22c55e; font-size: 0.9rem; font-weight: 600;">💡 Solution recommandée</span>
                <button onclick="copySolution()" class="btn" style="background: #16a34a; color: white;">
                    📋 Copier la solution
                </button>
            </div>
            
            <pre style="margin: 0; white-space: pre-wrap; line-height: 1.6; color: #e6e6e6; font-family: 'Courier New', Consolas, monospace; font-size: 0.9rem;"><code><?= htmlspecialchars($exercice['solution'] ?? 'Solution non disponible') ?></code></pre>
        </div>
    </div>
    
    <!-- Actions -->
    <div style="display: flex; gap: var(--spacing-md); justify-content: center; padding-top: var(--spacing-lg); border-top: 1px solid #e5e7eb; flex-wrap: wrap;">
        <button onclick="resetCode()" class="btn btn-secondary">
            🔄 Réinitialiser
        </button>
        <button onclick="validateSolution()" class="btn btn-primary">
            ✅ Valider ma solution
        </button>
        <button onclick="closeModal('exerciceModal')" class="btn" style="background: #16a34a; color: white;">
            🎯 Exercice terminé
        </button>
    </div>
</div>