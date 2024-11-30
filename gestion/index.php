<?php
session_start();

// Vérifier si une session est déjà active
if (isset($_SESSION['user_id'])) {
    // Rediriger l'utilisateur vers la page d'accueil ou une autre page
    header("Location: accueil.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<!-- Logo en haut de la page -->
<div class="logo-container">
    <img src="logo2.png" alt="Logo" class="logo">
</div>

<!-- Conteneur parent pour aligner les trois containers -->
<div class="container-wrapper">
    <!-- Premier container : Connexion Candidat -->
    <div class="container">
        <h2 class="title">Candidats :</h2>
        <form action="traitementSiCandidat.php" method="post">
            <p>Email :</p>
            <input type="text" name="email" required>
            <p>Mot de passe :</p>
            <input type="password" name="mdp" required>
            <input type="submit" value="Connexion">

            <?php
            if (isset($_GET['error']) && $_GET['error'] == 1) {
                echo "<p class='error-message'>Email ou mot de passe incorrect.</p>";
                
                // Redirection après affichage de l'erreur pour réinitialiser l'URL
                echo "<script>window.history.replaceState({}, document.title, window.location.pathname);</script>";
            }
            ?>
        </form>
    </div>

    <!-- Conteneur central : Description de l'entreprise -->
    <div class="container3">
        <h2 class="title">À propos de IT-Corporation</h2>
        <p>IT-Corporation est une entreprise leader dans le domaine de la technologie, offrant des solutions innovantes pour les entreprises du monde entier. Notre équipe d'experts travaille sans relâche pour offrir des produits de qualité, adaptés aux besoins de chaque client.</p>
    </div>

    <!-- Deuxième container : Connexion Plateforme -->
    <div class="container2">
        <h2 class="title">Recrutements :</h2>
        <form action="traitement.php" method="post">
            <p>Email :</p>
            <input type="text" name="email" required>
            <p>Mot de passe :</p>
            <input type="password" name="mdp" required>
            <input type="submit" value="Connexion">
            <p>
                <label>
                    <input type="checkbox" name="is_admin" value="1"> Admin
                </label>
            </p>
            <?php
            if (isset($_GET['errorr']) && $_GET['errorr'] == 1) {
                echo "<p class='error-message'>Email ou mot de passe incorrect.</p>";
                
                // Redirection après affichage de l'erreur pour réinitialiser l'URL
                echo "<script>window.history.replaceState({}, document.title, window.location.pathname);</script>";
            }
            ?>
        </form>
    </div>
</div>


</body>

</html>
