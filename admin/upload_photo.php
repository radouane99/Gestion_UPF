<?php
// admin/upload_photo.php
require_once '../includes/auth_check_admin.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
    header('Location: profil.php');
    exit();
}

$pdo = getConnexion();

// Récupérer l'admin connecté
$stmt = $pdo->prepare("SELECT photo FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// Configuration upload
$upload_dir = '../uploads/photos/';
$max_size = 2 * 1024 * 1024; // 2 Mo
$allowed_types = ['image/jpeg', 'image/png'];
$allowed_extensions = ['jpg', 'jpeg', 'png'];

$file = $_FILES['photo'];
$errors = [];

// 1. Vérifier s'il n'y a pas d'erreur d'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    // Gérer les différentes erreurs possibles
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errors[] = "Le fichier dépasse la taille maximale autorisée (2 Mo).";
            break;
        case UPLOAD_ERR_PARTIAL:
            $errors[] = "Le fichier n'a été que partiellement téléchargé.";
            break;
        case UPLOAD_ERR_NO_FILE:
            $errors[] = "Aucun fichier sélectionné.";
            break;
        default:
            $errors[] = "Erreur inconnue lors de l'upload.";
    }
} else {
    // 2. Vérifier la taille (en plus de la vérification serveur)
    if ($file['size'] > $max_size) {
        $errors[] = "Le fichier est trop volumineux (max 2 Mo).";
    }

    // 3. Vérifier l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        $errors[] = "Extension non autorisée. Utilisez JPG, JPEG ou PNG.";
    }

    // 4. Vérifier le type MIME réel (côté serveur)
    if (class_exists('finfo')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file['tmp_name']);
    } else {
        $mime_type = $file['type']; // fallback (moins fiable)
    }

    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Type de fichier non autorisé. Seuls JPG et PNG sont acceptés.";
    }
}

if (empty($errors)) {
    // Créer le dossier s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Générer un nom unique
    $new_filename = 'admin_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;
    $relative_path = 'uploads/photos/' . $new_filename;

    // Supprimer l'ancienne photo si elle existe
    if (!empty($admin['photo']) && file_exists('../' . $admin['photo'])) {
        unlink('../' . $admin['photo']);
    }

    // 5. Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // 6. Mettre à jour la base de données
        $stmt = $pdo->prepare("UPDATE utilisateurs SET photo = ? WHERE id = ?");
        $stmt->execute([$relative_path, $_SESSION['user_id']]);

        // Mettre à jour la session
        $_SESSION['photo'] = $relative_path;

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Photo de profil mise à jour avec succès."
        ];
    } else {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur lors de l'enregistrement du fichier."
        ];
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
}

header('Location: profil.php');
exit();
?>