<?php
require 'vendor/autoload.php'; // Charger automatiquement les bibliothèques via Composer

use Smalot\PdfParser\Parser; // Importer la bibliothèque pour PDF

// Fonction pour extraire le texte d'un fichier PDF
function extractTextFromPDF($filePath) {
    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    return $pdf->getText();
}

// Fonction pour extraire le texte d'un fichier DOCX
function extractTextFromDocx($filePath) {
    $zip = new ZipArchive;
    if ($zip->open($filePath) === TRUE) {
        $content = $zip->getFromName("word/document.xml");
        $zip->close();
        return strip_tags($content);
    }
    return '';
}

// Fonction pour extraire les mots-clés
function extractKeywords($text) {
    $keywords = [];

    // Regex pour identifier les sections importantes
    $patterns = [
        'diplômes' => '/(licence|master|doctorat|BTS|DUT|bac \+ [0-9])/i',
        'compétences' => '/(PHP|Java|Python|SQL|JavaScript|HTML|CSS|Vue\.js|Eclipse|Oracle|EJB|MySQL|Sphinx)/i',
        'expériences' => '/(développeur|ingénieur|analyste|consultant|gestionnaire|technicien)/i'
    ];

    // Extraction des mots-clés pour chaque section
    foreach ($patterns as $category => $pattern) {
        preg_match_all($pattern, $text, $matches);
        $keywords[$category] = array_unique($matches[0]);
    }

    return $keywords;
}

// Gestion de l'upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['cv'])) {
    $file = $_FILES['cv'];
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'src';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir);
    }
    $filePath = $uploadDir . DIRECTORY_SEPARATOR . basename($file['name']);
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Vérifier l'extension du fichier
    if (!in_array($fileExtension, ['pdf', 'docx'])) {
        die("Seuls les fichiers PDF et DOCX sont autorisés.");
    }

    // Déplacer le fichier dans le dossier uploads
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Extraction du texte
        $text = '';
        if ($fileExtension === 'pdf') {
            $text = extractTextFromPDF($filePath);
        } elseif ($fileExtension === 'docx') {
            $text = extractTextFromDocx($filePath);
        }

        // Extraire les mots-clés
        $keywords = extractKeywords($text);

        // Afficher le texte brut
        echo "<h2>Texte extrait :</h2>";
        echo "<pre>$text</pre>";

        // Afficher les mots-clés
        echo "<h2>Résultats</h2>";
        foreach ($keywords as $category => $words) {
            echo "<h3>" . ucfirst($category) . "</h3>";
            if (!empty($words)) {
                echo "<ul>";
                foreach ($words as $word) {
                    echo "<li>$word;</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Aucun mot-clé trouvé.</p>";
            }
        }
    } else {
        echo "Erreur lors de l'upload du fichier.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Upload CV et Extraction des Mots-clés</title>
</head>
<body>
    <h1>Uploader votre CV</h1>
    <form action="pdftotext.php" method="post" enctype="multipart/form-data">
        <label for="cv">Sélectionnez un fichier PDF ou DOCX :</label>
        <input type="file" name="cv" id="cv" accept=".pdf, .docx" required>
        <button type="submit">Uploader</button>
    </form>
</body>
</html>
