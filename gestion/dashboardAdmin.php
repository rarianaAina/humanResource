<?php
session_start();

// Vérifiez si l'utilisateur est un administrateur
// if (!isset($_SESSION['user_id'])) {
//     die("Accès non autorisé. Vous devez être un administrateur.");
// }

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
$query = "SELECT candidat_id, compatibilite, middle_name, last_name FROM compatibilite";
$stmt = $pdo->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de compatibilité des candidats</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #333;
            padding-top: 50px;
            font-size: 2em;
        }

        table {
            width: 80%;
            margin: 50px auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        th,
        td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
            font-size: 1.1em;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td {
            font-size: 1em;
            color: #333;
        }

        .compatibility-high {
            color: green;
            font-weight: bold;
        }

        .compatibility-low {
            color: red;
            font-weight: bold;
        }

        .compatibility-medium {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h1>Tableau de compatibilité des candidats</h1>
    <button><a href="logout.php">Se déconnecter</a></button>
    <table>
        <tr>
            <!-- <th>ID Candidat</th> -->
            <th>Prénom du candidat</th>
            <!-- <th>Nom du candidat</th> -->
            <th>Score de compatibilité</th>
            <th>Pourcentage de compatibilité</th>
        </tr>

        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $candidat_id = $row['candidat_id'];
            $compatibility_score = $row['compatibilite'];
            $candidat_middle_name = $row['middle_name'];
            $candidat_last_name = $row['last_name'];

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
            // echo "<td>$candidat_id</td>";
            //echo "<td>$candidat_middle_name</td>";
            echo "<td>$candidat_last_name</td>";
            echo "<td>$compatibility_score</td>";
            echo "<td class='$compatibility_class'>" . round($compatibility_percentage, 2) . "%</td>";
            echo "</tr>";
        }
        ?>

    </table>

</body>

</html>