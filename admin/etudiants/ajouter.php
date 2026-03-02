<?php
// admin/etudiants/ajouter.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$pdo = getConnexion();

// Récupérer les filières pour le select
$filieres = $pdo->query("SELECT CodeF, IntituleF FROM filieres ORDER BY IntituleF")->fetchAll();

// Générer un code automatique (optionnel)
$lastCode = $pdo->query("SELECT Code FROM etudiants ORDER BY Code DESC LIMIT 1")->fetchColumn();
if ($lastCode) {
    $num = intval(substr($lastCode, 1)) + 1;
    $newCode = 'E' . str_pad($num, 3, '0', STR_PAD_LEFT);
} else {
    $newCode = 'E001';
}
?>

<style>
    /* ============================================= */
    /* STYLES POUR LE FORMULAIRE D'AJOUT ÉTUDIANT */
    /* ============================================= */
    :root {
        --upf-blue: #294898;
        --upf-pink: #C72C82;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --dark: #1e293b;
        --light: #f8fafc;
        --gray: #64748b;
        --gradient: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
    }

    .form-page {
        padding: 20px;
        max-width: 1000px;
        margin: 0 auto;
    }

    /* En-tête */
    .form-header {
        background: white;
        border-radius: 30px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
    }

    .form-header h1 {
        font-size: 2.2rem;
        color: var(--dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .form-header h1 i {
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--gray);
    }

    .breadcrumb a {
        color: var(--upf-pink);
        text-decoration: none;
    }

    /* Formulaire */
    .modern-form {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
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

    .form-group label .required {
        color: var(--danger);
        margin-left: 3px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
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

    /* Zone upload photo */
    .photo-upload {
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        background: #f8fafc;
    }

    .photo-upload:hover {
        border-color: var(--upf-pink);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
    }

    .photo-upload.has-image {
        border-style: solid;
        border-color: var(--success);
    }

    .photo-preview {
        width: 150px;
        height: 150px;
        margin: 0 auto 15px;
        border-radius: 20px;
        overflow: hidden;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        font-weight: 700;
    }

    .photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-upload i {
        font-size: 2rem;
        color: var(--upf-pink);
        margin-bottom: 10px;
    }

    .photo-upload p {
        color: var(--gray);
        margin-bottom: 10px;
    }

    .photo-upload small {
        color: var(--gray);
        font-size: 0.8rem;
    }

    .photo-upload input {
        display: none;
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
        border-radius: 16px;
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
        background: var(--gradient);
        color: white;
        box-shadow: 0 5px 15px rgba(199,44,130,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(199,44,130,0.4);
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

    .btn-success {
        background: var(--success);
        color: white;
    }

    /* Checkbox créer compte */
    .create-account-box {
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
        border-radius: 20px;
        padding: 25px;
        margin-top: 20px;
        border: 1px solid rgba(199,44,130,0.2);
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 15px;
        cursor: pointer;
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .checkbox-group label {
        margin: 0;
        cursor: pointer;
    }

    /* Validation en temps réel */
    .validation-icon {
        position: absolute;
        right: 15px;
        top: 45px;
        font-size: 1.2rem;
    }

    .valid {
        color: var(--success);
    }

    .invalid {
        color: var(--danger);
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

    /* Loading */
    .loading-spinner {
        display: inline-block;
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
</style>

<div class="form-page">
    
    <!-- En-tête -->
    <div class="form-header">
        <h1>
            <i class="fas fa-user-plus"></i>
            Ajouter un étudiant
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-user-graduate"></i> Étudiants</a>
            <i class="fas fa-chevron-right"></i>
            <span>Ajouter</span>
        </div>
    </div>

    <!-- Formulaire -->
    <form class="modern-form" action="ajouter_traitement.php" method="POST" enctype="multipart/form-data" id="studentForm">
        <div class="form-grid">
            
            <!-- Code (auto-généré mais modifiable) -->
            <div class="form-group">
                <label>
                    <i class="fas fa-id-card"></i>
                    Code étudiant <span class="required">*</span>
                </label>
                <input type="text" 
                       name="code" 
                       id="code" 
                       value="<?php echo $newCode; ?>" 
                       required 
                       pattern="E[0-9]{3}"
                       placeholder="E001"
                       onkeyup="checkCode()">
                <small>Format: E001, E002, ...</small>
                <div id="codeStatus" class="error-message"></div>
            </div>

            <!-- Nom -->
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i>
                    Nom <span class="required">*</span>
                </label>
                <input type="text" 
                       name="nom" 
                       id="nom" 
                       required 
                       placeholder="Ex: ALAOUI"
                       onkeyup="this.value = this.value.toUpperCase()">
            </div>

            <!-- Prénom -->
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i>
                    Prénom <span class="required">*</span>
                </label>
                <input type="text" 
                       name="prenom" 
                       id="prenom" 
                       required 
                       placeholder="Ex: Ahmed"
                       onkeyup="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase()">
            </div>

            <!-- Filière -->
            <div class="form-group">
                <label>
                    <i class="fas fa-building"></i>
                    Filière
                </label>
                <select name="filiere" id="filiere">
                    <option value="">-- Sélectionner une filière --</option>
                    <?php foreach ($filieres as $f): ?>
                        <option value="<?php echo $f['CodeF']; ?>">
                            <?php echo htmlspecialchars($f['IntituleF']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date naissance -->
            <div class="form-group">
                <label>
                    <i class="fas fa-calendar"></i>
                    Date de naissance
                </label>
                <input type="date" name="date_naissance" id="date_naissance" max="<?php echo date('Y-m-d', strtotime('-15 years')); ?>">
                <small>Âge minimum: 15 ans</small>
            </div>

            <!-- Note -->
            <div class="form-group">
                <label>
                    <i class="fas fa-star"></i>
                    Note /20
                </label>
                <input type="number" 
                       name="note" 
                       id="note" 
                       step="0.01" 
                       min="0" 
                       max="20" 
                       placeholder="0.00 - 20.00">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label>
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       placeholder="exemple@upf.ac.ma"
                       onkeyup="checkEmail()">
                <div id="emailStatus" class="error-message"></div>
            </div>

            <!-- Téléphone -->
            <div class="form-group">
                <label>
                    <i class="fas fa-phone"></i>
                    Téléphone
                </label>
                <input type="tel" 
                       name="telephone" 
                       id="telephone" 
                       placeholder="06XXXXXXXX"
                       pattern="[0-9]{10}">
            </div>

            <!-- Upload Photo (full width) -->
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-camera"></i>
                    Photo de profil
                </label>
                <div class="photo-upload" id="photoUpload" onclick="document.getElementById('photo').click()">
                    <div class="photo-preview" id="photoPreview">
                        <i class="fas fa-user"></i>
                    </div>
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Cliquez pour uploader une photo</p>
                    <small>Formats: JPG, PNG • Max: 2 Mo</small>
                    <input type="file" 
                           name="photo" 
                           id="photo" 
                           accept="image/jpeg,image/png"
                           onchange="previewImage(this)">
                </div>
            </div>

            <!-- Option création compte utilisateur -->
            <div class="form-group full-width">
                <div class="create-account-box">
                    <div class="checkbox-group">
                        <input type="checkbox" name="create_account" id="create_account" value="1" checked>
                        <label for="create_account">
                            <strong><i class="fas fa-user-plus"></i> Créer automatiquement un compte utilisateur</strong>
                            <p style="font-weight: normal; margin-top: 5px; color: var(--gray);">
                                Le login sera généré automatiquement (prenom.nom) et le mot de passe sera envoyé par email
                            </p>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <a href="liste.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="btn-text">Ajouter l'étudiant</span>
                <span class="loading-spinner" style="display: none;"></span>
            </button>
        </div>
    </form>
</div>

<script>
    // Preview image avant upload
    function previewImage(input) {
        const preview = document.getElementById('photoPreview');
        const uploadArea = document.getElementById('photoUpload');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                uploadArea.classList.add('has-image');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Vérifier disponibilité du code
    function checkCode() {
        const code = document.getElementById('code').value;
        const status = document.getElementById('codeStatus');
        
        if (code.length >= 4) {
            fetch('check_code.php?code=' + encodeURIComponent(code))
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        status.innerHTML = '✅ Code disponible';
                        status.style.color = '#10b981';
                    } else {
                        status.innerHTML = '❌ Ce code existe déjà';
                        status.style.color = '#ef4444';
                    }
                });
        } else {
            status.innerHTML = '';
        }
    }

    // Vérifier disponibilité de l'email
    function checkEmail() {
        const email = document.getElementById('email').value;
        const status = document.getElementById('emailStatus');
        
        if (email.length > 5 && email.includes('@')) {
            fetch('check_email.php?email=' + encodeURIComponent(email))
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        status.innerHTML = '✅ Email disponible';
                        status.style.color = '#10b981';
                    } else {
                        status.innerHTML = '❌ Cet email existe déjà';
                        status.style.color = '#ef4444';
                    }
                });
        } else {
            status.innerHTML = '';
        }
    }

    // Validation formulaire
    document.getElementById('studentForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');
        
        // Désactiver le bouton
        submitBtn.disabled = true;
        btnText.style.opacity = '0.7';
        spinner.style.display = 'inline-block';
        
        // Vérifications supplémentaires si besoin
        const code = document.getElementById('code').value;
        if (!code.match(/^E[0-9]{3}$/)) {
            e.preventDefault();
            alert('Le code doit être au format E001, E002, etc.');
            return false;
        }
    });

    // Formatage téléphone
    document.getElementById('telephone').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>

<?php require_once '../../includes/footer.php'; ?>