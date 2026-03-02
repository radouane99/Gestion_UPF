<?php
// admin/etudiants/modifier.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';
require_once '../../includes/functions.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

// Récupérer l'étudiant
$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE Code = ?");
$stmt->execute([$code]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "Étudiant introuvable"
    ];
    header('Location: liste.php');
    exit();
}

// Récupérer les filières
$filieres = $pdo->query("SELECT CodeF, IntituleF FROM filieres ORDER BY IntituleF")->fetchAll();

// Récupérer les statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE etudiant_id = ?");
$stmt->execute([$code]);
$nbDocuments = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE etudiant_id = ?");
$stmt->execute([$code]);
$hasAccount = $stmt->fetch() ? true : false;
?>

<style>
    /* ============================================= */
    /* STYLES POUR MODIFICATION ÉTUDIANT */
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
        position: relative;
        overflow: hidden;
    }

    .form-header::before {
        content: '✏️';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 120px;
        opacity: 0.1;
        transform: rotate(15deg);
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

    /* Info étudiant */
    .student-info-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .student-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 700;
        overflow: hidden;
    }

    .student-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-meta {
        flex: 1;
    }

    .student-meta h2 {
        font-size: 2rem;
        color: var(--dark);
        margin-bottom: 10px;
    }

    .student-badges {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .badge {
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .badge-code {
        background: rgba(41,72,152,0.1);
        color: var(--upf-blue);
    }

    .badge-docs {
        background: rgba(199,44,130,0.1);
        color: var(--upf-pink);
    }

    .badge-account {
        background: rgba(16,185,129,0.1);
        color: #10b981;
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

    .form-group input[readonly] {
        background: #f8fafc;
        cursor: not-allowed;
        border-color: #e2e8f0;
        color: var(--gray);
    }

    /* Zone photo */
    .photo-section {
        background: #f8fafc;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .current-photo {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .current-photo img {
        width: 80px;
        height: 80px;
        border-radius: 15px;
        object-fit: cover;
    }

    .photo-upload {
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        background: white;
    }

    .photo-upload:hover {
        border-color: var(--upf-pink);
        background: linear-gradient(135deg, rgba(41,72,152,0.02), rgba(199,44,130,0.02));
    }

    .photo-upload i {
        font-size: 2rem;
        color: var(--upf-pink);
        margin-bottom: 10px;
    }

    .photo-upload input {
        display: none;
    }

    /* Actions */
    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f1f5f9;
    }

    .left-actions, .right-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
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

    .btn-danger {
        background: white;
        color: var(--danger);
        border: 2px solid var(--danger);
    }

    .btn-danger:hover {
        background: var(--danger);
        color: white;
    }

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-warning {
        background: var(--warning);
        color: white;
    }

    /* Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid var(--warning);
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border-left: 4px solid var(--info);
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

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-group.full-width {
            grid-column: span 1;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .left-actions, .right-actions {
            width: 100%;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
        
        .student-info-card {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="form-page">
    
    <!-- En-tête -->
    <div class="form-header">
        <h1>
            <i class="fas fa-user-edit"></i>
            Modifier étudiant
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-user-graduate"></i> Étudiants</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></span>
        </div>
    </div>

    <!-- Messages d'info -->
    <?php if ($hasAccount): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Cet étudiant a déjà un compte utilisateur. La modification du code n'est pas possible.
        </div>
    <?php endif; ?>

    <!-- Info étudiant -->
    <div class="student-info-card">
        <div class="student-avatar-large">
            <?php if (!empty($etudiant['Photo']) && file_exists('../../' . $etudiant['Photo'])): ?>
                <img src="../../<?php echo $etudiant['Photo']; ?>" alt="Photo">
            <?php else: ?>
                <?php echo strtoupper(substr($etudiant['Prenom'], 0, 1) . substr($etudiant['Nom'], 0, 1)); ?>
            <?php endif; ?>
        </div>
        <div class="student-meta">
            <h2><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></h2>
            <div class="student-badges">
                <span class="badge badge-code">
                    <i class="fas fa-id-card"></i>
                    <?php echo $etudiant['Code']; ?>
                </span>
                <span class="badge badge-docs">
                    <i class="fas fa-file-pdf"></i>
                    <?php echo $nbDocuments; ?> document(s)
                </span>
                <?php if ($hasAccount): ?>
                    <span class="badge badge-account">
                        <i class="fas fa-check-circle"></i>
                        Compte actif
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulaire -->
    <form class="modern-form" action="modifier_traitement.php" method="POST" enctype="multipart/form-data" id="studentForm">
        <input type="hidden" name="original_code" value="<?php echo $etudiant['Code']; ?>">
        
        <div class="form-grid">
            <!-- Code (lecture seule si a un compte) -->
            <div class="form-group">
                <label>
                    <i class="fas fa-id-card"></i>
                    Code étudiant
                </label>
                <input type="text" 
                       name="code" 
                       id="code" 
                       value="<?php echo $etudiant['Code']; ?>" 
                       <?php echo $hasAccount ? 'readonly' : ''; ?>
                       pattern="E[0-9]{3}"
                       onkeyup="checkCode()">
                <?php if ($hasAccount): ?>
                    <small>Le code ne peut pas être modifié car un compte utilisateur existe</small>
                <?php else: ?>
                    <small>Format: E001, E002, ...</small>
                <?php endif; ?>
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
                       value="<?php echo htmlspecialchars($etudiant['Nom']); ?>" 
                       required
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
                       value="<?php echo htmlspecialchars($etudiant['Prenom']); ?>" 
                       required
                       onkeyup="this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1).toLowerCase()">
            </div>

            <!-- Filière -->
            <div class="form-group">
                <label>
                    <i class="fas fa-building"></i>
                    Filière
                </label>
                <select name="filiere" id="filiere">
                    <option value="">-- Non assigné --</option>
                    <?php foreach ($filieres as $f): ?>
                        <option value="<?php echo $f['CodeF']; ?>" 
                                <?php echo $etudiant['Filiere'] == $f['CodeF'] ? 'selected' : ''; ?>>
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
                <input type="date" 
                       name="date_naissance" 
                       id="date_naissance" 
                       value="<?php echo $etudiant['date_naissance']; ?>"
                       max="<?php echo date('Y-m-d', strtotime('-15 years')); ?>">
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
                       value="<?php echo $etudiant['FNote']; ?>">
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
                       value="<?php echo htmlspecialchars($etudiant['email']); ?>"
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
                       value="<?php echo $etudiant['telephone']; ?>"
                       pattern="[0-9]{10}">
            </div>
        </div>

        <!-- Section photo -->
        <div class="photo-section">
            <div class="current-photo">
                <span style="font-weight: 600;">Photo actuelle:</span>
                <?php if (!empty($etudiant['Photo']) && file_exists('../../' . $etudiant['Photo'])): ?>
                    <img src="../../<?php echo $etudiant['Photo']; ?>" alt="Photo actuelle">
                    <span><?php echo basename($etudiant['Photo']); ?></span>
                <?php else: ?>
                    <span style="color: var(--gray);">Aucune photo</span>
                <?php endif; ?>
            </div>

            <div class="photo-upload" onclick="document.getElementById('photo').click()">
                <i class="fas fa-camera"></i>
                <p>Changer la photo</p>
                <small>Formats: JPG, PNG • Max: 2 Mo</small>
                <input type="file" 
                       name="photo" 
                       id="photo" 
                       accept="image/jpeg,image/png"
                       onchange="previewImage(this)">
                <div id="photoPreview" style="margin-top: 15px;"></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <div class="left-actions">
                <a href="liste.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                <a href="detail.php?code=<?php echo $etudiant['Code']; ?>" class="btn btn-info" style="background: var(--info); color: white;">
                    <i class="fas fa-eye"></i>
                    Voir détails
                </a>
            </div>
            <div class="right-actions">
                <a href="supprimer.php?code=<?php echo $etudiant['Code']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span class="btn-text">Enregistrer</span>
                    <span class="loading-spinner" style="display: none;"></span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('photoPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 10px;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function checkCode() {
        const code = document.getElementById('code').value;
        const originalCode = '<?php echo $etudiant['Code']; ?>';
        const status = document.getElementById('codeStatus');
        
        if (code === originalCode) {
            status.innerHTML = '';
            return;
        }
        
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

    function checkEmail() {
        const email = document.getElementById('email').value;
        const originalEmail = '<?php echo $etudiant['email']; ?>';
        const status = document.getElementById('emailStatus');
        
        if (email === originalEmail) {
            status.innerHTML = '';
            return;
        }
        
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

    document.getElementById('studentForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');
        
        submitBtn.disabled = true;
        btnText.style.opacity = '0.7';
        spinner.style.display = 'inline-block';
    });
</script>

<?php require_once '../../includes/footer.php'; ?>