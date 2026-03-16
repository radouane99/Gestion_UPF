<?php
// check_tokens.php - Vérifier l'état des tokens
require_once 'config/database.php';

$pdo = getConnexion();

echo "<h2>🔍 Vérification des tokens</h2>";

// 1. Voir tous les tokens
echo "<h3>Tokens dans password_resets:</h3>";
$tokens = $pdo->query("SELECT * FROM password_resets ORDER BY created_at DESC")->fetchAll();

if (count($tokens) > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Email</th><th>Token</th><th>Expire</th><th>Used</th><th>Créé</th></tr>";
    foreach ($tokens as $t) {
        $expired = strtotime($t['expires_at']) < time();
        echo "<tr>";
        echo "<td>" . $t['id'] . "</td>";
        echo "<td>" . $t['email'] . "</td>";
        echo "<td><small>" . substr($t['token'], 0, 20) . "...</small></td>";
        echo "<td>" . $t['expires_at'] . ($expired ? " <span style='color:red;'>(Expiré)</span>" : "") . "</td>";
        echo "<td>" . ($t['used'] ? "✅" : "❌") . "</td>";
        echo "<td>" . $t['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Aucun token trouvé</p>";
}

// 2. Nettoyer les tokens expirés
$stmt = $pdo->query("DELETE FROM password_resets WHERE expires_at < NOW()");
echo "<p>🧹 Tokens expirés supprimés: " . $stmt->rowCount() . "</p>";

// 3. Test avec un email spécifique
if (isset($_GET['email'])) {
    $email = $_GET['email'];
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? ORDER BY created_at DESC");
    $stmt->execute([$email]);
    $user_tokens = $stmt->fetchAll();
    
    echo "<h3>Tokens pour $email:</h3>";
    if (count($user_tokens) > 0) {
        foreach ($user_tokens as $t) {
            echo "<p>Token: " . substr($t['token'], 0, 30) . "...</p>";
        }
    } else {
        echo "<p>Aucun token pour cet email</p>";
    }
}

// Formulaire de test
?>
<form method="GET" style="margin-top: 20px;">
    <input type="email" name="email" placeholder="Email à vérifier">
    <button type="submit">Vérifier</button>
</form>

<hr>
<p><a href="forgot_password.php">← Retour</a></p>