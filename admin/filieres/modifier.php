<?php
// admin/filieres/modifier.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

// Récupérer la filière
$stmt = $pdo->prepare("SELECT * FROM filieres WHERE CodeF = ?");
$stmt->execute([$code]);
$filiere = $stmt->fetch();

if (!$filiere) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "Filière introuvable"
    ];
    header('Location: liste.php');
    exit();
}

// Récupérer statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) as nb, AVG(FNote) as moyenne FROM etudiants WHERE Filiere = ?");
$stmt->execute([$code]);
$stats = $stmt->fetch();
?>

<style>
    /* Mêmes styles que ajouter.php */
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

    .form-page {
        padding: 20px;
        max-width: 700px;
        margin: 0 auto;
    }

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

    .stats-mini {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        gap: 30px;
        justify-content: space-around;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .stat-mini-item {
        text-align: center;
    }

    .stat-mini-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--upf-pink);
    }

    .stat-mini-label {
        color: var(--gray);
        font-size: 0.9rem;
    }

    .modern-form {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
    }

    .form-group label i {
        color: var(--upf-pink);
        margin-right: 8px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--upf-pink);
        box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
        outline: none;
    }

    .form-group input[readonly] {
        background: #f8fafc;
        cursor: not-allowed;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

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

    .left-actions, .right-actions {
        display: flex;
        gap: 10px;
    }

    .form-actions {
        justify-content: space-between;
    }
</style>

<div class="form-page">
    
    <!-- En-tête -->
    <div class="form-header">
        <h1>
            <i class="fas fa-edit"></i>
            Modifier filière
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-building"></i> Filières</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($filiere['CodeF']); ?></span>
        </div>
    </div>

    <!-- Mini statistiques -->
    <div class="stats-mini">
        <div class="stat-mini-item">
            <div class="stat-mini-value"><?php echo $stats['nb']; ?></div>
            <div class="stat-mini-label">Étudiants</div>
        </div>
        <div class="stat-mini-item">
            <div class="stat-mini-value"><?php echo $stats['moyenne'] ? number_format($stats['moyenne'], 2) : '-'; ?></div>
            <div class="stat-mini-label">Moyenne</div>
        </div>
        <div class="stat-mini-item">
            <div class="stat-mini-value"><?php echo $filiere['nbPlaces'] ?? '∞'; ?></div>
            <div class="stat-mini-label">Places</div>
        </div>
    </div>

    <!-- Formulaire -->
    <form class="modern-form" action="modifier_traitement.php" method="POST">
        <input type="hidden" name="original_code" value="<?php echo $filiere['CodeF']; ?>">
        
        <!-- Code (lecture seule) -->
        <div class="form-group">
            <label>
                <i class="fas fa-id-card"></i>
                Code filière
            </label>
            <input type="text" value="<?php echo $filiere['CodeF']; ?>" readonly>
            <small>Le code ne peut pas être modifié</small>
        </div>

        <!-- Intitulé -->
        <div class="form-group">
            <label>
                <i class="fas fa-heading"></i>
                Intitulé complet <span style="color: var(--danger);">*</span>
            </label>
            <input type="text" 
                   name="intitule" 
                   value="<?php echo htmlspecialchars($filiere['IntituleF']); ?>" 
                   required>
        </div>

        <!-- Responsable et Places -->
        <div class="form-row">
            <div class="form-group">
                <label>
                    <i class="fas fa-user-tie"></i>
                    Responsable
                </label>
                <input type="text" 
                       name="responsable" 
                       value="<?php echo htmlspecialchars($filiere['responsable'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-users"></i>
                    Places
                </label>
                <input type="number" 
                       name="nbPlaces" 
                       min="1" 
                       max="500" 
                       value="<?php echo $filiere['nbPlaces']; ?>">
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <div class="left-actions">
                <a href="liste.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </div>
            <div class="right-actions">
                <a href="supprimer.php?code=<?php echo $filiere['CodeF']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Supprimer cette filière ? Les étudiants seront dissociés.')">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>