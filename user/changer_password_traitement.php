<?php
// user/changer_password_traitement.php
// On utilise un fichier de vérification commun qui vérifie simplement que l'utilisateur est connecté
require_once '../includes/auth_check.php'; // À créer (ou on peut vérifier manuellement)
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

if (empty($errors)) {
    try {
        // Récupérer l'utilisateur par son ID (présent dans la session)
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Utilisateur introuvable");
        }

        // Vérifier l'ancien mot de passe
        if (!password_verify($current_password, $user['password'])) {
            throw new Exception("Le mot de passe actuel est incorrect");
        }

        // Éviter de réutiliser le même mot de passe
        if (password_verify($new_password, $user['password'])) {
            throw new Exception("Le nouveau mot de passe doit être différent de l'ancien");
        }

        // Hasher le nouveau mot de passe
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        // Mettre à jour
        $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user_id]);

        // Log
        error_log("Utilisateur {$user['login']} a changé son mot de passe");

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Mot de passe changé avec succès !"
        ];

        // Redirection selon le rôle
        if ($user['role'] === 'admin') {
            header('Location: ../admin/profil.php');
        } else {
            header('Location: profil.php');
        }
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