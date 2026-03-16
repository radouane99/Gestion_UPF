<?php
// admin/profil.php
require_once '../includes/auth_check_admin.php';
require_once '../config/database.php';
require_once '../includes/header.php';

$pdo = getConnexion();

// Récupérer les infos de l'admin connecté
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// Statistiques globales
$stats = [
    'etudiants' => $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn(),
    'filieres'  => $pdo->query("SELECT COUNT(*) FROM filieres")->fetchColumn(),
    'users'     => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'user'")->fetchColumn(),
    'documents' => $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn(),
];

// Détection du navigateur
$ua = $_SERVER['HTTP_USER_AGENT'];
if (strpos($ua, 'Chrome') !== false) $browser = 'Chrome';
elseif (strpos($ua, 'Firefox') !== false) $browser = 'Firefox';
elseif (strpos($ua, 'Safari') !== false) $browser = 'Safari';
else $browser = 'Autre';
?>

<style>
    /* ============================================= */
    /* STYLES MODERNES POUR LE PROFIL ADMIN */
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

    .profile-page {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* En-tête du profil */
    .profile-header {
        background: white;
        border-radius: 30px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 200px;
        background: var(--gradient);
        opacity: 0.1;
    }

    .avatar-container {
        position: relative;
        width: 150px;
        height: 150px;
        z-index: 2;
    }

    .profile-avatar {
        width: 100%;
        height: 100%;
        border-radius: 30px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: 700;
        color: white;
        box-shadow: 0 10px 30px rgba(199,44,130,0.3);
        border: 4px solid white;
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-upload {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: white;
        width: 45px;
        height: 45px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: all 0.3s;
        border: none;
        z-index: 10;
    }

    .avatar-upload:hover {
        transform: scale(1.1);
        background: var(--gradient);
        color: white;
    }

    .profile-info {
        flex: 1;
        z-index: 2;
    }

    .profile-info h1 {
        font-size: 2.5rem;
        color: var(--dark);
        margin-bottom: 10px;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .profile-badges {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 15px;
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

    .badge-role {
        background: rgba(41,72,152,0.1);
        color: var(--upf-blue);
    }

    .badge-online {
        background: rgba(16,185,129,0.1);
        color: #10b981;
    }

    .badge-ip {
        background: rgba(100,116,139,0.1);
        color: var(--gray);
    }

    /* Grille de statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s;
        border: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(41,72,152,0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgba(41,72,152,0.1), rgba(199,44,130,0.1));
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i {
        font-size: 2rem;
        color: var(--upf-pink);
    }

    .stat-card h3 {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
    }

    /* Grille d'informations */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .info-card {
        background: white;
        border-radius: 25px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(41,72,152,0.15);
    }

    .info-card h3 {
        font-size: 1.3rem;
        color: var(--dark);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f5f9;
    }

    .info-list {
        list-style: none;
    }

    .info-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 150px;
        color: var(--gray);
        font-size: 0.9rem;
    }

    .info-value {
        flex: 1;
        font-weight: 500;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Actions rapides */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .action-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        text-align: center;
        text-decoration: none;
        color: var(--dark);
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
    }

    .action-card:hover {
        transform: translateY(-5px);
        border-color: var(--upf-pink);
        box-shadow: 0 10px 30px rgba(199,44,130,0.15);
    }

    .action-card i {
        font-size: 2.5rem;
        background: var(--gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 15px;
    }

    .action-card h4 {
        margin-bottom: 5px;
    }

    .action-card p {
        color: var(--gray);
        font-size: 0.85rem;
    }

    /* Alertes */
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

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        .info-label {
            width: 120px;
        }
    }
</style>

<div class="profile-page">

    <!-- En-tête du profil avec avatar upload -->
    <div class="profile-header">
        <div class="avatar-container">
            <div class="profile-avatar">
                <?php if (!empty($admin['photo']) && file_exists('../' . $admin['photo'])): ?>
                    <img id="avatarPreview" src="../<?= htmlspecialchars($admin['photo']) ?>" alt="Photo admin">
                <?php else: ?>
                    <?php 
                    // Générer initiales à partir du login
                    $login_parts = explode(' ', $admin['login']);
                    $initials = '';
                    foreach ($login_parts as $part) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                    echo $initials ?: 'A';
                    ?>
                <?php endif; ?>
            </div>
            <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="photoForm">
                <label for="photoUpload" class="avatar-upload">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="photo" id="photoUpload" accept="image/png,image/jpeg" hidden>
            </form>
        </div>

        <div class="profile-info">
            <h1><?= htmlspecialchars($admin['login']) ?></h1>
            <div class="profile-badges">
                <span class="badge badge-role">
                    <i class="fas fa-crown"></i> Administrateur
                </span>
                <span class="badge badge-online">
                    <i class="fas fa-circle"></i> En ligne
                </span>
                <span class="badge badge-ip">
                    <i class="fas fa-network-wired"></i> <?= $_SERVER['REMOTE_ADDR'] ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $_SESSION['flash']['message'] ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Cartes statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
                <h3>Étudiants</h3>
                <div class="stat-number"><?= $stats['etudiants'] ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div>
                <h3>Filières</h3>
                <div class="stat-number"><?= $stats['filieres'] ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div>
                <h3>Utilisateurs</h3>
                <div class="stat-number"><?= $stats['users'] ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-pdf"></i></div>
            <div>
                <h3>Documents</h3>
                <div class="stat-number"><?= $stats['documents'] ?></div>
            </div>
        </div>
    </div>

    <!-- Grille d'informations -->
    <div class="info-grid">
        <!-- Informations du compte -->
        <div class="info-card">
            <h3>Mon compte</h3>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">Login</span>
                    <span class="info-value"><?= htmlspecialchars($admin['login']) ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Rôle</span>
                    <span class="info-value">Administrateur</span>
                </li>
                <li class="info-item">
                    <span class="info-label">Dernière connexion</span>
                    <span class="info-value">
                        <i class="fas fa-clock"></i>
                        <?= $admin['derniere_connexion'] ? date('d/m/Y H:i', strtotime($admin['derniere_connexion'])) : 'Première connexion' ?>
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">Compte créé</span>
                    <span class="info-value">
                        <i class="fas fa-calendar"></i>
                        <?= date('d/m/Y', strtotime($admin['created_at'])) ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- Informations de session -->
        <div class="info-card">
            <h3>Session actuelle</h3>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">IP</span>
                    <span class="info-value"><i class="fas fa-network-wired"></i> <?= $_SERVER['REMOTE_ADDR'] ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Navigateur</span>
                    <span class="info-value"><i class="fas fa-globe"></i> <?= $browser ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Connexion</span>
                    <span class="info-value"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($_SESSION['heure_connexion'])) ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
        <a href="dashboard.php" class="action-card">
            <i class="fas fa-chart-pie"></i>
            <h4>Dashboard</h4>
            <p>Retour au tableau de bord</p>
        </a>
        <a href="etudiants/liste.php" class="action-card">
            <i class="fas fa-user-graduate"></i>
            <h4>Étudiants</h4>
            <p>Gestion des étudiants</p>
        </a>
        <a href="filieres/liste.php" class="action-card">
            <i class="fas fa-building"></i>
            <h4>Filières</h4>
            <p>Gestion des filières</p>
        </a>
        <a href="utilisateurs/liste.php" class="action-card">
            <i class="fas fa-users-cog"></i>
            <h4>Utilisateurs</h4>
            <p>Gestion des comptes</p>
        </a>
        <a href="../auth/logout.php" class="action-card">
            <i class="fas fa-sign-out-alt"></i>
            <h4>Déconnexion</h4>
            <p>Quitter l'application</p>
        </a>
    </div>
</div>

<script>
    const input = document.getElementById('photoUpload');
    const preview = document.getElementById('avatarPreview');
    const form = document.getElementById('photoForm');

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Optionnel : afficher un aperçu avant envoi
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);

        // Soumettre automatiquement le formulaire
        form.submit();
    });
</script>

