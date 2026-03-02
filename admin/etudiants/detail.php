<?php
// admin/etudiants/detail.php - Version Moderne
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

// Récupérer informations étudiant
$stmt = $pdo->prepare("
    SELECT e.*, f.IntituleF, f.CodeF, f.responsable,
           (SELECT COUNT(*) FROM documents WHERE etudiant_id = e.Code) as nb_documents,
           (SELECT COUNT(*) FROM utilisateurs WHERE etudiant_id = e.Code) as has_account
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

// Récupérer les documents de l'étudiant
$stmt = $pdo->prepare("
    SELECT d.*, u.login as uploaded_by_login 
    FROM documents d
    LEFT JOIN utilisateurs u ON d.uploaded_by = u.id
    WHERE d.etudiant_id = ?
    ORDER BY d.uploaded_at DESC
");
$stmt->execute([$code]);
$documents = $stmt->fetchAll();

// Mention et statut
$mentionInfo = getMention($etudiant['FNote']);
$statusInfo = getStatus($etudiant['FNote']);

// Statistiques documents par type
$stats_docs = [
    'releve_notes' => 0,
    'attestation' => 0,
    'autre' => 0
];
foreach ($documents as $doc) {
    $stats_docs[$doc['type_doc']]++;
}
?>

<style>
    /* ============================================= */
    /* STYLES MODERNES PAGE DÉTAIL ÉTUDIANT */
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

    .detail-page {
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
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '📋';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 120px;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .page-header h1 {
        font-size: 2.2rem;
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

    /* Profil header */
    .profile-header {
        background: white;
        border-radius: 30px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
        align-items: center;
        position: relative;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 30px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3.5rem;
        font-weight: 700;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(199,44,130,0.3);
        border: 4px solid white;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-info {
        flex: 1;
    }

    .profile-info h2 {
        font-size: 2.5rem;
        color: var(--dark);
        margin-bottom: 10px;
    }

    .profile-badges {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .badge {
        padding: 10px 20px;
        border-radius: 30px;
        font-size: 0.95rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .badge-code {
        background: rgba(41,72,152,0.1);
        color: var(--upf-blue);
    }

    .badge-filiere {
        background: rgba(199,44,130,0.1);
        color: var(--upf-pink);
    }

    .badge-docs {
        background: rgba(16,185,129,0.1);
        color: #10b981;
    }

    .badge-account {
        background: rgba(59,130,246,0.1);
        color: #3b82f6;
    }

    .profile-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-action {
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-edit {
        background: var(--warning);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(245,158,11,0.3);
    }

    .btn-delete {
        background: var(--danger);
        color: white;
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(239,68,68,0.3);
    }

    .btn-back {
        background: var(--gray);
        color: white;
    }

    .btn-back:hover {
        background: var(--dark);
    }

    /* Grille d'informations */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
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

    .card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }

    .card-header i {
        width: 45px;
        height: 45px;
        background: var(--gradient);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
    }

    .card-header h3 {
        font-size: 1.2rem;
        color: var(--dark);
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
        width: 120px;
        color: var(--gray);
        font-size: 0.9rem;
    }

    .info-value {
        flex: 1;
        font-weight: 500;
        color: var(--dark);
    }

    /* Carte note */
    .note-card {
        background: var(--gradient);
        border-radius: 25px;
        padding: 25px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .note-card::after {
        content: '🎓';
        position: absolute;
        bottom: -20px;
        right: -20px;
        font-size: 100px;
        opacity: 0.2;
        transform: rotate(-15deg);
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .note-value {
        font-size: 3rem;
        font-weight: 700;
    }

    .note-mention {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .note-status {
        display: inline-block;
        padding: 5px 15px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 0.9rem;
        backdrop-filter: blur(5px);
    }

    /* Section documents */
    .documents-section {
        background: white;
        border-radius: 30px;
        padding: 30px;
        margin-top: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .section-header h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.5rem;
    }

    .btn-upload {
        background: var(--success);
        color: white;
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16,185,129,0.3);
    }

    /* Stats documents */
    .docs-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .doc-stat {
        background: #f8fafc;
        border-radius: 15px;
        padding: 15px;
        text-align: center;
        border-left: 4px solid;
    }

    .doc-stat.releve { border-color: #3b82f6; }
    .doc-stat.attestation { border-color: #10b981; }
    .doc-stat.autre { border-color: #f59e0b; }

    .doc-stat i {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }

    .doc-stat .count {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--dark);
    }

    /* Tableau documents */
    .documents-table {
        width: 100%;
        border-collapse: collapse;
    }

    .documents-table th {
        text-align: left;
        padding: 15px;
        color: var(--gray);
        font-weight: 600;
        border-bottom: 2px solid #f1f5f9;
    }

    .documents-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .documents-table tbody tr:hover {
        background: #f8fafc;
    }

    .file-icon {
        width: 40px;
        height: 40px;
        background: #fee2e2;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--danger);
    }

    .file-info {
        display: flex;
        flex-direction: column;
    }

    .file-name {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 3px;
    }

    .file-meta {
        font-size: 0.8rem;
        color: var(--gray);
        display: flex;
        gap: 15px;
    }

    .file-type-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .type-releve { background: #dbeafe; color: #1e40af; }
    .type-attestation { background: #d1fae5; color: #065f46; }
    .type-autre { background: #fef3c7; color: #92400e; }

    .btn-download {
        padding: 8px 15px;
        border-radius: 8px;
        background: rgba(41,72,152,0.1);
        color: var(--upf-blue);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .btn-download:hover {
        background: var(--upf-blue);
        color: white;
    }

    .btn-delete-doc {
        padding: 8px 12px;
        border-radius: 8px;
        background: rgba(239,68,68,0.1);
        color: var(--danger);
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-delete-doc:hover {
        background: var(--danger);
        color: white;
    }

    /* Modal upload */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .modal.active {
        display: flex;
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background: white;
        border-radius: 30px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }

    .upload-area {
        border: 2px dashed #e2e8f0;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        margin: 20px 0;
        cursor: pointer;
        transition: all 0.3s;
    }

    .upload-area:hover {
        border-color: var(--upf-pink);
        background: #f8fafc;
    }

    .upload-area i {
        font-size: 3rem;
        color: var(--upf-pink);
        margin-bottom: 15px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-actions {
            justify-content: center;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .docs-stats {
            grid-template-columns: 1fr;
        }
        
        .documents-table {
            font-size: 0.85rem;
        }
        
        .documents-table th:nth-child(4),
        .documents-table td:nth-child(4) {
            display: none;
        }
    }
</style>

<div class="detail-page">
    
    <!-- En-tête -->
    <div class="page-header">
        <h1>
            <i class="fas fa-eye"></i>
            Détail de l'étudiant
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-user-graduate"></i> Étudiants</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></span>
        </div>
    </div>

    <!-- Profil header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if (!empty($etudiant['Photo']) && file_exists('../../' . $etudiant['Photo'])): ?>
                <img src="../../<?php echo $etudiant['Photo']; ?>" alt="Photo">
            <?php else: ?>
                <?php echo strtoupper(substr($etudiant['Prenom'], 0, 1) . substr($etudiant['Nom'], 0, 1)); ?>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></h2>
            
            <div class="profile-badges">
                <span class="badge badge-code">
                    <i class="fas fa-id-card"></i>
                    <?php echo $etudiant['Code']; ?>
                </span>
                <span class="badge badge-filiere">
                    <i class="fas fa-building"></i>
                    <?php echo $etudiant['IntituleF'] ?? 'Non assigné'; ?>
                </span>
                <span class="badge badge-docs">
                    <i class="fas fa-file-pdf"></i>
                    <?php echo $etudiant['nb_documents']; ?> document(s)
                </span>
                <?php if ($etudiant['has_account']): ?>
                    <span class="badge badge-account">
                        <i class="fas fa-check-circle"></i>
                        Compte actif
                    </span>
                <?php endif; ?>
            </div>

            <div class="profile-actions">
                <a href="liste.php" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
                <a href="modifier.php?code=<?php echo $etudiant['Code']; ?>" class="btn-action btn-edit">
                    <i class="fas fa-edit"></i>
                    Modifier
                </a>
                <a href="supprimer.php?code=<?php echo $etudiant['Code']; ?>" class="btn-action btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </a>
            </div>
        </div>
    </div>

    <!-- Grille d'informations -->
    <div class="info-grid">
        <!-- Informations personnelles -->
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-user"></i>
                <h3>Informations personnelles</h3>
            </div>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">Nom</span>
                    <span class="info-value"><?php echo htmlspecialchars($etudiant['Nom']); ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Prénom</span>
                    <span class="info-value"><?php echo htmlspecialchars($etudiant['Prenom']); ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Date naissance</span>
                    <span class="info-value">
                        <?php echo $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : 'Non renseignée'; ?>
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">Âge</span>
                    <span class="info-value">
                        <?php 
                        if ($etudiant['date_naissance']) {
                            $age = date_diff(date_create($etudiant['date_naissance']), date_create('now'))->y;
                            echo $age . ' ans';
                        } else {
                            echo '-';
                        }
                        ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-envelope"></i>
                <h3>Contact</h3>
            </div>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">
                        <?php if ($etudiant['email']): ?>
                            <a href="mailto:<?php echo $etudiant['email']; ?>" style="color: var(--upf-pink);">
                                <?php echo htmlspecialchars($etudiant['email']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: var(--gray);">Non renseigné</span>
                        <?php endif; ?>
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value">
                        <?php if ($etudiant['telephone']): ?>
                            <a href="tel:<?php echo $etudiant['telephone']; ?>">
                                <?php echo $etudiant['telephone']; ?>
                            </a>
                        <?php else: ?>
                            <span style="color: var(--gray);">Non renseigné</span>
                        <?php endif; ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- Filière -->
        <div class="info-card">
            <div class="card-header">
                <i class="fas fa-building"></i>
                <h3>Filière</h3>
            </div>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">Code</span>
                    <span class="info-value"><?php echo $etudiant['CodeF'] ?? 'N/A'; ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Intitulé</span>
                    <span class="info-value"><?php echo htmlspecialchars($etudiant['IntituleF'] ?? 'Non assigné'); ?></span>
                </li>
                <li class="info-item">
                    <span class="info-label">Responsable</span>
                    <span class="info-value"><?php echo htmlspecialchars($etudiant['responsable'] ?? 'Non défini'); ?></span>
                </li>
            </ul>
        </div>

        <!-- Note et performance -->
        <div class="note-card">
            <div class="note-header">
                <span>Note /20</span>
                <i class="fas fa-star"></i>
            </div>
            <div class="note-value">
                <?php echo $etudiant['FNote'] !== null ? number_format($etudiant['FNote'], 2) : '---'; ?>
            </div>
            <div class="note-mention">
                <?php echo $mentionInfo['mention']; ?>
            </div>
            <div class="note-status">
                <i class="fas fa-<?php echo $statusInfo['status'] == 'Reçu' ? 'check-circle' : ($statusInfo['status'] == 'Ajourné' ? 'times-circle' : 'clock'); ?>"></i>
                <?php echo $statusInfo['status']; ?>
            </div>
        </div>
    </div>

    <!-- Section Documents -->
    <div class="documents-section">
        <div class="section-header">
            <h2>
                <i class="fas fa-file-pdf" style="color: var(--danger);"></i>
                Documents (<?php echo count($documents); ?>)
            </h2>
            <button class="btn-upload" onclick="openUploadModal()">
                <i class="fas fa-cloud-upload-alt"></i>
                Uploader un document
            </button>
        </div>

        <!-- Statistiques documents -->
        <?php if (count($documents) > 0): ?>
            <div class="docs-stats">
                <div class="doc-stat releve">
                    <i class="fas fa-chart-line" style="color: #3b82f6;"></i>
                    <div class="count"><?php echo $stats_docs['releve_notes']; ?></div>
                    <div>Relevés</div>
                </div>
                <div class="doc-stat attestation">
                    <i class="fas fa-certificate" style="color: #10b981;"></i>
                    <div class="count"><?php echo $stats_docs['attestation']; ?></div>
                    <div>Attestations</div>
                </div>
                <div class="doc-stat autre">
                    <i class="fas fa-file" style="color: #f59e0b;"></i>
                    <div class="count"><?php echo $stats_docs['autre']; ?></div>
                    <div>Autres</div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($documents) > 0): ?>
            <table class="documents-table">
                <thead>
                    <tr>
                        <th>Fichier</th>
                        <th>Type</th>
                        <th>Taille</th>
                        <th>Uploadé par</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): 
                        $fileExists = file_exists('../../' . $doc['chemin']);
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="file-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="file-info">
                                        <span class="file-name"><?php echo htmlspecialchars($doc['nom_fichier']); ?></span>
                                        <?php if (!$fileExists): ?>
                                            <span style="color: var(--danger); font-size: 0.8rem;">
                                                <i class="fas fa-exclamation-triangle"></i> Fichier introuvable
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="file-type-badge type-<?php 
                                    echo $doc['type_doc'] == 'releve_notes' ? 'releve' : 
                                        ($doc['type_doc'] == 'attestation' ? 'attestation' : 'autre'); 
                                ?>">
                                    <?php 
                                        echo $doc['type_doc'] == 'releve_notes' ? 'Relevé' : 
                                            ($doc['type_doc'] == 'attestation' ? 'Attestation' : 'Autre'); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo round($doc['taille'] / 1024, 2); ?> Ko</td>
                            <td><?php echo htmlspecialchars($doc['uploaded_by_login']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <?php if ($fileExists): ?>
                                        <a href="../../<?php echo $doc['chemin']; ?>" class="btn-download" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="supprimer_document.php?id=<?php echo $doc['id']; ?>&code=<?php echo $etudiant['Code']; ?>" 
                                       class="btn-delete-doc"
                                       onclick="return confirm('Supprimer ce document ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-folder-open" style="font-size: 4rem; color: var(--gray); margin-bottom: 20px; opacity: 0.3;"></i>
                <h3 style="color: var(--dark); margin-bottom: 10px;">Aucun document</h3>
                <p style="color: var(--gray);">Cliquez sur "Uploader un document" pour ajouter un fichier</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal" id="uploadModal">
    <div class="modal-content">
        <h2 style="margin-bottom: 20px;">
            <i class="fas fa-cloud-upload-alt" style="color: var(--upf-pink);"></i>
            Uploader un document
        </h2>
        
        <form action="upload_document.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="etudiant_id" value="<?php echo $etudiant['Code']; ?>">
            
            <!-- Type de document -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Type de document</label>
                <select name="type_doc" required style="
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e2e8f0;
                    border-radius: 12px;
                    font-size: 1rem;
                ">
                    <option value="releve_notes">Relevé de notes</option>
                    <option value="attestation">Attestation</option>
                    <option value="autre">Autre</option>
                </select>
            </div>

            <!-- Upload area -->
            <div class="upload-area" onclick="document.getElementById('documentFile').click()">
                <i class="fas fa-file-pdf"></i>
                <p>Cliquez pour sélectionner un fichier PDF</p>
                <small style="color: var(--gray);">Taille max: 5 Mo</small>
                <input type="file" 
                       name="document" 
                       id="documentFile" 
                       accept=".pdf"
                       required
                       style="display: none;"
                       onchange="updateFileName(this)">
            </div>

            <!-- Nom fichier sélectionné -->
            <div id="selectedFileName" style="margin: 15px 0; padding: 10px; background: #f8fafc; border-radius: 8px; display: none;">
                <i class="fas fa-check-circle" style="color: var(--success);"></i>
                <span id="fileName"></span>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">
                    Annuler
                </button>
                <button type="submit" class="btn btn-primary" id="uploadBtn">
                    Uploader
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal() {
        document.getElementById('uploadModal').classList.add('active');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.remove('active');
        document.getElementById('uploadForm').reset();
        document.getElementById('selectedFileName').style.display = 'none';
    }

    function updateFileName(input) {
        const fileName = document.getElementById('fileName');
        const selectedDiv = document.getElementById('selectedFileName');
        
        if (input.files && input.files[0]) {
            fileName.textContent = input.files[0].name;
            selectedDiv.style.display = 'block';
        }
    }

    // Fermer modal avec Echap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUploadModal();
        }
    });

    // Validation upload
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const file = document.getElementById('documentFile').files[0];
        
        if (!file) {
            e.preventDefault();
            alert('Veuillez sélectionner un fichier');
            return;
        }
        
        if (file.type !== 'application/pdf') {
            e.preventDefault();
            alert('Seuls les fichiers PDF sont acceptés');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('Le fichier ne doit pas dépasser 5 Mo');
            return;
        }
        
        document.getElementById('uploadBtn').disabled = true;
        document.getElementById('uploadBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Upload...';
    });
</script>

<?php require_once '../../includes/footer.php'; ?>