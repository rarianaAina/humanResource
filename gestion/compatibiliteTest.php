<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Utilisateur non connecté. Veuillez vous connecter.");
}

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


$candidat_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT MAX(created_at) AS last_test_date FROM compatibilite WHERE candidat_id = :candidat_id");
$stmt->bindParam(':candidat_id', $candidat_id);
$stmt->execute();

$lastTest = $stmt->fetch(PDO::FETCH_ASSOC);

// if ($lastTest && $lastTest['last_test_date']) {
//     $lastTestDate = new DateTime($lastTest['last_test_date']);
//     $currentDate = new DateTime();
//     $interval = $currentDate->diff($lastTestDate);

//     if ($interval->days < 3) {
//         $hoursElapsed = ($interval->days * 24) + $interval->h;
//         $hoursRemaining = 72 - $hoursElapsed;
//         die("Vous ne pouvez pas effectuer un nouveau test avant " . $hoursRemaining . " heures.");

//     }
//     echo $lastTestDate;
// }
// Fonction pour analyser les réponses et calculer la compatibilité avec analyse par mots-clés
function analyzeAnswers($answers)
{
    $criteria = [
        1 => ['keywords' => ['120 pièces', '4 heures', '6 heures'], 'weight' => 2],
        2 => ['keywords' => ['suite', '1, 4, 9, 16, 25', 'prochain nombre'], 'weight' => 1],
        3 => ['keywords' => ['traverser le pont', 'deux lampes', 'deux personnes', 'ensemble'], 'weight' => 3],
        4 => ['keywords' => ['cube peint', '27 petits cubes', 'peinture sur', 'au moins une face'], 'weight' => 2],
        5 => ['keywords' => ['secteur des données', 'bases de données', 'cloud computing'], 'weight' => 3],
        6 => ['keywords' => ['système d’exploitation', 'multitâche', 'windows'], 'weight' => 2],
        7 => ['keywords' => ['algebra', 'logique mathématique', 'propositional'], 'weight' => 3],
        8 => ['keywords' => ['ressources humaines', 'gestion des talents', 'interview'], 'weight' => 2],
        9 => ['keywords' => ['sécurité informatique', 'cybersécurité', 'virus'], 'weight' => 3],
        10 => ['keywords' => ['cloud', 'servicelayer', 'pivots'], 'weight' => 2],
        11 => ['keywords' => ['protocole HTTP', 'transfert de données', 'serveur client'], 'weight' => 2],
        12 => ['keywords' => ['PHP', 'développement web', 'back-end'], 'weight' => 3],
        13 => ['keywords' => ['algorithme', 'tri', 'complexité'], 'weight' => 2],
        14 => ['keywords' => ['fonctionalité', 'architecture logicielle', 'design'], 'weight' => 3],
        15 => ['keywords' => ['langage C', 'programmation structurée', 'variables'], 'weight' => 2],
        16 => ['keywords' => ['paysage informatique', 'cloud storage', 'data centers'], 'weight' => 2],
        17 => ['keywords' => ['Développement Agile', 'scrum', 'sprints'], 'weight' => 3],
        18 => ['keywords' => ['certifications', 'comptabilité', 'compliance'], 'weight' => 1],
        19 => ['keywords' => ['gestion projet', 'planification', 'timelines'], 'weight' => 2],
        20 => ['keywords' => ['python', 'programmation orientée objet', 'réseaux'], 'weight' => 3],
    ];

    $compatibility_score = 0;

    foreach ($answers as $index => $answer) {
        $isCompatible = false;
        foreach ($criteria[$index + 1]['keywords'] as $keyword) {
            if (stripos($answer, $keyword) !== false) {
                $isCompatible = true;
                break;
            }
        }

        if ($isCompatible) {
            $compatibility_score += $criteria[$index + 1]['weight'];
        }
    }

    return $compatibility_score;
}

// Liste des questions
$questions = [
    "1. Vous avez 120 pièces identiques. Si vous les empilez en colonnes de 4, combien de colonnes obtiendrez-vous ?",
    "2. Quelle est la suite logique de cette série : 1, 4, 9, 16, 25 ? Quel est le prochain nombre ?",
    "3. Deux personnes doivent traverser un pont la nuit, avec seulement deux lampes. Le pont peut supporter au maximum deux personnes à la fois, et chaque personne prend le temps de traverser en fonction de sa propre vitesse. Personne 1 prend 1 minute pour traverser, et la personne 2 prend 2 minutes. Combien de temps faut-il pour que les deux personnes traversent le pont ?",
    "4. Un cube est peint en rouge sur toutes ses faces, puis il est découpé en 27 petits cubes. Combien de petits cubes ont au moins une face peinte en rouge ?",
    "5. Vous travaillez dans le secteur des données et vous êtes responsable de la gestion des bases de données. Qu'est-ce que vous pouvez faire pour garantir une gestion efficace des bases de données et un environnement sécurisé dans un cloud computing ?",
    "6. Vous êtes confronté à un problème sur un système d'exploitation multitâche. Comment gérez-vous un programme qui entre en conflit avec un autre programme exécuté simultanément ?",
    "7. Résolvez cet exercice d'algèbre : (x + 2)(x - 4) = 0. Quelles sont les solutions ?",
    "8. Quelle est la meilleure méthode pour gérer les ressources humaines dans une entreprise informatique, en particulier lors d'une entrevue pour un poste en développement logiciel ?",
    "9. Vous êtes responsable de la sécurité informatique dans une entreprise. Quelle mesure prendriez-vous pour vous assurer que les données sensibles sont protégées contre les attaques potentielles ?",
    "10. Expliquez comment les services en cloud peuvent optimiser les ressources d'une entreprise et améliorer l'efficacité du travail des équipes ?",
    "11. En tant qu'administrateur réseau, vous devez résoudre un problème de transfert de données via HTTP. Quels outils utiliseriez-vous pour diagnostiquer et résoudre ce problème ?",
    "12. Quel est votre niveau d'expérience avec le développement web en PHP, en particulier dans la création d'applications back-end sécurisées ?",
    "13. Imaginez que vous avez un problème avec un algorithme de tri dans une application. Comment mesureriez-vous la complexité de cet algorithme pour identifier s'il est optimisé ?",
    "14. En tant que développeur, vous devez choisir entre plusieurs architectures logicielles pour un projet. Quels critères utiliseriez-vous pour choisir la meilleure solution ?",
    "15. Expliquez les principaux concepts du langage C, en particulier les variables et les structures de contrôle dans le cadre d'un projet informatique ?",
    "16. Quel est votre avis sur l'infrastructure informatique basée sur le cloud, en particulier pour la gestion des data centers et le stockage à grande échelle ?",
    "17. Que savez-vous de la méthodologie de développement Agile ? Comment implémentez-vous des projets en utilisant Scrum et les sprints ?",
    "18. Avez-vous des certifications professionnelles dans le domaine de l'informatique ? Comment ces certifications peuvent-elles influencer votre manière de gérer des projets IT ?",
    "19. Comment gérez-vous un projet informatique du début à la fin, notamment la planification et la gestion des timelines ?",
    "20. Quel est votre niveau de compétence en Python ? Avez-vous déjà utilisé Python pour des projets de programmation orientée objet et de gestion des réseaux ?"
];

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = 0;
    $answers = [];

    // Collecter les réponses de l'utilisateur
    for ($i = 1; $i <= count($questions); $i++) {
        if (isset($_POST["answer$i"])) {
            $answers[] = $_POST["answer$i"];
        }
    }

    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    // Calculer la compatibilité
    $score = analyzeAnswers($answers);

    // Vérifiez si l'utilisateur est connecté et récupérez son ID
    $candidat_id = $_SESSION['user_id']; // Utilisation de l'ID de l'utilisateur dans la session


    // Insérer le score dans la table compatibilite
    $stmt = $pdo->prepare("INSERT INTO compatibilite (candidat_id, compatibilite, middle_name, last_name, email) VALUES (:candidat_id, :compatibilite, :middle_name, :last_name, :email)");
    $stmt->bindParam(':candidat_id', $candidat_id);
    $stmt->bindParam(':compatibilite', $score);
    $stmt->bindParam(':middle_name', $middle_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);


    if ($stmt->execute()) {
        echo "Votre score de compatibilité a été enregistré avec succès.";
    } else {
        echo "Erreur lors de l'enregistrement du score.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Test Psycho-Technique et Culturel Dynamique</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .question {
            margin-bottom: 20px;
        }

        .question p {
            font-size: 18px;
            color: #333;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 900px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <div class="retour" style="margin-bottom: 20px;">
            <a href="accueilUser.php" class="btn btn-secondary mb-4">Retourner à l'accueil</a>
        </div>

        <form method="POST" action="">
            <div class="form-group mb-3">
                <label for="middle_name">Prénoms :</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" required>
            </div>

            <div class="form-group mb-3">
                <label for="last_name">Nom :</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>

            <div class="form-group mb-3">
                <label for="email">Adresse e-mail :</label>
                <input type="text" class="form-control" id="email" name="email" required>
            </div>

            <?php foreach ($questions as $index => $question) : ?>
                <div class="question">
                    <p><?= htmlspecialchars($question) ?></p>
                    <textarea class="form-control mb-3" name="answer<?= $index + 1 ?>" rows="3"></textarea>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="submit-btn">Soumettre</button>
        </form>
    </div>
</body>
<div>
    <br>
</div>
<footer class="footer bg-light text-center py-3">
    <p>© 2024 IT-Corporation. Tous droits réservés.</p>
</footer>

</html>