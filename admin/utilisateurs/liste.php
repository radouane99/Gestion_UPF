<?php
// admin/utilisateurs/liste.php - Version Moderne Complète
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';

$pdo = getConnexion();

// Configuration de la pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Recherche et filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Construction de la requête
$sql = "SELECT u.*, e.Nom, e.Prenom, e.Code as etudiant_code 
        FROM utilisateurs u 
        LEFT JOIN etudiants e ON u.etudiant_id = e.Code 
        WHERE 1=1";

$countSql = "SELECT COUNT(*) FROM utilisateurs u LEFT JOIN etudiants e ON u.etudiant_id = e.Code WHERE 1=1";
$params = [];

// Filtre de recherche
if (!empty($search)) {
    $sql .= " AND (u.login LIKE :search 
                   OR e.Nom LIKE :search 
                   OR e.Prenom LIKE :search 
                   OR u.role LIKE :search)";
    $countSql .= " AND (u.login LIKE :search 
                       OR e.Nom LIKE :search 
                       OR e.Prenom LIKE :search 
                       OR u.role LIKE :search)";
    $params[':search'] = "%$search%";
}

// Filtre par rôle
if (!empty($role_filter)) {
    $sql .= " AND u.role = :role";
    $countSql .= " AND u.role = :role";
    $params[':role'] = $role_filter;
}

// Tri
$allowedSorts = ['id', 'login', 'role', 'derniere_connexion'];
$sort = in_array($sort, $allowedSorts) ? $sort : 'id';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
$sql .= " ORDER BY u.$sort $order LIMIT :limit OFFSET :offset";

// Compter total
$stmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Récupérer les utilisateurs
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Statistiques pour les cartes
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'admin'")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'user'")->fetchColumn(),
    'online' => $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE derniere_connexion > DATE_SUB(NOW(), INTERVAL 30 MINUTE)")->fetchColumn()
];
?>

<style>
    /* ============================================= */
    /* STYLES MODERNES POUR LA GESTION DES UTILISATEURS */
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
    }

    .users-management {
        padding: 20px;
    }

    /* En-tête de la page */
    .page-header {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
    }

    .page-header h1 {
        font-size: 2.2em;
        color: var(--dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-header h1 i {
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.2em;
    }

    .page-header .breadcrumb {
        color: var(--gray);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
    }

    .page-header .breadcrumb a {
        color: var(--upf-pink);
        text-decoration: none;
    }

    /* Cartes de statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
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

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(41,72,152,0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, rgba(41,72,152,0.1), rgba(199,44,130,0.1));
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .stat-icon i {
        font-size: 24px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-content h3 {
        color: var(--gray);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .stat-number {
        font-size: 2.2em;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .stat-label {
        color: var(--gray);
        font-size: 0.85rem;
    }

    /* Barre d'outils */
    .toolbar {
        background: white;
        border-radius: 15px;
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
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        color: white;
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(199,44,130,0.3);
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(199,44,130,0.4);
    }

    /* Filtres et recherche */
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
        font-size: 0.95rem;
        background: white;
        cursor: pointer;
    }

    /* Tableau moderne */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table th {
        text-align: left;
        padding: 15px;
        font-weight: 600;
        color: var(--gray);
        border-bottom: 2px solid #e2e8f0;
        cursor: pointer;
        transition: color 0.3s;
    }

    .modern-table th:hover {
        color: var(--upf-pink);
    }

    .modern-table th i {
        margin-left: 5px;
        font-size: 0.8rem;
    }

    .modern-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .modern-table tbody tr {
        transition: all 0.3s;
    }

    .modern-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(41,72,152,0.02), rgba(199,44,130,0.02));
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Badges pour les rôles */
    .role-badge {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .role-admin {
        background: linear-gradient(135deg, #294898, #1e3a8a);
        color: white;
    }

    .role-user {
        background: linear-gradient(135deg, #C72C82, #9d174d);
        color: white;
    }

    /* Status en ligne */
    .online-status {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .online-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #cbd5e1;
    }

    .online-dot.active {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
        70% { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
        100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .action-btn.edit {
        background: rgba(59,130,246,0.1);
        color: #3b82f6;
    }

    .action-btn.edit:hover {
        background: #3b82f6;
        color: white;
        transform: translateY(-2px);
    }

    .action-btn.reset {
        background: rgba(245,158,11,0.1);
        color: #f59e0b;
    }

    .action-btn.reset:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-2px);
    }

    .action-btn.delete {
        background: rgba(239,68,68,0.1);
        color: #ef4444;
    }

    .action-btn.delete:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
    }

    /* Pagination moderne */
    .pagination {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .page-item {
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

    .page-item:hover {
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        color: white;
        transform: translateY(-2px);
        border-color: transparent;
    }

    .page-item.active {
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        color: white;
        border: none;
    }

    .page-item.disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Alertes modernes */
    .alert-modern {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: #d1fae5;
        border-left: 4px solid #10b981;
        color: #065f46;
    }

    .alert-danger {
        background: #fee2e2;
        border-left: 4px solid #ef4444;
        color: #991b1b;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filters {
            flex-direction: column;
        }
        
        .search-box {
            min-width: auto;
        }
        
        .modern-table {
            font-size: 0.85rem;
        }
        
        .actions {
            flex-wrap: wrap;
        }
    }
</style>

<div class="users-management">
    
    <!-- En-tête de page -->
    <div class="page-header">
        <h1>
            <i class="fas fa-users-cog"></i>
            Gestion des Utilisateurs
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <span>Utilisateurs</span>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total</h3>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Utilisateurs enregistrés</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-content">
                <h3>Administrateurs</h3>
                <div class="stat-number"><?php echo $stats['admins']; ?></div>
                <div class="stat-label">Comptes admin</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <h3>Étudiants</h3>
                <div class="stat-number"><?php echo $stats['users']; ?></div>
                <div class="stat-label">Comptes utilisateurs</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-circle"></i>
            </div>
            <div class="stat-content">
                <h3>En ligne</h3>
                <div class="stat-number"><?php echo $stats['online']; ?></div>
                <div class="stat-label">Dernières 30 minutes</div>
            </div>
        </div>
    </div>

    <!-- Barre d'outils -->
    <div class="toolbar">
        <a href="ajouter.php" class="btn-add">
            <i class="fas fa-plus-circle"></i>
            Nouvel utilisateur
        </a>

        <div class="filters">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher par login, nom..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <select class="filter-select" id="roleFilter">
                <option value="">Tous les rôles</option>
                <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Administrateurs</option>
                <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Utilisateurs</option>
            </select>

            <button class="btn-add" onclick="applyFilters()" style="padding: 12px 20px;">
                <i class="fas fa-filter"></i>
                Filtrer
            </button>
        </div>
    </div>

    <!-- Messages flash -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert-modern alert-<?php echo $_SESSION['flash']['type']; ?>">
            <i class="fas fa-<?php echo $_SESSION['flash']['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $_SESSION['flash']['message']; ?></span>
            <button onclick="this.parentElement.remove()" style="margin-left: auto; background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Tableau des utilisateurs -->
    <div class="table-container">
        <table class="modern-table">
            <thead>
                <tr>
                    <th onclick="sortTable('id')">
                        ID <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="sortTable('login')">
                        Login <i class="fas fa-sort"></i>
                    </th>
                    <th>Utilisateur</th>
                    <th onclick="sortTable('role')">
                        Rôle <i class="fas fa-sort"></i>
                    </th>
                    <th onclick="sortTable('derniere_connexion')">
                        Dernière connexion <i class="fas fa-sort"></i>
                    </th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): 
                        $isOnline = $user['derniere_connexion'] && strtotime($user['derniere_connexion']) > strtotime('-30 minutes');
                    ?>
                        <tr>
                            <td><strong>#<?php echo $user['id']; ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="online-status">
                                        <span class="online-dot <?php echo $isOnline ? 'active' : ''; ?>"></span>
                                    </div>
                                    <strong><?php echo htmlspecialchars($user['login']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php if ($user['etudiant_id']): ?>
                                    <div style="display: flex; flex-direction: column;">
                                        <strong><?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?></strong>
                                        <small style="color: var(--gray);"><?php echo $user['etudiant_id']; ?></small>
                                    </div>
                                <?php else: ?>
                                    <em style="color: var(--gray);">- Admin système -</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="role-badge role-admin">
                                        <i class="fas fa-crown"></i>
                                        Administrateur
                                    </span>
                                <?php else: ?>
                                    <span class="role-badge role-user">
                                        <i class="fas fa-user"></i>
                                        Étudiant
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['derniere_connexion']): ?>
                                    <div style="display: flex; flex-direction: column;">
                                        <span><?php echo date('d/m/Y H:i', strtotime($user['derniere_connexion'])); ?></span>
                                        <?php if ($isOnline): ?>
                                            <small style="color: #10b981;">En ligne</small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Jamais connecté</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="modifier.php?id=<?php echo $user['id']; ?>" class="action-btn edit" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="reset_password.php?id=<?php echo $user['id']; ?>" class="action-btn reset" title="Réinitialiser mot de passe" onclick="return confirm('Voulez-vous réinitialiser le mot de passe de cet utilisateur ?')">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="supprimer.php?id=<?php echo $user['id']; ?>" class="action-btn delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px;">
                            <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--gray); margin-bottom: 15px; display: block;"></i>
                            <h3 style="color: var(--gray);">Aucun utilisateur trouvé</h3>
                            <p style="color: var(--gray); margin-top: 10px;">Essayez de modifier vos filtres de recherche</p>
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
                <a href="?page=1&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" class="page-item">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" class="page-item">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" 
                   class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" class="page-item">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" class="page-item">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Recherche en temps réel
let searchTimeout;
document.getElementById('searchInput').addEventListener('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Filtre par rôle
document.getElementById('roleFilter').addEventListener('change', function() {
    applyFilters();
});

// Appliquer les filtres
function applyFilters() {
    let search = document.getElementById('searchInput').value;
    let role = document.getElementById('roleFilter').value;
    let url = new URL(window.location.href);
    
    url.searchParams.set('search', search);
    url.searchParams.set('role', role);
    url.searchParams.set('page', '1'); // Reset à la première page
    
    window.location.href = url.toString();
}

// Trier le tableau
function sortTable(column) {
    let url = new URL(window.location.href);
    let currentSort = url.searchParams.get('sort') || 'id';
    let currentOrder = url.searchParams.get('order') || 'DESC';
    
    let newOrder = 'ASC';
    if (column === currentSort && currentOrder === 'ASC') {
        newOrder = 'DESC';
    }
    
    url.searchParams.set('sort', column);
    url.searchParams.set('order', newOrder);
    
    window.location.href = url.toString();
}

// Rafraîchir automatique les statuts en ligne (optionnel)
setInterval(() => {
    // Recharger seulement les indicateurs en ligne via AJAX si besoin
    console.log('Vérification des statuts en ligne...');
}, 60000); // Toutes les minutes
</script>

<?php require_once '../../includes/footer.php'; ?>