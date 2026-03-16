<?php
// reset_password_traitement.php - VERSION FINALE CORRIGÉE
require_once 'config/database.php';

// Activation des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log pour debug
error_log("=== RÉINITIALISATION MOT DE PASSE ===");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit();
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

error_log("Token reçu: " . $token);

// Validation
if (empty($token) || empty($password) || empty($confirm)) {
    die("Paramètres manquants");
}

if ($password !== $confirm) {
    die("Les mots de passe ne correspondent pas");
}

if (strlen($password) < 8) {
    die("Le mot de passe doit contenir au moins 8 caractères");
}

$pdo = getConnexion();

try {
    $pdo->beginTransaction();

    // 1. Vérifier le token
    $stmt = $pdo->prepare("
        SELECT * FROM password_resets 
        WHERE token = ? AND used = 0 AND expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        error_log("Token invalide ou expiré");
        
        // Voir tous les tokens pour debug
        $tokens = $pdo->query("SELECT * FROM password_resets ORDER BY created_at DESC")->fetchAll();
        error_log("Tokens disponibles: " . print_r($tokens, true));
        
        throw new Exception("Lien invalide ou expiré");
    }

    error_log("Token valide pour email: " . $reset['email']);

    // 2. Chercher l'utilisateur par email
    // Stratégie: Chercher d'abord comme admin (login = email)
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ?");
    $stmt->execute([$reset['email']]);
    $user = $stmt->fetch();

    // Si pas trouvé, chercher comme étudiant (email dans table etudiants)
    if (!$user) {
        error_log("Pas trouvé comme admin, recherche comme étudiant...");
        $stmt = $pdo->prepare("
            SELECT u.* 
            FROM utilisateurs u
            JOIN etudiants e ON u.etudiant_id = e.Code
            WHERE e.email = ?
        ");
        $stmt->execute([$reset['email']]);
        $user = $stmt->fetch();
    }

    if (!$user) {
        error_log("Utilisateur non trouvé pour email: " . $reset['email']);
        throw new Exception("Utilisateur non trouvé");
    }

    error_log("Utilisateur trouvé - ID: " . $user['id'] . ", Login: " . $user['login']);

    // 3. Hasher le nouveau mot de passe
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    error_log("Nouveau hash: " . $hashed);

    // 4. Mettre à jour le mot de passe
    $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
    $result = $stmt->execute([$hashed, $user['id']]);
    
    if (!$result) {
        throw new Exception("Échec de la mise à jour");
    }
    
    error_log("Lignes affectées: " . $stmt->rowCount());

    // 5. Marquer le token comme utilisé
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
    $stmt->execute([$reset['id']]);

    $pdo->commit();
    error_log("SUCCÈS - Mot de passe changé pour " . $user['login']);

    // Rediriger vers login avec message
    header('Location: login.php?msg=reset_ok');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("ERREUR: " . $e->getMessage());
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Erreur</title>
        <style>
            body {
                background: linear-gradient(135deg, #294898, #C72C82);
                font-family: Arial;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .error-box {
                background: white;
                padding: 40px;
                border-radius: 20px;
                max-width: 500px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            h2 { color: #dc3545; }
            .debug {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 10px;
                font-family: monospace;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>❌ Erreur</h2>
            <p><?php echo $e->getMessage(); ?></p>
            <div class="debug">
                <strong>Debug:</strong><br>
                Token: <?php echo htmlspecialchars($token); ?><br>
                Email: <?php echo isset($reset) ? $reset['email'] : 'Non trouvé'; ?>
            </div>
            <a href="forgot_password.php" style="display: inline-block; margin-top: 20px; color: #294898;">
                ← Retour
            </a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>