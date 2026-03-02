<?php
// admin/utilisateurs/ajouter.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$pdo = getConnexion();

// Récupérer la liste des étudiants pour liaison
$stmt = $pdo->query("SELECT Code, Nom, Prenom, email FROM etudiants ORDER BY Nom, Prenom");
$etudiants = $stmt->fetchAll();

// Récupérer les rôles disponibles
$roles = ['admin', 'user'];
?>

<style>
    /* ============================================= */
    /* STYLES POUR LE FORMULAIRE D'AJOUT */
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
    }

    .form-page {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    /* En-tête */
    .form-header {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
        border: 1px solid rgba(255,255,255,0.1);
    }

    .form-header h1 {
        font-size: 2.2em;
        color: var(--dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .form-header h1 i {
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.2em;
    }

    .form-header .breadcrumb {
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
    }

    .form-header .breadcrumb a {
        color: var(--upf-pink);
        text-decoration: none;
    }

    /* Formulaire moderne */
    .modern-form {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }

    .form-group {
        position: relative;
    }

    .form-group.full-width {
        grid-column: span 2;
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

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: var(--upf-pink);
        box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
        outline: none;
    }

    .form-group input.error {
        border-color: var(--danger);
    }

    .form-group small {
        display: block;
        margin-top: 6px;
        color: var(--gray);
        font-size: 0.8rem;
    }

    .form-group .error-message {
        color: var(--danger);
        font-size: 0.8rem;
        margin-top: 5px;
    }

    /* Indicateur de force du mot de passe */
    .password-strength {
        margin-top: 15px;
    }

    .strength-meter {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .strength-meter-fill {
        height: 100%;
        width: 0;
        transition: width 0.3s ease, background 0.3s ease;
        border-radius: 4px;
    }

    .strength-text {
        font-size: 0.85rem;
        color: var(--gray);
    }

    .strength-weak .strength-meter-fill { background: var(--danger); width: 25%; }
    .strength-medium .strength-meter-fill { background: var(--warning); width: 50%; }
    .strength-strong .strength-meter-fill { background: var(--info); width: 75%; }
    .strength-very-strong .strength-meter-fill { background: var(--success); width: 100%; }

    /* Checklist mot de passe */
    .password-checklist {
        background: #f8fafc;
        border-radius: 12px;
        padding: 15px;
        margin-top: 15px;
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
        padding: 5px 0;
        color: var(--gray);
        font-size: 0.9rem;
    }

    .checklist-item.valid {
        color: var(--success);
    }

    .checklist-item i {
        width: 20px;
        font-size: 0.9rem;
    }

    /* Sélection d'étudiant */
    .student-search {
        margin-bottom: 15px;
    }

    .student-search input {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
    }

    .student-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
    }

    .student-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid transparent;
    }

    .student-item:hover {
        background: #f8fafc;
        border-color: var(--upf-pink);
    }

    .student-item.selected {
        background: linear-gradient(135deg, rgba(41,72,152,0.1), rgba(199,44,130,0.1));
        border-color: var(--upf-pink);
    }

    .student-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .student-info {
        flex: 1;
    }

    .student-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 3px;
    }

    .student-details {
        font-size: 0.8rem;
        color: var(--gray);
        display: flex;
        gap: 15px;
    }

    /* Actions */
    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f1f5f9;
    }

    .btn {
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        color: white;
        box-shadow: 0 5px 15px rgba(199,44,130,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(199,44,130,0.4);
    }

    .btn-secondary {
        background: white;
        color: var(--dark);
        border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: var(--gray);
    }

    /* Animations */
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

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-group.full-width {
            grid-column: span 1;
        }
        
        .modern-form {
            padding: 20px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
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

    .btn.loading .spinner {
        display: inline-block;
    }

    .btn.loading .btn-text {
        opacity: 0.7;
    }
</style>

<div class="form-page">
    
    <!-- En-tête -->
    <div class="form-header">
        <h1>
            <i class="fas fa-user-plus"></i>
            Ajouter un utilisateur
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Ajouter</span>
        </div>
    </div>

    <!-- Formulaire -->
    <form class="modern-form" id="userForm" action="ajouter_traitement.php" method="POST">
        <div class="form-grid">
            
            <!-- Login -->
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-user"></i>
                    Login <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" 
                       name="login" 
                       id="login" 
                       required 
                       placeholder="ex: john.doe"
                       autocomplete="off"
                       onkeyup="checkLoginAvailability(this.value)">
                <small>Le login doit être unique, minimum 3 caractères</small>
                <div id="loginStatus" class="error-message"></div>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i>
                    Mot de passe <span style="color: var(--danger);">*</span>
                </label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       required 
                       placeholder="Minimum 8 caractères"
                       onkeyup="checkPasswordStrength(this.value)">
                <small>8 caractères minimum avec majuscule, minuscule et chiffre</small>
                
                <!-- Force du mot de passe -->
                <div class="password-strength">
                    <div class="strength-meter">
                        <div class="strength-meter-fill" id="strengthMeter"></div>
                    </div>
                    <span class="strength-text" id="strengthText">Très faible</span>
                </div>

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

            <!-- Confirmation mot de passe -->
            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i>
                    Confirmer le mot de passe <span style="color: var(--danger);">*</span>
                </label>
                <input type="password" 
                       name="confirm_password" 
                       id="confirm_password" 
                       required 
                       placeholder="Retapez le mot de passe"
                       onkeyup="checkPasswordMatch()">
                <div id="passwordMatchStatus" class="error-message"></div>
            </div>

            <!-- Rôle -->
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-tag"></i>
                    Rôle <span style="color: var(--danger);">*</span>
                </label>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="radio" name="role" value="admin" onchange="toggleStudentSelection()">
                        <span class="role-badge role-admin" style="padding: 8px 20px;">
                            <i class="fas fa-crown"></i>
                            Administrateur
                        </span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="radio" name="role" value="user" checked onchange="toggleStudentSelection()">
                        <span class="role-badge role-user" style="padding: 8px 20px;">
                            <i class="fas fa-user-graduate"></i>
                            Étudiant
                        </span>
                    </label>
                </div>
            </div>

            <!-- Sélection étudiant (caché par défaut pour admin) -->
            <div class="form-group full-width" id="studentSelection" style="display: block;">
                <label>
                    <i class="fas fa-user-graduate"></i>
                    Lier à un étudiant <span style="color: var(--danger);">*</span>
                </label>
                
                <!-- Recherche étudiant -->
                <div class="student-search">
                    <input type="text" id="studentSearch" placeholder="Rechercher un étudiant par nom, prénom ou email...">
                </div>

                <!-- Liste des étudiants -->
                <div class="student-list" id="studentList">
                    <?php foreach ($etudiants as $e): ?>
                    <div class="student-item" onclick="selectStudent('<?php echo $e['Code']; ?>', this)">
                        <div class="student-avatar">
                            <?php echo strtoupper(substr($e['Prenom'], 0, 1) . substr($e['Nom'], 0, 1)); ?>
                        </div>
                        <div class="student-info">
                            <div class="student-name"><?php echo htmlspecialchars($e['Prenom'] . ' ' . $e['Nom']); ?></div>
                            <div class="student-details">
                                <span><i class="fas fa-id-card"></i> <?php echo $e['Code']; ?></span>
                                <span><i class="fas fa-envelope"></i> <?php echo $e['email'] ?: 'Email non renseigné'; ?></span>
                            </div>
                        </div>
                        <i class="fas fa-check-circle" style="color: var(--success); opacity: 0;"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <input type="hidden" name="etudiant_id" id="selectedStudentId" value="">
                <small>Sélectionnez l'étudiant à associer à ce compte</small>
            </div>

            <!-- Options supplémentaires -->
            <div class="form-group full-width">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="send_email" value="1" checked>
                    <i class="fas fa-envelope" style="color: var(--upf-pink);"></i>
                    Envoyer les identifiants par email à l'étudiant
                </label>
            </div>

            <div class="form-group full-width">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="force_password_change" value="1">
                    <i class="fas fa-sync-alt" style="color: var(--warning);"></i>
                    Forcer le changement de mot de passe à la première connexion
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <a href="liste.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="btn-text">Créer l'utilisateur</span>
                <span class="spinner"></span>
            </button>
        </div>
    </form>
</div>

<script>
    // =============================================
    // VÉRIFICATION EN TEMPS RÉEL
    // =============================================

    // Vérifier disponibilité du login
    function checkLoginAvailability(login) {
        if (login.length < 3) {
            document.getElementById('loginStatus').innerHTML = 'Le login doit contenir au moins 3 caractères';
            return;
        }

        // Simulation AJAX (à remplacer par vraie requête)
        fetch('check_login.php?login=' + encodeURIComponent(login))
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    document.getElementById('loginStatus').innerHTML = '✅ Login disponible';
                    document.getElementById('loginStatus').style.color = '#10b981';
                } else {
                    document.getElementById('loginStatus').innerHTML = '❌ Ce login existe déjà';
                    document.getElementById('loginStatus').style.color = '#ef4444';
                }
            })
            .catch(error => {
                console.log('Erreur vérification login:', error);
            });
    }

    // Vérifier force du mot de passe
    function checkPasswordStrength(password) {
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthText = document.getElementById('strengthText');
        const checklist = document.getElementById('passwordChecklist');
        
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
            strengthMeter.classList.add('strength-weak');
            strengthText.innerHTML = 'Faible';
        } else if (strength <= 3) {
            strengthMeter.classList.add('strength-medium');
            strengthText.innerHTML = 'Moyen';
        } else if (strength <= 4) {
            strengthMeter.classList.add('strength-strong');
            strengthText.innerHTML = 'Fort';
        } else {
            strengthMeter.classList.add('strength-very-strong');
            strengthText.innerHTML = 'Très fort';
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
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        const status = document.getElementById('passwordMatchStatus');

        if (confirm.length > 0) {
            if (password === confirm) {
                status.innerHTML = '✅ Les mots de passe correspondent';
                status.style.color = '#10b981';
            } else {
                status.innerHTML = '❌ Les mots de passe ne correspondent pas';
                status.style.color = '#ef4444';
            }
        } else {
            status.innerHTML = '';
        }
    }

    // Toggle sélection étudiant selon rôle
    function toggleStudentSelection() {
        const role = document.querySelector('input[name="role"]:checked').value;
        const studentSelection = document.getElementById('studentSelection');
        
        if (role === 'admin') {
            studentSelection.style.display = 'none';
            document.getElementById('selectedStudentId').value = '';
        } else {
            studentSelection.style.display = 'block';
        }
    }

    // Sélectionner un étudiant
    function selectStudent(studentId, element) {
        // Enlever sélection précédente
        document.querySelectorAll('.student-item').forEach(item => {
            item.classList.remove('selected');
            item.querySelector('i.fa-check-circle').style.opacity = '0';
        });

        // Ajouter nouvelle sélection
        element.classList.add('selected');
        element.querySelector('i.fa-check-circle').style.opacity = '1';
        
        // Mettre à jour champ caché
        document.getElementById('selectedStudentId').value = studentId;
    }

    // Recherche étudiant
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        const items = document.querySelectorAll('.student-item');
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(search)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Validation formulaire
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        const role = document.querySelector('input[name="role"]:checked').value;
        const studentId = document.getElementById('selectedStudentId').value;

        // Vérifier mots de passe
        if (password !== confirm) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas !');
            return;
        }

        // Vérifier force mot de passe
        if (password.length < 8) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 8 caractères !');
            return;
        }

        // Vérifier sélection étudiant pour rôle user
        if (role === 'user' && !studentId) {
            e.preventDefault();
            alert('Veuillez sélectionner un étudiant à associer !');
            return;
        }

        // Afficher loading
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    // Initialisation
    toggleStudentSelection();
</script>

<?php require_once '../../includes/footer.php'; ?>