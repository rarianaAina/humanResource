<?php
// Récupération du vacancy_id depuis l'URL
$vacancy_id = isset($_GET['vacancy_id']) ? (int)$_GET['vacancy_id'] : null;
$vacancy_name = isset($_GET['vacancy_name']) ? (string)$_GET['vacancy_name'] : null;
?>
!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Candidature</title>
    <link rel="stylesheet" href="style-formulaire.css">
</head>

<body>

    <div class="container">
        <h1>Formulaire de Candidature</h1>

        <!-- Formulaire de candidature -->
        <form action="traitementCandidat.php" method="POST" enctype="multipart/form-data">
            <!-- Prénom -->
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>

            <!-- Deuxième Prénom -->
            <div class="form-group">
                <label for="deuxieme_prenom">Deuxième Prénom</label>
                <input type="text" id="deuxieme_prenom" name="deuxieme_prenom">
            </div>

            <!-- Nom -->
            <div class="form-group">
                <label for="nom">Nom de famille</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <!-- Contact -->
            <div class="form-group">
                <label for="contact">Contact</label>
                <input type="tel" id="contact" name="contact" required>
            </div>

            <!-- CV (fichier PDF) -->
            <div class="form-group">
                <label for="cv">CV (seul fichier PDF, moins de 1 Mo)</label>
                <input type="file" id="cv" name="cv" accept=".pdf" required>
            </div>

            <!-- Mots-clés -->
            <div class="form-group">
                <label for="mots_cles">Mots-clés</label>
                <input type="text" id="mots_cles" name="mots_cles" required>
            </div>

            <!-- Commentaires -->
            <div class="form-group">
                <label for="commentaires">Commentaires</label>
                <textarea id="commentaires" name="commentaires"></textarea>
            </div>

            <!-- Boutons -->
            <div class="button-container">
                <button type="submit" class="btn">Soumettre</button>
                <a href="offres.php" class="btn btn-back">Retour</a>
            </div>
            <!-- Champ caché pour envoyer vacancy_id -->
            <input type="hidden" name="vacancy_id" value="<?php echo $vacancy_id; ?>">
            <input type="hidden" name="vacancy_name" value="<?php echo $vacancy_name; ?>">

        </form>
    </div>
    <script>
        // Récupérer le vacancy_id passé dans l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var vacancyId = urlParams.get('vacancy_id');
        var vacancyName = urlParams.get('vacancy_name');

        // Vérifier si vacancy_id existe et l'afficher dans la console
        if (vacancyId && vacancyName) {
            console.log("ID de vacance : " + vacancyId);
            console.log("Nom du poste: " + vacancyName)
        } else {
            console.log("Aucun ID de vacance trouvé dans l'URL.");
        }
    </script>
</body>

<div>
    <br>
</div>
<footer class="footer bg-light text-center py-3">
    <p>© 2024 IT-Corporation. Tous droits réservés.</p>
</footer>

</html>