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
$query = "SELECT candidat_id, compatibilite, middle_name, last_name, email FROM compatibilite";
$stmt = $pdo->prepare($query);
$stmt->execute();
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
        <a href="accueilAdmin.php">Retourner à l'accueil</a>
    </div>

    <h1>Tableau de compatibilité des candidats</h1>

    <table>
        <tr>
            <th>Email du candidat</th>
            <th>Prénom du candidat</th>
            <th>Nom du candidat</th>
            <th>Score de compatibilité</th>
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
            echo "<td>$compatibility_score</td>";
            echo "<td class='$compatibility_class'>" . round($compatibility_percentage, 2) . "%</td>";
            echo "<td>Envoyer un email</td>";
            echo "</tr>";
        }
        ?>

    </table>
    <div style="float: right; width: 40%; height: 400px;">
        <canvas id="compatibilityChart"></canvas>
    </div>
    <script>
        // Récupérer les données des candidats PHP dans un tableau JavaScript
        const compatibilityData = <?php
                                    $compatibilityScores = [];
                                    $compatibilityLabels = [];
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $compatibilityScores[] = $row['compatibilite'];
                                        $compatibilityLabels[] = $row['middle_name'] . ' ' . $row['last_name'];
                                    }
                                    echo json_encode(['labels' => $compatibilityLabels, 'scores' => $compatibilityScores]);
                                    ?>;

        // Préparer les données pour le diagramme circulaire
        const data = {
            labels: compatibilityData.labels,
            datasets: [{
                label: 'Compatibilité des candidats',
                data: compatibilityData.scores,
                backgroundColor: ['#4CAF50', '#FF9800', '#f44336'], // Couleurs des segments
                borderWidth: 1
            }]
        };

        // Créer le diagramme circulaire
        const ctx = document.getElementById('compatibilityChart').getContext('2d');
        const compatibilityChart = new Chart(ctx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' points';
                            }
                        }
                    }
                }
            }
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