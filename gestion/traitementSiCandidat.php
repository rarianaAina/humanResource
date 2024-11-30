<?php
// Détails de connexion à la base de données MySQL
$host = "localhost";
$dbname = "orangehrm";
$user = "rariana";
$password = "rariana";

// Connexion à MySQL
$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Erreur de connexion à la base de données : " . mysqli_connect_error());
}

// Démarrer la session seulement si l'admin n'est pas coché
session_start();

// Récupérer les données du formulaire
$email = mysqli_real_escape_string($conn, $_POST['email']);
$mdp = mysqli_real_escape_string($conn, $_POST['mdp']);
$is_admin = isset($_POST['is_admin']) ? true : false; // Vérifier si la case Admin est cochée

// Déterminer la table à utiliser en fonction de l'option "Admin"
$table = $is_admin ? 'ohrm_job_candidate' : 'ohrm_job_candidate'; // Table des utilisateurs ou des candidats

// Si Admin est coché, il n'y a pas de session à initialiser
if ($is_admin) {
    // Requête pour vérifier l'admin
    $query = "SELECT * FROM $table WHERE email = '$email' AND PASSWORD = '$mdp'";
} else {
    // Si l'utilisateur n'est pas un admin, on vérifie dans la table ohrm_job_candidate
    $query = "SELECT * FROM $table WHERE email = '$email' AND PASSWORD = '$mdp'";
}

// Exécution de la requête
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        // Récupérer les données de l'utilisateur
        $user = mysqli_fetch_assoc($result);

        // Si Admin est coché, ne pas stocker en session, juste rediriger
        
            // Stocker les informations dans la session pour les utilisateurs
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['middle_name'] = $user['middle_name'];
            $_SESSION['last_name'] = $user['last_name'];
        

        // Si admin est coché, rediriger vers la page admin
        $redirectPage = $is_admin ? 'accueilAdmin.php' : 'accueil.php';

        // Formulaire POST caché pour envoyer l'ID de l'utilisateur
        echo "<form id='redirectForm' action='$redirectPage' method='POST'>";
        echo "<input type='hidden' name='user_id' value='" . $_SESSION['user_id'] . "' />";
        echo "<input type='hidden' name='middle_name' value='" . $_SESSION['middle_name'] . "' />";
        echo "<input type='hidden' name='last_name' value='" . $_SESSION['last_name'] . "' />";
        echo "<script>document.getElementById('redirectForm').submit();</script>";
        exit();
    } else {
        // Si échec, rediriger vers la page de connexion avec un message d'erreur
        header("Location: index.php?error=1");
        exit();
    }
} else {
    echo "Erreur dans la requête : " . mysqli_error($conn);
}

// Fermer la connexion
mysqli_close($conn);
?>
