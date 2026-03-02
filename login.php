<?php
// login.php (في المجلد الرئيسي)
session_start();

// إذا كان المستخدم مسجل دخوله بالفعل، نوجهه مباشرة
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/profil.php');
    }
    exit();
}

// نجلب آخر login من الكوكيز إذا كان موجوداً
$last_login = $_COOKIE['last_login'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - UPF</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="assets/upf-logo.png" alt="UPF Logo" class="logo">
            <h1>Université Privée de Fès</h1>
            <h2>Application de Gestion</h2>
            
            <!-- عرض رسالة الخطأ إذا كانت موجودة -->
            <?php if (isset($_GET['erreur'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    if ($_GET['erreur'] == 1) echo "Login ou mot de passe incorrect";
                    elseif ($_GET['erreur'] == 'acces') echo "Accès non autorisé";
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- عرض رسالة التأكيد إذا كانت موجودة -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deconnecte'): ?>
                <div class="alert alert-success">
                    Vous avez été déconnecté avec succès
                </div>
            <?php endif; ?>
            
            <!-- formulaire de connexion -->
            <form action="login_traitement.php" method="POST">
                <div class="form-group">
                    <label for="login">Login:</label>
                    <input type="text" id="login" name="login" 
                           value="<?php echo htmlspecialchars($last_login); ?>" 
                           placeholder="Entrez votre login" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Entrez votre mot de passe" required>
                </div>
                
                <button type="submit" class="btn-login">Se connecter</button>
            </form>
            
            <!-- عرض آخر تسجيل دخول إذا كان موجوداً -->
            <?php if (!empty($last_login)): ?>
                <div class="last-login">
                    Dernier login utilisé: <?php echo htmlspecialchars($last_login); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>