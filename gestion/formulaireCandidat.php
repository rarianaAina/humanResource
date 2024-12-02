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

            <div class="form-group">
                <button type="button" id="btn-traiter">Traiter</button>
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
    <script>
        document.getElementById('btn-traiter').addEventListener('click', function() {
            // Récupérer le fichier CV
            var fileInput = document.getElementById('cv');
            var file = fileInput.files[0];

            if (!file) {
                alert('Veuillez sélectionner un fichier CV.');
                return;
            }

            // Créer un FormData pour envoyer le fichier
            var formData = new FormData();
            formData.append('cv', file);

            // Envoi du fichier via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'pdftotext.php', true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = xhr.responseText;

                    // Chercher les mots-clés dans la réponse
                    var keywords = '';
                    var regex = /<h3>([^<]+)<\/h3>.*?<ul>(.*?)<\/ul>/g;
                    var match;

                    while ((match = regex.exec(response)) !== null) {
                        var category = match[1];
                        var words = match[2].replace(/<li>/g, '').replace(/<\/li>/g, '').replace(/<\/ul>/g, '').replace(/<\/h3>/g, '').trim();
                        keywords += words + ''; // Utiliser un point-virgule pour séparer les mots-clés
                    }

                    // Remplir le champ mots_cles
                    if (keywords) {
                        document.getElementById('mots_cles').value = keywords.slice(0, -2); // Supprimer le dernier point-virgule
                    } else {
                        alert('Aucun mot-clé trouvé dans le CV.');
                    }
                } else {
                    alert('Erreur lors du traitement du fichier.');
                }
            };

            xhr.send(formData);
        });
    </script>


</body>

<div>
    <br>
</div>
<footer class="footer bg-light text-center py-3">
    <p>© 2024 IT-Corporation. Tous droits réservés.</p>
</footer>

</html>