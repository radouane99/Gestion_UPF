<?php
// forgot_password.php - Version unifiée (Admin/Étudiant)
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - UPF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #294898, #C72C82);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 30px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #294898;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .tab-container {
            display: flex;
            margin-bottom: 30px;
            border-radius: 50px;
            background: #f0f0f0;
            padding: 5px;
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            border-radius: 50px;
            transition: all 0.3s;
            font-weight: 600;
        }
        .tab.active {
            background: linear-gradient(135deg, #294898, #C72C82);
            color: white;
        }
        .tab.etudiant.active {
            background: linear-gradient(135deg, #C72C82, #294898);
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
        label i {
            color: #C72C82;
            margin-right: 8px;
        }
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s;
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
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(199,44,130,0.3);
        }
        .info-box {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #C72C82;
        }
        .info-box i {
            color: #C72C82;
            margin-right: 10px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #294898;
            text-decoration: none;
        }
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-lock"></i> Mot de passe oublié ?</h1>
        
        <!-- Tabs pour choisir Admin/Étudiant -->
        <div class="tab-container" id="tabContainer">
            <div class="tab active" onclick="switchTab('admin')" id="tabAdmin">
                <i class="fas fa-user-shield"></i> Administrateur
            </div>
            <div class="tab" onclick="switchTab('etudiant')" id="tabEtudiant">
                <i class="fas fa-user-graduate"></i> Étudiant
            </div>
        </div>

        <!-- Message de succès/erreur -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                ✅ Email envoyé ! Vérifiez votre boîte de réception.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                ❌ <?php 
                    switch($_GET['error']) {
                        case 1: echo "Veuillez entrer votre identifiant";
                            break;
                        case 2: echo "Identifiant non trouvé";
                            break;
                        case 3: echo "Aucun email associé à ce compte";
                            break;
                        case 4: echo "Erreur technique";
                            break;
                    }
                ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire Admin -->
        <form action="forgot_password_traitement.php" method="POST" id="formAdmin" style="display: block;">
            <input type="hidden" name="type" value="admin">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Admin :</strong> Entrez votre email (votre login)
            </div>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email / Login</label>
                <input type="email" name="identifiant" placeholder="votre.email@example.com" required>
            </div>
            <button type="submit">
                <i class="fas fa-paper-plane"></i> Envoyer le lien
            </button>
        </form>

        <!-- Formulaire Étudiant -->
        <form action="forgot_password_traitement.php" method="POST" id="formEtudiant" style="display: none;">
            <input type="hidden" name="type" value="etudiant">
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Étudiant :</strong> Entrez votre login (ex: ahmed.alaoui)
            </div>
            <div class="form-group">
                <label><i class="fas fa-user"></i> Login étudiant</label>
                <input type="text" name="identifiant" placeholder="prenom.nom" required>
            </div>
            <button type="submit">
                <i class="fas fa-paper-plane"></i> Envoyer le lien
            </button>
        </form>

        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
        </div>
    </div>

    <script>
        function switchTab(type) {
            const tabAdmin = document.getElementById('tabAdmin');
            const tabEtudiant = document.getElementById('tabEtudiant');
            const formAdmin = document.getElementById('formAdmin');
            const formEtudiant = document.getElementById('formEtudiant');
            
            if (type === 'admin') {
                tabAdmin.classList.add('active');
                tabEtudiant.classList.remove('active');
                formAdmin.style.display = 'block';
                formEtudiant.style.display = 'none';
            } else {
                tabEtudiant.classList.add('active');
                tabAdmin.classList.remove('active');
                formEtudiant.style.display = 'block';
                formAdmin.style.display = 'none';
            }
        }
    </script>
</body>
</html>