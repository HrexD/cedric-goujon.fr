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
            $email = filter_var(trim($_POST['email_pro'] ?? ''), FILTER_VALIDATE_EMAIL);
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
    <link rel="stylesheet" href="admin-modern.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="admin-page">

    <div class="admin-layout">
        <!-- Hamburger Menu Button (Mobile) -->
        <button class="admin-hamburger" id="adminHamburger" aria-label="Toggle Menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
        
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="user-info">
                <strong>👤 Admin</strong>
                <div style="font-size: 0.8em; opacity: 0.8; margin-top: 0.5rem;">
                    Interface d'administration
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                <li><a href="admin">📊 Tableau de bord</a></li>
                <li><a href="admin_candidatures.php">💼 Candidatures</a></li>
                <li><a href="admin_messages.php">📧 Messages</a></li>
                <li><a href="admin_projets.php">🚀 Projets</a></li>
                <li><a href="admin_gallery.php">🖼️ Galerie</a></li>
                <li><a href="admin_utilisateur.php" class="active">👤 Utilisateur</a></li>
                <li><a href="admin_systeme.php">⚙️ Système</a></li>
                <li style="margin-top: var(--spacing-xl); border-top: 1px solid var(--border-color); padding-top: var(--spacing-lg);">
                    <a href="index">🌐 Voir le site</a>
                </li>
                <li><a href="?logout=1" style="color: var(--danger-color);">🚪 Déconnexion</a></li>
            </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>👤 Profil Administrateur</h1>
                <p class="admin-subtitle">Gestion des informations personnelles et sécurité</p>
            </div>
            
            <!-- Messages -->
            <?php if ($success): ?>
                <div class="notification notification-success">
                    <span class="notification-icon">✅</span>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="notification notification-error">
                    <span class="notification-icon">❌</span>
                    <?= htmlspecialchars($error) ?>
                </div>
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
            <!-- <div class="profile-avatar">
                <?= strtoupper(substr($user['nom'] ?? 'CG', 0, 2)) ?>
            </div> -->
            <div>
                <h2 style="margin: 0;"><?= htmlspecialchars($user['nom'] ?? 'Nom non défini') ?></h2>
                <p style="color: var(--text-muted); margin: 0.5rem 0;">
                    📧 <?= htmlspecialchars($user['email_pro'] ?? 'Email non défini') ?>
                </p>
                <p style="color: var(--text-muted); margin: 0;">
                    📞 <?= htmlspecialchars($user['telephone'] ?? 'Téléphone non défini') ?>
                </p>
                <div style="margin-top: 1rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <?php if ($user['github'] ?? ''): ?>
                        <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" 
                           class="btn btn-secondary" style="background: linear-gradient(135deg, #24292e, #586069); color: white; border: none; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; padding: 0.6rem 1rem;">
                            <span style="font-size: 1.1em;">�</span>
                            GitHub
                        </a>
                    <?php endif; ?>
                    <?php if ($user['linkedin'] ?? ''): ?>
                        <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" 
                           class="btn btn-secondary" style="background: linear-gradient(135deg, #0077b5, #005885); color: white; border: none; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; padding: 0.6rem 1rem;">
                            <span style="font-size: 1.1em;">💼</span>
                            LinkedIn
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">📝 Modifier les informations</h3>
        
        <form method="POST" class="modal-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="nom" class="form-label">
                        <span class="label-icon">👤</span>
                        Nom complet *
                    </label>
                    <input type="text" 
                           id="nom"
                           name="nom" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['nom'] ?? '') ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <span class="label-icon">📧</span>
                        Email *
                    </label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['email_pro'] ?? '') ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="telephone" class="form-label">
                        <span class="label-icon">📞</span>
                        Téléphone
                    </label>
                    <input type="tel" 
                           id="telephone"
                           name="telephone" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="adresse" class="form-label">
                        <span class="label-icon">📍</span>
                        Adresse
                    </label>
                    <input type="text" 
                           id="adresse"
                           name="adresse" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['adresse'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="github" class="form-label">
                        <span class="label-icon">📂</span>
                        GitHub
                    </label>
                    <input type="url" 
                           id="github"
                           name="github" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['github'] ?? '') ?>" 
                           placeholder="https://github.com/username">
                    <small class="form-help">Lien vers votre profil GitHub</small>
                </div>
                
                <div class="form-group">
                    <label for="linkedin" class="form-label">
                        <span class="label-icon">💼</span>
                        LinkedIn
                    </label>
                    <input type="url" 
                           id="linkedin"
                           name="linkedin" 
                           class="form-input"
                           value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" 
                           placeholder="https://linkedin.com/in/username">
                    <small class="form-help">Lien vers votre profil LinkedIn</small>
                </div>
                
                <div class="form-group form-group-full">
                    <label for="bio" class="form-label">
                        <span class="label-icon">📝</span>
                        Biographie
                    </label>
                    <textarea id="bio"
                              name="bio" 
                              class="form-textarea"
                              rows="4" 
                              placeholder="Présentez-vous en quelques lignes..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    <small class="form-help">Description qui apparaîtra sur votre portfolio</small>
                </div>
                
                <div class="form-group form-group-full">
                    <label for="competences" class="form-label">
                        <span class="label-icon">🛠️</span>
                        Compétences principales
                    </label>
                    <textarea id="competences"
                              name="competences" 
                              class="form-textarea"
                              rows="3" 
                              placeholder="PHP, JavaScript, React, etc. (séparées par des virgules)"><?= htmlspecialchars($user['competences'] ?? '') ?></textarea>
                    <small class="form-help">Listez vos compétences principales séparées par des virgules</small>
                </div>
            </div>
            
            <div class="form-actions" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <button type="submit" class="btn btn-primary">
                    <span class="btn-icon">✅</span>
                    Mettre à jour le profil
                </button>
            </div>
        </form>
    </div>
    
    <!-- Sécurité -->
    <div class="profile-section">
        <div class="security-section">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--radius-lg); padding: var(--spacing-xl); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                
                <h3 style="margin: 0 0 1.5rem 0; display: flex; align-items: center; gap: 0.5rem; color: white; font-size: var(--font-size-xl);">
                    �️ Sécurité du compte
                    <span style="font-size: 0.7em; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); padding: 0.3rem 0.8rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.3);">
                        Accès administrateur
                    </span>
                </h3>
                
                <form method="POST" class="modal-form" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: var(--radius-md); padding: var(--spacing-xl); box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <input type="hidden" name="action" value="update_password">
                    
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <div class="form-group">
                            <label for="old_password" class="form-label">
                                <span class="label-icon">�️</span>
                                Mot de passe actuel
                            </label>
                            <input type="password" 
                                   id="old_password"
                                   name="old_password" 
                                   class="form-input"
                                   required
                                   placeholder="Saisissez votre mot de passe actuel"
                                   style="margin-top: 0.5rem;">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                <span class="label-icon">🔐</span>
                                Nouveau mot de passe
                            </label>
                            <input type="password" 
                                   id="new_password"
                                   name="new_password" 
                                   class="form-input"
                                   required 
                                   minlength="6"
                                   placeholder="Minimum 6 caractères"
                                   style="margin-top: 0.5rem;">
                            <small class="form-help" style="margin-top: 0.5rem; display: block;">Utilisez un mot de passe fort et unique</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <span class="label-icon">✅</span>
                                Confirmer le nouveau mot de passe
                            </label>
                            <input type="password" 
                                   id="confirm_password"
                                   name="confirm_password" 
                                   class="form-input"
                                   required 
                                   minlength="6"
                                   placeholder="Répétez le nouveau mot de passe"
                                   style="margin-top: 0.5rem;">
                        </div>
                    </div>
                    
                    <div class="form-actions" style="margin-top: 2.5rem;">
                        <button type="submit" class="btn" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; border: none; font-weight: 600; width: 100%; padding: 1rem;">
                            <span class="btn-icon">🔄</span>
                            Mettre à jour le mot de passe
                        </button>
                    </div>
                </form>
                
                <div style="margin-top: 1.5rem; padding: var(--spacing-md); background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: var(--radius-md); color: white; font-size: 0.9em; border: 1px solid rgba(255,255,255,0.2);">
                    💡 <strong>Conseil :</strong> Après modification, n'oubliez pas de mettre à jour le fichier 
                    <code style="background: rgba(0,0,0,0.2); padding: 0.2rem 0.5rem; border-radius: 4px; color: #ffd700;">admin.php</code> 
                    avec votre nouveau mot de passe.
                </div>
                
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
    
        </main>
    </div>

    <script src="admin-modern.js"></script>
    <script>
        // Validation pour le formulaire utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            // Validation URL en temps réel
            const urlFields = ['github', 'linkedin'];
            urlFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.addEventListener('blur', function() {
                        const value = this.value.trim();
                        const formGroup = this.closest('.form-group');
                        
                        if (value && !isValidUrl(value)) {
                            formGroup.classList.add('error');
                            addFieldError(formGroup, 'URL invalide');
                        } else {
                            formGroup.classList.remove('error');
                            removeFieldError(formGroup);
                        }
                    });
                }
            });
            
            // Validation confirmation mot de passe
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword && confirmPassword) {
                confirmPassword.addEventListener('input', function() {
                    const formGroup = this.closest('.form-group');
                    
                    if (this.value !== newPassword.value) {
                        formGroup.classList.add('error');
                        addFieldError(formGroup, 'Les mots de passe ne correspondent pas');
                    } else {
                        formGroup.classList.remove('error');
                        removeFieldError(formGroup);
                    }
                });
            }
            
            // Validation force du mot de passe
            if (newPassword) {
                newPassword.addEventListener('input', function() {
                    const formGroup = this.closest('.form-group');
                    const value = this.value;
                    
                    if (value.length < 6) {
                        formGroup.classList.add('error');
                        addFieldError(formGroup, 'Le mot de passe doit contenir au moins 6 caractères');
                    } else {
                        formGroup.classList.add('success');
                        removeFieldError(formGroup);
                    }
                });
            }
        });
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        function addFieldError(formGroup, message) {
            removeFieldError(formGroup);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-error';
            errorDiv.textContent = message;
            formGroup.appendChild(errorDiv);
        }
        
        function removeFieldError(formGroup) {
            const errorDiv = formGroup.querySelector('.form-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    </script>
</body>
</html>