<?php
// user/notes.php
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/functions.php';

$pdo = getConnexion();

// Récupérer les informations de l'étudiant
$stmt = $pdo->prepare("
    SELECT e.*, f.IntituleF, f.CodeF,
           (SELECT AVG(FNote) FROM etudiants WHERE Filiere = e.Filiere AND FNote IS NOT NULL) as moyenne_filiere,
           (SELECT COUNT(*) FROM etudiants WHERE Filiere = e.Filiere) as total_filiere,
           (SELECT COUNT(*) FROM etudiants WHERE Filiere = e.Filiere AND FNote > e.FNote) as mieux_classe,
           (SELECT MIN(FNote) FROM etudiants WHERE Filiere = e.Filiere AND FNote IS NOT NULL) as note_min_filiere,
           (SELECT MAX(FNote) FROM etudiants WHERE Filiere = e.Filiere AND FNote IS NOT NULL) as note_max_filiere
    FROM etudiants e
    LEFT JOIN filieres f ON e.Filiere = f.CodeF
    WHERE e.Code = ?
");
$stmt->execute([$_SESSION['etudiant_id']]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    header('Location: ../logout.php');
    exit();
}

// Déterminer mention et statut
$mentionInfo = getMention($etudiant['FNote']);
$statusInfo = getStatus($etudiant['FNote']);

// Classement
$rang = $etudiant['mieux_classe'] + 1;
$total = $etudiant['total_filiere'];

// Récupérer le top 5 de la filière
$stmt = $pdo->prepare("
    SELECT Code, Nom, Prenom, FNote,
           (SELECT COUNT(*) FROM etudiants e2 WHERE e2.Filiere = e.Filiere AND e2.FNote > e.FNote) + 1 as rang
    FROM etudiants e
    WHERE Filiere = ? AND FNote IS NOT NULL
    ORDER BY FNote DESC
    LIMIT 5
");
$stmt->execute([$etudiant['Filiere']]);
$topEtudiants = $stmt->fetchAll();

// Statistiques de la filière
$stats = [
    'moyenne' => $etudiant['moyenne_filiere'],
    'min' => $etudiant['note_min_filiere'],
    'max' => $etudiant['note_max_filiere'],
    'total' => $etudiant['total_filiere']
];
?>

<style>
    /* ============================================= */
    /* STYLES POUR LA PAGE NOTES */
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

    .notes-page {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
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

    /* Carte principale */
    .main-card {
        background: white;
        border-radius: 30px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }

    .main-card::before {
        content: '📊';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 150px;
        opacity: 0.05;
        transform: rotate(15deg);
    }

    /* Grille de notes */
    .notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    /* Carte note principale */
    .note-principale {
        background: var(--gradient);
        border-radius: 25px;
        padding: 30px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .note-principale::after {
        content: '🎓';
        position: absolute;
        bottom: -20px;
        right: -20px;
        font-size: 100px;
        opacity: 0.2;
        transform: rotate(-15deg);
    }

    .note-label {
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        opacity: 0.9;
        margin-bottom: 15px;
    }

    .note-valeur {
        font-size: 5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 15px;
    }

    .note-mention {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .note-statut {
        display: inline-block;
        padding: 8px 25px;
        background: rgba(255,255,255,0.2);
        border-radius: 30px;
        font-size: 1rem;
        backdrop-filter: blur(5px);
    }

    /* Cartes statistiques */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(41,72,152,0.15);
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-header i {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, rgba(41,72,152,0.1), rgba(199,44,130,0.1));
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--upf-pink);
    }

    .stat-header h3 {
        font-size: 1.1rem;
        color: var(--gray);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .stat-compare {
        color: var(--gray);
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .trend-up {
        color: var(--success);
    }

    .trend-down {
        color: var(--danger);
    }

    /* Classement */
    .classement-card {
        background: white;
        border-radius: 25px;
        padding: 30px;
        margin-top: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .classement-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .classement-header h2 {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rang-badge {
        background: var(--gradient);
        color: white;
        padding: 10px 30px;
        border-radius: 50px;
        font-size: 1.2rem;
        font-weight: 600;
    }

    /* Tableau top */
    .top-table {
        width: 100%;
        border-collapse: collapse;
    }

    .top-table th {
        text-align: left;
        padding: 15px;
        color: var(--gray);
        font-weight: 600;
        border-bottom: 2px solid #f1f5f9;
    }

    .top-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .top-table tr:last-child td {
        border-bottom: none;
    }

    .top-table tr:hover {
        background: #f8fafc;
    }

    .rang-1 {
        background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(255,215,0,0.05));
    }

    .rang-1 .rang-numero {
        color: gold;
        font-weight: 700;
    }

    .rang-2 {
        background: linear-gradient(135deg, rgba(192,192,192,0.1), rgba(192,192,192,0.05));
    }

    .rang-2 .rang-numero {
        color: silver;
        font-weight: 700;
    }

    .rang-3 {
        background: linear-gradient(135deg, rgba(205,127,50,0.1), rgba(205,127,50,0.05));
    }

    .rang-3 .rang-numero {
        color: #cd7f32;
        font-weight: 700;
    }

    .current-student {
        background: linear-gradient(135deg, rgba(41,72,152,0.1), rgba(199,44,130,0.1));
        font-weight: 600;
        border-left: 4px solid var(--upf-pink);
    }

    /* Graphique comparatif */
    .comparison-chart {
        margin-top: 30px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 20px;
    }

    .chart-bar {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .chart-label {
        width: 120px;
        color: var(--gray);
    }

    .chart-progress {
        flex: 1;
        height: 30px;
        background: #e2e8f0;
        border-radius: 15px;
        overflow: hidden;
        position: relative;
    }

    .chart-fill {
        height: 100%;
        background: var(--gradient);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 15px;
        color: white;
        font-weight: 600;
        transition: width 1s ease;
    }

    .chart-value {
        min-width: 50px;
        text-align: right;
        font-weight: 600;
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

    /* Responsive */
    @media (max-width: 768px) {
        .note-valeur {
            font-size: 3.5rem;
        }
        
        .note-mention {
            font-size: 1.3rem;
        }
        
        .classement-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .top-table {
            font-size: 0.85rem;
        }
    }
</style>

<div class="notes-page">
    
    <!-- En-tête -->
    <div class="page-header">
        <h1>
            <i class="fas fa-chart-line"></i>
            Mes notes et performances
        </h1>
        <div class="breadcrumb">
            <a href="profil.php"><i class="fas fa-user"></i> Mon profil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Notes</span>
        </div>
    </div>

    <!-- Carte principale -->
    <div class="main-card">
        <div class="notes-grid">
            <!-- Note principale -->
            <div class="note-principale">
                <div class="note-label">Ma note</div>
                <div class="note-valeur">
                    <?php echo $etudiant['FNote'] !== null ? number_format($etudiant['FNote'], 2) : '---'; ?>
                </div>
                <div class="note-mention"><?php echo $mentionInfo['mention']; ?></div>
                <div class="note-statut">
                    <i class="fas fa-<?php echo $statusInfo['status'] == 'Reçu' ? 'check-circle' : ($statusInfo['status'] == 'Ajourné' ? 'times-circle' : 'clock'); ?>"></i>
                    <?php echo $statusInfo['status']; ?>
                </div>
            </div>

            <!-- Comparaison filière -->
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-building"></i>
                    <h3>Ma filière</h3>
                </div>
                <div class="stat-value"><?php echo htmlspecialchars($etudiant['IntituleF'] ?? 'Non assigné'); ?></div>
                <div class="stat-compare">
                    <i class="fas fa-users"></i>
                    <?php echo $stats['total']; ?> étudiants
                </div>
            </div>

            <!-- Moyenne filière -->
            <div class="stat-card">
                <div class="stat-header">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Moyenne filière</h3>
                </div>
                <div class="stat-value"><?php echo $stats['moyenne'] ? number_format($stats['moyenne'], 2) : 'N/A'; ?></div>
                <?php if ($etudiant['FNote'] !== null && $stats['moyenne'] !== null): ?>
                    <?php $diff = $etudiant['FNote'] - $stats['moyenne']; ?>
                    <div class="stat-compare <?php echo $diff >= 0 ? 'trend-up' : 'trend-down'; ?>">
                        <i class="fas fa-<?php echo $diff >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo number_format(abs($diff), 2); ?> points 
                        <?php echo $diff >= 0 ? 'au-dessus' : 'en-dessous'; ?> de la moyenne
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Classement -->
        <div class="classement-card">
            <div class="classement-header">
                <h2>
                    <i class="fas fa-trophy" style="color: gold;"></i>
                    Classement dans la filière
                </h2>
                <?php if ($etudiant['FNote'] !== null): ?>
                    <div class="rang-badge">
                        Rang <?php echo $rang; ?> / <?php echo $total; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Graphique comparatif -->
            <div class="comparison-chart">
                <div class="chart-bar">
                    <span class="chart-label">Ma note</span>
                    <div class="chart-progress">
                        <?php if ($etudiant['FNote'] !== null): ?>
                            <?php $pourcentage = ($etudiant['FNote'] / 20) * 100; ?>
                            <div class="chart-fill" style="width: <?php echo $pourcentage; ?>%;">
                                <?php echo number_format($etudiant['FNote'], 2); ?>
                            </div>
                        <?php else: ?>
                            <div class="chart-fill" style="width: 0%;">Non évalué</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="chart-bar">
                    <span class="chart-label">Moyenne filière</span>
                    <div class="chart-progress">
                        <?php if ($stats['moyenne'] !== null): ?>
                            <?php $pourcentageMoy = ($stats['moyenne'] / 20) * 100; ?>
                            <div class="chart-fill" style="width: <?php echo $pourcentageMoy; ?>%; background: rgba(41,72,152,0.5);">
                                <?php echo number_format($stats['moyenne'], 2); ?>
                            </div>
                        <?php else: ?>
                            <div class="chart-fill" style="width: 0%; background: #94a3b8;">-</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="chart-bar">
                    <span class="chart-label">Meilleure note</span>
                    <div class="chart-progress">
                        <?php if ($stats['max'] !== null): ?>
                            <?php $pourcentageMax = ($stats['max'] / 20) * 100; ?>
                            <div class="chart-fill" style="width: <?php echo $pourcentageMax; ?>%; background: gold;">
                                <?php echo number_format($stats['max'], 2); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 de la filière -->
        <?php if (count($topEtudiants) > 0): ?>
            <div class="classement-card" style="margin-top: 20px;">
                <h2 style="margin-bottom: 20px;">
                    <i class="fas fa-crown" style="color: gold;"></i>
                    Top 5 de la filière
                </h2>

                <table class="top-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Étudiant</th>
                            <th>Note</th>
                            <th>Mention</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topEtudiants as $index => $top): 
                            $topMention = getMention($top['FNote']);
                            $rowClass = '';
                            if ($top['Code'] == $etudiant['Code']) {
                                $rowClass = 'current-student';
                            } elseif ($top['rang'] <= 3) {
                                $rowClass = 'rang-' . $top['rang'];
                            }
                        ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td>
                                    <?php if ($top['rang'] == 1): ?>
                                        <i class="fas fa-crown" style="color: gold;"></i>
                                    <?php elseif ($top['rang'] == 2): ?>
                                        <i class="fas fa-medal" style="color: silver;"></i>
                                    <?php elseif ($top['rang'] == 3): ?>
                                        <i class="fas fa-medal" style="color: #cd7f32;"></i>
                                    <?php endif; ?>
                                    #<?php echo $top['rang']; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($top['Prenom'] . ' ' . $top['Nom']); ?></strong>
                                    <?php if ($top['Code'] == $etudiant['Code']): ?>
                                        <span style="color: var(--upf-pink); font-size: 0.8rem;"> (Moi)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $topMention['class']; ?>">
                                        <?php echo number_format($top['FNote'], 2); ?>
                                    </span>
                                </td>
                                <td><?php echo $topMention['mention']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>