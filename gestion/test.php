<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Utilisateur non connecté. Veuillez vous connecter.");
}

// Définition du thème, avec "technologie" comme valeur par défaut
$theme = isset($_POST['theme']) ? $_POST['theme'] : 'technologie';

// Fonction pour générer des questions à partir de l'API Cody AI (ou une autre API similaire)
function generateQuestionsFromAI($theme) {
    $apiKey = 'zUYelH7K3l9sVomQmFIkrk92xLNqvaamXUiBtPjl9a8023c8';  // Remplacez par votre clé API Cody AI
    $url = "https://getcody.ai/api/v1/conversations";  // Remplacez par l'URL de votre API

    // Définir les données de la requête
    $data = [
        'model' => 'cody-default',  // Modèle utilisé par Cody (remplacez-le si nécessaire)
        'prompt' => "Génère 5 questions difficiles sur le thème suivant : $theme.",
        'max_tokens' => 300,
        'temperature' => 0.7
    ];

    // Configuration des headers pour l'authentification et le contenu JSON
    $headers = [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    // Initialisation de cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Exécution de la requête et récupération de la réponse
    $response = curl_exec($ch);

    // Gestion des erreurs cURL
    if (curl_errno($ch)) {
        echo 'Erreur : ' . curl_error($ch);
    }

    curl_close($ch);

    // Décoder la réponse JSON
    $responseData = json_decode($response, true);

    // Vérification et extraction des questions générées
    if (isset($responseData['choices'][0]['text'])) {
        return explode("\n", trim($responseData['choices'][0]['text']));
    } else {
        return ["Impossible de générer des questions. Vérifiez la connexion à l'API."];
    }
}

// Génération des questions
$questions = generateQuestionsFromAI($theme);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test de Compatibilité</title>
</head>
<body>
    <h2>Test de compatibilité sur le thème : <?php echo htmlspecialchars($theme); ?></h2>
    <form method="POST" action="traitement.php">
        <?php foreach ($questions as $index => $question): ?>
            <p><?php echo ($index + 1) . ". " . htmlspecialchars($question); ?></p>
            <textarea name="answer<?php echo $index + 1; ?>" required></textarea>
        <?php endforeach; ?>
        <input type="hidden" name="theme" value="<?php echo htmlspecialchars($theme); ?>">
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
