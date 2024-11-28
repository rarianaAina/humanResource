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
</head>

<body>
    <div class="container">
        <h1>Résultat du Test</h1>
        <p>Vous avez obtenu <?php echo $score; ?> sur <?php echo $totalQuestions; ?> (<?php echo number_format($percentage, 2); ?>%).</p>
        
        <?php if ($canApply) : ?>
            <p><?php echo $message; ?></p>
            <a href="formulaireCandidat.php?vacancy_id=<?php echo urlencode($vacancy_id); ?>&vacancy_name=<?php echo urlencode($vacancy_name); ?>">Passer au formulaire de candidature</a>
            
        <?php else : ?>
            <p><?php echo $message; ?></p>
            <a href="offres.php">Retour aux offres</a>
        <?php endif; ?>
    </div>
</body>

</html>
