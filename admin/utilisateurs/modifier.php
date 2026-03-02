<?php
// admin/utilisateurs/modifier.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;

$pdo = getConnexion();

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "Utilisateur introuvable"
    ];
    header('Location: liste.php');
    exit();
}

// Récupérer la liste des étudiants
$stmt = $pdo->query("SELECT Code, Nom, Prenom, email FROM etudiants ORDER BY Nom, Prenom");
$etudiants = $stmt->fetchAll();
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
    }

    .form-page {
        padding: 20px;
        max-width: 900px;
        margin: 0 auto;
    }

    .form-header {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
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
    }

    .breadcrumb {
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .breadcrumb a {
        color: var(--upf-pink);
        text-decoration: none;
    }

    .modern-form {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
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
    }

    .form-group label i {
        color: var(--upf-pink);
        margin-right: 8px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: var(--upf-pink);
        box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
        outline: none;
    }

    .form-group input[readonly] {
        background: #f8fafc;
        cursor: not-allowed;
    }

    .info-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        border-left: 4px solid var(--upf-pink);
    }

    .info-box h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--dark);
        margin-bottom: 10px;
    }

    .student-info {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        margin-top: 15px;
    }

    .student-avatar {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .student-details p {
        margin: 5px 0;
        color: var(--dark);
    }

    .student-details small {
        color: var(--gray);
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
    }

    .warning-box {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 15px;
        border-radius: 10px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

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
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="form-page">
    
    <!-- En-tête -->
    <div class="form-header">
        <h1>
            <i class="fas fa-user-edit"></i>
            Modifier l'utilisateur #<?php echo $user['id']; ?>
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier <?php echo htmlspecialchars($user['login']); ?></span>
        </div>
    </div>

    <!-- Message d'avertissement si modification de son propre compte -->
    <?php if ($user['id'] == $_SESSION['user_id']): ?>
        <div class="warning-box">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
            <div>
                <strong>Attention !</strong> Vous êtes en train de modifier votre propre compte.
                Soyez prudent avec les changements de rôle.
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form class="modern-form" action="modifier_traitement.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

        <div class="form-grid">
            <!-- Login (lecture seule) -->
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-user"></i>
                    Login
                </label>
                <input type="text" value="<?php echo htmlspecialchars($user['login']); ?>" readonly>
                <small>Le login ne peut pas être modifié</small>
            </div>

            <!-- Rôle -->
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-tag"></i>
                    Rôle
                </label>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="radio" name="role" value="admin" 
                               <?php echo $user['role'] == 'admin' ? 'checked' : ''; ?>
                               onchange="toggleStudentSelection()">
                        <span class="role-badge role-admin" style="padding: 8px 20px;">
                            <i class="fas fa-crown"></i>
                            Administrateur
                        </span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="radio" name="role" value="user" 
                               <?php echo $user['role'] == 'user' ? 'checked' : ''; ?>
                               onchange="toggleStudentSelection()">
                        <span class="role-badge role-user" style="padding: 8px 20px;">
                            <i class="fas fa-user-graduate"></i>
                            Étudiant
                        </span>
                    </label>
                </div>
            </div>

            <!-- Sélection étudiant -->
            <div class="form-group full-width" id="studentSelection" 
                 style="<?php echo $user['role'] == 'user' ? 'display: block;' : 'display: none;'; ?>">
                <label>
                    <i class="fas fa-user-graduate"></i>
                    Lier à un étudiant
                </label>

                <?php if ($user['role'] == 'user' && $user['etudiant_id']): ?>
                    <!-- Info étudiant actuel -->
                    <div class="info-box">
                        <h3><i class="fas fa-info-circle"></i> Étudiant actuellement lié</h3>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE Code = ?");
                        $stmt->execute([$user['etudiant_id']]);
                        $currentEtudiant = $stmt->fetch();
                        ?>
                        <?php if ($currentEtudiant): ?>
                            <div class="student-info">
                                <div class="student-avatar">
                                    <?php echo strtoupper(substr($currentEtudiant['Prenom'], 0, 1) . substr($currentEtudiant['Nom'], 0, 1)); ?>
                                </div>
                                <div class="student-details">
                                    <p><strong><?php echo htmlspecialchars($currentEtudiant['Prenom'] . ' ' . $currentEtudiant['Nom']); ?></strong></p>
                                    <p><small>Code: <?php echo $currentEtudiant['Code']; ?></small></p>
                                    <p><small>Email: <?php echo $currentEtudiant['email'] ?: 'Non renseigné'; ?></small></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <select name="etudiant_id" id="etudiantSelect" class="student-select">
                    <option value="">-- Choisir un étudiant --</option>
                    <?php foreach ($etudiants as $e): ?>
                        <option value="<?php echo $e['Code']; ?>" 
                                <?php echo $user['etudiant_id'] == $e['Code'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e['Prenom'] . ' ' . $e['Nom'] . ' (' . $e['Code'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Sélectionnez l'étudiant à associer à ce compte</small>
            </div>

            <!-- Informations complémentaires -->
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-calendar"></i>
                    Date de création
                </label>
                <input type="text" value="<?php echo date('d/m/Y H:i:s', strtotime($user['created_at'])); ?>" readonly>
            </div>

            <?php if ($user['derniere_connexion']): ?>
                <div class="form-group full-width">
                    <label>
                        <i class="fas fa-clock"></i>
                        Dernière connexion
                    </label>
                    <input type="text" value="<?php echo date('d/m/Y H:i:s', strtotime($user['derniere_connexion'])); ?>" readonly>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <div class="left-actions">
                <a href="liste.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
            </div>
            <div class="right-actions">
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <a href="reset_password.php?id=<?php echo $user['id']; ?>" 
                       class="btn btn-warning"
                       onclick="return confirm('Voulez-vous réinitialiser le mot de passe ?')">
                        <i class="fas fa-key"></i>
                        Réinitialiser mot de passe
                    </a>
                    <a href="supprimer.php?id=<?php echo $user['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                        <i class="fas fa-trash"></i>
                        Supprimer
                    </a>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleStudentSelection() {
        const role = document.querySelector('input[name="role"]:checked').value;
        const studentSelection = document.getElementById('studentSelection');
        
        if (role === 'admin') {
            studentSelection.style.display = 'none';
            document.getElementById('etudiantSelect').value = '';
        } else {
            studentSelection.style.display = 'block';
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>