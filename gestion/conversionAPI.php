<?php

// Cloudmersive PDF to Text API
$apiKey = "ed62d328-45cf-450d-8b13-276aa367a38c";

// Chemin du fichier PDF à convertir
$file = "C:/PROCESSRH.pdf";

// URL de l'API Cloudmersive PDF to Text
$url = 'https://api.cloudmersive.com/convert/pdf/to/text';

// Ouvrir le fichier PDF
$fileData = file_get_contents($file);

// Initialiser cURL
$ch = curl_init();

// Définir les options pour la requête cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// Définir les en-têtes nécessaires
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Apikey: $apiKey",
]);

// Définir le contenu du corps de la requête
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => new CURLFile($file),
]);

// Exécuter la requête
$response = curl_exec($ch);

// Vérifier si une erreur cURL s'est produite
if (curl_errno($ch)) {
    echo 'Erreur cURL: ' . curl_error($ch);
} else {
    // Afficher la réponse brute pour le diagnostic
    echo "Réponse de l'API: <br><pre>$response</pre>";

    // Décoder la réponse JSON
    $data = json_decode($response, true);

    // Vérifier si la réponse est valide
    if ($data === null) {
        echo "Erreur de décodage JSON. La réponse est vide ou mal formée.";
    } else {
        // Accéder au texte extrait du PDF
        if (isset($data['Text'])) {
            $text = $data['Text'];
            echo "Texte extrait du PDF: <br><pre>$text</pre>";
        } else {
            echo "Aucun texte extrait du PDF.";
        }
    }
}

// Fermer la session cURL
curl_close($ch);
?>
