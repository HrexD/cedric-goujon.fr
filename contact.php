<?php
require 'config.php';
require 'auth_helper.php';

// Récupérer les données de l'utilisateur
$user = $pdo->query("SELECT * FROM utilisateur_principal LIMIT 1")->fetch();

$success = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize_input($_POST['nom'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $sujet = sanitize_input($_POST['sujet'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Validation
    if (empty($nom) || !$email || empty($sujet) || empty($message)) {
        $error = "Merci de remplir tous les champs correctement.";
    } else {
        try {
            // Insérer le message en base de données (recommandé plutôt que mail())
            $stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, sujet, message, date_envoi) VALUES (?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$nom, $email, $sujet, $message])) {
                $success = "Merci ! Votre message a été envoyé avec succès.";
                // Optionnel : tentative d'envoi d'email en plus
                $to = $user['email'] ?? 'contact@cedric-goujon.fr';
                $subject = "Contact site web: " . $sujet;
                $body = "Nom: $nom\nEmail: $email\nSujet: $sujet\n\nMessage:\n$message";
                $headers = "From: no-reply@cedric-goujon.fr\r\nReply-To: $email\r\n";
                
                // Tentative d'envoi email (peut échouer selon la config serveur)
                @mail($to, $subject, $body, $headers);
                
                // Vider les champs après succès
                $_POST = [];
            } else {
                $error = "Erreur lors de l'envoi du message. Veuillez réessayer.";
            }
        } catch (PDOException $e) {
            // Si la table n'existe pas, fallback vers email seulement
            $to = $user['email_contact'] ?? 'cedric.adam.goujon@gmail.com';
            $subject = "Contact site web: " . $sujet;
            $body = "Nom: $nom\nEmail: $email\nSujet: $sujet\n\nMessage:\n$message";
            $headers = "From: no-reply@cedric-goujon.fr\r\nReply-To: $email\r\n";
            
            if (@mail($to, $subject, $body, $headers)) {
                $success = "Merci ! Votre message a été envoyé.";
                // Vider les champs après succès
                $_POST = [];
            } else {
                $error = "Une erreur est survenue. Contactez-moi directement par email.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact – Cédric Goujon</title>
  
  <!-- Styles modernes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="cv-modern.css">
  <link rel="stylesheet" href="cv-animations.css">
  <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body class="cv-page">
  <!-- Navigation -->
  <?= generateNavigation('contact') ?>

  <div class="cv-container">
    <!-- Messages de feedback -->
    <?php if ($success): ?>
      <div class="alert alert-success slide-in">
        <i class="fas fa-check-circle"></i>
        <strong>Succès !</strong> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert alert-error slide-in">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Erreur :</strong> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="cv-hero contact-hero fade-in">
      <div class="hero-content">
        <div class="hero-icon">
          <i class="fas fa-envelope"></i>
        </div>
        
        <div class="hero-info">
          <h1>Contactez-moi</h1>
          <p class="hero-subtitle">Discutons de votre projet ensemble</p>
          
          <div class="contact-quick-info">
            <div class="hero-detail">
              <i class="fas fa-envelope"></i>
              <a href="mailto:<?= htmlspecialchars($user['email_contact']) ?>"><?= htmlspecialchars($user['email_contact']) ?></a>
            </div>
            <div class="hero-detail">
              <i class="fas fa-clock"></i>
              <span>Réponse sous 24h</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <main class="cv-main single-column">
      <section class="cv-section slide-up">
        <div class="contact-container">
          <!-- Colonne gauche : informations -->
          <div class="contact-info">
            <div class="section-header">
              <h2 class="section-title">
                <i class="fas fa-info-circle section-icon"></i>
                Informations de contact
              </h2>
            </div>
            
            <div class="contact-methods">
              <div class="contact-method">
                <i class="fas fa-envelope"></i>
                <div>
                  <h4>Email</h4>
                  <a href="mailto:<?= htmlspecialchars($user['email_contact']) ?>"><?= htmlspecialchars($user['email_contact']) ?></a>
                </div>
              </div>
              
              <div class="contact-method">
                <i class="fab fa-github"></i>
                <div>
                  <h4>GitHub</h4>
                  <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" rel="noopener">Voir mon profil</a>
                </div>
              </div>
              
              <div class="contact-method">
                <i class="fab fa-linkedin"></i>
                <div>
                  <h4>LinkedIn</h4>
                  <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" rel="noopener">Me contacter</a>
                </div>
              </div>
            </div>
            
            <div class="contact-availability">
              <h4><i class="fas fa-clock"></i> Disponibilité</h4>
              <p>Je réponds généralement sous 24h. N'hésitez pas à me contacter pour discuter de votre projet !</p>
            </div>
          </div>

          <!-- Colonne droite : formulaire -->
          <div class="contact-form-container">
            <div class="section-header">
              <h2 class="section-title">
                <i class="fas fa-paper-plane section-icon"></i>
                Envoyez-moi un message
              </h2>
            </div>
            
            <form action="contact.php" method="POST" class="contact-form modern-form">
              <div class="form-group">
                <label for="nom">Votre nom</label>
                <input type="text" id="nom" name="nom" placeholder="Votre nom complet" 
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label for="email">Votre email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label for="sujet">Sujet</label>
                <input type="text" id="sujet" name="sujet" placeholder="Sujet de votre message" 
                       value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>" required>
              </div>
              
              <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" placeholder="Décrivez votre projet ou votre demande..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>
              
              <button type="submit" class="btn-download primary">
                <i class="fas fa-paper-plane"></i>
                <span>Envoyer le message</span>
              </button>
            </form>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Footer moderne -->
  <footer class="cv-footer">
    <div class="footer-content">
      <span>© <script>document.write(new Date().getFullYear())</script> Cédric Goujon. Tous droits réservés.</span>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="cv-interactions.js"></script>
</body>
</html>
