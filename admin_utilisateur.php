<?php
require 'config.php';

// Vérifier l'authentification admin
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: admin');
    exit;
}

$success = '';
$error = '';

// Récupérer les données utilisateur
try {
    $user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
    if (!$user) {
        // Créer un utilisateur par défaut si aucun n'existe
        $pdo->exec("INSERT INTO utilisateur_principal (nom, email, telephone, github, linkedin) VALUES ('Cédric Goujon', 'cedric.adam.goujon@gmail.com', '06.51.77.97.80', 'https://github.com/HrexD', 'https://www.linkedin.com/in/cédric-goujon-884522b6/')");
        $user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
    }
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
    $user = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $nom = sanitize_input($_POST['nom'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $telephone = sanitize_input($_POST['telephone'] ?? '');
            $adresse = sanitize_input($_POST['adresse'] ?? '');
            $github = sanitize_input($_POST['github'] ?? '');
            $linkedin = sanitize_input($_POST['linkedin'] ?? '');
            $bio = sanitize_input($_POST['bio'] ?? '');
            $competences = sanitize_input($_POST['competences'] ?? '');
            
            if ($nom && $email) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE utilisateur_principal 
                        SET nom = ?, email = ?, telephone = ?, adresse = ?, github = ?, linkedin = ?, bio = ?, competences = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$nom, $email, $telephone, $adresse, $github, $linkedin, $bio, $competences, $user['id']]);
                    $success = "Profil mis à jour avec succès !";
                    
                    // Recharger les données
                    $user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();
                } catch (PDOException $e) {
                    $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                }
            } else {
                $error = "Le nom et l'email sont obligatoires.";
            }
            break;
            
        case 'update_password':
            $old_password = $_POST['old_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Configuration admin actuelle (vous devrez adapter selon votre système)
            $admin_password = 'admin123'; // Récupérer depuis votre configuration
            
            if ($old_password === $admin_password) {
                if ($new_password === $confirm_password && strlen($new_password) >= 6) {
                    // Ici vous devriez mettre à jour le mot de passe dans votre fichier de config
                    // Pour cet exemple, on simule juste le succès
                    $success = "Mot de passe mis à jour ! N'oubliez pas de mettre à jour admin.php";
                } else {
                    $error = "Les mots de passe ne correspondent pas ou sont trop courts (min 6 caractères).";
                }
            } else {
                $error = "Ancien mot de passe incorrect.";
            }
            break;
    }
}

// Statistiques du profil
try {
    $profile_stats = [
        'messages_reçus' => $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn(),
        'candidatures' => $pdo->query("SELECT COUNT(*) FROM candidatures")->fetchColumn(),
        'projets' => $pdo->query("SELECT COUNT(*) FROM projets")->fetchColumn(),
        'derniere_connexion' => $_SESSION['login_time'] ?? time()
    ];
} catch (PDOException $e) {
    $profile_stats = ['messages_reçus' => 0, 'candidatures' => 0, 'projets' => 0, 'derniere_connexion' => time()];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>👤 Administration - Profil</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">
    <button id="theme-toggle" aria-label="Basculer thème">☀️</button>
    
    <div class="message-header">
        <div>
            <h1>👤 Profil Administrateur</h1>
            <p style="color: var(--text-muted);">Gestion des informations personnelles et sécurité</p>
        </div>
        <div>
            <a href="admin" class="btn-small" style="background: var(--text-muted); color: white;">
                ← Tableau de bord
            </a>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Statistiques du profil -->
    <div class="stats-profile">
        <div class="stat-card">
            <span class="stat-number"><?= $profile_stats['messages_reçus'] ?></span>
            <div class="stat-label">Messages reçus</div>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $profile_stats['candidatures'] ?></span>
            <div class="stat-label">Candidatures</div>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= $profile_stats['projets'] ?></span>
            <div class="stat-label">Projets</div>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?= date('H:i', $profile_stats['derniere_connexion']) ?></span>
            <div class="stat-label">Dernière connexion</div>
        </div>
    </div>
    
    <!-- Informations principales -->
    <div class="profile-section">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['nom'] ?? 'CG', 0, 2)) ?>
            </div>
            <div>
                <h2 style="margin: 0;"><?= htmlspecialchars($user['nom'] ?? 'Nom non défini') ?></h2>
                <p style="color: var(--text-muted); margin: 0.5rem 0;">
                    📧 <?= htmlspecialchars($user['email'] ?? 'Email non défini') ?>
                </p>
                <p style="color: var(--text-muted); margin: 0;">
                    📞 <?= htmlspecialchars($user['telephone'] ?? 'Téléphone non défini') ?>
                </p>
                <div style="margin-top: 1rem;">
                    <?php if ($user['github'] ?? ''): ?>
                        <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" 
                           class="btn-small" style="background: #333; margin-right: 0.5rem;">
                            📂 GitHub
                        </a>
                    <?php endif; ?>
                    <?php if ($user['linkedin'] ?? ''): ?>
                        <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" 
                           class="btn-small" style="background: #0077b5;">
                            💼 LinkedIn
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">📝 Modifier les informations</h3>
        
        <form method="POST" class="profile-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label>👤 Nom complet *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>📧 Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>📞 Téléphone</label>
                <input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>📍 Adresse</label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>📂 GitHub</label>
                <input type="url" name="github" value="<?= htmlspecialchars($user['github'] ?? '') ?>" 
                       placeholder="https://github.com/username">
            </div>
            
            <div class="form-group">
                <label>💼 LinkedIn</label>
                <input type="url" name="linkedin" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" 
                       placeholder="https://linkedin.com/in/username">
            </div>
            
            <div class="form-group full-width">
                <label>📝 Biographie</label>
                <textarea name="bio" rows="4" placeholder="Présentez-vous en quelques lignes..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group full-width">
                <label>🛠️ Compétences principales</label>
                <textarea name="competences" rows="3" placeholder="PHP, JavaScript, React, etc. (séparées par des virgules)"><?= htmlspecialchars($user['competences'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group full-width" style="text-align: right;">
                <button type="submit" class="cta">✅ Mettre à jour le profil</button>
            </div>
        </form>
    </div>
    
    <!-- Sécurité -->
    <div class="profile-section">
        <div class="security-section">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                🔒 Sécurité
                <span style="font-size: 0.8em; background: rgba(255,255,255,0.2); padding: 0.3rem 0.8rem; border-radius: 15px;">
                    Zone sensible
                </span>
            </h3>
            
            <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <input type="hidden" name="action" value="update_password">
                
                <div class="form-group">
                    <label style="color: white;">🔑 Ancien mot de passe</label>
                    <input type="password" name="old_password" required 
                           style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white;">
                </div>
                
                <div class="form-group">
                    <label style="color: white;">🆕 Nouveau mot de passe</label>
                    <input type="password" name="new_password" required minlength="6"
                           style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white;">
                </div>
                
                <div class="form-group">
                    <label style="color: white;">🔄 Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white;">
                </div>
                
                <div class="form-group" style="display: flex; align-items: end;">
                    <button type="submit" class="btn-small" style="background: white; color: #dc3545; font-weight: bold;">
                        🔐 Changer le mot de passe
                    </button>
                </div>
            </form>
            
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.2); font-size: 0.9em; opacity: 0.8;">
                ⚠️ <strong>Important :</strong> Après changement du mot de passe, pensez à mettre à jour le fichier <code>admin.php</code> 
                avec le nouveau mot de passe.
            </div>
        </div>
    </div>
    
    <!-- Informations système -->
    <div class="profile-section">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">⚙️ Informations système</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <div>
                <strong>🖥️ Serveur web :</strong><br>
                <span style="color: var(--text-muted);"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Non détecté' ?></span>
            </div>
            <div>
                <strong>🐘 Version PHP :</strong><br>
                <span style="color: var(--text-muted);"><?= PHP_VERSION ?></span>
            </div>
            <div>
                <strong>🗄️ Version MySQL :</strong><br>
                <span style="color: var(--text-muted);">
                    <?php
                    try {
                        echo $pdo->query("SELECT VERSION()")->fetchColumn();
                    } catch (Exception $e) {
                        echo "Non disponible";
                    }
                    ?>
                </span>
            </div>
            <div>
                <strong>🌐 Nom de domaine :</strong><br>
                <span style="color: var(--text-muted);"><?= $_SERVER['HTTP_HOST'] ?? 'Non détecté' ?></span>
            </div>
            <div>
                <strong>📅 Session démarrée :</strong><br>
                <span style="color: var(--text-muted);"><?= date('d/m/Y H:i', $_SESSION['login_time'] ?? time()) ?></span>
            </div>
            <div>
                <strong>💾 Espace libre :</strong><br>
                <span style="color: var(--text-muted);">
                    <?php
                    if (function_exists('disk_free_space')) {
                        $bytes = disk_free_space('.');
                        $gb = round($bytes / 1024 / 1024 / 1024, 2);
                        echo $gb . ' GB';
                    } else {
                        echo 'Non disponible';
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
    <script src="admin.js"></script>
</body>
</html>