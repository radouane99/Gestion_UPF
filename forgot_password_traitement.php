<?php
// forgot_password_traitement.php
require_once 'config/database.php';
require_once 'includes/EmailService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit();
}

$type = $_POST['type'] ?? '';
$identifiant = trim($_POST['identifiant'] ?? '');

if (empty($identifiant)) {
    header('Location: forgot_password.php?error=1');
    exit();
}

$pdo = getConnexion();

try {
    $pdo->beginTransaction();

    // =============================================
    // CAS ADMIN : Login = Email
    // =============================================
    if ($type === 'admin') {
        // Chercher directement dans utilisateurs (login = email)
        $stmt = $pdo->prepare("
            SELECT u.* 
            FROM utilisateurs u
            WHERE u.login = ? AND u.role = 'admin'
        ");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();

        if (!$user) {
            $pdo->rollBack();
            header('Location: forgot_password.php?error=2');
            exit();
        }

        $email = $identifiant; // Le login EST l'email
        $nom = $identifiant;
        $prenom = 'Admin';
    }

    // =============================================
    // CAS ÉTUDIANT : Login -> Chercher email dans etudiants
    // =============================================
    else {
        // Chercher l'utilisateur par son login
        $stmt = $pdo->prepare("
            SELECT u.*, e.email, e.Prenom, e.Nom 
            FROM utilisateurs u
            LEFT JOIN etudiants e ON u.etudiant_id = e.Code
            WHERE u.login = ? AND u.role = 'user'
        ");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();

        if (!$user) {
            $pdo->rollBack();
            header('Location: forgot_password.php?error=2');
            exit();
        }

        if (empty($user['email'])) {
            $pdo->rollBack();
            header('Location: forgot_password.php?error=3');
            exit();
        }

        $email = $user['email'];
        $nom = $user['Nom'];
        $prenom = $user['Prenom'];
    }

    // =============================================
    // PARTIE COMMUNE : Générer token et envoyer email
    // =============================================

    // Supprimer anciens tokens
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);

    // Générer token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Sauvegarder token
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (email, token, expires_at, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$email, $token, $expires]);

    $pdo->commit();

    // Envoyer email
    $emailService = new EmailService();
    
    $nomComplet = ($type === 'admin') ? $identifiant : $prenom . ' ' . $nom;
    
    $result = $emailService->sendPasswordResetEmail(
        $email,
        $nomComplet,
        $token
    );

    if ($result['success']) {
        header('Location: forgot_password.php?success=1');
    } else {
        error_log("Erreur envoi email: " . $result['message']);
        header('Location: forgot_password.php?error=4');
    }

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Erreur: " . $e->getMessage());
    header('Location: forgot_password.php?error=4');
}
exit();
?>