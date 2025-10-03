<?php
require 'config.php';

$success = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    if (!$nom || !$email || !$message) {
        $error = "Merci de remplir tous les champs correctement.";
    } else {
        // Destinataire
        $to = $user['email'];
        $subject = "Message depuis le formulaire de contact de votre site";
        $body = "Nom: $nom\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email";

        if (mail($to, $subject, $body, $headers)) {
            $success = "Merci ! Votre message a été envoyé.";
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
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
</head>
<body>
  <!-- Navigation -->
  <nav>
    <a href="index">Accueil</a>
    <a href="cv">Mon CV</a>
    <a href="projets">Mes Projets</a>
    <a href="contact" id="active">Contact</a>
  </nav>

  <main>
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
      <form action="contact.php" method="POST" class="contact-form">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="sujet" placeholder="Sujet" required>
        <textarea name="message" rows="6" placeholder="Votre message" required></textarea>
        <button type="submit" class="cta">Envoyer</button>
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
</body>
</html>
