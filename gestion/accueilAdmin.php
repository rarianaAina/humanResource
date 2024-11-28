<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si la session n'est pas active
    header("Location: index.php");
    exit();
}

echo "Bienvenue, utilisateur ID : " . $_SESSION['user_id'] . "<br>";
echo "Email : " . $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plateforme de Recrutement</title>
    <link rel="stylesheet" href="style-accueil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>


<body>
    <div class="logout-container">
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-button">Se déconnecter</button>
        </form>
    </div>
    <div class="logo-container">
        <img src="logo2.png" alt="Logo" class="logo">
    </div>

    <div class="container">

        <div class="left-section">
            <h1>Bienvenue à vous </h1>
            <p> Choisissez :</p>
            <div class="button-container">

                <form action="http://localhost:8084/gestionrh/" method="GET">
                    <div class="button-with-icon">

                        <button type="submit"><i class="fa fa-envelope"></i>Gestion Administrative</button>
                    </div>
                </form>
                <form action="http://localhost:8084/gestionrh/fiche_de_paie/employee.php" method="GET">
                    <div class="button-with-icon">

                        <button type="submit"><i class="fa fa-envelope"></i>Gestion Paie</button>
                    </div>
                </form>

                <form action="dashboardAdmin.php" method="GET">
                    <div class="button-with-icon">

                        <button type="submit"><i class="fa fa-envelope"></i>Resultats compatibilité</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Partie droite avec les informations dynamiques -->
        <div class="right-section">
            <!-- Texte par défaut à afficher lorsqu'aucun bouton n'est survolé -->
            <div id="defaultInfo" class="info-box">
                <p> * ETU000739 - ETU002076 - ETU002378 - ETU001015 - ETU000925 *</p>
            </div>
            <div id="societeInfo" class="info-box">
                <img src="logo.png" alt="Logo-logo" class="logo">
                <p>Copyright 2024</p>
            </div>

            <div id="infoPostuler" class="info-box" style="display: none;">
                <p>En appuyant sur ce bouton, vous allez pouvoir postuler pour une offre. </p>
            </div>
            <div id="infoGestionPaie" class="info-box" style="display: none;">
                <p>En appuyant sur ce bouton, vous allez pouvoir postuler pour une offre. </p>
            </div>
            <div id="infoDashboard" class="info-box" style="display: none;">
                <p>En appuyant sur ce bouton, vous allez pouvoir postuler pour une offre. </p>
            </div>
            <div id="infoChatbot" class="info-box" style="display: none;">
                <p>Discutez avec notre chatbot pour</p><br>
                <p>- Obtenir des informations sur la société.</p>
                <p>- Savoir les étapes de notre recrutement</p>
                <p>- Savoir quels sont les postes disponibles</p>
            </div>
            <div id="infoTransformation" class="info-box" style="display: none;">
                <p>Avec ce bouton :</p><br>
                <p>- Vous pourrez transformer votre CV sous format texte</p>
                <p>- Le fichier texte sera copié dans le formulaire lorsque vous postulerez</p>
                <p>- Cela aidera directement nos services recrutement pour vous choisir </p>
            </div>
            <div id="infoTestTechnique" class="info-box" style="display: none;">
                <p>Test technique</p>
            </div>
            <div id="infoNotifications" class="info-box" style="display: none;">
                <p>Vous pouvez voir ici les notifications pour les offres sur lesquelles vous avez postulé</p>
            </div>
            <div id="infoCompatibilite" class="info-box" style="display: none;">
                <p>Vous aurez ici un test de compatibilité avec notre société qui pourrait vous aider aussi à avoir un avantage par rapport
                    aux autres candidats. <br> Nous vous invitons donc à bien remplir les tests.
                </p>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>