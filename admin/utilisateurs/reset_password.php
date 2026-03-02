<?php
// admin/utilisateurs/reset_password.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

$pdo = getConnexion();

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT u.*, e.Nom, e.Prenom, e.email 
                       FROM utilisateurs u 
                       LEFT JOIN etudiants e ON u.etudiant_id = e.Code 
                       WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "Utilisateur introuvable"
    ];
    header('Location: liste.php');
    exit();
}

// Générer un nouveau mot de passe
$newPassword = bin2hex(random_bytes(4)); // 8 caractères
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();

    // Mettre à jour
    $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $id]);

    // Envoyer email si l'utilisateur a un email
    if (!empty($user['email'])) {
        $to = $user['email'];
        $subject = "Réinitialisation de votre mot de passe - UPF Gestion";
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
                <h3>Bonjour " . htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']) . ",</h3>
                <p>Votre mot de passe a été réinitialisé par l'administrateur.</p>
                
                <div class='credentials'>
                    <p><strong>Login :</strong> " . htmlspecialchars($user['login']) . "</p>
                    <p><strong>Nouveau mot de passe :</strong> " . htmlspecialchars($newPassword) . "</p>
                </div>
                
                <p><strong>Lien de connexion :</strong> <a href='http://localhost/Gestion%20UPF/login.php'>Cliquez ici</a></p>
                
                <p style='color: orange;'><strong>⚠️ Important :</strong> Nous vous recommandons de changer ce mot de passe après votre connexion.</p>
                
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: noreply@upf.ac.ma\r\n";

        mail($to, $subject, $message, $headers);
        $emailSent = true;
    }

    // Log l'action
    error_log("Admin {$_SESSION['login']} a réinitialisé le mot de passe de {$user['login']}");

    $pdo->commit();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => "✅ Mot de passe réinitialisé avec succès !<br>" .
                    "Nouveau mot de passe : <strong>$newPassword</strong><br>" .
                    (!empty($user['email']) && $emailSent ? "Un email a été envoyé à l'utilisateur." : "")
    ];

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Erreur : " . $e->getMessage()
    ];
}

header('Location: liste.php');
exit();
?>