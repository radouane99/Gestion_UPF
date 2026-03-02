<?php
// admin/etudiants/modifier_traitement.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

$original_code = $_POST['original_code'] ?? '';
$new_code = trim($_POST['code'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$filiere = !empty($_POST['filiere']) ? $_POST['filiere'] : null;
$note = !empty($_POST['note']) ? floatval($_POST['note']) : null;
$date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
$email = !empty($_POST['email']) ? trim($_POST['email']) : null;
$telephone = !empty($_POST['telephone']) ? trim($_POST['telephone']) : null;

// Validation
$errors = [];

if (empty($original_code)) {
    $errors[] = "Code original manquant";
}

if (empty($nom)) $errors[] = "Le nom est requis";
if (empty($prenom)) $errors[] = "Le prénom est requis";

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide";
}

if (!empty($telephone) && !preg_match('/^[0-9]{10}$/', $telephone)) {
    $errors[] = "Le téléphone doit contenir 10 chiffres";
}

if ($note !== null && ($note < 0 || $note > 20)) {
    $errors[] = "La note doit être comprise entre 0 et 20";
}

if (empty($errors)) {
    try {
        $pdo->beginTransaction();

        // Vérifier si l'étudiant existe
        $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE Code = ?");
        $stmt->execute([$original_code]);
        $etudiant = $stmt->fetch();

        if (!$etudiant) {
            throw new Exception("Étudiant introuvable");
        }

        // Vérifier si l'étudiant a un compte utilisateur
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE etudiant_id = ?");
        $stmt->execute([$original_code]);
        $hasAccount = $stmt->fetch();

        // Si le code change et que l'étudiant a un compte, erreur
        if ($new_code !== $original_code && $hasAccount) {
            throw new Exception("Impossible de modifier le code car un compte utilisateur existe");
        }

        // Si le code change, vérifier qu'il est unique
        if ($new_code !== $original_code) {
            $stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE Code = ?");
            $stmt->execute([$new_code]);
            if ($stmt->fetch()) {
                throw new Exception("Le nouveau code $new_code est déjà utilisé");
            }
        }

        // Si email change, vérifier qu'il est unique
        if (!empty($email) && $email !== $etudiant['email']) {
            $stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE email = ? AND Code != ?");
            $stmt->execute([$email, $original_code]);
            if ($stmt->fetch()) {
                throw new Exception("Cet email est déjà utilisé par un autre étudiant");
            }
        }

        // Upload nouvelle photo si fournie
        $photo_path = $etudiant['Photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Supprimer ancienne photo
            if (!empty($etudiant['Photo']) && file_exists('../../' . $etudiant['Photo'])) {
                unlink('../../' . $etudiant['Photo']);
            }
            
            $upload_result = uploadPhoto($_FILES['photo'], $new_code);
            if ($upload_result['success']) {
                $photo_path = $upload_result['path'];
            } else {
                throw new Exception($upload_result['error']);
            }
        }

        // Mise à jour
        $sql = "UPDATE etudiants SET 
                Code = ?,
                Nom = ?,
                Prenom = ?,
                Filiere = ?,
                FNote = ?,
                date_naissance = ?,
                email = ?,
                telephone = ?,
                Photo = ?
                WHERE Code = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $new_code,
            $nom,
            $prenom,
            $filiere,
            $note,
            $date_naissance,
            $email,
            $telephone,
            $photo_path,
            $original_code
        ]);

        // Si le code a changé et que l'étudiant a un compte, mettre à jour la liaison
        if ($new_code !== $original_code && $hasAccount) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET etudiant_id = ? WHERE etudiant_id = ?");
            $stmt->execute([$new_code, $original_code]);
        }

        $pdo->commit();

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Étudiant modifié avec succès !"
        ];

        header('Location: liste.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur : " . $e->getMessage()
        ];
        header('Location: modifier.php?code=' . $original_code);
        exit();
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
    header('Location: modifier.php?code=' . $original_code);
    exit();
}

function uploadPhoto($file, $code) {
    $upload_dir = '../../uploads/photos/';
    $max_size = 2 * 1024 * 1024;
    $allowed_types = ['image/jpeg', 'image/png'];
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => "Erreur upload"];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => "Fichier trop volumineux (max 2 Mo)"];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => "Type de fichier non autorisé"];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'error' => "Extension non autorisée"];
    }

    $new_filename = 'photo_' . $code . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;
    $relative_path = 'uploads/photos/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'path' => $relative_path];
    } else {
        return ['success' => false, 'error' => "Erreur sauvegarde fichier"];
    }
}
?>