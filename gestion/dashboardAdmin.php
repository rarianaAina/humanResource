<?php
session_start();

require_once 'vendor/autoload.php';

// Vérifiez si l'utilisateur est un administrateur
// if (!isset($_SESSION['user_id'])) {
//     die("Accès non autorisé. Vous devez être un administrateur.");
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'orangehrm'; // Remplacez par le nom de votre base de données
$username = 'rariana'; // Remplacez par votre nom d'utilisateur MySQL
$password = 'rariana'; // Remplacez par votre mot de passe MySQL

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Définir les scores requis par la société (exemple)
$max_required_score = 2; // Exemple de score requis par la société

// Récupérer les scores des candidats dans la table compatibilite
$query = "SELECT candidat_id, compatibilite, middle_name, last_name, email FROM compatibilite";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Envoi de l'email si le formulaire est soumis
if (isset($_POST['send_email'])) {
    $email = $_POST['email'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];

    // Paramètres d'envoi d'email via SwiftMailer
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
        ->setUsername('rarianamiadana@gmail.com')
        ->setPassword('mgxy pljh fskt zlbk')
        ->setStreamOptions([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

    // Création du mailer
    $mailer = new Swift_Mailer($transport);

    // Création du message
    $message = (new Swift_Message('IT-Corporation'))
        ->setFrom(['rarianamiadana@gmail.com' => 'Informations de connexion'])
        ->setTo([$email => $nom]) // Envoyer au candidat
        ->setBody("Bonjour $prenom $nom,\n\nVotre test de compatibilité avec la société a été une réussite, nous vous invitons donc à postuler sur notre plateforme pour un poste qui vous convient le mieux pour que nous puissions traiter le plus vite possible votre dossier\n\nMerci.");

    // Envoi du message
    if ($mailer->send($message)) {
        echo 'L\'email a été envoyé avec succés.';
    } else {
        echo 'Échec de l\'envoi de l\'email.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>Tableau de compatibilité des candidats</title>

</head>

<body>
    <div class="logout-container">
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-button">Se déconnecter</button>
        </form>
    </div>

    <div class="retour">
        <a href="accueilAdmin.php">Accueil</a>
    </div>

    <h1>Tableau de compatibilité des candidats</h1>

    <table>
        <tr>
            <th>Email du candidat</th>
            <th>Prénom du candidat</th>
            <th>Nom du candidat</th>
            <!-- <th>Score de compatibilité</th> -->
            <th>Pourcentage de compatibilité</th>
            <th>Actions</th>
        </tr>

        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $candidat_id = $row['candidat_id'];
            $compatibility_score = $row['compatibilite'];
            $candidat_middle_name = $row['middle_name'];
            $candidat_last_name = $row['last_name'];
            $candidat_email = $row['email'];

            // Calculer le pourcentage de compatibilité
            $compatibility_percentage = ($compatibility_score / $max_required_score) * 100;

            // Déterminer le style basé sur le pourcentage de compatibilité
            if ($compatibility_percentage >= 75) {
                $compatibility_class = "compatibility-high";
            } elseif ($compatibility_percentage >= 50) {
                $compatibility_class = "compatibility-medium";
            } else {
                $compatibility_class = "compatibility-low";
            }

            // Afficher les résultats dans le tableau
            echo "<tr>";
            echo "<td>$candidat_email</td>";
            echo "<td>$candidat_middle_name</td>";
            echo "<td>$candidat_last_name</td>";
            //echo "<td>$compatibility_score</td>";
            echo "<td class='$compatibility_class'>" . round($compatibility_percentage, 2) . "%</td>";
            echo "<td>
                    <form method='POST'>
                        <input type='hidden' name='email' value='$candidat_email'>
                        <input type='hidden' name='prenom' value='$candidat_middle_name'>
                        <input type='hidden' name='nom' value='$candidat_last_name'>
                        <button type='submit' name='send_email'>Envoyer un email</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>

    </table>
    <div style="float: right; width: 40%; height: 400px;">
        <canvas id="compatibilityChart"></canvas>
    </div>

</body>
<div>
    <br>
</div>
<footer class="footer bg-light text-center py-3">
    <p>© 2024 IT-Corporation. Tous droits réservés.</p>
</footer>

</html>
