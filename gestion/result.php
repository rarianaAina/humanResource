<?php
session_start();

// Vérification si le candidat a passé le test
if (!isset($_SESSION['score'])) {
    header("Location: index.php");
    exit();
}
$score = $_SESSION['score'] ?? 0;
$totalQuestions = $_SESSION['totalQuestions'] ?? 0;
$vacancy_id = $_SESSION['vacancy_id'] ?? null;
$vacancy_name = $_SESSION['vacancy_name'] ?? null;
$percentage = ($score / $totalQuestions) * 100;

// Vérification si le candidat peut passer à la candidature
if ($percentage >= 50) {
    $message = "Félicitations, vous avez réussi le test ! Vous pouvez maintenant remplir votre formulaire de candidature.";
    $canApply = true;
} else {
    $message = "Désolé, vous n'avez pas réussi le test. Vous devez attendre 5 minutes pour repasser le test.";
    $canApply = false;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat du Test</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        p {
            color: #34495e;
            font-size: 1.1rem;
            margin: 15px 0;
        }

        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #e74c3c;
        }

        .btn-secondary:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Résultat du Test</h1>
        <p>Vous avez obtenu <strong><?php echo $score; ?></strong> sur <strong><?php echo $totalQuestions; ?></strong> 
        (<?php echo number_format($percentage, 2); ?>%).</p>

        <?php if ($canApply) : ?>
            <p><?php echo $message; ?></p>
            <a href="formulaireCandidat.php?vacancy_id=<?php echo urlencode($vacancy_id); ?>&vacancy_name=<?php echo urlencode($vacancy_name); ?>" class="btn">Passer au formulaire de candidature</a>
        <?php else : ?>
            <p><?php echo $message; ?></p>
            <a href="offres.php" class="btn btn-secondary">Retour aux offres</a>
        <?php endif; ?>
    </div>
</body>

</html>
