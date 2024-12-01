<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style-emp.css">
    <title>Liste des Employés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <!-- <header class="header bg-primary text-white py-3">
        <div class="container">
            <h1 class="text-center">Gestion PAIE</h1>
        </div>
    </header> -->

    <div class="container my-5">
        <div class="logout-container">
            <form action="../gestion/logout.php" method="POST">
                <button type="submit" class="logout-button">Se déconnecter</button>
            </form>
        </div>
        <div class="accueil">
            <a href="http://localhost:8084/gestionrh/gestion/accueilAdmin.php">Accueil</a>
        </div>
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="display-6">Liste des Employés</h1>
        </div>

        <!-- Tableau des Employés -->
        <div class="table-responsive shadow p-4 bg-white rounded">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-primary">
                    <tr>

                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    session_start(); // Démarrer la session

                    // Connexion à la base de données
                    $conn = new mysqli("localhost", "rariana", "rariana", "orangehrm");

                    // Vérification de la connexion
                    if ($conn->connect_error) {
                        die("Connexion échouée : " . $conn->connect_error);
                    }

                    // Requête SQL pour récupérer les employés avec leur état
                    $sql = "
    SELECT e.emp_number, e.emp_lastname, e.emp_firstname, t.termination_date
    FROM hs_hr_employee e
    LEFT JOIN ohrm_emp_termination t ON e.emp_number = t.emp_number
";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $_SESSION['employe_etats'] = []; // Initialisation de la session pour stocker les états des employés
                        $_SESSION['employe_terminations'] = [];
                        
                        while ($row = $result->fetch_assoc()) {
                            $etat = 'Actif'; // État par défaut

                            if (!is_null($row['termination_date'])) {
                                $currentDate = date('Y-m-d');
                                if ($row['termination_date'] < $currentDate) {
                                    $etat = 'Inactif'; // Contrat terminé
                                } else {
                                    $etat = 'En préavis'; // Contrat en cours mais avec une date de fin future
                                }
                            }

                            // Enregistrer l'état de l'employé dans la session
                            $_SESSION['employe_etats'][$row['emp_number']] = $etat;
                            $_SESSION['employe_terminations'][$row['emp_number']] = $row['termination_date'];
                            
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['emp_lastname']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['emp_firstname']) . '</td>';
                            echo '<td><span class="badge ' . ($etat == 'Actif' ? 'bg-success' : ($etat == 'Inactif' ? 'bg-danger' : 'bg-warning')) . '">' . htmlspecialchars($etat) . '</span></td>';
                            echo '<td><a href="index.php?emp_number=' . urlencode($row['emp_number']) . '" class="btn btn-primary btn-sm">Détails</a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>Aucun employé trouvé</td></tr>";
                    }

                    // Fermeture de la connexion
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer bg-light text-center py-3">
        <p>© 2024 IT-Corporation. Tous droits réservés.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>