<?php
// admin/etudiants/supprimer.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

// Récupérer l'étudiant avec ses infos
$stmt = $pdo->prepare("
    SELECT e.*, f.IntituleF,
           (SELECT COUNT(*) FROM documents WHERE etudiant_id = e.Code) as nb_documents,
           (SELECT id FROM utilisateurs WHERE etudiant_id = e.Code) as user_id
    FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Code = ?
");
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
?>

<style>
    /* ============================================= */
    /* STYLES PAGE SUPPRESSION */
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

    .delete-page {
        padding: 20px;
        max-width: 700px;
        margin: 40px auto;
    }

    .delete-card {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .delete-card::before {
        content: '⚠️';
        position: absolute;
        top: -30px;
        right: -30px;
        font-size: 150px;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .warning-icon {
        width: 100px;
        height: 100px;
        background: #fee2e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
    }

    .warning-icon i {
        font-size: 3rem;
        color: var(--danger);
    }

    h1 {
        font-size: 2rem;
        color: var(--dark);
        margin-bottom: 15px;
    }

    .warning-text {
        color: var(--danger);
        font-weight: 600;
        margin-bottom: 30px;
        padding: 15px;
        background: #fee2e2;
        border-radius: 15px;
        display: inline-block;
    }

    .student-summary {
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
        border-radius: 20px;
        padding: 30px;
        margin: 30px 0;
        text-align: left;
    }

    .student-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin: 0 auto 20px;
        overflow: hidden;
    }

    .student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-name {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 10px;
        text-align: center;
    }

    .student-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 20px;
    }

    .detail-item {
        padding: 15px;
        background: white;
        border-radius: 15px;
        text-align: center;
    }

    .detail-label {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .detail-value {
        font-weight: 600;
        color: var(--dark);
    }

    .danger-zone {
        background: #fee2e2;
        border-radius: 20px;
        padding: 25px;
        margin: 30px 0;
        border: 2px solid var(--danger);
    }

    .danger-zone h3 {
        color: var(--danger);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .danger-zone ul {
        text-align: left;
        padding-left: 20px;
        color: #991b1b;
    }

    .danger-zone li {
        margin: 10px 0;
    }

    .actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
    }

    .btn {
        padding: 15px 40px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239,68,68,0.4);
    }

    .btn-secondary {
        background: white;
        color: var(--dark);
        border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
    }

    .checkbox-confirm {
        margin: 20px 0;
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .checkbox-confirm input {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .checkbox-confirm label {
        cursor: pointer;
        color: var(--dark);
    }
</style>

<div class="delete-page">
    <div class="delete-card">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h1>Supprimer l'étudiant</h1>
        <div class="warning-text">
            ⚠️ Cette action est irréversible
        </div>

        <!-- Résumé étudiant -->
        <div class="student-summary">
            <div class="student-avatar">
                <?php if (!empty($etudiant['Photo']) && file_exists('../../' . $etudiant['Photo'])): ?>
                    <img src="../../<?php echo $etudiant['Photo']; ?>" alt="Photo">
                <?php else: ?>
                    <?php echo strtoupper(substr($etudiant['Prenom'], 0, 1) . substr($etudiant['Nom'], 0, 1)); ?>
                <?php endif; ?>
            </div>
            
            <div class="student-name">
                <?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?>
            </div>

            <div class="student-details">
                <div class="detail-item">
                    <div class="detail-label">Code</div>
                    <div class="detail-value"><?php echo $etudiant['Code']; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Filière</div>
                    <div class="detail-value"><?php echo $etudiant['IntituleF'] ?? 'Non assigné'; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Documents</div>
                    <div class="detail-value"><?php echo $etudiant['nb_documents']; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Note</div>
                    <div class="detail-value"><?php echo $etudiant['FNote'] ?? '---'; ?>/20</div>
                </div>
            </div>
        </div>

        <!-- Zone de danger - éléments à supprimer -->
        <div class="danger-zone">
            <h3><i class="fas fa-trash-alt"></i> Les éléments suivants seront supprimés :</h3>
            <ul>
                <li><i class="fas fa-user"></i> Les informations de l'étudiant</li>
                <?php if (!empty($etudiant['Photo'])): ?>
                    <li><i class="fas fa-image"></i> La photo de profil</li>
                <?php endif; ?>
                <?php if ($etudiant['nb_documents'] > 0): ?>
                    <li><i class="fas fa-file-pdf"></i> <?php echo $etudiant['nb_documents']; ?> document(s) PDF</li>
                <?php endif; ?>
                <?php if ($etudiant['user_id']): ?>
                    <li><i class="fas fa-user-cog"></i> Le compte utilisateur associé</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Formulaire de confirmation -->
        <form action="supprimer_traitement.php" method="POST" id="deleteForm">
            <input type="hidden" name="code" value="<?php echo $etudiant['Code']; ?>">
            
            <div class="checkbox-confirm">
                <input type="checkbox" id="confirm" name="confirm" required>
                <label for="confirm">Je confirme vouloir supprimer définitivement cet étudiant</label>
            </div>

            <div class="actions">
                <a href="liste.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
                <button type="submit" class="btn btn-danger" id="submitBtn">
                    <i class="fas fa-trash"></i>
                    Supprimer définitivement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        const confirmCheck = document.getElementById('confirm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (!confirmCheck.checked) {
            e.preventDefault();
            alert('Veuillez confirmer la suppression');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression...';
    });
</script>

<?php require_once '../../includes/footer.php'; ?>