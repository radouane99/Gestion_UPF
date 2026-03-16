<?php
// login.php - Version Moderne et Élégante
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ./admin/dashboard.php');
    } else {
        header('Location: ./user/profil.php');
    }
    exit();
}

// Récupérer le dernier login du cookie
$last_login = $_COOKIE['last_login'] ?? '';

// Déterminer l'heure de la journée pour le message d'accueil
$hour = date('H');
if ($hour < 12) {
    $greeting = "Bonjour";
    $icon = "🌅";
} elseif ($hour < 18) {
    $greeting = "Bon après-midi";
    $icon = "☀️";
} else {
    $greeting = "Bonsoir";
    $icon = "🌙";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - UPF Gestion</title>
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================= */
        /* STYLES MODERNES PAGE DE CONNEXION */
        /* ============================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --upf-blue: #294898;
            --upf-pink: #C72C82;
            --upf-gradient: linear-gradient(135deg, #294898, #C72C82);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --white: #ffffff;
            --shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 30px 60px rgba(199, 44, 130, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Particules flottantes */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(90deg); }
            50% { transform: translateY(0) rotate(180deg); }
            75% { transform: translateY(20px) rotate(270deg); }
        }

        /* Créer plusieurs particules */
        .particle:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; top: 60%; right: 15%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; bottom: 10%; left: 20%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 40px; height: 40px; top: 30%; right: 30%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 120px; height: 120px; bottom: 20%; right: 10%; animation-delay: 8s; }

        .container {
            max-width: 1100px;
            width: 90%;
            margin: 20px;
            position: relative;
            z-index: 10;
            animation: fadeInUp 1s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-wrapper {
            display: flex;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .login-wrapper:hover {
            box-shadow: var(--shadow-hover);
            transform: scale(1.02);
        }

        /* ============================================= */
        /* PARTIE GAUCHE - BANNIÈRE */
        /* ============================================= */
        .banner {
            flex: 1;
            background: var(--upf-gradient);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .banner-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 50px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .logo-text h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .logo-text p {
            font-size: 14px;
            opacity: 0.9;
        }

        .banner h1 {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .banner h1 span {
            display: block;
            font-size: 24px;
            font-weight: 400;
            opacity: 0.9;
            margin-top: 10px;
        }

        .features {
            margin: 40px 0;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(5px);
            transition: all 0.3s;
        }

        .feature:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.2);
        }

        .feature i {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .feature-text h4 {
            font-size: 16px;
            margin-bottom: 3px;
        }

        .feature-text p {
            font-size: 13px;
            opacity: 0.8;
        }

        .testimonial {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .testimonial p {
            font-style: italic;
            margin-bottom: 10px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .testimonial-author img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* ============================================= */
        /* PARTIE DROITE - FORMULAIRE */
        /* ============================================= */
        .login-section {
            flex: 1;
            padding: 60px 50px;
            background: white;
        }

        .greeting {
            margin-bottom: 40px;
        }

        .greeting h3 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .greeting h3 span {
            background: var(--upf-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .greeting p {
            color: var(--gray);
            font-size: 16px;
        }

        /* Alertes */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid var(--warning);
        }

        .alert i {
            font-size: 24px;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .alert-message {
            font-size: 14px;
            opacity: 0.9;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
            transition: opacity 0.3s;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* Formulaire */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 14px;
        }

        .form-group label i {
            color: var(--upf-pink);
            margin-right: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            transition: color 0.3s;
            font-size: 18px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
            font-family: 'Poppins', sans-serif;
        }

        .input-wrapper input:focus {
            border-color: var(--upf-pink);
            box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
            outline: none;
        }

        .input-wrapper input:focus + i {
            color: var(--upf-pink);
        }

        .input-wrapper .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
            transition: color 0.3s;
            background: none;
            border: none;
            font-size: 18px;
        }

        .input-wrapper .toggle-password:hover {
            color: var(--upf-pink);
        }

        /* Options (se souvenir de moi) */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--upf-pink);
        }

        .remember-me span {
            color: var(--dark);
            font-size: 14px;
        }

        .forgot-password {
            color: var(--upf-pink);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: var(--upf-blue);
            text-decoration: underline;
        }

        /* Bouton de connexion */
        .btn-login {
            width: 100%;
            padding: 18px;
            background: var(--upf-gradient);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(199,44,130,0.4);
        }

        .btn-login i {
            font-size: 18px;
            transition: transform 0.3s;
        }

        .btn-login:hover i {
            transform: translateX(5px);
        }

        /* Dernière connexion */
        .last-login {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
            border: 1px dashed var(--gray);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .last-login i {
            font-size: 24px;
            color: var(--upf-pink);
        }

        .last-login-content {
            flex: 1;
        }

        .last-login-label {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 3px;
        }

        .last-login-value {
            font-weight: 600;
            color: var(--dark);
        }

        /* Loading spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-login.loading .btn-text {
            opacity: 0.7;
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        /* Informations complémentaires */
        .demo-info {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
        }

        .demo-info h4 {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .demo-accounts {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .demo-account {
            flex: 1;
            padding: 12px;
            background: white;
            border-radius: 10px;
            text-align: center;
        }

        .demo-account.admin {
            border-left: 4px solid var(--upf-blue);
        }

        .demo-account.user {
            border-left: 4px solid var(--upf-pink);
        }

        .demo-account .role {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 5px;
        }

        .demo-account .login {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .demo-account .password {
            font-size: 12px;
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .login-wrapper {
                flex-direction: column;
            }
            
            .banner {
                padding: 40px 30px;
            }
            
            .banner h1 {
                font-size: 36px;
            }
            
            .login-section {
                padding: 40px 30px;
            }
            
            .demo-accounts {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .greeting h3 {
                font-size: 28px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .last-login {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Particules animées -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <div class="login-wrapper">
            <!-- Partie gauche - Bannière -->
            <div class="banner">
                <div class="banner-content">
                    <div class="logo">
                        <div class="logo-icon">UPF</div>
                        <div class="logo-text">
                            <h2>Université Privée</h2>
                            <p>de Fès</p>
                        </div>
                    </div>

                    <h1>
                        Application de Gestion
                        <span>Espace Étudiant & Administrateur</span>
                    </h1>

                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-graduation-cap"></i>
                            <div class="feature-text">
                                <h4>Espace Étudiant</h4>
                                <p>Consultez vos notes, documents et performances</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-chart-line"></i>
                            <div class="feature-text">
                                <h4>Espace Admin</h4>
                                <p>Gérez les étudiants, filières et documents</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <div class="feature-text">
                                <h4>Sécurisé</h4>
                                <p>Connexion protégée et données cryptées</p>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial">
                        <p>"Une application intuitive qui facilite la gestion des étudiants et le suivi académique."</p>
                        <div class="testimonial-author">
                            <img src="https://randomuser.me/api/portraits/men/44.jpg" alt="User">
                            <div>
                                <strong>Pr. Mr. KZADRI</strong>
                                <p style="font-size: 12px;">Responsable pédagogique</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partie droite - Formulaire -->
            <div class="login-section">
                <div class="greeting">
                    <h3>
                        <span><?php echo $greeting; ?></span> <?php echo $icon; ?>
                    </h3>
                    <p>Connectez-vous pour accéder à votre espace personnel</p>
                </div>

                <!-- Messages d'alerte -->
                <?php if (isset($_GET['erreur'])): ?>
                    <div class="alert alert-danger" id="alertMessage">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="alert-content">
                            <div class="alert-title">Échec de connexion</div>
                            <div class="alert-message">
                                <?php 
                                if ($_GET['erreur'] == 1) echo "Login ou mot de passe incorrect";
                                elseif ($_GET['erreur'] == 'acces') echo "Accès non autorisé. Veuillez vous connecter.";
                                else echo "Erreur de connexion";
                                ?>
                            </div>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deconnecte'): ?>
                    <div class="alert alert-success" id="alertMessage">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <div class="alert-title">Déconnexion réussie</div>
                            <div class="alert-message">Vous avez été déconnecté avec succès</div>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'reset_ok'): ?>
                    <div class="alert alert-success" id="alertMessage">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <div class="alert-title">Mot de passe réinitialisé</div>
                            <div class="alert-message">Votre mot de passe a été changé avec succès. Vous pouvez maintenant vous connecter.</div>
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire de connexion -->
                <form action="login_traitement.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            Nom d'utilisateur
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" 
                                   name="login" 
                                   id="login" 
                                   value="<?php echo htmlspecialchars($last_login); ?>" 
                                   placeholder="Entrez votre login"
                                   required
                                   autocomplete="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   placeholder="Entrez votre mot de passe"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember" <?php echo !empty($last_login) ? 'checked' : ''; ?>>
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="forgot_password.php" class="forgot-password">
                            <i class="fas fa-lock"></i> Mot de passe oublié ?
                        </a>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn">
                        <span class="btn-text">Se connecter</span>
                        <span class="spinner"></span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <!-- Dernière connexion -->
                <?php if (!empty($last_login)): ?>
                    <div class="last-login">
                        <i class="fas fa-history"></i>
                        <div class="last-login-content">
                            <div class="last-login-label">Dernière connexion</div>
                            <div class="last-login-value">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($last_login); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informations de démo (à supprimer en production) -->
                <div class="demo-info">
                    <h4>
                        <i class="fas fa-flask"></i>
                        Comptes de test
                    </h4>
                    <div class="demo-accounts">
                        <div class="demo-account admin">
                            <div class="role">Administrateur</div>
                            <div class="login">admin</div>
                            <div class="password">admin123</div>
                        </div>
                        <div class="demo-account user">
                            <div class="role">Étudiant</div>
                            <div class="login">ahmed.alaoui</div>
                            <div class="password">password123</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        // Loading animation on submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const login = document.getElementById('login').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!login || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
                return;
            }
            
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alertMessage');
            if (alert) {
                alert.style.transition = 'opacity 1s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 1000);
            }
        }, 5000);

        // Animation sur les champs
        const inputs = document.querySelectorAll('.input-wrapper input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Remplissage automatique avec les comptes de démo (optionnel)
        document.querySelectorAll('.demo-account').forEach(account => {
            account.addEventListener('click', function() {
                const login = this.querySelector('.login').textContent;
                const password = this.querySelector('.password').textContent;
                
                document.getElementById('login').value = login;
                document.getElementById('password').value = password;
                
                // Effet visuel
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 200);
            });
        });
    </script>
</body>
</html>