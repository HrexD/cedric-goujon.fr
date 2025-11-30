<?php
require_once "config.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo '<div class="error-message">ID de cours invalide.</div>';
    exit;
}

$id = (int)$_GET["id"];

try {
    $stmt = $pdo->prepare("SELECT * FROM cours WHERE id = ? AND statut = 'actif'");
    $stmt->execute([$id]);
    $cours = $stmt->fetch();

    if (!$cours) {
        echo '<div class="error-message">Cours introuvable ou non disponible.</div>';
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM cours_sections WHERE cours_id = ? ORDER BY ordre_section ASC");
    $stmt->execute([$id]);
    $sections = $stmt->fetchAll();
    
} catch (PDOException $e) {
    echo '<div class="error-message">Erreur de base de donnees : Impossible de charger le cours.</div>';
    error_log("Erreur cours_detail.php : " . $e->getMessage());
    exit;
}
?>

<!-- Inclusion des styles CSS pour le contenu chargé en AJAX -->
<style>
/* Styles essentiels pour les blocs de code dans la modal */
.cours-section {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
}

/* Styles de base pour les blocs de code */
.cours-section pre {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    font-family: 'Fira Code', 'Monaco', 'Consolas', 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.5;
    margin: 1.5rem 0;
}

.cours-section pre code {
    font-family: 'Fira Code', 'Monaco', 'Consolas', 'Courier New', monospace;
    font-size: 14px;
    color: #d4d4d4;
    background: none;
}

.cours-section code {
    background: #f4f4f4;
    
    font-family: 'Fira Code', 'Monaco', 'Consolas', 'Courier New', monospace;
    font-size: 13px;
}

/* Style pour le contenu highlight */

</style>

<div class="cours-detail">
    <div class="cours-header">
        <div class="cours-title-section">
            <h2><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($cours["titre"]) ?></h2>
            <div class="cours-meta">
                <span class="niveau-badge niveau-<?= $cours["niveau"] ?>">
                    <i class="fas fa-chart-line"></i> <?= ucfirst($cours["niveau"]) ?>
                </span>
                <span class="langage-badge">
                    <i class="fas fa-code"></i> <?= htmlspecialchars($cours["langage"]) ?>
                </span>
                <span class="auteur-badge">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($cours["auteur"]) ?>
                </span>
            </div>
        </div>
        
        <div class="description-section">
            <div class="description-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <p class="description"><?= htmlspecialchars($cours["description"]) ?></p>
        </div>
        
        <?php if (!empty($cours["objectifs"])): ?>
        <div class="objectifs">
            <h3><i class="fas fa-target"></i> Objectifs du cours</h3>
            <ul class="objectifs-list">
                <?php 
                $objectifs = json_decode($cours["objectifs"], true);
                if ($objectifs && is_array($objectifs)) {
                    foreach ($objectifs as $objectif): ?>
                    <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($objectif) ?></li>
                    <?php endforeach;
                } else {
                    echo '<li><i class="fas fa-check-circle"></i> Objectifs en cours de mise a jour</li>';
                }
                ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($cours["prerequis"])): ?>
        <div class="prerequis">
            <h3><i class="fas fa-clipboard-list"></i> Prerequis</h3>
            <ul class="prerequis-list">
                <?php 
                $prerequis = json_decode($cours["prerequis"], true);
                if ($prerequis && is_array($prerequis)) {
                    foreach ($prerequis as $requis): ?>
                    <li><i class="fas fa-bookmark"></i> <?= htmlspecialchars($requis) ?></li>
                    <?php endforeach;
                } else {
                    echo '<li><i class="fas fa-bookmark"></i> Aucun prerequis specifique</li>';
                }
                ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($cours["contenu"])): ?>
    <div class="cours-contenu">
        <h3><i class="fas fa-book-open"></i> Contenu principal</h3>
        <div class="contenu-html cours-section">
            <?= $cours["contenu"] ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($sections)): ?>
    <div class="cours-sections">
        <h3><i class="fas fa-list-ol"></i> Sections detaillees</h3>
        <div class="sections-container">
            <?php foreach ($sections as $index => $section): ?>
                <div class="section-card">
                    <div class="section-header">
                        <h4>
                            <span class="section-number"><?= $index + 1 ?></span>
                            <?= htmlspecialchars($section["titre"]) ?>
                        </h4>
                    </div>
                    
                    <div class="section-content cours-section">
                        <?= $section["contenu"] ?>
                    </div>
                    
                    <?php if (!empty($section["code_exemple"])): ?>
                        <div class="code-example">
                            <div class="code-header">
                                <h5><i class="fas fa-terminal"></i> Exemple de code</h5>
                            </div>
                            <pre><code><?= htmlspecialchars($section["code_exemple"]) ?></code></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="cours-footer">
        <div class="footer-info">
            <p><i class="fas fa-calendar"></i> Cree le : <?= date("d/m/Y", strtotime($cours["date_creation"])) ?></p>
            <p><i class="fas fa-edit"></i> Modifie le : <?= date("d/m/Y", strtotime($cours["date_modification"])) ?></p>
        </div>
    </div>
</div>

<style>
.error-message {
    background: linear-gradient(135deg, #fed7d7, #feb2b2);
    color: #c53030;
    padding: 2rem;
    border-radius: 15px;
    border: 2px solid #fc8181;
    text-align: center;
    font-weight: 600;
    margin: 2rem;
    box-shadow: 0 8px 25px rgba(197, 48, 48, 0.2);
    font-size: 1.1rem;
}

.cours-detail {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.7;
    color: #2d3748;
    max-width: 100%;
    margin: 0 auto;
}

.cours-header {
    margin-bottom: 3rem;
}

.cours-title-section {
    text-align: center;
    margin-bottom: 2rem;
}

.cours-header h2 {
    color: #1a365d;
    margin-bottom: 1.5rem;
    font-size: 2.5rem;
    font-weight: 800;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.cours-header h2 i {
    color: #4299e1;
    margin-right: 1rem;
}

.cours-meta {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.niveau-badge, .langage-badge, .auteur-badge {
    padding: 0.7rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.niveau-badge:hover, .langage-badge:hover, .auteur-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.niveau-badge.niveau-debutant {
    background: linear-gradient(135deg, #48bb78, #38a169);
    color: white;
}

.niveau-badge.niveau-intermediaire {
    background: linear-gradient(135deg, #ed8936, #dd6b20);
    color: white;
}

.niveau-badge.niveau-avance {
    background: linear-gradient(135deg, #e53e3e, #c53030);
    color: white;
}

.langage-badge {
    background: linear-gradient(135deg, #4299e1, #3182ce);
    color: white;
}

.auteur-badge {
    background: linear-gradient(135deg, #9f7aea, #805ad5);
    color: white;
}

.description-section {
    background: linear-gradient(135deg, #f7fafc, #edf2f7);
    padding: 2rem;
    border-radius: 15px;
    border-left: 5px solid #4299e1;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.description-icon {
    color: #4299e1;
    font-size: 1.5rem;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.description {
    font-size: 1.2rem;
    color: #4a5568;
    margin: 0;
    font-weight: 500;
    line-height: 1.8;
}

.objectifs {
    background: linear-gradient(135deg, #f0fff4, #e6fffa);
    padding: 2rem;
    border-radius: 15px;
    border: 2px solid #68d391;
    box-shadow: 0 4px 20px rgba(72, 187, 120, 0.1);
    margin-bottom: 2rem;
}

.objectifs h3 {
    margin: 0 0 1.5rem 0;
    color: #22543d;
    font-weight: 700;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.objectifs-list {
    margin: 0;
    padding-left: 0;
    list-style: none;
    display: grid;
    gap: 0.8rem;
}

.objectifs-list li {
    color: #2f855a;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.5rem 0;
}

.objectifs-list li i {
    color: #48bb78;
    font-size: 1.1rem;
}

.prerequis {
    background: linear-gradient(135deg, #fffaf0, #fef5e7);
    padding: 2rem;
    border-radius: 15px;
    border: 2px solid #f6ad55;
    box-shadow: 0 4px 20px rgba(246, 173, 85, 0.1);
    margin-bottom: 2rem;
}

.prerequis h3 {
    margin: 0 0 1.5rem 0;
    color: #744210;
    font-weight: 700;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.prerequis-list {
    margin: 0;
    padding-left: 0;
    list-style: none;
    display: grid;
    gap: 0.8rem;
}

.prerequis-list li {
    color: #975a16;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.5rem 0;
}

.prerequis-list li i {
    color: #ed8936;
    font-size: 1.1rem;
}

.cours-contenu h3 {
    color: #1a365d;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 3rem 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.contenu-html {
    background: white;
    padding: 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    margin-bottom: 3rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.cours-sections h3 {
    color: #1a365d;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 3rem 0 2rem 0;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

/* .sections-container {
    display: grid;
    gap: 2rem;
} */

.section-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.4s ease;
}

.section-card:hover {
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
    transform: translateY(-5px);
}

.section-header {
    background: linear-gradient(135deg, #4299e1, #3182ce);
    color: white;
    padding: 1.5rem 2rem;
    position: relative;
}

.section-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.section-number {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 50%;
    font-size: 1rem;
    font-weight: 700;
    min-width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.section-content {
    padding: 2rem;
    font-size: 1.05rem;
    line-height: 1.8;
    color: #4a5568;
}

.code-example {
    margin: 2rem;
    margin-bottom: 0;
    border-radius: 10px;
    overflow: hidden;
    background: #1a202c;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.code-header {
    background: #2d3748;
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #4a5568;
}

.code-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #63b3ed;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.code-example pre {
    margin: 0;
    overflow-x: auto;
    padding: 2rem;
    background: #1a202c;
    color: #e2e8f0;
    font-family: 'Fira Code', 'Monaco', 'Courier New', monospace;
    font-size: 0.95rem;
    line-height: 1.6;
}

.code-example code {
    color: inherit;
    background: none;
}

.cours-footer {
    margin-top: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, #f7fafc, #edf2f7);
    border-radius: 15px;
    border-top: 3px solid #4299e1;
}

.footer-info {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.footer-info p {
    margin: 0;
    color: #4a5568;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-info i {
    color: #4299e1;
}

@media (max-width: 768px) {
    .cours-detail {
        padding: 0;
    }
    
    .cours-header h2 {
        font-size: 2rem;
    }
    
    .cours-meta {
        flex-direction: column;
        align-items: center;
        gap: 0.8rem;
    }
    
    .description-section {
        padding: 1.5rem;
        flex-direction: column;
        text-align: center;
    }
    
    .objectifs,
    .prerequis {
        padding: 1.5rem;
    }
    
    .section-content {
        padding: 1.5rem;
    }
    
    .code-example {
        margin: 1rem;
        margin-bottom: 0;
    }
    
    .code-example pre {
        padding: 1.5rem;
        font-size: 0.85rem;
    }
    
    .section-header h4 {
        flex-direction: column;
        gap: 0.8rem;
        text-align: center;
    }
    
    .footer-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
}
</style>