<?php
// reset_admin.php - À SUPPRIMER APRÈS UTILISATION !
require_once 'config/database.php';

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? 'admin';
    $new_password = $_POST['new_password'] ?? 'admin123';
    
    $pdo = getConnexion();
    
    // Hasher le nouveau mot de passe
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Mettre à jour
    $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE login = ? AND role = 'admin'");
    $result = $stmt->execute([$hashed, $login]);
    
    if ($result && $stmt->rowCount() > 0) {
        $message = "✅ Mot de passe changé avec succès !<br>
                   Login: $login<br>
                   Nouveau mot de passe: $new_password";
    } else {
        $error = "❌ Admin non trouvé ou erreur !";
    }
}

// Récupérer la liste des admins
$pdo = getConnexion();
$admins = $pdo->query("SELECT id, login FROM utilisateurs WHERE role = 'admin'")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réinitialisation Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #294898, #C72C82);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #294898;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            border-color: #C72C82;
            outline: none;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #294898, #C72C82);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #e7f3ff;
            color: #004085;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #007bff;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
        .admin-list {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 10px;
        }
        .admin-item {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .admin-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Réinitialisation Admin</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Sélectionner l'admin :</label>
                <select name="login" required>
                    <?php foreach ($admins as $admin): ?>
                        <option value="<?php echo $admin['login']; ?>">
                            <?php echo $admin['login']; ?> (ID: <?php echo $admin['id']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Nouveau mot de passe :</label>
                <input type="text" name="new_password" value="admin123" required>
                <small style="color: #666;">Par défaut: admin123</small>
            </div>
            
            <button type="submit">Changer le mot de passe</button>
        </form>
        
        <div class="info">
            <strong>📝 Instructions :</strong>
            <ol style="margin-top: 10px; padding-left: 20px;">
                <li>Sélectionne l'admin dans la liste</li>
                <li>Laisse le mot de passe par défaut ou change-le</li>
                <li>Clique sur le bouton</li>
                <li>Note le nouveau mot de passe</li>
                <li><strong style="color: red;">SUPPRIME CE FICHIER APRÈS !</strong></li>
            </ol>
        </div>
        
        <div class="admin-list">
            <strong>👥 Admins trouvés :</strong>
            <?php foreach ($admins as $admin): ?>
                <div class="admin-item">
                    • <?php echo $admin['login']; ?> (ID: <?php echo $admin['id']; ?>)
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="warning">
            <strong>⚠️ ATTENTION :</strong> Supprime ce fichier immédiatement après utilisation !
        </div>
    </div>
</body>
</html>