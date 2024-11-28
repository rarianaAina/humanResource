<?php
// Paramètres de connexion à la base de données
$host = "localhost";
$username = "rariana";
$password = "rariana";
$database = "orangehrm";

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $database);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if (isset($_POST['vacancy_id']) && isset($_POST['vacancy_name'])) {
    $vacancy_name = (string)$_POST['vacancy_name'];
    $vacancy_id = (int)$_POST['vacancy_id'];
    echo "ID de poste: " . $vacancy_id; 
    echo "Nom du poste: " . $vacancy_name; 

} else {
    echo "Erreur : aucun ID de vacance trouvé.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    $prenom = htmlspecialchars($_POST['prenom']);
    $deuxieme_prenom = htmlspecialchars($_POST['deuxieme_prenom']);
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $contact = htmlspecialchars($_POST['contact']);
    $mots_cles = htmlspecialchars($_POST['mots_cles']);
    $commentaires = htmlspecialchars($_POST['commentaires']);
    $action = 17; 

    $cv = $_FILES['cv'];
    $cv_err = "";
    $cv_file_path = null;

    if ($cv['error'] == 0) {
        $file_ext = pathinfo($cv['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['pdf'];

        if (!in_array(strtolower($file_ext), $allowed_extensions)) {
            $cv_err = "Seuls les fichiers PDF sont autorisés.";
        }

        if ($cv['size'] > 1048576) {
            $cv_err = "Le fichier ne doit pas dépasser 1 Mo.";
        }

        if (empty($cv_err)) {
            $cv_file_path = 'C:/uploads/' . time() . '-' . basename($cv['name']);
            move_uploaded_file($cv['tmp_name'], $cv_file_path);
        }
    }

    if (empty($cv_err)) {
 
        $sql = "INSERT INTO ohrm_job_candidate 
                (first_name, middle_name, last_name, email, contact_number, status, comment, 
                 mode_of_application, date_of_application, cv_file_id, cv_text_version, keywords, consent_to_keep_data)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        

        $date_of_application = date('Y-m-d');
        $status = 1;
        $mode_of_application = 1;
        $cv_file_id = 0;
        $cv_text_version = null;
        $consent_to_keep_data = 0;

        $stmt->bind_param("ssssissssisss", $prenom, $deuxieme_prenom, $nom, $email, $contact, 
                          $status, $commentaires, $mode_of_application, $date_of_application, 
                          $cv_file_id, $cv_text_version, $mots_cles, $consent_to_keep_data);
        
        if ($stmt->execute()) {
            $candidate_id = $stmt->insert_id;  // Récupérer l'ID du candidat inséré

            // Insertion dans la table ohrm_job_candidate_history
            $history_sql = "INSERT INTO ohrm_job_candidate_history 
                            (candidate_id, vacancy_id, candidate_vacancy_name, action, performed_date)
                            VALUES (?, ?, ?, ?, ?)";
            $history_stmt = $conn->prepare($history_sql);
            $vacancy_name = "Nom de l'offre";  // À remplacer par le nom réel de l'offre, peut être récupéré avec une autre requête SQL si nécessaire
            $performed_date = date('Y-m-d H:i:s'); // Date et heure de l'action

            // Exécution de la requête pour insérer dans l'historique
            $history_stmt->bind_param("iisss", $candidate_id, $vacancy_id, $vacancy_name, $action, $performed_date);
            if ($history_stmt->execute()) {
                echo "Candidature soumise avec succès!";
            } else {
                echo "Erreur lors de l'insertion dans l'historique: " . $history_stmt->error;
            }

            // Fermeture de la requête pour l'historique
            $history_stmt->close();

            // Insérer dans la table ohrm_job_candidate_vacancy
            $status = 'APPLICATION INITIATED';  // Par défaut, selon la table
            $applied_date = date('Y-m-d'); // Date de la candidature

            $vacancy_sql = "INSERT INTO ohrm_job_candidate_vacancy 
                            (candidate_id, vacancy_id, status, applied_date)
                            VALUES (?, ?, ?, ?)";
            $vacancy_stmt = $conn->prepare($vacancy_sql);
            $vacancy_stmt->bind_param("iiss", $candidate_id, $vacancy_id, $status, $applied_date);

            if ($vacancy_stmt->execute()) {
                echo "Candidature ajoutée dans ohrm_job_candidate_vacancy!";
            } else {
                echo "Erreur lors de l'ajout dans ohrm_job_candidate_vacancy: " . $vacancy_stmt->error;
            }
            
            // Fermeture de la requête pour l'insertion dans ohrm_job_candidate_vacancy
            $vacancy_stmt->close();

            // Insertion dans la table ohrm_job_candidate_attachment
            if ($cv['error'] == 0) {
                $file_name = basename($cv['name']);
                $file_type = $cv['type'];
                $file_size = $cv['size'];
                $file_content = file_get_contents($cv['tmp_name']);
                $attachment_type = 1; // Vous pouvez définir l'ID de type d'attachement selon votre logique

                $attachment_sql = "INSERT INTO ohrm_job_candidate_attachment
                                (candidate_id, file_name, file_type, file_size, file_content, attachment_type)
                                VALUES (?, ?, ?, ?, ?, ?)";
                $attachment_stmt = $conn->prepare($attachment_sql);
                $attachment_stmt->bind_param("isssbi", $candidate_id, $file_name, $file_type, $file_size, $file_content, $attachment_type);

                if ($attachment_stmt->execute()) {
                    echo "Fichier attaché avec succès!";
                } else {
                    echo "Erreur lors de l'ajout du fichier dans ohrm_job_candidate_attachment: " . $attachment_stmt->error;
                }
                
                // Fermeture de la requête pour l'insertion dans ohrm_job_candidate_attachment
                $attachment_stmt->close();
            }

        } else {
            echo "Erreur lors de l'insertion du candidat: " . $stmt->error;
        }
    } else {
        echo "<div class='error'>$cv_err</div>";
    }
    
    // Fermeture de la requête et de la connexion
    $stmt->close();
}

// Fermeture de la connexion à la base de données
$conn->close();
?>
