<?php
// admin/etudiants/ajouter_traitement.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ajouter.php');
    exit();
}

$pdo = getConnexion();

// Récupération des données
$code = trim($_POST['code'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$filiere = !empty($_POST['filiere']) ? $_POST['filiere'] : null;
$note = !empty($_POST['note']) ? floatval($_POST['note']) : null;
$date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
$email = !empty($_POST['email']) ? trim($_POST['email']) : null;
$telephone = !empty($_POST['telephone']) ? trim($_POST['telephone']) : null;
$create_account = isset($_POST['create_account']);

// Validation
$errors = [];

// Code
if (empty($code)) {
    $errors[] = "Le code est requis";
} elseif (!preg_match('/^E[0-9]{3}$/', $code)) {
    $errors[] = "Le code doit être au format E001, E002, etc.";
}

// Nom/Prénom
if (empty($nom)) $errors[] = "Le nom est requis";
if (empty($prenom)) $errors[] = "Le prénom est requis";

// Email (si fourni)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide";
}

// Téléphone (si fourni)
if (!empty($telephone) && !preg_match('/^[0-9]{10}$/', $telephone)) {
    $errors[] = "Le téléphone doit contenir 10 chiffres";
}

// Note (si fournie)
if ($note !== null && ($note < 0 || $note > 20)) {
    $errors[] = "La note doit être comprise entre 0 et 20";
}

if (empty($errors)) {
    try {
        $pdo->beginTransaction();

        // Vérifier code unique
        $stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE Code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            throw new Exception("Ce code étudiant existe déjà");
        }

        // Vérifier email unique (si fourni)
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("Cet email est déjà utilisé");
            }
        }

        // Upload photo
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadPhoto($_FILES['photo'], $code);
            if ($upload_result['success']) {
                $photo_path = $upload_result['path'];
            } else {
                throw new Exception($upload_result['error']);
            }
        }

        // Insertion étudiant
        $sql = "INSERT INTO etudiants (Code, Nom, Prenom, Filiere, FNote, date_naissance, email, telephone, Photo, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code, $nom, $prenom, $filiere, $note, $date_naissance, $email, $telephone, $photo_path]);

        // Création compte utilisateur si demandé
        $account_info = null;
        if ($create_account) {
            // Générer login (prenom.nom)
            $login = generateLogin($nom, $prenom);
            
            // Générer mot de passe aléatoire
            $password = generateRandomPassword(8);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Vérifier si login existe déjà
            $original_login = $login;
            $i = 1;
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
            while (true) {
                $stmt->execute([$login]);
                if (!$stmt->fetch()) break;
                $login = $original_login . $i;
                $i++;
            }

            // Insérer utilisateur
            $sql = "INSERT INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                    VALUES (?, ?, 'user', ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$login, $hashedPassword, $code]);

            $account_info = [
                'login' => $login,
                'password' => $password
            ];

            // Envoyer email si adresse fournie
            if (!empty($email)) {
                $subject = "Création de votre compte - UPF Gestion";
                $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { background: linear-gradient(135deg, #294898, #C72C82); color: white; padding: 20px; }
                        .content { padding: 20px; }
                        .credentials { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>Université Privée de Fès</h2>
                    </div>
                    <div class='content'>
                        <h3>Bonjour $prenom $nom,</h3>
                        <p>Votre compte étudiant a été créé sur l'application de gestion UPF.</p>
                        
                        <div class='credentials'>
                            <p><strong>Code étudiant :</strong> $code</p>
                            <p><strong>Login :</strong> $login</p>
                            <p><strong>Mot de passe :</strong> $password</p>
                        </div>
                        
                        <p><strong>Lien de connexion :</strong> <a href='http://localhost/Gestion%20UPF/login.php'>Cliquez ici</a></p>
                        
                        <p style='color: orange;'><strong>⚠️ Important :</strong> Changez votre mot de passe après votre première connexion.</p>
                        
                        <p>Cordialement,<br>L'équipe administrative</p>
                    </div>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: noreply@upf.ac.ma\r\n";

                mail($email, $subject, $message, $headers);
            }
        }

        $pdo->commit();

        // Message de succès
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Étudiant ajouté avec succès !"
        ];

        if ($account_info) {
            $_SESSION['flash']['message'] .= "<br>🔐 Compte créé - Login: <strong>{$account_info['login']}</strong> | Mot de passe: <strong>{$account_info['password']}</strong>";
            if (!empty($email)) {
                $_SESSION['flash']['message'] .= "<br>📧 Email envoyé à $email";
            }
        }

        header('Location: liste.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur : " . $e->getMessage()
        ];
        header('Location: ajouter.php');
        exit();
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
    header('Location: ajouter.php');
    exit();
}

// Fonction upload photo
function uploadPhoto($file, $code) {
    $upload_dir = '../../uploads/photos/';
    $max_size = 2 * 1024 * 1024; // 2 Mo
    $allowed_types = ['image/jpeg', 'image/png'];
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    // Vérifier erreur
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => "Erreur upload"];
    }

    // Vérifier taille
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => "Fichier trop volumineux (max 2 Mo)"];
    }

    // Vérifier type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => "Type de fichier non autorisé"];
    }

    // Vérifier extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'error' => "Extension non autorisée"];
    }

    // Générer nom unique
    $new_filename = 'photo_' . $code . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;
    $relative_path = 'uploads/photos/' . $new_filename;

    // Déplacer fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'path' => $relative_path];
    } else {
        return ['success' => false, 'error' => "Erreur sauvegarde fichier"];
    }
}
?>