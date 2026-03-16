<?php
// admin_forgot.php - Récupération mot de passe admin
require_once 'config/database.php';
session_start();

$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getConnexion();
    
    if ($step == 1) {
        // Étape 1: Vérifier le login admin
        $login = $_POST['login'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ? AND role = 'admin'");
        $stmt->execute([$login]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Stocker l'ID dans session temporaire
            $_SESSION['reset_admin_id'] = $admin['id'];
            header('Location: admin_forgot.php?step=2');
            exit();
        } else {
            $error = "Admin non trouvé";
        }
    }
    
    elseif ($step == 2) {
        // Étape 2: Question secrète (ou code généré)
        if (!isset($_SESSION['reset_admin_id'])) {
            header('Location: admin_forgot.php');
            exit();
        }
        
        $code = $_POST['code'] ?? '';
        
        // Code simple - à personnaliser
        if ($code === 'ADMIN123') { // Ou un code que tu définis
            header('Location: admin_forgot.php?step=3');
            exit();
        } else {
            $error = "Code incorrect";
        }
    }
    
    elseif ($step == 3) {
        // Étape 3: Nouveau mot de passe
        if (!isset($_SESSION['reset_admin_id'])) {
            header('Location: admin_forgot.php');
            exit();
        }
        
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirm) {
            $error = "Les mots de passe ne correspondent pas";
        } elseif (strlen($password) < 8) {
            $error = "Minimum 8 caractères";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['reset_admin_id']]);
            
            unset($_SESSION['reset_admin_id']);
            $success = "Mot de passe changé ! <a href='login.php'>Se connecter</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Récupération Admin - UPF</title>
    <style>
        body {
            background: linear-gradient(135deg, #294898, #C72C82);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h2 {
            color: #294898;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
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
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: 600;
        }
        .step.active {
            background: linear-gradient(135deg, #294898, #C72C82);
            color: white;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #294898;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 Récupération Admin</h2>
        
        <div class="step-indicator">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">1</div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">2</div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
        
        <form method="POST">
            <?php if ($step == 1): ?>
                <div class="form-group">
                    <label>Login administrateur</label>
                    <input type="text" name="login" placeholder="Entrez votre login admin" required>
                </div>
                <button type="submit">Vérifier</button>
                
            <?php elseif ($step == 2): ?>
                <div class="form-group">
                    <label>Code de sécurité</label>
                    <input type="text" name="code" placeholder="Entrez le code secret" required>
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Code par défaut: ADMIN123
                    </small>
                </div>
                <button type="submit">Vérifier</button>
                
            <?php elseif ($step == 3): ?>
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="password" placeholder="Minimum 8 caractères" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirmer</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit">Changer le mot de passe</button>
            <?php endif; ?>
        </form>
        
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php">← Retour à la connexion</a>
        </div>
    </div>
</body>
</html>