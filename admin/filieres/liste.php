<?php
// admin/filieres/liste.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$pdo = getConnexion();

// Récupérer les filières avec nombre d'étudiants
$sql = "SELECT f.*, 
               COUNT(e.Code) as nb_etudiants,
               AVG(e.FNote) as moyenne_filiere,
               SUM(CASE WHEN e.FNote >= 10 THEN 1 ELSE 0 END) as nb_reussite
        FROM filieres f
        LEFT JOIN etudiants e ON f.CodeF = e.Filiere
        GROUP BY f.CodeF
        ORDER BY f.CodeF";

$filieres = $pdo->query($sql)->fetchAll();

// Statistiques globales
$stats = [
    'total' => count($filieres),
    'total_etudiants' => $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn(),
    'places_dispo' => array_sum(array_column($filieres, 'nbPlaces')),
    'moyenne_globale' => $pdo->query("SELECT AVG(FNote) FROM etudiants WHERE FNote IS NOT NULL")->fetchColumn()
];
?>

<style>
    /* ============================================= */
    /* STYLES POUR LA GESTION DES FILIÈRES */
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

    .filieres-page {
        padding: 20px;
    }

    /* En-tête */
    .page-header {
        background: white;
        border-radius: 30px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
    }

    .page-header h1 {
        font-size: 2.5rem;
        color: var(--dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-header h1 i {
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

    /* Stats cards */
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
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(41,72,152,0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: rgba(41,72,152,0.1);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .stat-icon i {
        font-size: 24px;
        color: var(--upf-blue);
    }

    .stat-content h3 {
        color: var(--gray);
        font-size: 0.9rem;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
    }

    /* Barre d'outils */
    .toolbar {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .btn-add {
        background: var(--gradient);
        color: white;
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        box-shadow: 0 5px 15px rgba(199,44,130,0.3);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(199,44,130,0.4);
    }

    /* Tableau */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow-x: auto;
    }

    .filieres-table {
        width: 100%;
        border-collapse: collapse;
    }

    .filieres-table th {
        text-align: left;
        padding: 15px;
        font-weight: 600;
        color: var(--gray);
        border-bottom: 2px solid #e2e8f0;
    }

    .filieres-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .filieres-table tbody tr {
        transition: all 0.3s;
    }

    .filieres-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(41,72,152,0.02), rgba(199,44,130,0.02));
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Badges */
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Progress bar */
    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin: 5px 0;
    }

    .progress-fill {
        height: 100%;
        background: var(--gradient);
        border-radius: 4px;
        transition: width 0.3s;
    }

    /* Actions */
    .actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s;
    }

    .action-btn.edit {
        background: rgba(245,158,11,0.1);
        color: #f59e0b;
    }

    .action-btn.edit:hover {
        background: #f59e0b;
        color: white;
    }

    .action-btn.delete {
        background: rgba(239,68,68,0.1);
        color: #ef4444;
    }

    .action-btn.delete:hover {
        background: #ef4444;
        color: white;
    }

    .action-btn.view {
        background: rgba(59,130,246,0.1);
        color: #3b82f6;
    }

    .action-btn.view:hover {
        background: #3b82f6;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .toolbar {
            flex-direction: column;
        }
        
        .btn-add {
            width: 100%;
            justify-content: center;
        }
        
        .filieres-table {
            font-size: 0.85rem;
        }
        
        .filieres-table th:nth-child(4),
        .filieres-table td:nth-child(4) {
            display: none;
        }
    }
</style>

<div class="filieres-page">
    
    <!-- En-tête -->
    <div class="page-header">
        <h1>
            <i class="fas fa-building"></i>
            Gestion des Filières
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <span>Filières</span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content">
                <h3>Total filières</h3>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Étudiants</h3>
                <div class="stat-number"><?php echo $stats['total_etudiants']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chair"></i>
            </div>
            <div class="stat-content">
                <h3>Places totales</h3>
                <div class="stat-number"><?php echo $stats['places_dispo'] ?: 'N/A'; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>Moyenne globale</h3>
                <div class="stat-number"><?php echo number_format($stats['moyenne_globale'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Barre d'outils -->
    <div class="toolbar">
        <a href="ajouter.php" class="btn-add">
            <i class="fas fa-plus-circle"></i>
            Nouvelle filière
        </a>
    </div>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert" style="
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            background: <?php echo $_SESSION['flash']['type'] == 'success' ? '#d1fae5' : '#fee2e2'; ?>;
            color: <?php echo $_SESSION['flash']['type'] == 'success' ? '#065f46' : '#991b1b'; ?>;
            border-left: 4px solid <?php echo $_SESSION['flash']['type'] == 'success' ? '#10b981' : '#ef4444'; ?>;
            display: flex;
            justify-content: space-between;
            align-items: center;
        ">
            <span><?php echo $_SESSION['flash']['message']; ?></span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Tableau des filières -->
    <div class="table-container">
        <table class="filieres-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Intitulé</th>
                    <th>Responsable</th>
                    <th>Places</th>
                    <th>Étudiants</th>
                    <th>Occupation</th>
                    <th>Moyenne</th>
                    <th>Réussite</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($filieres) > 0): ?>
                    <?php foreach ($filieres as $f): 
                        $pourcentage = $f['nbPlaces'] > 0 ? round(($f['nb_etudiants'] / $f['nbPlaces']) * 100) : 0;
                        $taux_reussite = $f['nb_etudiants'] > 0 ? round(($f['nb_reussite'] / $f['nb_etudiants']) * 100) : 0;
                    ?>
                        <tr>
                            <td><strong><?php echo $f['CodeF']; ?></strong></td>
                            <td><?php echo htmlspecialchars($f['IntituleF']); ?></td>
                            <td><?php echo htmlspecialchars($f['responsable'] ?? 'Non défini'); ?></td>
                            <td><?php echo $f['nbPlaces'] ?? 'N/A'; ?></td>
                            <td>
                                <span style="font-weight: 600;"><?php echo $f['nb_etudiants']; ?></span>
                            </td>
                            <td style="min-width: 120px;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $pourcentage; ?>%;"></div>
                                </div>
                                <small><?php echo $pourcentage; ?>%</small>
                            </td>
                            <td>
                                <?php if ($f['moyenne_filiere']): ?>
                                    <span class="badge <?php 
                                        echo $f['moyenne_filiere'] >= 14 ? 'badge-success' : 
                                            ($f['moyenne_filiere'] >= 10 ? 'badge-warning' : 'badge-danger'); 
                                    ?>">
                                        <?php echo number_format($f['moyenne_filiere'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($taux_reussite > 0): ?>
                                    <span class="badge badge-success">
                                        <?php echo $taux_reussite; ?>%
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="voir.php?code=<?php echo $f['CodeF']; ?>" class="action-btn view" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="modifier.php?code=<?php echo $f['CodeF']; ?>" class="action-btn edit" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?code=<?php echo $f['CodeF']; ?>" class="action-btn delete" title="Supprimer" onclick="return confirm('Supprimer cette filière ? Les étudiants seront dissociés.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 50px;">
                            <i class="fas fa-building" style="font-size: 3rem; color: var(--gray); margin-bottom: 15px;"></i>
                            <h3 style="color: var(--gray);">Aucune filière</h3>
                            <p style="color: var(--gray);">Commencez par <a href="ajouter.php" style="color: var(--upf-pink);">ajouter une filière</a></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>