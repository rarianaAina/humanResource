<?php
session_start();

$vacancy_id = $_GET['vacancy_id'] ?? null;
$vacancy_name = $_GET['vacancy_name'] ?? null;


// Temps de restriction (5 minutes)
$time_limit = 5 * 60; // 5 minutes en secondes

// Vérification si l'utilisateur a déjà passé un test
// if (isset($_SESSION['last_test_time']) && (time() - $_SESSION['last_test_time']) < $time_limit) {
//     $remaining_time = $time_limit - (time() - $_SESSION['last_test_time']);
//     $message = "Vous devez attendre " . round($remaining_time / 60) . " minutes avant de passer un autre test.";
//     echo "<script>alert('$message'); window.history.back();</script>";
//     exit();
// }
$questions = [
    [
        "question" => "Que signifie 'PHP' ?",
        "choices" => [
            "A" => "Private Home Page",
            "B" => "Personal Hypertext Processor",
            "C" => "PHP: Hypertext Preprocessor",
            "D" => "Pretext Hypertext Processor"
        ],
        "answer" => "C"
    ],
    [
        "question" => "Quel opérateur est utilisé pour concaténer des chaînes en PHP ?",
        "choices" => [
            "A" => "+",
            "B" => ".",
            "C" => "&",
            "D" => "-"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Quelle méthode est utilisée pour envoyer des données avec la méthode POST ?",
        "choices" => [
            "A" => "Dollar Get",
            "B" => "Dollar Post",
            "C" => "file_get_contents()",
            "D" => "header()"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Quelle version de PHP introduit les types scalaires stricts ?",
        "choices" => [
            "A" => "PHP 5.6",
            "B" => "PHP 7.0",
            "C" => "PHP 7.2",
            "D" => "PHP 8.0"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Quel est le rôle de la fonction 'var_dump()' en PHP ?",
        "choices" => [
            "A" => "Afficher une variable sous forme lisible",
            "B" => "Retourner une valeur numérique",
            "C" => "Afficher des informations sur une variable",
            "D" => "Changer la valeur d'une variable"
        ],
        "answer" => "C"
    ],
    [
        "question" => "Quelle est la portée d'une variable déclarée avec 'global' ?",
        "choices" => [
            "A" => "Elle est accessible uniquement dans la fonction",
            "B" => "Elle est accessible dans toute la page",
            "C" => "Elle est accessible uniquement dans le même fichier",
            "D" => "Elle est accessible dans tous les fichiers de l'application"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Que fait la fonction 'include()' en PHP ?",
        "choices" => [
            "A" => "Exécute un fichier externe",
            "B" => "Inclut le contenu d'un fichier dans un autre fichier",
            "C" => "Exécute une fonction dans un fichier",
            "D" => "Inclut un fichier CSS"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Quel type de données représente 'null' en PHP ?",
        "choices" => [
            "A" => "Un entier",
            "B" => "Une chaîne",
            "C" => "Un objet",
            "D" => "Une valeur spéciale représentant l'absence de valeur"
        ],
        "answer" => "D"
    ],
    [
        "question" => "Qu'est-ce qu'une fonction anonyme en PHP ?",
        "choices" => [
            "A" => "Une fonction sans nom qui peut être assignée à une variable",
            "B" => "Une fonction qui ne retourne pas de valeur",
            "C" => "Une fonction qui n'est pas utilisée dans le code",
            "D" => "Une fonction sans paramètres"
        ],
        "answer" => "A"
    ],
    [
        "question" => "Quelle fonction PHP est utilisée pour envoyer un e-mail ?",
        "choices" => [
            "A" => "send_email()",
            "B" => "mail()",
            "C" => "send_mail()",
            "D" => "email()"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Quel est l'opérateur de comparaison pour tester l'égalité stricte en PHP ?",
        "choices" => [
            "A" => "==",
            "B" => "===",
            "C" => "=",
            "D" => "<>"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Comment inclure un fichier PHP uniquement s'il existe ?",
        "choices" => [
            "A" => "include_if_exists()",
            "B" => "require_once()",
            "C" => "include_once()",
            "D" => "include()"
        ],
        "answer" => "D"
    ],
    [
        "question" => "Comment créer une fonction PHP qui retourne un tableau ?",
        "choices" => [
            "A" => "function myFunction() { return array(); }",
            "B" => "function myFunction() { return []; }",
            "C" => "function myFunction() { array(); }",
            "D" => "function myFunction() { return array(1,2,3); }"
        ],
        "answer" => "A"
    ],
    [
        "question" => "Que fait la fonction 'explode()' en PHP ?",
        "choices" => [
            "A" => "Divise une chaîne en un tableau",
            "B" => "Concatène deux chaînes",
            "C" => "Réunit les éléments d'un tableau en une chaîne",
            "D" => "Réduit la taille d'un tableau"
        ],
        "answer" => "A"
    ],
    [
        "question" => "Comment connecter une base de données MySQL en PHP ?",
        "choices" => [
            "A" => "mysql_connect()",
            "B" => "mysqli_connect()",
            "C" => "db_connect()",
            "D" => "connect_db()"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Comment déclarer une constante en PHP ?",
        "choices" => [
            "A" => "const NOM = 'valeur';",
            "B" => "define('NOM', 'valeur');",
            "C" => "define(NOM, 'valeur');",
            "D" => "const('NOM', 'valeur');"
        ],
        "answer" => "B"
    ],
    [
        "question" => "Comment créer une classe en PHP ?",
        "choices" => [
            "A" => "class MyClass { }",
            "B" => "class MyClass() { }",
            "C" => "class MyClass : { }",
            "D" => "MyClass class { }"
        ],
        "answer" => "A"
    ],
    [
        "question" => "Qu'est-ce que l'héritage en PHP ?",
        "choices" => [
            "A" => "Quand une classe hérite des méthodes et propriétés d'une autre classe",
            "B" => "Quand une classe hérite des variables globales",
            "C" => "Quand une classe hérite des variables statiques",
            "D" => "Quand une classe hérite des fonctions"
        ],
        "answer" => "A"
    ],
    [
        "question" => "Quel est le rôle du mot-clé 'static' en PHP ?",
        "choices" => [
            "A" => "Déclarer une fonction statique",
            "B" => "Déclarer une variable qui appartient à la classe et non à une instance",
            "C" => "Déclarer une constante",
            "D" => "Créer une instance de classe"
        ],
        "answer" => "B"
    ]
];


$randomQuestionsKeys = array_rand($questions, 4);

$selectedQuestions = [];
foreach ($randomQuestionsKeys as $key) {
    $selectedQuestions[] = $questions[$key];
}

$totalQuestions = count($questions);
$score = 0;

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($selectedQuestions as $index => $question) {
        $userAnswer = $_POST["q" . $index] ?? null;
        if ($userAnswer === $question["answer"]) {
            $score++;
        }
    }
    $_SESSION['score'] = $score;
    $_SESSION['totalQuestions'] = count($selectedQuestions);
    $_SESSION['vacancy_id'] = $vacancy_id;  
    $_SESSION['vacancy_name'] = $vacancy_name;
    $_SESSION['last_test_time'] = time();
    

    $conn = new mysqli("localhost", "rariana", "rariana", "orangehrm"); 
    
    if ($conn->connect_error) {
        die("La connexion échouée : " . $conn->connect_error);
    }
    

    $candidat_id = $_SESSION['user_id'] ?? 0; 
    

    $stmt = $conn->prepare("INSERT INTO compatibilite (candidat_id, compatibilite) VALUES (?, ?)");
    $stmt->bind_param("ii", $candidat_id, $score);
    
    if ($stmt->execute()) {

        header("Location: result.php");
        exit();
    } else {
        echo "Erreur : " . $stmt->error;
    }


    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Test Technique PHP</title>
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

        .choices label {
            display: block;
            margin-bottom: 5px;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="retour">
        <a href="accueil.php" class="btn btn-secondary mb-4">Retourner à l'accueil</a>
    </div>
    <div class="container">
    <h1>Test Technique PHP</h1>
    <form method="POST">
        <?php foreach ($selectedQuestions as $index => $question) : ?>
            <div class="question">
                <p><strong>Question <?php echo $index + 1; ?>:</strong> <?php echo $question["question"]; ?></p>
                <div class="choices">
                    <?php foreach ($question["choices"] as $key => $choice) : ?>
                        <label>
                            <input type="radio" name="q<?php echo $index; ?>" value="<?php echo $key; ?>" required> <?php echo $choice; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="submit-btn">Soumettre</button>
    </form>
</div></body>

</html>