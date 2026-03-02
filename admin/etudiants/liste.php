<?php
// admin/etudiants/liste.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$pdo = getConnexion();

// Configuration pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filiere_filter = isset($_GET['filiere']) ? $_GET['filiere'] : '';
$note_filter = isset($_GET['note']) ? $_GET['note'] : '';

// Construction requête
$sql = "SELECT e.*, f.IntituleF, f.CodeF,
        (SELECT COUNT(*) FROM documents WHERE etudiant_id = e.Code) as nb_documents
        FROM etudiants e
        LEFT JOIN filieres f ON e.Filiere = f.CodeF
        WHERE 1=1";

$countSql = "SELECT COUNT(*) FROM etudiants e WHERE 1=1";
$params = [];

// Recherche
if (!empty($search)) {
    $sql .= " AND (e.Nom LIKE :search OR e.Prenom LIKE :search OR e.Code LIKE :search OR e.email LIKE :search)";
    $countSql .= " AND (Nom LIKE :search OR Prenom LIKE :search OR Code LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Filtre filière
if (!empty($filiere_filter)) {
    $sql .= " AND e.Filiere = :filiere";
    $countSql .= " AND Filiere = :filiere";
    $params[':filiere'] = $filiere_filter;
}

// Filtre note
if (!empty($note_filter)) {
    switch($note_filter) {
        case 'excellent':
            $sql .= " AND e.FNote >= 16";
            $countSql .= " AND FNote >= 16";
            break;
        case 'bien':
            $sql .= " AND e.FNote >= 14 AND e.FNote < 16";
            $countSql .= " AND FNote >= 14 AND FNote < 16";
            break;
        case 'assez_bien':
            $sql .= " AND e.FNote >= 12 AND e.FNote < 14";
            $countSql .= " AND FNote >= 12 AND FNote < 14";
            break;
        case 'passable':
            $sql .= " AND e.FNote >= 10 AND e.FNote < 12";
            $countSql .= " AND FNote >= 10 AND FNote < 12";
            break;
        case 'insuffisant':
            $sql .= " AND e.FNote < 10 AND e.FNote IS NOT NULL";
            $countSql .= " AND FNote < 10 AND FNote IS NOT NULL";
            break;
        case 'non_evalue':
            $sql .= " AND e.FNote IS NULL";
            $countSql .= " AND FNote IS NULL";
            break;
    }
}

// Tri
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'e.Code';
$order = isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
$allowedSorts = ['e.Code', 'e.Nom', 'e.Prenom', 'e.FNote', 'e.created_at'];
if (!in_array($sort, $allowedSorts)) {
    $sort = 'e.Code';
}

$sql .= " ORDER BY $sort $order LIMIT :limit OFFSET :offset";

// Compter total
$stmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Récupérer étudiants
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$etudiants = $stmt->fetchAll();

// Récupérer filières pour filtre
$filieres = $pdo->query("SELECT CodeF, IntituleF FROM filieres ORDER BY IntituleF")->fetchAll();

// Statistiques
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn(),
    'avec_photo' => $pdo->query("SELECT COUNT(*) FROM etudiants WHERE Photo IS NOT NULL AND Photo != ''")->fetchColumn(),
    'documents' => $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn(),
    'notes' => $pdo->query("SELECT COUNT(*) FROM etudiants WHERE FNote IS NOT NULL")->fetchColumn()
];
?>

<style>
    /* ============================================= */
    /* STYLES POUR LA LISTE DES ÉTUDIANTS */
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

    .students-page {
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

    .filters {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        position: relative;
        min-width: 300px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    .search-box input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: var(--upf-pink);
        box-shadow: 0 0 0 3px rgba(199,44,130,0.1);
        outline: none;
    }

    .filter-select {
        padding: 12px 20px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: white;
        cursor: pointer;
        min-width: 150px;
    }

    .btn-filter {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 12px 20px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-filter:hover {
        border-color: var(--upf-pink);
        color: var(--upf-pink);
    }

    /* Tableau */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow-x: auto;
    }

    .students-table {
        width: 100%;
        border-collapse: collapse;
    }

    .students-table th {
        text-align: left;
        padding: 15px;
        font-weight: 600;
        color: var(--gray);
        border-bottom: 2px solid #e2e8f0;
        cursor: pointer;
        transition: color 0.3s;
    }

    .students-table th:hover {
        color: var(--upf-pink);
    }

    .students-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .students-table tbody tr {
        transition: all 0.3s;
    }

    .students-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(41,72,152,0.02), rgba(199,44,130,0.02));
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Avatar */
    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .student-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 12px;
        object-fit: cover;
    }

    /* Badge note */
    .note-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
    }

    .note-excellent { background: #d1fae5; color: #065f46; }
    .note-bien { background: #dbeafe; color: #1e40af; }
    .note-assez-bien { background: #fef3c7; color: #92400e; }
    .note-passable { background: #ffedd5; color: #9a3412; }
    .note-insuffisant { background: #fee2e2; color: #991b1b; }
    .note-non-evalue { background: #f1f5f9; color: #475569; }

    /* Statut */
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-reçu { background: #d1fae5; color: #065f46; }
    .status-ajourné { background: #fee2e2; color: #991b1b; }
    .status-attente { background: #fef3c7; color: #92400e; }

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

    .action-btn.view {
        background: rgba(59,130,246,0.1);
        color: #3b82f6;
    }

    .action-btn.view:hover {
        background: #3b82f6;
        color: white;
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

    /* Pagination */
    .pagination {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .page-link {
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: white;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
        border: 1px solid #e2e8f0;
    }

    .page-link:hover {
        background: var(--gradient);
        color: white;
        border-color: transparent;
    }

    .page-link.active {
        background: var(--gradient);
        color: white;
        border: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .toolbar {
            flex-direction: column;
        }
        
        .filters {
            width: 100%;
        }
        
        .search-box {
            min-width: auto;
            width: 100%;
        }
        
        .students-table {
            font-size: 0.85rem;
        }
    }
</style>

<div class="students-page">
    
    <!-- En-tête -->
    <div class="page-header">
        <h1>
            <i class="fas fa-user-graduate"></i>
            Gestion des Étudiants
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <span>Étudiants</span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total</h3>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-camera"></i>
            </div>
            <div class="stat-content">
                <h3>Avec photo</h3>
                <div class="stat-number"><?php echo $stats['avec_photo']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="stat-content">
                <h3>Documents</h3>
                <div class="stat-number"><?php echo $stats['documents']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3>Notés</h3>
                <div class="stat-number"><?php echo $stats['notes']; ?></div>
            </div>
        </div>
    </div>

    <!-- Barre d'outils -->
    <div class="toolbar">
        <a href="ajouter.php" class="btn-add">
            <i class="fas fa-plus-circle"></i>
            Ajouter un étudiant
        </a>

        <form method="GET" class="filters" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Rechercher par nom, prénom, code..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select name="filiere" class="filter-select">
                <option value="">Toutes filières</option>
                <?php foreach ($filieres as $f): ?>
                    <option value="<?php echo $f['CodeF']; ?>" 
                            <?php echo $filiere_filter == $f['CodeF'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($f['IntituleF']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="note" class="filter-select">
                <option value="">Toutes notes</option>
                <option value="excellent" <?php echo $note_filter == 'excellent' ? 'selected' : ''; ?>>Excellent (16-20)</option>
                <option value="bien" <?php echo $note_filter == 'bien' ? 'selected' : ''; ?>>Bien (14-16)</option>
                <option value="assez_bien" <?php echo $note_filter == 'assez_bien' ? 'selected' : ''; ?>>Assez Bien (12-14)</option>
                <option value="passable" <?php echo $note_filter == 'passable' ? 'selected' : ''; ?>>Passable (10-12)</option>
                <option value="insuffisant" <?php echo $note_filter == 'insuffisant' ? 'selected' : ''; ?>>Insuffisant (<10)</option>
                <option value="non_evalue" <?php echo $note_filter == 'non_evalue' ? 'selected' : ''; ?>>Non évalué</option>
            </select>

            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i>
                Filtrer
            </button>
            
            <?php if (!empty($search) || !empty($filiere_filter) || !empty($note_filter)): ?>
                <a href="liste.php" class="btn-filter" style="color: var(--danger);">
                    <i class="fas fa-times"></i>
                    Effacer
                </a>
            <?php endif; ?>
        </form>
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

    <!-- Tableau -->
    <div class="table-container">
        <table class="students-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th onclick="sortTable('e.Code')">Code <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable('e.Nom')">Nom <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable('e.Prenom')">Prénom <i class="fas fa-sort"></i></th>
                    <th>Filière</th>
                    <th onclick="sortTable('e.FNote')">Note <i class="fas fa-sort"></i></th>
                    <th>Mention</th>
                    <th>Statut</th>
                    <th>Docs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($etudiants) > 0): ?>
                    <?php foreach ($etudiants as $e): 
                        // Déterminer mention et couleur
                        if ($e['FNote'] !== null) {
                            if ($e['FNote'] >= 16) {
                                $mention = 'Très Bien';
                                $noteClass = 'note-excellent';
                                $status = 'Reçu';
                                $statusClass = 'status-reçu';
                            } elseif ($e['FNote'] >= 14) {
                                $mention = 'Bien';
                                $noteClass = 'note-bien';
                                $status = 'Reçu';
                                $statusClass = 'status-reçu';
                            } elseif ($e['FNote'] >= 12) {
                                $mention = 'Assez Bien';
                                $noteClass = 'note-assez-bien';
                                $status = 'Reçu';
                                $statusClass = 'status-reçu';
                            } elseif ($e['FNote'] >= 10) {
                                $mention = 'Passable';
                                $noteClass = 'note-passable';
                                $status = 'Reçu';
                                $statusClass = 'status-reçu';
                            } else {
                                $mention = 'Insuffisant';
                                $noteClass = 'note-insuffisant';
                                $status = 'Ajourné';
                                $statusClass = 'status-ajourné';
                            }
                        } else {
                            $mention = 'Non évalué';
                            $noteClass = 'note-non-evalue';
                            $status = 'En attente';
                            $statusClass = 'status-attente';
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="student-avatar">
                                    <?php if (!empty($e['Photo']) && file_exists('../../' . $e['Photo'])): ?>
                                        <img src="../../<?php echo $e['Photo']; ?>" alt="Photo">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($e['Prenom'], 0, 1) . substr($e['Nom'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?php echo $e['Code']; ?></strong></td>
                            <td><?php echo htmlspecialchars($e['Nom']); ?></td>
                            <td><?php echo htmlspecialchars($e['Prenom']); ?></td>
                            <td>
                                <?php if ($e['IntituleF']): ?>
                                    <span style="color: var(--upf-blue);"><?php echo htmlspecialchars($e['IntituleF']); ?></span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Non assigné</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($e['FNote'] !== null): ?>
                                    <span class="note-badge <?php echo $noteClass; ?>">
                                        <?php echo number_format($e['FNote'], 2); ?>/20
                                    </span>
                                <?php else: ?>
                                    <span class="note-badge note-non-evalue">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="note-badge <?php echo $noteClass; ?>">
                                    <?php echo $mention; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($e['nb_documents'] > 0): ?>
                                    <span style="background: var(--upf-pink); color: white; padding: 3px 8px; border-radius: 10px;">
                                        <?php echo $e['nb_documents']; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="detail.php?code=<?php echo $e['Code']; ?>" class="action-btn view" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="modifier.php?code=<?php echo $e['Code']; ?>" class="action-btn edit" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?code=<?php echo $e['Code']; ?>" class="action-btn delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 50px;">
                            <i class="fas fa-user-slash" style="font-size: 3rem; color: var(--gray); margin-bottom: 15px;"></i>
                            <h3 style="color: var(--gray);">Aucun étudiant trouvé</h3>
                            <p style="color: var(--gray);">Essayez de modifier vos filtres ou <a href="ajouter.php" style="color: var(--upf-pink);">ajoutez un étudiant</a></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1&search=<?php echo urlencode($search); ?>&filiere=<?php echo $filiere_filter; ?>&note=<?php echo $note_filter; ?>" class="page-link">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filiere=<?php echo $filiere_filter; ?>&note=<?php echo $note_filter; ?>" class="page-link">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filiere=<?php echo $filiere_filter; ?>&note=<?php echo $note_filter; ?>" 
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filiere=<?php echo $filiere_filter; ?>&note=<?php echo $note_filter; ?>" class="page-link">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&filiere=<?php echo $filiere_filter; ?>&note=<?php echo $note_filter; ?>" class="page-link">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function sortTable(column) {
        let url = new URL(window.location.href);
        let currentSort = url.searchParams.get('sort') || 'e.Code';
        let currentOrder = url.searchParams.get('order') || 'DESC';
        
        let newOrder = 'ASC';
        if (column === currentSort && currentOrder === 'ASC') {
            newOrder = 'DESC';
        }
        
        url.searchParams.set('sort', column);
        url.searchParams.set('order', newOrder);
        window.location.href = url.toString();
    }

    // Auto-submit des filtres (optionnel)
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
</script>

<?php require_once '../../includes/footer.php'; ?>