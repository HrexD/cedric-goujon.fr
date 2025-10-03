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
                $to = $user['email'] ?? 'cedric.adam.goujon@gmail.com';
                $subject = "Contact site web: " . $sujet;
                $body = "Nom: $nom\nEmail: $email\nSujet: $sujet\n\nMessage:\n$message";
                $headers = "From: no-reply@cedric-goujon.fr\r\nReply-To: $email\r\n";
                
                // Tentative d'envoi email (peut échouer selon la config serveur)
                @mail($to, $subject, $body, $headers);
            } else {
                $error = "Erreur lors de l'envoi du message. Veuillez réessayer.";
            }
        } catch (PDOException $e) {
            // Si la table n'existe pas, fallback vers email seulement
            $to = $user['email'] ?? 'cedric.adam.goujon@gmail.com';
            $subject = "Contact site web: " . $sujet;
            $body = "Nom: $nom\nEmail: $email\nSujet: $sujet\n\nMessage:\n$message";
            $headers = "From: no-reply@cedric-goujon.fr\r\nReply-To: $email\r\n";
            
            if (@mail($to, $subject, $body, $headers)) {
                $success = "Merci ! Votre message a été envoyé.";
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
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body>
  <!-- Bouton thème -->
  <button id="theme-toggle" aria-label="Basculer thème">☀️</button>

  <!-- Navigation -->
  <?= generateNavigation('contact') ?>

  <main>
    <!-- Messages de feedback -->
    <?php if ($success): ?>
      <div class="alert alert-success">
        <strong>✅ Succès !</strong> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="alert alert-error">
        <strong>❌ Erreur :</strong> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

<section class="contact-section">
  <div class="contact-container">
    <!-- Colonne gauche : texte et coordonnées -->
    <div class="contact-info">
      <h2>Une question ?</h2>
      <p>Remplissez le formulaire ou contactez-moi directement par email. Je vous répondrai dans les plus brefs délais.</p>
      <p>Email : <a href="mailto:<?= htmlspecialchars($user['email']) ?>"><?= htmlspecialchars($user['email']) ?></a></p>
      <p>GitHub : <a href="<?= htmlspecialchars($user['github']) ?>">Mon profil</a></p>
      <p>LinkedIn : <a href="<?= htmlspecialchars($user['linkedin']) ?>">Mon profil</a></p>
    </div>

    <!-- Colonne droite : formulaire -->
    <div class="contact-form-container">
      <form action="contact" method="POST" class="contact-form">
        <input type="text" name="nom" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
        <input type="email" name="email" placeholder="Votre email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <input type="text" name="sujet" placeholder="Sujet de votre message" value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>" required>
        <textarea name="message" rows="6" placeholder="Votre message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        <button type="submit" class="cta">Envoyer le message</button>
      </form>
    </div>
  </div>
</section>


  </main>

  <footer>
    <div class="footer-content">
      <span>© <script>document.write(new Date().getFullYear())</script> Cédric Goujon. Tous droits réservés.</span>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
