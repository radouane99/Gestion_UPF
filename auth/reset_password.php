<?php
// reset_password.php - Page pour entrer nouveau mot de passe
require_once __DIR__ . '/../config/database.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

$pdo = getConnexion();

// Vérifier si le token est valide
$stmt = $pdo->prepare("
    SELECT * FROM password_resets 
    WHERE token = ? AND used = 0 AND expires_at > NOW()
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = "Lien invalide ou expiré.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe - UPF</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #294898, #C72C82);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h1 {
            color: #294898;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #C72C82;
            box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
            outline: none;
        }
        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #294898, #C72C82);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(199,44,130,0.3);
        }
        .strength-meter {
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }
        .info-text {
            font-size: 0.9rem;
            margin-top: 5px;
            color: #666;
        }
        .match-text {
            font-size: 0.9rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-key"></i> Nouveau mot de passe</h1>

        <?php if (isset($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?><br>
                <a href="forgot_password.php" style="color: #991b1b;">Demander un nouveau lien</a>
            </div>
        <?php else: ?>

        <form action="reset_password_traitement.php" method="POST" id="resetForm">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                <input type="password" name="password" id="password" required minlength="8"
                       placeholder="Minimum 8 caractères">
                <div class="strength-meter">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <div class="info-text" id="strengthText">Force: Très faible</div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                       placeholder="Retapez le mot de passe">
                <div class="match-text" id="matchText"></div>
            </div>

            <button type="submit">
                <i class="fas fa-save"></i> Changer mon mot de passe
            </button>
        </form>

        <?php endif; ?>

        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #294898; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Retour à la connexion
            </a>
        </div>
    </div>

    <script>
        const password = document.getElementById('password');
        const confirm = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const matchText = document.getElementById('matchText');

        password.addEventListener('input', function() {
            const val = this.value;
            let strength = 0;
            
            if (val.length >= 8) strength++;
            if (val.match(/[a-z]/)) strength++;
            if (val.match(/[A-Z]/)) strength++;
            if (val.match(/[0-9]/)) strength++;
            if (val.match(/[^a-zA-Z0-9]/)) strength++;
            
            const colors = ['#ef4444', '#f59e0b', '#f59e0b', '#3b82f6', '#10b981'];
            const texts = ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];
            
            strengthFill.style.width = (strength * 20) + '%';
            strengthFill.style.background = colors[strength-1] || '#ef4444';
            strengthText.innerHTML = 'Force: ' + (texts[strength-1] || 'Très faible');
        });

        confirm.addEventListener('input', function() {
            if (this.value === password.value) {
                matchText.innerHTML = '✅ Les mots de passe correspondent';
                matchText.style.color = '#10b981';
            } else {
                matchText.innerHTML = '❌ Les mots de passe ne correspondent pas';
                matchText.style.color = '#ef4444';
            }
        });

        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            if (password.value !== confirm.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
            }
        });
    </script>
</body>
</html>