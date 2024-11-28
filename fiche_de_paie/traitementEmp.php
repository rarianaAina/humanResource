<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "rariana", "rariana", "orangehrm");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Assumons que l'ID de l'employé (emp_number) soit passé via GET
$emp_number = $_GET['emp_number'] ?? null;

if ($emp_number) {
    // Requête pour récupérer les informations de l'employé
    $sql = "
        SELECT e.emp_number, e.emp_lastname, e.emp_firstname, e.joined_date,
               TIMESTAMPDIFF(YEAR, e.joined_date, CURDATE()) AS anciennete
        FROM hs_hr_employee e
        WHERE e.emp_number = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $emp_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Récupération des données de l'employé
        $row = $result->fetch_assoc();
        $nom = $row['emp_lastname'];
        $prenom = $row['emp_firstname'];
        $matricule = $row['emp_number'];
        $date_embauche = $row['joined_date'];
        $anciennete = $row['anciennete'];
    } else {
        echo "Aucun employé trouvé.";
        exit;
    }
} else {
    echo "Matricule manquant.";
    exit;
}

// Fermeture de la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fiche de paie</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
  </head>
  <body>
    <div>
      <a href="http://localhost:8084/gestionrh/fiche_de_paie/employee.php">Accueil</a>
    </div>
    <div class="container my-5">
      <!-- Header -->
      <div class="text-center mb-4">
        <h1 class="display-4">Fiche de paie</h1>
        <h2 class="h5">Arrêté au <span id="date" class="fw-bold"></span></h2>
      </div>

      <!-- Informations Employé -->
      <div class="row gy-4">
        <div class="col-md-6">
          <div class="p-3 border rounded">
            <h4 class="h6 fw-bold mb-3">Informations Employé</h4>
            <p><strong>Nom et Prénoms :</strong> <?php echo $nom . ' ' . $prenom; ?></p>
            <p><strong>Matricule :</strong> <?php echo $matricule; ?></p>
            <p><strong>Date d'embauche :</strong> <?php echo $date_embauche; ?></p>
            <p><strong>Ancienneté :</strong> <?php echo $anciennete . ' ans'; ?></p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-3 border rounded">
            <h4 class="h6 fw-bold mb-3">Informations Salaire</h4>
            <p><strong>Classification :</strong> </p>
            <p><strong>Salaire de base :</strong></p>
            <p><strong>Taux journaliers :</strong></p>
            <p><strong>Taux horaires :</strong></p>
            <p><strong>Indice :</strong></p>
          </div>
        </div>
      </div>

      <!-- Tableau des Détails -->
      <div class="mt-5">
        <h4 class="h5 fw-bold">Détails du Salaire</h4>
        <table class="table table-striped table-bordered">
          <thead class="table-light">
            <tr>
              <th>Désignations</th>
              <th>Nombre</th>
              <th>Taux</th>
              <th>Montant</th>
            </tr>
          </thead>
          <tbody>
            <!-- Lignes de détails de salaire -->
          </tbody>
        </table>
      </div>

      <!-- Tableau des Retenues -->
      <div class="mt-5">
        <h4 class="h5 fw-bold">Retenues et Net à Payer</h4>
        <table class="table table-bordered">
          <tbody>
            <tr class="table-secondary">
              <td colspan="2" class="text-end"><strong>TOTAL IRSA</strong></td>
              <td><strong></strong></td>
            </tr>
            <tr>
              <td colspan="2" class="text-end">Autres indemnités</td>
              <td></td>
            </tr>
            <tr class="table-secondary">
              <td colspan="2" class="text-end"><strong>Net à payer</strong></td>
              <td><strong></strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
    <script>
      document.getElementById("date").textContent =
        new Date().toLocaleDateString();
    </script>
  </body>
</html>
