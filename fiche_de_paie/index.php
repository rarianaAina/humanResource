<?php
// Connexion à la base de données

use LDAP\Result;

require_once '../lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$conn = new mysqli("localhost", "rariana", "rariana", "orangehrm");

$conn->set_charset("utf8");
// Vérification de la connexion
if ($conn->connect_error) {
  die("Connexion échouée : " . $conn->connect_error);
}

// Assumons que l'ID de l'employé (emp_number) soit passé via GET
$emp_number = $_GET['emp_number'] ?? null;
// Récupération de l'année et du mois depuis le formulaire POST
$annee = $_POST['annee'] ?? date('Y'); // Par défaut, l'année en cours
$mois = $_POST['mois'] ?? date('m');   // Par défaut, le mois en cours
if ($emp_number) {
  // Requête pour récupérer les informations de l'employé, y compris le job_title_code et la classification
  $sql = "
    SELECT e.emp_number, e.emp_lastname, e.emp_firstname, e.joined_date, e.job_title_code,
           j.job_title, c.name AS classification, s.ebsal_basic_salary AS salaire_base
    FROM hs_hr_employee e
    LEFT JOIN ohrm_job_title j ON e.job_title_code = j.id
    LEFT JOIN ohrm_job_category c ON e.eeo_cat_code = c.id
    LEFT JOIN hs_hr_emp_basicsalary s ON e.emp_number = s.emp_number
    WHERE e.emp_number = ?";

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
    $fonction = $row['job_title']; // Nom de la fonction
    $classification = $row['classification']; // Classification
    $salaire_base = $row['salaire_base'] ?? 0;
    $taux_journalier = $salaire_base / 30;
    $taux_horaire = $salaire_base / 173;

    $anciennete = calculerAnciennete($date_embauche);

    $annee_embauche = date('Y', strtotime($date_embauche));
    $mois_embauche = date('m', strtotime($date_embauche));


    // Vérification : Si le mois et l'année sélectionnés sont antérieurs à la date d'embauche
    if ($annee < $annee_embauche || ($annee == $annee_embauche && $mois < $mois_embauche)) {
      echo "Erreur : La fiche de paie demandée pour $mois/$annee est antérieure à la date d'embauche de l'employé ($joined_date).";
      exit; // Arrête l'exécution du script
    }
    // Requête pour calculer les heures travaillées et leurs majorations


    $sql_heures = "
SELECT 
    -- Total des heures travaillées
    SUM(TIMESTAMPDIFF(SECOND, punch_in_utc_time, punch_out_utc_time)) / 3600 AS total_hours,

    -- Heures de nuit : entre 20h et 5h (avec majoration de 1.25)
    SUM(CASE 
        WHEN (HOUR(punch_in_utc_time) < 20 AND HOUR(punch_out_utc_time) >= 20) THEN 
            TIMESTAMPDIFF(SECOND, CAST(DATE(punch_in_utc_time) AS DATETIME) + INTERVAL 20 HOUR, punch_out_utc_time)
        WHEN (HOUR(punch_in_utc_time) >= 20 AND HOUR(punch_out_utc_time) < 5) THEN 
            TIMESTAMPDIFF(SECOND, punch_in_utc_time, LEAST(punch_out_utc_time, CAST(DATE(punch_out_utc_time) AS DATETIME) + INTERVAL 5 HOUR))
        WHEN (HOUR(punch_in_utc_time) >= 20 AND HOUR(punch_out_utc_time) >= 5) THEN 
            TIMESTAMPDIFF(SECOND, punch_in_utc_time, LEAST(punch_out_utc_time, CAST(DATE(punch_in_utc_time) AS DATETIME) + INTERVAL 5 HOUR))
        WHEN (HOUR(punch_in_utc_time) < 5 AND HOUR(punch_out_utc_time) >= 5) THEN 
            TIMESTAMPDIFF(SECOND, CAST(DATE(punch_in_utc_time) AS DATETIME) + INTERVAL 0 HOUR, CAST(DATE(punch_in_utc_time) AS DATETIME) + INTERVAL 5 HOUR)
        ELSE 0 
    END) / 3600 * 1.25 AS night_hours,

    -- Heures du dimanche (avec majoration de 1.5)
    SUM(CASE 
            WHEN DAYOFWEEK(punch_in_utc_time) = 1 THEN 
                TIMESTAMPDIFF(SECOND, punch_in_utc_time, punch_out_utc_time) 
            ELSE 0 
        END) / 3600 AS sunday_hours,

    -- Heures des jours fériés (avec majoration de 2)
    SUM(CASE 
            WHEN DATE(punch_in_utc_time) IN ('2024-11-01', '2024-11-11') THEN 
                TIMESTAMPDIFF(SECOND, punch_in_utc_time, punch_out_utc_time) 
            ELSE 0 
        END) / 3600 AS holiday_hours

FROM ohrm_attendance_record
WHERE employee_id = ? 
AND MONTH(punch_in_utc_time) = ? 
AND YEAR(punch_in_utc_time) = ?
";



    $stmt_heures = $conn->prepare($sql_heures);
    $stmt_heures->bind_param("iii", $emp_number, $mois, $annee);
    $stmt_heures->execute();
    $result_heures = $stmt_heures->get_result();

    if ($result_heures->num_rows > 0) {
      $row_heures = $result_heures->fetch_assoc();
      $total_hours = $row_heures['total_hours'] ?? 0;
      $night_hours = $row_heures['night_hours'] ?? 0;
      $sunday_hours = $row_heures['sunday_hours'] ?? 0;
      $holiday_hours = $row_heures['holiday_hours'] ?? 0;
    } else {
      $total_hours = 0;
      $night_hours = 0;
      $sunday_hours = 0;
      $holiday_hours = 0;
    }

    // Calcul des heures supplémentaires et de leurs majorations
    $heures_supp_41_48 = 0;
    $heures_supp_au_dela_48 = 0;

    if ($total_hours > 40) {
      $heures_supp_41_48 = min($total_hours - 40, 8);
      if ($total_hours > 48) {
        $heures_supp_au_dela_48 = $total_hours - 48;
      }
    }
    $sql_conge = "
SELECT 
    e.emp_number,
    e.no_of_days - e.days_used AS remaining_days
FROM 
    ohrm_leave_entitlement e
WHERE 
    e.emp_number = ?
ORDER BY
    e.emp_number;
";

    $stmt_conges = $conn->prepare($sql_conge);
    $stmt_conges->bind_param("i", $emp_number);
    $stmt_conges->execute();
    $result_conges = $stmt_conges->get_result();

    if ($result_conges->num_rows > 0) {
      while ($row = $result_conges->fetch_assoc()) {
        $nombreConge = $row['remaining_days'];
      }
    } else {
      //echo "No leave entitlement found for the employee.";
    }

    $nombreCongeMois = 0;
    // Requête SQL pour compter les jours de congé pris dans le mois sélectionné
    $sql_conge_mois = "
SELECT 
    emp_number,
    COUNT(*) AS days_taken
FROM 
    ohrm_leave
WHERE 
    emp_number = ? 
    AND YEAR(date) = ? 
    AND MONTH(date) = ?
GROUP BY 
    emp_number;
";

    $stmt_conges_mois = $conn->prepare($sql_conge_mois);
    $stmt_conges_mois->bind_param("iii", $emp_number, $annee, $mois);
    $stmt_conges_mois->execute();
    $result_conges_mois = $stmt_conges_mois->get_result();


    if ($result_conges_mois->num_rows > 0) {
      while ($row = $result_conges_mois->fetch_assoc()) {
        $nombreCongeMois = $row['days_taken'];
        //echo "Employee Number: " . $row['emp_number'] . " - Days Taken in the Month: " . $row['days_taken'] . "<br>";
      }
    } else {

      //echo "No leave taken for the selected month.";
    }

    // Calcul des montants des heures supplémentaires avec les majorations
    $montant_supp_41_48 = $heures_supp_41_48 * $taux_horaire * 1.3;  // 30% de majoration
    $montant_nuit = $night_hours * $taux_horaire * 1.4;               // 40% de majoration
    $montant_au_dela_48 = $heures_supp_au_dela_48 * $taux_horaire * 1.5; // 50% de majoration
    $montant_dimanche = $sunday_hours * $taux_horaire * 1.5;           // 50% de majoration
    $montant_ferie = $holiday_hours * $taux_horaire * 2;               // 100% de majoration

    // Calcul du montant total des heures supplémentaires
    $montant_total_supp = $montant_supp_41_48 + $montant_nuit + $montant_au_dela_48 + $montant_dimanche + $montant_ferie;

    // Calcul du montant total pour les heures travaillées
    $montant_travaille = $total_hours * $taux_horaire;
  } else {
    echo "Aucun employé trouvé.";
    exit;
  }
} else {
  echo "Matricule manquant.";
  exit;
}

$montantTravailles = $total_hours * $taux_horaire;
if (($montantTravailles * 1) / 100  > 20000) {
  $retenuIRSA = 20000;
} else {
  $retenuIRSA = ($montantTravailles * 1) / 100;
}
$retenuSanitaire = ($montantTravailles * 1) / 100;
$totalRetenues = $retenuIRSA + $retenuSanitaire;


// Fermeture de la connexion
$conn->close();
/**
 * Fonction pour calculer l'ancienneté à partir de la date d'embauche.
 * Retourne l'ancienneté sous forme d'années, mois et jours.
 */
function calculerAnciennete($date_embauche)
{
  $dateEmbauche = new DateTime($date_embauche);
  $dateActuelle = new DateTime();

  $interval = $dateEmbauche->diff($dateActuelle);

  $anneesAnciennete = $interval->y;
  $moisAnciennete = $interval->m;
  $joursAnciennete = $interval->d;

  // Vérifier que les années, mois et jours d'ancienneté sont numériques
  if (!is_numeric($anneesAnciennete) || !is_numeric($moisAnciennete) || !is_numeric($joursAnciennete)) {
    return 0;  // Retourne 0 si une des valeurs n'est pas numérique
  }

  // Retourner l'ancienneté au format texte
  if ($anneesAnciennete > 0) {
    return "$anneesAnciennete an" . ($anneesAnciennete > 1 ? "s" : "") . ($moisAnciennete > 0 ? " et $moisAnciennete mois" : "") . ($joursAnciennete > 0 ? " et $joursAnciennete jour" . ($joursAnciennete > 1 ? "s" : "") : "");
  } elseif ($moisAnciennete > 0) {
    return "$moisAnciennete mois" . ($joursAnciennete > 0 ? " et $joursAnciennete jour" . ($joursAnciennete > 1 ? "s" : "") : "");
  } else {
    return "$joursAnciennete jour" . ($joursAnciennete > 1 ? "s" : "");
  }
}

function calculerMoisAnciennete($date_embauche)
{
  $dateEmbauche = new DateTime($date_embauche);
  $dateActuelle = new DateTime();

  $interval = $dateEmbauche->diff($dateActuelle);

  // Calcul du nombre total de mois d'ancienneté
  $totalMois = ($interval->y * 12) + $interval->m;

  return $totalMois;  // Retourne un nombre (total de mois)
}


function calculerCongeTotal($date_embauche)
{
  $moisAnciennete = calculerMoisAnciennete($date_embauche);
  $congeTotal = $moisAnciennete * 2.5;  // 2,5 jours par mois
  return $congeTotal;
}

$congeTotal = calculerCongeTotal($date_embauche);
//echo $congeTotal;
$mois_precedent = new DateTime('first day of last month');
$mois_precedent = $mois_precedent->format('F Y');

// Calcul des retenues IRSA dynamiques
$tranche1 = 0; // Pour les montants jusqu'à 350 000 Ar
$tranche2 = 0; // Pour les montants de 350 001 à 400 000 Ar
$tranche3 = 0; // Pour les montants de 400 001 à 500 000 Ar
$tranche4 = 0; // Pour les montants de 500 001 à 600 000 Ar
$tranche5 = 0; // Pour les montants supérieurs à 600 000 Ar

if ($montantTravailles > 350000) {
  if ($montantTravailles > 400000) {
    $tranche2 = 5000; // Calcul fixe pour cette tranche
  } else {
    $tranche2 = ($montantTravailles - 350000) * 0.05;
  }

  if ($montantTravailles > 500000) {
    $tranche3 = 10000; // Calcul fixe pour cette tranche
  } else if ($montantTravailles > 400000) {
    $tranche3 = ($montantTravailles - 400000) * 0.10;
  }

  if ($montantTravailles > 600000) {
    $tranche4 = 15000; // Calcul fixe pour cette tranche
  } else if ($montantTravailles > 500000) {
    $tranche4 = ($montantTravailles - 500000) * 0.15;
  }

  if ($montantTravailles > 600000) {
    $tranche5 = ($montantImposable - 600000) * 0.20;
  }
}


// Total IRSA
$totalIRSA = $tranche2 + $tranche3 + $tranche4 + $tranche5;
$netAPayer = $montantTravailles - ($totalIRSA + $totalRetenues);
$salaire_brut = $montantTravailles + $montant_total_supp;
$montantImposable = $salaire_brut - $totalRetenues;
$nombreConge = $congeTotal - $nombreCongeMois;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fiche de paie</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
  <link rel="stylesheet" href="style-fpaie.css">
  <link

    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet" />
</head>

<div>
    <a href="http://localhost:8084/gestionrh/fiche_de_paie/employee.php" class="btn btn-secondary mb-4">Accueil</a>
  </div>
  <div id="content">
  <body>

  <div class="container my-5">
    <!-- Header -->
    <div class="text-center mb-4">
      <h1 class="display-4">Fiche de paie</h1>
      <h2 class="h5">Fiche de paie du mois de :</h2>
      <form method="POST" action="">
        <div class="row justify-content-center">
          <!-- Sélection de l'année -->
          <div class="col-md-3">
            <label for="annee" class="form-label">Année</label>
            <select name="annee" id="annee" class="form-select" aria-label="Année de la fiche de paie">
              <?php
              // Générer une liste déroulante pour les 5 dernières années
              $anneeCourante = date('Y');
              for ($i = $anneeCourante; $i >= $anneeCourante - 5; $i--) {
                echo "<option value=\"$i\">$i</option>";
              }
              ?>
            </select>
          </div>

          <!-- Sélection du mois -->
          <div class="col-md-3">
            <label for="mois" class="form-label">Mois</label>
            <select name="mois" id="mois" class="form-select" aria-label="Mois de la fiche de paie">
              <?php
              // Tableau des mois en français
              $moisEcoules = [
                '01' => 'Janvier',
                '02' => 'Février',
                '03' => 'Mars',
                '04' => 'Avril',
                '05' => 'Mai',
                '06' => 'Juin',
                '07' => 'Juillet',
                '08' => 'Août',
                '09' => 'Septembre',
                '10' => 'Octobre',
                '11' => 'Novembre',
                '12' => 'Décembre'
              ];
              foreach ($moisEcoules as $numMois => $nomMois) {
                echo "<option value=\"$numMois\">$nomMois</option>";
              }
              ?>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Afficher</button>
      </form>
    </div>
  </div>


  <!-- Informations Employé -->

  <div class="row gy-4">

    <!-- Section Informations Employé avec une classe personnalisée -->
    <div class="col-md-6 section-info-employe">
      <div class="p-3 border rounded">
        <h4 class="h6 fw-bold mb-3">Informations Employé</h4>
        <p><strong>Nom et Prénoms :</strong> <?php echo $nom . ' ' . $prenom; ?></p>
        <p><strong>Matricule :</strong> <?php echo $matricule; ?></p>
        <p><strong>Fonction :</strong> <?php echo $fonction ?? 'Non spécifiée'; ?></p>
        <p><strong>Date d'embauche :</strong> <?php echo $date_embauche; ?></p>
        <p><strong>Ancienneté :</strong> <?php echo $anciennete; ?></p>
      </div>
    </div>

    <!-- Section Informations Salaire avec une classe personnalisée -->
    <div class="col-md-6 section-info-salaire">
      <div class="p-3 border rounded">
        <h4 class="h6 fw-bold mb-3">Informations Salaire</h4>
        <p><strong>Classification :</strong> <?php echo $classification ?? 'Non spécifiée'; ?></p>
        <p><strong>Salaire de base :</strong> <?php echo number_format($salaire_base, 2, ',', ' ') . ' Ar'; ?></p>
        <p><strong>Taux journalier :</strong> <?php echo number_format($taux_journalier, 2, ',', ' ') . ' Ar'; ?></p>
        <p><strong>Taux horaire :</strong> <?php echo number_format($taux_horaire, 2, ',', ' ') . ' Ar'; ?></p>
        <p><strong>Indice :</strong> </p>
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
        <tr>
          <th>Heures travaillées: </th>
          <th><?php echo number_format($total_hours, 2, ',', ' ') . ' heures'; ?></th>
          <th><?php echo number_format($taux_horaire, 2, ',', ' ') . ' Ar'; ?></th>
          <th><?php echo number_format($montantTravailles, 2, ',', ' ') . ' Ar'; ?></th>
        </tr>
        <tr>
          <td>Absences déductibles</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Primes de rendement</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Primes d'ancienneté</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Heures supplémentaires majorées de 30%</td>


          <!-- // Affichage des résultats
    echo "Total des heures travaillées : " . round($total_hours, 2) . " heures<br>";
    echo "Montant des heures travaillées : " . round($montant_travaille, 2) . " Ar<br>";
    
    echo "Heures supplémentaires au-delà de 48 heures : " . round($heures_supp_au_dela_48, 2) . " heures<br>";
    echo "Heures de nuit : " . round($night_hours, 2) . " heures<br>";
    echo "Heures travaillées le dimanche : " . round($sunday_hours, 2) . " heures<br>";
    echo "Heures travaillées pendant les jours fériés : " . round($holiday_hours, 2) . " heures<br>";
    echo "Montant total des heures supplémentaires : " . round($montant_total_supp, 2) . " Ar<br>"; -->
          <td><?php echo round($heures_supp_41_48, 2) . " heures<br>"; ?></td>
          <td></td>
          <td><?php echo number_format(round($montant_supp_41_48, 2), 2, ',', ' ') . " Ar<br>"; ?></td>

        </tr>
        <tr>
          <td>Heures supplémentaires majorées de 40%</td>
          <td><?php echo round($heures_supp_au_dela_48, 2) . " heures<br>"; ?></td>
          <td></td>
          <td><?php echo number_format(round($montant_au_dela_48, 2), 2, ',', ' ') . " Ar<br>"; ?></td>

        </tr>
        <tr>
          <td>Heures supplémentaires majorées de 50%</td>
          <td><?php echo round($sunday_hours, 2) . " heures<br>"; ?></td>
          <td></td>
          <td><?php echo number_format(round($montant_dimanche, 2), 2, ',', ' ') . " Ar<br>"; ?></td>

        </tr>
        <tr>
          <td>Heures supplémentaires majorées de 100%</td>
          <td><?php echo round($holiday_hours, 2) . " heures<br>"; ?></td>
          <td></td>
          <td><?php echo number_format(round($montant_ferie, 2), 2, ',', ' ') . " Ar<br>"; ?></td>

        </tr>
        <tr>
          <td>Majoration pour heures de nuit</td>
          <td><?php echo round($night_hours, 2) . " heures<br>"; ?></td>
          <td></td>
          <td><?php echo number_format(round($montant_nuit, 2), 2, ',', ' ') . " Ar<br>"; ?></td>

        </tr>
        <tr>
          <td>Primes diverses</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Rappels sur période antérieure</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Droits de congés</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Droits de préavis</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td>Droits de licenciement</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3" class="text-end"><strong>Salaire brut</strong></td>
          <td><?php echo number_format($salaire_brut, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
        <tr>
          <td colspan="3" class="text-end"><strong>Salaire imposable</strong></td>
          <td><?php echo number_format($montantImposable, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Tableau des Retenues -->
  <div class="mt-5">
    <h4 class="h5 fw-bold">Retenues et Net à Payer</h4>
    <table class="table table-bordered">
      <tbody>
        <tr>
          <td colspan="2" class="text-end"></td>
          <td>Taux</td>
          <td>Total</td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Retenue CNaPS</td>
          <td>1%</td>
          <td><?php echo number_format($retenuIRSA, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Retenue Sanitaire</td>
          <td>1%</td>
          <td><?php echo number_format($retenuSanitaire, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Tranche IRSA INF 350 000</td>
          <td>0%</td>
          <td><?php echo $montantTravailles <= 350000 ? "0 Ar" : ""; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Tranche IRSA DE 350 001 à 400 000</td>
          <td>5%</td>
          <td><?php echo $tranche2 > 0 ? number_format($tranche2, 2, ',', ' ') . ' Ar' : ''; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Tranche IRSA DE 400 001 à 500 000</td>
          <td>10%</td>
          <td><?php echo $tranche3 > 0 ? number_format($tranche3, 2, ',', ' ') . ' Ar' : ''; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Tranche IRSA DE 500 001 à 600 000</td>
          <td>15%</td>
          <td><?php echo $tranche4 > 0 ? number_format($tranche4, 2, ',', ' ') . ' Ar' : ''; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end">Tranche IRSA SUP 600 000</td>
          <td>20%</td>
          <td><?php echo $tranche5 > 0 ? number_format($tranche5, 2, ',', ' ') . ' Ar' : ''; ?></td>
        </tr>
        <tr class="table-secondary">
          <td colspan="2" class="text-end"></td>
          <td><strong>TOTAL IRSA</strong></td>
          <td><?php echo number_format($totalIRSA, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
        <tr class="table-secondary">
          <td colspan="2" class="text-end"></td>
          <td><strong>TOTAL RETENUES</strong></td>
          <td><?php echo number_format($totalRetenues, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
        <tr>
          <td colspan="2" class="text-end"></td>
          <td>Autres indemnités</td>
          <td></td>
        </tr>
        <tr class="table-secondary">
          <td colspan="2" class="text-end"></td>
          <td><strong>Net à Payer</strong></td>
          <td><?php echo number_format($netAPayer, 2, ',', ' ') . ' Ar'; ?></td>
        </tr>
      </tbody>
    </table>
    <table>
      <tbody>
        <tr>
          <td><strong>Solde congé : </strong><?php echo number_format($nombreConge, 2, ',', '') . ' Jours'; ?></td>
        </tr>
        <tr>
          <td><strong>Congé pris pour ce mois: </strong><?php echo number_format($nombreCongeMois, 2, ',', '') . ' Jours'; ?></td>
        </tr>
      </tbody>
    </table>
    </body>
    </div>
    </div>
    <button id="exportButton" class="btn btn-success mt-3">Exporter en PDF</button>
    <div>
      <br>
    </div>
    <footer class="footer bg-light text-center py-3">
        <p>© 2024 IT-Corporation. Tous droits réservés.</p>
    </footer>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById("date").textContent =
      new Date().toLocaleDateString();
  </script>

</html>
<script>
  document.getElementById('exportButton').addEventListener('click', function() {
    const element = document.getElementById('content');
    
    // Configuration des options pour ajouter des marges
    html2pdf()
      .from(element)  // L'élément à convertir en PDF
      .set({
        margin: [6, 6, 6, 6],  // Marges : [haut, droite, bas, gauche]
        filename: 'Fiche de Paie.pdf',  // Nom du fichier PDF
        html2canvas: {
          scale: 2  // Augmente la qualité du rendu
        },
        jsPDF: {
          unit: 'mm',  // Unité de mesure (mm, cm, etc.)
          format: 'a4',  // Format de la page (A4, Letter, etc.)
          orientation: 'portrait'  // Orientation (portrait ou paysage)
        }
      })
      .save();  // Sauvegarder le fichier
  });
</script>
