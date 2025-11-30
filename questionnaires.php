<?php
require_once 'config.php';

// Récupérer tous les questionnaires
try {
    $stmt = $pdo->prepare("SELECT * FROM questionnaire ORDER BY id DESC");
    $stmt->execute();
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des questionnaires : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaires - Cédric Goujon</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="questionnaire-styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>Cédric Goujon</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a href="questionnaires.php" class="nav-link active">
                        <i class="fas fa-poll"></i> Questionnaires
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_questionnaires.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i> Créer
                    </a>
                </li>
                <li class="nav-item">
                    <a href="contact.php" class="nav-link">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero questionnaire-hero">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-poll"></i>
                    Questionnaires Interactifs
                </h1>
                <p class="hero-description">
                    Participez à nos questionnaires pour nous aider à mieux vous connaître et améliorer nos services.
                </p>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= count($questionnaires) ?></span>
                    <span class="stat-label">Questionnaires</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Anonyme</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">2-5</span>
                    <span class="stat-label">Minutes</span>
                </div>
            </div>
        </section>

        <!-- Liste des questionnaires -->
        <section class="questionnaires-section">
            <div class="container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($questionnaires)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3>Aucun questionnaire disponible</h3>
                        <p>Il n'y a actuellement aucun questionnaire disponible. Revenez plus tard !</p>
                    </div>
                <?php else: ?>
                    <div class="questionnaires-grid">
                        <?php foreach ($questionnaires as $questionnaire): ?>
                            <?php
                            // Compter les questions
                            try {
                                $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE questionnaire_id = ?");
                                $stmt_count->execute([$questionnaire['id']]);
                                $nb_questions = $stmt_count->fetchColumn();
                            } catch(PDOException $e) {
                                $nb_questions = 0;
                            }
                            ?>
                            <div class="questionnaire-card">
                                <div class="card-header">
                                    <div class="card-icon">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <div class="card-badge">
                                        <?= $nb_questions ?> questions
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h3 class="card-title"><?= htmlspecialchars($questionnaire['titre']) ?></h3>
                                    <p class="card-description">
                                        <?= htmlspecialchars($questionnaire['description']) ?>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <div class="card-meta">
                                        <span class="meta-item">
                                            <i class="fas fa-clock"></i>
                                            <?= ceil($nb_questions * 0.5) ?> min
                                        </span>
                                        <span class="meta-item">
                                            <i class="fas fa-user-secret"></i>
                                            Anonyme
                                        </span>
                                    </div>
                                    <a href="questionnaire_reponse.php?id=<?= $questionnaire['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-play"></i>
                                        Commencer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section informative -->
        <!-- <section class="info-section">
            <div class="container">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Confidentialité</h3>
                        <p>Vos réponses sont totalement anonymes et utilisées uniquement à des fins statistiques.</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Rapidité</h3>
                        <p>Nos questionnaires sont conçus pour être complétés en quelques minutes seulement.</p>
                    </div>
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Utilité</h3>
                        <p>Vos réponses nous aident à améliorer nos services et mieux comprendre vos besoins.</p>
                    </div>
                </div>
            </div>
        </section> -->
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Cédric Goujon</h3>
                    <p>Développeur Web Full Stack</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Cédric Goujon. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script src="questionnaire-script.js"></script>
</body>
</html>