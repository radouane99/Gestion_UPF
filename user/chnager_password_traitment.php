<?php
// user/changer_password_traitement.php
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: changer_password.php');
    exit();
}

$pdo = getConnexion();

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

// Validation
if (empty($current_password)) {
    $errors[] = "Le mot de passe actuel est requis";
}

if (empty($new_password)) {
    $errors[] = "Le nouveau mot de passe est requis";
} elseif (strlen($new_password) < 8) {
    $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères";
} elseif (!preg_match('/[A-Z]/', $new_password)) {
    $errors[] = "Le nouveau mot de passe doit contenir au moins une majuscule";
} elseif (!preg_match('/[a-z]/', $new_password)) {
    $errors[] = "Le nouveau mot de passe doit contenir au moins une minuscule";
} elseif (!preg_match('/[0-9]/', $new_password)) {
    $errors[] = "Le nouveau mot de passe doit contenir au moins un chiffre";
}

if ($new_password !== $confirm_password) {
    $errors[] = "Les nouveaux mots de passe ne correspondent pas";
}

if ($new_password === $current_password) {
    $errors[] = "Le nouveau mot de passe doit être différent de l'ancien";
}

if (empty($errors)) {
    try {
        // Récupérer le mot de passe actuel
        $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE etudiant_id = ?");
        $stmt->execute([$_SESSION['etudiant_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Utilisateur introuvable");
        }

        // Vérifier l'ancien mot de passe
        if (!password_verify($current_password, $user['password'])) {
            throw new Exception("Le mot de passe actuel est incorrect");
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        // Mettre à jour
        $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE etudiant_id = ?");
        $stmt->execute([$hashedPassword, $_SESSION['etudiant_id']]);

        // Log
        error_log("Utilisateur {$_SESSION['etudiant_id']} a changé son mot de passe");

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Mot de passe changé avec succès !"
        ];

        header('Location: profil.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ " . $e->getMessage()
        ];
        header('Location: changer_password.php');
        exit();
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
    header('Location: changer_password.php');
    exit();
}
?>