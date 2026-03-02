<?php
// admin/dashboard.php - Version Moderne
require_once '../includes/auth_check_admin.php';
require_once '../config/database.php';
require_once '../includes/header.php';

$pdo = getConnexion();

// =============================================
// STATISTIQUES GÉNÉRALES
// =============================================

// Total étudiants
$stmt = $pdo->query("SELECT COUNT(*) FROM etudiants");
$totalEtudiants = $stmt->fetchColumn();

// Total filières
$stmt = $pdo->query("SELECT COUNT(*) FROM filieres");
$totalFilieres = $stmt->fetchColumn();

// Total utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
$totalUtilisateurs = $stmt->fetchColumn();

// Total documents
$stmt = $pdo->query("SELECT COUNT(*) FROM documents");
$totalDocuments = $stmt->fetchColumn();

// =============================================
// STATISTIQUES PÉDAGOGIQUES
// =============================================

// Étudiants par filière
$stmt = $pdo->query("
    SELECT f.CodeF, f.IntituleF, COUNT(e.Code) as nb_etudiants
    FROM filieres f
    LEFT JOIN etudiants e ON f.CodeF = e.Filiere
    GROUP BY f.CodeF
    ORDER BY nb_etudiants DESC
");
$etudiantsParFiliere = $stmt->fetchAll();

// Répartition des notes
$stmt = $pdo->query("
    SELECT 
        SUM(CASE WHEN FNote >= 16 THEN 1 ELSE 0 END) as tres_bien,
        SUM(CASE WHEN FNote >= 14 AND FNote < 16 THEN 1 ELSE 0 END) as bien,
        SUM(CASE WHEN FNote >= 12 AND FNote < 14 THEN 1 ELSE 0 END) as assez_bien,
        SUM(CASE WHEN FNote >= 10 AND FNote < 12 THEN 1 ELSE 0 END) as passable,
        SUM(CASE WHEN FNote < 10 AND FNote IS NOT NULL THEN 1 ELSE 0 END) as insuffisant,
        SUM(CASE WHEN FNote IS NULL THEN 1 ELSE 0 END) as non_evalue
    FROM etudiants
");
$repartitionNotes = $stmt->fetch();

// Statistiques de notes
$stmt = $pdo->query("
    SELECT 
        AVG(FNote) as moyenne_generale,
        MAX(FNote) as note_max,
        MIN(FNote) as note_min,
        COUNT(*) as nb_notes
    FROM etudiants 
    WHERE FNote IS NOT NULL
");
$statsNotes = $stmt->fetch();

// Meilleurs étudiants
$stmt = $pdo->query("
    SELECT e.*, f.IntituleF 
    FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.FNote IS NOT NULL
    ORDER BY e.FNote DESC
    LIMIT 5
");
$meilleursEtudiants = $stmt->fetchAll();

// Derniers étudiants inscrits
$stmt = $pdo->query("
    SELECT e.*, f.IntituleF 
    FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    ORDER BY e.created_at DESC
    LIMIT 5
");
$derniersEtudiants = $stmt->fetchAll();

// =============================================
// ACTIVITÉ RÉCENTE
// =============================================

// Dernières connexions
$stmt = $pdo->query("
    SELECT u.login, u.role, u.derniere_connexion, e.Nom, e.Prenom
    FROM utilisateurs u
    LEFT JOIN etudiants e ON u.etudiant_id = e.Code
    WHERE u.derniere_connexion IS NOT NULL
    ORDER BY u.derniere_connexion DESC
    LIMIT 5
");
$dernieresConnexions = $stmt->fetchAll();

// Derniers documents uploadés
$stmt = $pdo->query("
    SELECT d.*, u.login as uploaded_by_login, e.Nom, e.Prenom
    FROM documents d
    JOIN utilisateurs u ON d.uploaded_by = u.id
    JOIN etudiants e ON d.etudiant_id = e.Code
    ORDER BY d.uploaded_at DESC
    LIMIT 5
");
$derniersDocuments = $stmt->fetchAll();

// =============================================
// TAUX DE RÉUSSITE
// =============================================

$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN FNote >= 10 THEN 1 ELSE 0 END) as reussite
    FROM etudiants 
    WHERE FNote IS NOT NULL
");
$tauxReussite = $stmt->fetch();
$pourcentageReussite = $tauxReussite['total'] > 0 
    ? round(($tauxReussite['reussite'] / $tauxReussite['total']) * 100, 1) 
    : 0;

// =============================================
// FILIÈRE AVEC MEILLEURE MOYENNE
// =============================================

$stmt = $pdo->query("
    SELECT f.IntituleF, AVG(e.FNote) as moyenne
    FROM filieres f
    JOIN etudiants e ON f.CodeF = e.Filiere
    WHERE e.FNote IS NOT NULL
    GROUP BY f.CodeF
    ORDER BY moyenne DESC
    LIMIT 1
");
$meilleureFiliere = $stmt->fetch();
?>

<!-- ============================================= -->
<!-- STYLES CSS MODERNES -->
<!-- ============================================= -->
<style>
    :root {
        --upf-blue: #294898;
        --upf-pink: #C72C82;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --dark: #1f2937;
        --light: #f9fafb;
        --gray: #6b7280;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .modern-dashboard {
        padding: 20px;
        background: #f3f4f6;
        min-height: 100vh;
    }

    /* En-tête du dashboard */
    .dashboard-header {
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        color: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(41, 72, 152, 0.3);
    }

    .dashboard-header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .dashboard-header .welcome {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .welcome-info {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }

    .welcome-info span {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 10px 20px;
        border-radius: 50px;
        backdrop-filter: blur(10px);
    }

    /* Cartes de statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(41, 72, 152, 0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--upf-blue), var(--upf-pink));
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgba(41, 72, 152, 0.1), rgba(199, 44, 130, 0.1));
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .stat-icon i {
        font-size: 30px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-content h3 {
        color: var(--gray);
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .stat-number {
        font-size: 2.5em;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.9em;
        color: var(--success);
    }

    /* Grille à deux colonnes */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .dashboard-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .card-header h2 {
        font-size: 1.3em;
        color: var(--dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header h2 i {
        color: var(--upf-pink);
    }

    .card-header a {
        color: var(--upf-blue);
        text-decoration: none;
        font-size: 0.9em;
        font-weight: 500;
        transition: color 0.3s;
    }

    .card-header a:hover {
        color: var(--upf-pink);
    }

    /* Graphique en barres */
    .chart-container {
        margin: 20px 0;
    }

    .chart-bar {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .chart-label {
        width: 120px;
        font-size: 0.9em;
        color: var(--gray);
    }

    .chart-progress {
        flex: 1;
        height: 30px;
        background: #f0f0f0;
        border-radius: 15px;
        overflow: hidden;
        position: relative;
    }

    .chart-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--upf-blue), var(--upf-pink));
        border-radius: 15px;
        transition: width 1s ease;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 10px;
        color: white;
        font-size: 0.8em;
        font-weight: 600;
    }

    /* Liste des étudiants */
    .student-list {
        list-style: none;
    }

    .student-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.3s;
    }

    .student-item:hover {
        background: #f9fafb;
        transform: translateX(5px);
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        margin-right: 15px;
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
        font-size: 0.8em;
        color: var(--gray);
        display: flex;
        gap: 15px;
    }

    .student-note {
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.9em;
    }

    .note-excellent { background: #d1fae5; color: #065f46; }
    .note-bien { background: #dbeafe; color: #1e40af; }
    .note-passable { background: #fef3c7; color: #92400e; }
    .note-insuffisant { background: #fee2e2; color: #991b1b; }

    /* Badges */
    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.7em;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }

    /* Actions rapides */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 30px;
    }

    .action-btn {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: var(--dark);
        transition: all 0.3s;
    }

    .action-btn:hover {
        transform: translateY(-5px);
        border-color: var(--upf-pink);
        box-shadow: 0 10px 20px rgba(199, 44, 130, 0.1);
    }

    .action-btn i {
        font-size: 30px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 10px;
        display: block;
    }

    .action-btn span {
        font-weight: 600;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-in {
        animation: slideIn 0.5s ease forwards;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header h1 {
            font-size: 1.8em;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .welcome-info {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<!-- ============================================= -->
<!-- CONTENU DU DASHBOARD -->
<!-- ============================================= -->
<div class="modern-dashboard">
    
    <!-- En-tête -->
    <div class="dashboard-header animate-slide-in">
        <h1>📊 Tableau de bord</h1>
        <div class="welcome">
            <div class="welcome-info">
                <span>
                    <i>👋</i> Bienvenue, <strong><?php echo $_SESSION['login']; ?></strong>
                </span>
                <span>
                    <i>🕐</i> Connexion: <?php echo date('d/m/Y H:i', strtotime($_SESSION['heure_connexion'])); ?>
                </span>
                <span>
                    <i>🌐</i> IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
                </span>
            </div>
            <div class="date">
                📅 <?php echo date('l d F Y'); ?>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="stats-grid">
        <div class="stat-card animate-slide-in" style="animation-delay: 0.1s;">
            <div class="stat-icon">
                <i>👥</i>
            </div>
            <div class="stat-content">
                <h3>Étudiants</h3>
                <div class="stat-number"><?php echo $totalEtudiants; ?></div>
                <div class="stat-trend">
                    <span>📚 Répartis dans <?php echo $totalFilieres; ?> filières</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide-in" style="animation-delay: 0.2s;">
            <div class="stat-icon">
                <i>📊</i>
            </div>
            <div class="stat-content">
                <h3>Moyenne générale</h3>
                <div class="stat-number"><?php echo $statsNotes['moyenne_generale'] ? number_format($statsNotes['moyenne_generale'], 2) : 'N/A'; ?>/20</div>
                <div class="stat-trend">
                    <span>📈 Max: <?php echo $statsNotes['note_max'] ?? 'N/A'; ?> | Min: <?php echo $statsNotes['note_min'] ?? 'N/A'; ?></span>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide-in" style="animation-delay: 0.3s;">
            <div class="stat-icon">
                <i>✅</i>
            </div>
            <div class="stat-content">
                <h3>Taux de réussite</h3>
                <div class="stat-number"><?php echo $pourcentageReussite; ?>%</div>
                <div class="stat-trend">
                    <span>🏆 <?php echo $tauxReussite['reussite'] ?? 0; ?> étudiants reçus</span>
                </div>
            </div>
        </div>

        <div class="stat-card animate-slide-in" style="animation-delay: 0.4s;">
            <div class="stat-icon">
                <i>📄</i>
            </div>
            <div class="stat-content">
                <h3>Documents</h3>
                <div class="stat-number"><?php echo $totalDocuments; ?></div>
                <div class="stat-trend">
                    <span>📎 PDF uploadés</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille principale -->
    <div class="dashboard-grid">
        <!-- Répartition des notes -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.5s;">
            <div class="card-header">
                <h2><i>📊</i> Répartition des notes</h2>
                <a href="etudiants/liste.php">Voir tout →</a>
            </div>
            <div class="chart-container">
                <?php
                $totalNotes = array_sum($repartitionNotes);
                $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#6b7280'];
                $labels = ['Très bien (16-20)', 'Bien (14-16)', 'Assez bien (12-14)', 'Passable (10-12)', 'Insuffisant (<10)', 'Non évalué'];
                $values = [
                    $repartitionNotes['tres_bien'],
                    $repartitionNotes['bien'],
                    $repartitionNotes['assez_bien'],
                    $repartitionNotes['passable'],
                    $repartitionNotes['insuffisant'],
                    $repartitionNotes['non_evalue']
                ];
                
                foreach ($labels as $index => $label):
                    $value = $values[$index];
                    $pourcentage = $totalNotes > 0 ? round(($value / $totalNotes) * 100, 1) : 0;
                ?>
                <div class="chart-bar">
                    <div class="chart-label"><?php echo $label; ?></div>
                    <div class="chart-progress">
                        <div class="chart-fill" style="width: <?php echo $pourcentage; ?>%;">
                            <?php echo $value; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Meilleurs étudiants -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.6s;">
            <div class="card-header">
                <h2><i>🏆</i> Top 5 étudiants</h2>
                <a href="etudiants/liste.php?tri=notes">Voir tout →</a>
            </div>
            <ul class="student-list">
                <?php foreach ($meilleursEtudiants as $index => $etudiant): ?>
                <li class="student-item">
                    <div class="student-avatar">#<?php echo $index + 1; ?></div>
                    <div class="student-info">
                        <div class="student-name"><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></div>
                        <div class="student-details">
                            <span><?php echo $etudiant['IntituleF'] ?? 'Non assigné'; ?></span>
                        </div>
                    </div>
                    <div class="student-note <?php
                        if ($etudiant['FNote'] >= 16) echo 'note-excellent';
                        elseif ($etudiant['FNote'] >= 14) echo 'note-bien';
                        elseif ($etudiant['FNote'] >= 12) echo 'note-passable';
                        else echo 'note-insuffisant';
                    ?>">
                        <?php echo $etudiant['FNote']; ?>/20
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Derniers inscrits -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.7s;">
            <div class="card-header">
                <h2><i>🆕</i> Derniers inscrits</h2>
                <a href="etudiants/liste.php">Voir tout →</a>
            </div>
            <ul class="student-list">
                <?php foreach ($derniersEtudiants as $etudiant): ?>
                <li class="student-item">
                    <div class="student-avatar">
                        <?php echo strtoupper(substr($etudiant['Prenom'], 0, 1) . substr($etudiant['Nom'], 0, 1)); ?>
                    </div>
                    <div class="student-info">
                        <div class="student-name"><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></div>
                        <div class="student-details">
                            <span>📚 <?php echo $etudiant['IntituleF'] ?? 'Non assigné'; ?></span>
                            <span>📅 <?php echo date('d/m/Y', strtotime($etudiant['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php if ($etudiant['FNote']): ?>
                    <div class="student-note note-passable">
                        <?php echo $etudiant['FNote']; ?>/20
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Activité récente -->
        <div class="dashboard-card animate-slide-in" style="animation-delay: 0.8s;">
            <div class="card-header">
                <h2><i>🕐</i> Dernières connexions</h2>
                <a href="../utilisateurs/liste.php">Voir tout →</a>
            </div>
            <ul class="student-list">
                <?php foreach ($dernieresConnexions as $connexion): ?>
                <li class="student-item">
                    <div class="student-avatar">
                        <?php echo $connexion['role'] == 'admin' ? '👑' : '👤'; ?>
                    </div>
                    <div class="student-info">
                        <div class="student-name">
                            <?php echo htmlspecialchars($connexion['login']); ?>
                            <?php if ($connexion['role'] == 'admin'): ?>
                                <span class="badge badge-success">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-info">Étudiant</span>
                            <?php endif; ?>
                        </div>
                        <div class="student-details">
                            <span><?php echo $connexion['Prenom'] ? htmlspecialchars($connexion['Prenom'] . ' ' . $connexion['Nom']) : '—'; ?></span>
                            <span>📱 <?php echo date('d/m/Y H:i', strtotime($connexion['derniere_connexion'])); ?></span>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Statistiques par filière -->
    <div class="dashboard-card animate-slide-in" style="animation-delay: 0.9s;">
        <div class="card-header">
            <h2><i>🏛️</i> Effectifs par filière</h2>
            <a href="../filieres/liste.php">Gérer les filières →</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($etudiantsParFiliere as $filiere): ?>
            <div style="background: #f9fafb; padding: 15px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong><?php echo htmlspecialchars($filiere['IntituleF']); ?></strong>
                    <span style="background: var(--upf-blue); color: white; padding: 3px 10px; border-radius: 20px;">
                        <?php echo $filiere['nb_etudiants']; ?> étudiants
                    </span>
                </div>
                <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                    <?php 
                    $pourcentage = $totalEtudiants > 0 ? ($filiere['nb_etudiants'] / $totalEtudiants) * 100 : 0;
                    ?>
                    <div style="height: 100%; width: <?php echo $pourcentage; ?>%; background: linear-gradient(90deg, var(--upf-blue), var(--upf-pink));"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
        <a href="etudiants/ajouter.php" class="action-btn">
            <i>➕</i>
            <span>Ajouter étudiant</span>
        </a>
        <a href="etudiants/liste.php" class="action-btn">
            <i>📋</i>
            <span>Liste étudiants</span>
        </a>
        <a href="filieres/ajouter.php" class="action-btn">
            <i>🏛️</i>
            <span>Ajouter filière</span>
        </a>
        <a href="../utilisateurs/ajouter.php" class="action-btn">
            <i>👥</i>
            <span>Nouvel utilisateur</span>
        </a>
        <a href="etudiants/liste.php?statistiques=1" class="action-btn">
            <i>📊</i>
            <span>Statistiques</span>
        </a>
        <a href="../user/profil.php" class="action-btn">
            <i>👤</i>
            <span>Mon profil</span>
        </a>
    </div>
</div>

<!-- Script pour animations supplémentaires -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des chiffres
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const value = stat.innerText;
        if (!isNaN(parseFloat(value))) {
            let start = 0;
            let end = parseFloat(value);
            let duration = 1000;
            let stepTime = Math.abs(Math.floor(duration / end));
            
            let timer = setInterval(function() {
                start += 1;
                stat.innerText = start;
                if (start >= end) {
                    stat.innerText = value;
                    clearInterval(timer);
                }
            }, stepTime);
        }
    });

    // Tooltips automatiques
    const chartBars = document.querySelectorAll('.chart-bar');
    chartBars.forEach(bar => {
        bar.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.3s';
        });
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>