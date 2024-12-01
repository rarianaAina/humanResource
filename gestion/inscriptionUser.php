<?php
// Connexion à la base de données
$servername = "localhost";
$username = "rariana";  // Remplacez par votre nom d'utilisateur
$password = "rariana";  // Remplacez par votre mot de passe
$dbname = "orangehrm";  // Remplacez par le nom de votre base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des valeurs soumises par le formulaire
    $email = $_POST['email'];
    $password = $_POST['password']; // Hachage du mot de passe pour plus de sécurité
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];

    // Préparation de la requête d'insertion
    $sql = "INSERT INTO utilisateurs (email, password, middle_name, last_name) VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $email, $password, $middle_name, $last_name);
        if ($stmt->execute()) {
            echo "Inscription réussie !";
        } else {
            echo "Erreur lors de l'inscription : " . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(80deg, #d0d9d3, #a4d4cf);
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

        }

        .form-container h2 {
            text-align: center;
            color: #333;
        }

        .form-container label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .form-container input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-container button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-container button:hover {
            background-color: #45a049;
        }

        .form-container p {
            text-align: center;
            color: #555;
        }

        .form-container p a {
            color: #4CAF50;
            text-decoration: none;
        }

        .form-container p a:hover {
            text-decoration: underline;
        }


        /* Footer */
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            color: #6c757d;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 20px 0;
        }

        .logo {
            width: 200px;
            /* Ajuster la taille du logo selon besoin */
            height: auto;
            border-radius: 20px;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="logo2.png" alt="Logo" class="logo">
    </div>
    <div class="form-container">
        <h2>Inscription</h2>
        <form method="POST" action="inscriptionUser.php">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>

            <label for="middle_name">Prénom(s) :</label>
            <input type="text" id="middle_name" name="middle_name">

            <label for="last_name">Nom de famille :</label>
            <input type="text" id="last_name" name="last_name" required>

            <button type="submit">S'inscrire</button>
        </form>

        <p>Vous avez déjà un compte ? <a href="index.php">Se connecter</a></p>
    </div>
    <footer class="footer bg-light text-center py-3">
        <p>© 2024 IT-Corporation. Tous droits réservés.</p>
    </footer>
</body>

</html>