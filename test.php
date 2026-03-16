<?php
// test_reset.php - Tester directement la mise à jour
require_once 'config/database.php';

echo "<h2>🔧 Test de mise à jour directe</h2>";

$pdo = getConnexion();

// 1. Voir tous les utilisateurs
echo "<h3>Utilisateurs avant modification:</h3>";
$users = $pdo->query("SELECT id, login, role, LEFT(password, 30) as password_debut FROM utilisateurs")->fetchAll();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Login</th><th>Rôle</th><th>Hash (début)</th></tr>";
foreach ($users as $u) {
    echo "<tr>";
    echo "<td>" . $u['id'] . "</td>";
    echo "<td>" . $u['login'] . "</td>";
    echo "<td>" . $u['role'] . "</td>";
    echo "<td>" . $u['password_debut'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Tester mise à jour
if (isset($_POST['test'])) {
    $id = $_POST['user_id'];
    $new_pass = $_POST['new_pass'];
    
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
    $result = $stmt->execute([$hashed, $id]);
    
    echo "<p style='color: green;'>✅ Test effectué - Lignes affectées: " . $stmt->rowCount() . "</p>";
    
    // Vérifier
    $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $new_hash = $stmt->fetchColumn();
    
    echo "<p>Nouveau hash: " . $new_hash . "</p>";
    echo "<p>Vérification avec password_verify('$new_pass'): " . 
         (password_verify($new_pass, $new_hash) ? '✅ OK' : '❌ ÉCHEC') . "</p>";
}

// Formulaire de test
?>
<form method="POST">
    <h3>Tester mise à jour:</h3>
    <select name="user_id">
        <?php foreach ($users as $u): ?>
        <option value="<?php echo $u['id']; ?>"><?php echo $u['login']; ?> (<?php echo $u['role']; ?>)</option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="new_pass" value="admin123" required>
    <button type="submit" name="test">Tester mise à jour</button>
</form>