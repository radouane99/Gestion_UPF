<?php
// admin/etudiants/upload_document.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../liste.php');
    exit();
}

$etudiant_id = $_POST['etudiant_id'] ?? '';
$type_doc = $_POST['type_doc'] ?? 'autre';

if (empty($etudiant_id) || !isset($_FILES['document'])) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Paramètres manquants"
    ];
    header('Location: detail.php?code=' . $etudiant_id);
    exit();
}

$pdo = getConnexion();

// Vérifier que l'étudiant existe
$stmt = $pdo->prepare("SELECT Code, Nom, Prenom FROM etudiants WHERE Code = ?");
$stmt->execute([$etudiant_id]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Étudiant introuvable"
    ];
    header('Location: ../liste.php');
    exit();
}

// Configuration upload
$upload_dir = '../../uploads/documents/';
$max_size = 5 * 1024 * 1024; // 5 Mo
$allowed_types = ['application/pdf'];
$allowed_extensions = ['pdf'];

$file = $_FILES['document'];
$errors = [];

// 1. Vérifier erreur upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Erreur lors de l'upload (Code: " . $file['error'] . ")";
}

// 2. Vérifier taille
if ($file['size'] > $max_size) {
    $errors[] = "Le fichier est trop volumineux (max 5 Mo)";
}

// 3. Vérifier type MIME réel
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    $errors[] = "Type de fichier non autorisé. Seuls les PDF sont acceptés";
}

// 4. Vérifier extension
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowed_extensions)) {
    $errors[] = "Extension non autorisée. Utilisez .pdf";
}

if (empty($errors)) {
    try {
        $pdo->beginTransaction();

        // Générer nom unique
        $timestamp = time();
        $new_filename = 'doc_' . $etudiant_id . '_' . $timestamp . '.' . $extension;
        $destination = $upload_dir . $new_filename;
        $relative_path = 'uploads/documents/' . $new_filename;

        // Créer dossier s'il n'existe pas
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Enregistrer dans la BDD
            $sql = "INSERT INTO documents (etudiant_id, type_doc, nom_fichier, chemin, taille, mime_type, uploaded_by, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $etudiant_id,
                $type_doc,
                $file['name'],
                $relative_path,
                $file['size'],
                $mime_type,
                $_SESSION['user_id']
            ]);

            $pdo->commit();

            // Log
            error_log("Admin {$_SESSION['login']} a uploadé un document pour {$etudiant['Prenom']} {$etudiant['Nom']}");

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => "✅ Document uploadé avec succès !"
            ];
        } else {
            throw new Exception("Erreur lors de la sauvegarde du fichier");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur : " . $e->getMessage()
        ];
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
}

header('Location: detail.php?code=' . $etudiant_id);
exit();
?>