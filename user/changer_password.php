<?php
// user/changer_password.php
require_once '../includes/auth_check_user.php';
require_once '../includes/header.php';
?>

<style>
    /* ============================================= */
    /* STYLES POUR CHANGER MOT DE PASSE */
    /* ============================================= */
    :root {
        --upf-blue: #294898;
        --upf-pink: #C72C82;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --dark: #1e293b;
        --light: #f8fafc;
        --gray: #64748b;
        --gradient: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
    }

    .password-page {
        padding: 20px;
        max-width: 600px;
        margin: 40px auto;
    }

    .password-card {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .password-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 200px;
        background: var(--gradient);
        opacity: 0.1;
    }

    .password-header {
        text-align: center;
        margin-bottom: 40px;
        position: relative;
    }

    .password-header i {
        font-size: 3.5rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 15px;
    }

    .password-header h1 {
        font-size: 2rem;
        color: var(--dark);
        margin-bottom: 10px;
    }

    .password-header p {
        color: var(--gray);
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .form-group label i {
        color: var(--upf-pink);
        margin-right: 8px;
    }

    .password-input-wrapper {
        position: relative;
    }

    .password-input-wrapper input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-size: 1rem;
        transition: all 0.3s;
        background: white;
    }

    .password-input-wrapper input:focus {
        border-color: var(--upf-pink);
        box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
        outline: none;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray);
        cursor: pointer;
        font-size: 1.2rem;
    }

    .password-toggle:hover {
        color: var(--upf-pink);
    }

    .strength-meter {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin: 15px 0 10px;
    }

    .strength-meter-fill {
        height: 100%;
        width: 0;
        transition: width 0.3s, background 0.3s;
        border-radius: 4px;
    }

    .strength-text {
        font-size: 0.9rem;
        color: var(--gray);
        margin-bottom: 15px;
    }

    .password-checklist {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        margin: 20px 0;
        display: none;
    }

    .password-checklist.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        color: var(--gray);
    }

    .checklist-item.valid {
        color: var(--success);
    }

    .checklist-item i {
        width: 20px;
    }

    .btn {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 16px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-primary {
        background: var(--gradient);
        color: white;
        box-shadow: 0 5px 20px rgba(199,44,130,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(199,44,130,0.4);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .info-box {
        background: #eff6ff;
        border-radius: 16px;
        padding: 20px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid var(--info);
    }

    .info-box i {
        font-size: 2rem;
        color: var(--info);
    }

    .info-box p {
        color: var(--dark);
        font-size: 0.95rem;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Messages flash */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid var(--danger);
    }
</style>

<div class="password-page">
    <div class="password-card">
        
        <!-- En-tête -->
        <div class="password-header">
            <i class="fas fa-shield-alt"></i>
            <h1>Changer mon mot de passe</h1>
            <p>Sécurisez votre compte en changeant régulièrement votre mot de passe</p>
        </div>

        <!-- Messages flash -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?>">
                <i class="fas fa-<?php echo $_SESSION['flash']['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $_SESSION['flash']['message']; ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Formulaire -->
        <form action="changer_password_traitement.php" method="POST" id="passwordForm">
            
            <!-- Mot de passe actuel -->
            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i>
                    Mot de passe actuel
                </label>
                <div class="password-input-wrapper">
                    <input type="password" 
                           name="current_password" 
                           id="current_password" 
                           placeholder="Entrez votre mot de passe actuel"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Nouveau mot de passe -->
            <div class="form-group">
                <label>
                    <i class="fas fa-key"></i>
                    Nouveau mot de passe
                </label>
                <div class="password-input-wrapper">
                    <input type="password" 
                           name="new_password" 
                           id="new_password" 
                           placeholder="Minimum 8 caractères"
                           onkeyup="checkPasswordStrength(this.value)"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <!-- Indicateur de force -->
                <div class="strength-meter">
                    <div class="strength-meter-fill" id="strengthMeter"></div>
                </div>
                <div class="strength-text" id="strengthText">Force: Très faible</div>

                <!-- Checklist -->
                <div class="password-checklist" id="passwordChecklist">
                    <div class="checklist-item" id="checkLength">
                        <i class="fas fa-times-circle"></i>
                        8 caractères minimum
                    </div>
                    <div class="checklist-item" id="checkUppercase">
                        <i class="fas fa-times-circle"></i>
                        Au moins une majuscule
                    </div>
                    <div class="checklist-item" id="checkLowercase">
                        <i class="fas fa-times-circle"></i>
                        Au moins une minuscule
                    </div>
                    <div class="checklist-item" id="checkNumber">
                        <i class="fas fa-times-circle"></i>
                        Au moins un chiffre
                    </div>
                    <div class="checklist-item" id="checkSpecial">
                        <i class="fas fa-times-circle"></i>
                        Au moins un caractère spécial (!@#$%^&*)
                    </div>
                </div>
            </div>

            <!-- Confirmation -->
            <div class="form-group">
                <label>
                    <i class="fas fa-check-circle"></i>
                    Confirmer le nouveau mot de passe
                </label>
                <div class="password-input-wrapper">
                    <input type="password" 
                           name="confirm_password" 
                           id="confirm_password" 
                           placeholder="Retapez le nouveau mot de passe"
                           onkeyup="checkPasswordMatch()"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passwordMatchMessage" style="font-size: 0.9rem; margin-top: 5px;"></div>
            </div>

            <!-- Info box -->
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p>
                    Un mot de passe fort contient au moins 8 caractères, 
                    des majuscules, des minuscules, des chiffres et des caractères spéciaux.
                </p>
            </div>

            <!-- Bouton submit -->
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-sync-alt"></i>
                Changer mon mot de passe
            </button>

            <!-- Lien retour -->
            <div style="text-align: center; margin-top: 20px;">
                <a href="profil.php" style="color: var(--gray); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Retour au profil
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);
        
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');
        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    // Vérifier force du mot de passe
    function checkPasswordStrength(password) {
        const checklist = document.getElementById('passwordChecklist');
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthText = document.getElementById('strengthText');
        
        if (password.length > 0) {
            checklist.classList.add('show');
        } else {
            checklist.classList.remove('show');
        }

        let strength = 0;
        
        // Vérifications
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

        // Mise à jour checklist
        updateChecklist('checkLength', hasLength);
        updateChecklist('checkUppercase', hasUppercase);
        updateChecklist('checkLowercase', hasLowercase);
        updateChecklist('checkNumber', hasNumber);
        updateChecklist('checkSpecial', hasSpecial);

        // Calcul force
        if (hasLength) strength++;
        if (hasUppercase) strength++;
        if (hasLowercase) strength++;
        if (hasNumber) strength++;
        if (hasSpecial) strength++;

        // Mise à jour affichage
        strengthMeter.className = 'strength-meter-fill';
        if (strength <= 2) {
            strengthMeter.style.background = '#ef4444';
            strengthMeter.style.width = '25%';
            strengthText.innerHTML = 'Force: Faible';
        } else if (strength <= 3) {
            strengthMeter.style.background = '#f59e0b';
            strengthMeter.style.width = '50%';
            strengthText.innerHTML = 'Force: Moyen';
        } else if (strength <= 4) {
            strengthMeter.style.background = '#3b82f6';
            strengthMeter.style.width = '75%';
            strengthText.innerHTML = 'Force: Fort';
        } else {
            strengthMeter.style.background = '#10b981';
            strengthMeter.style.width = '100%';
            strengthText.innerHTML = 'Force: Très fort';
        }
    }

    function updateChecklist(id, isValid) {
        const element = document.getElementById(id);
        if (isValid) {
            element.classList.add('valid');
            element.querySelector('i').className = 'fas fa-check-circle';
        } else {
            element.classList.remove('valid');
            element.querySelector('i').className = 'fas fa-times-circle';
        }
    }

    // Vérifier correspondance mots de passe
    function checkPasswordMatch() {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        const message = document.getElementById('passwordMatchMessage');
        
        if (confirmPass.length > 0) {
            if (newPass === confirmPass) {
                message.innerHTML = '✅ Les mots de passe correspondent';
                message.style.color = '#10b981';
            } else {
                message.innerHTML = '❌ Les mots de passe ne correspondent pas';
                message.style.color = '#ef4444';
            }
        } else {
            message.innerHTML = '';
        }
    }

    // Validation formulaire
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        const currentPass = document.getElementById('current_password').value;

        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('Les nouveaux mots de passe ne correspondent pas !');
            return;
        }

        if (newPass.length < 8) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 8 caractères !');
            return;
        }

        if (newPass === currentPass) {
            e.preventDefault();
            alert('Le nouveau mot de passe doit être différent de l\'ancien !');
            return;
        }

        // Désactiver bouton
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    });
</script>

<?php require_once '../includes/footer.php'; ?>