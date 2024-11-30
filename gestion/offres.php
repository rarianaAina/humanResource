<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si la session n'est pas active
    header("Location: index.php");
    exit();
}

// Paramètres de connexion
$host = "localhost";
$username = "rariana";
$password = "rariana";
$database = "orangehrm";

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Définir l'encodage de la connexion à UTF-8
$conn->set_charset("utf8");

// Requête SQL pour récupérer les offres d'emploi
$sql = "SELECT id, name, description FROM ohrm_job_vacancy";
$result = $conn->query($sql);

// Vérification de la disponibilité des données
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres d'Emploi</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg, #9ec7ae, #6bd4ca);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 900px;
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .button-container {
            text-align: center;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .retour {
            margin: 20px 0;
            text-align: center;
        }

        .btn-back {
            background-color: #f44336;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body>
    <div class="retour">
        <a href="accueilUser.php" class="btn btn-back">Retourner à l'accueil</a>
    </div>

    <div class="container">
        <h1>Liste des Offres d'Emploi</h1>

        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Nom du Poste</th><th>Description</th><th>Postuler</th></tr>"; // En-têtes des colonnes

            // Affichage des données
            while ($row = $result->fetch_assoc()) {
                $vacancy_id = $row['id']; // Récupérer l'ID de l'offre
                $vacancy_name = $row['name'];
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                // Lien de postulation avec le `vacancy_id` en paramètre
                echo "<td class='button-container'><a href='testTechnique.php?vacancy_id=" . $vacancy_id . "&vacancy_name=" . urlencode($vacancy_name) . "' class='btn'>Postuler</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucune donnée trouvée.</p>";
        }

        // Fermeture de la connexion
        $conn->close();
        ?>

    </div>

</body>

</html>
