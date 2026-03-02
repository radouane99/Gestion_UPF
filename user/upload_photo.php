<?php
// user/upload_photo.php
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
    header('Location: profil.php');
    exit();
}

$pdo = getConnexion();

// Récupérer l'étudiant
$stmt = $pdo->prepare("SELECT Photo FROM etudiants WHERE Code = ?");
$stmt->execute([$_SESSION['etudiant_id']]);
$etudiant = $stmt->fetch();

// Configuration upload
$upload_dir = '../uploads/photos/';
$max_size = 2 * 1024 * 1024; // 2 Mo
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
$allowed_extensions = ['jpg', 'jpeg', 'png'];

$file = $_FILES['photo'];
$errors = [];

// 1. Vérifier erreur upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Erreur lors de l'upload (Code: " . $file['error'] . ")";
}

// 2. Vérifier taille
if ($file['size'] > $max_size) {
    $errors[] = "Le fichier est trop volumineux (max 2 Mo)";
}

// 3. Vérifier type MIME réel
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    $errors[] = "Type de fichier non autorisé. Utilisez JPG ou PNG";
}

// 4. Vérifier extension
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowed_extensions)) {
    $errors[] = "Extension non autorisée";
}

if (empty($errors)) {
    // Créer dossier si besoin
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Générer nom unique
    $new_filename = 'photo_' . $_SESSION['etudiant_id'] . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;
    $relative_path = 'uploads/photos/' . $new_filename;

    // Supprimer ancienne photo si existe
    if (!empty($etudiant['Photo']) && file_exists('../' . $etudiant['Photo'])) {
        unlink('../' . $etudiant['Photo']);
    }

    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Mettre à jour BDD
        $stmt = $pdo->prepare("UPDATE etudiants SET Photo = ? WHERE Code = ?");
        $stmt->execute([$relative_path, $_SESSION['etudiant_id']]);
        
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Photo de profil mise à jour avec succès"
        ];
    } else {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur lors de l'enregistrement du fichier"
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