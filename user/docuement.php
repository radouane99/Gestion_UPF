<?php
// user/documents.php
require_once '../includes/auth_check_user.php';
require_once '../config/database.php';
require_once '../includes/header.php';

$pdo = getConnexion();

// Récupérer l'étudiant
$stmt = $pdo->prepare("SELECT Code, Nom, Prenom FROM etudiants WHERE Code = ?");
$stmt->execute([$_SESSION['etudiant_id']]);
$etudiant = $stmt->fetch();

// Récupérer tous les documents de l'étudiant
$stmt = $pdo->prepare("
    SELECT d.*, u.login as uploaded_by_login 
    FROM documents d
    LEFT JOIN utilisateurs u ON d.uploaded_by = u.id
    WHERE d.etudiant_id = ?
    ORDER BY d.uploaded_at DESC
");
$stmt->execute([$_SESSION['etudiant_id']]);
$documents = $stmt->fetchAll();

// Statistiques
$totalTaille = array_sum(array_column($documents, 'taille'));
$types = [
    'releve_notes' => 0,
    'attestation' => 0,
    'autre' => 0
];
foreach ($documents as $doc) {
    $types[$doc['type_doc']]++;
}
?>

<style>
    /* ============================================= */
    /* STYLES POUR LA PAGE DOCUMENTS ÉTUDIANT */
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

    .documents-page {
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

    .stat-content h3 {
        color: var(--gray);
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark);
    }

    /* Types badges */
    .types-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .type-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-left: 4px solid;
    }

    .type-releve { border-color: #3b82f6; }
    .type-attestation { border-color: #10b981; }
    .type-autre { border-color: #f59e0b; }

    .type-card i {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .type-card h4 {
        margin-bottom: 5px;
    }

    /* Liste documents */
    .documents-list {
        background: white;
        border-radius: 30px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }

    .documents-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .documents-header h2 {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding: 12px 20px 12px 45px;
        border: 2px solid #e2e8f0;
        border-radius: 15px;
        width: 300px;
        font-size: 0.95rem;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    /* Timeline */
    .timeline {
        position: relative;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }

    .timeline-item {
        position: relative;
        padding-left: 80px;
        margin-bottom: 30px;
    }

    .timeline-dot {
        position: absolute;
        left: 22px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--gradient);
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(199,44,130,0.3);
        z-index: 2;
    }

    .timeline-date {
        position: absolute;
        left: -30px;
        top: -5px;
        background: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        color: var(--gray);
        border: 1px solid #e2e8f0;
    }

    .document-card {
        background: #f8fafc;
        border-radius: 20px;
        padding: 20px;
        transition: all 0.3s;
        border: 1px solid transparent;
    }

    .document-card:hover {
        transform: translateX(10px);
        border-color: var(--upf-pink);
        box-shadow: 0 5px 20px rgba(199,44,130,0.1);
    }

    .document-icon {
        width: 50px;
        height: 50px;
        background: #fee2e2;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--danger);
        font-size: 1.5rem;
    }

    .document-info {
        flex: 1;
    }

    .document-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .document-meta {
        display: flex;
        gap: 20px;
        color: var(--gray);
        font-size: 0.9rem;
        flex-wrap: wrap;
    }

    .document-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .document-type {
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .type-releve { background: #dbeafe; color: #1e40af; }
    .type-attestation { background: #d1fae5; color: #065f46; }
    .type-autre { background: #fef3c7; color: #92400e; }

    .btn-download {
        padding: 12px 25px;
        background: var(--gradient);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(199,44,130,0.4);
    }

    .btn-download i {
        font-size: 1.1rem;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 5rem;
        color: var(--gray);
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .empty-state h3 {
        color: var(--dark);
        margin-bottom: 10px;
    }

    .empty-state p {
        color: var(--gray);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .types-grid {
            grid-template-columns: 1fr;
        }
        
        .search-box input {
            width: 100%;
        }
        
        .timeline::before {
            left: 20px;
        }
        
        .timeline-item {
            padding-left: 60px;
        }
        
        .timeline-date {
            position: static;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .document-card {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<div class="documents-page">
    
    <!-- En-tête -->
    <div class="page-header">
        <h1>
            <i class="fas fa-file-pdf"></i>
            Mes documents
        </h1>
        <div class="breadcrumb">
            <a href="profil.php"><i class="fas fa-user"></i> Mon profil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Documents</span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="stat-content">
                <h3>Total documents</h3>
                <div class="stat-number"><?php echo count($documents); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-weight"></i>
            </div>
            <div class="stat-content">
                <h3>Taille totale</h3>
                <div class="stat-number"><?php echo round($totalTaille / (1024 * 1024), 2); ?> Mo</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-content">
                <h3>Dernier ajout</h3>
                <div class="stat-number">
                    <?php 
                    if (count($documents) > 0) {
                        echo date('d/m/Y', strtotime($documents[0]['uploaded_at']));
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Types de documents -->
    <?php if (count($documents) > 0): ?>
        <div class="types-grid">
            <div class="type-card type-releve">
                <i class="fas fa-chart-line" style="color: #3b82f6;"></i>
                <h4>Relevés de notes</h4>
                <p style="font-size: 1.5rem; font-weight: 700;"><?php echo $types['releve_notes']; ?></p>
            </div>
            <div class="type-card type-attestation">
                <i class="fas fa-certificate" style="color: #10b981;"></i>
                <h4>Attestations</h4>
                <p style="font-size: 1.5rem; font-weight: 700;"><?php echo $types['attestation']; ?></p>
            </div>
            <div class="type-card type-autre">
                <i class="fas fa-file" style="color: #f59e0b;"></i>
                <h4>Autres documents</h4>
                <p style="font-size: 1.5rem; font-weight: 700;"><?php echo $types['autre']; ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Liste des documents -->
    <div class="documents-list">
        <div class="documents-header">
            <h2>
                <i class="fas fa-list"></i>
                Tous mes documents
            </h2>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchDoc" placeholder="Rechercher un document...">
            </div>
        </div>

        <?php if (count($documents) > 0): ?>
            <!-- Timeline -->
            <div class="timeline" id="documentsContainer">
                <?php 
                $currentDate = '';
                foreach ($documents as $doc):
                    $date = date('d/m/Y', strtotime($doc['uploaded_at']));
                    $time = date('H:i', strtotime($doc['uploaded_at']));
                    
                    // Vérifier si le fichier existe
                    $fileExists = file_exists('../' . $doc['chemin']);
                ?>
                    <?php if ($date != $currentDate): ?>
                        <?php $currentDate = $date; ?>
                        <div class="timeline-item">
                            <span class="timeline-date"><?php echo $date; ?></span>
                            <span class="timeline-dot"></span>
                    <?php endif; ?>

                    <div class="document-card" style="margin-left: 30px; margin-bottom: 15px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <div class="document-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        
                        <div class="document-info">
                            <div class="document-name">
                                <?php echo htmlspecialchars($doc['nom_fichier']); ?>
                                <?php if (!$fileExists): ?>
                                    <span style="color: var(--danger);"> (Fichier introuvable)</span>
                                <?php endif; ?>
                            </div>
                            <div class="document-meta">
                                <span>
                                    <i class="far fa-clock"></i>
                                    <?php echo $time; ?>
                                </span>
                                <span>
                                    <i class="fas fa-weight"></i>
                                    <?php echo round($doc['taille'] / 1024, 2); ?> Ko
                                </span>
                                <span>
                                    <i class="fas fa-user"></i>
                                    Admin: <?php echo htmlspecialchars($doc['uploaded_by_login']); ?>
                                </span>
                                <span class="document-type type-<?php 
                                    echo $doc['type_doc'] == 'releve_notes' ? 'releve' : 
                                        ($doc['type_doc'] == 'attestation' ? 'attestation' : 'autre'); 
                                ?>">
                                    <?php 
                                        echo $doc['type_doc'] == 'releve_notes' ? 'Relevé de notes' : 
                                            ($doc['type_doc'] == 'attestation' ? 'Attestation' : 'Autre'); 
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($fileExists): ?>
                            <a href="../<?php echo $doc['chemin']; ?>" class="btn-download" download>
                                <i class="fas fa-download"></i>
                                Télécharger
                            </a>
                        <?php else: ?>
                            <button class="btn-download" style="background: var(--gray);" disabled>
                                <i class="fas fa-exclamation-triangle"></i>
                                Indisponible
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php 
                    // Fermer la timeline-item si c'est le dernier du jour ou le dernier document
                    $nextDoc = next($documents);
                    $nextDate = $nextDoc ? date('d/m/Y', strtotime($nextDoc['uploaded_at'])) : null;
                    if (!$nextDoc || $nextDate != $date):
                    ?>
                        </div> <!-- Fermeture timeline-item -->
                    <?php endif; ?>
                    <?php prev($documents); // Revenir en arrière pour que foreach continue normalement ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty state -->
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>Aucun document disponible</h3>
                <p>L'administration n'a pas encore uploadé de documents pour vous.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Recherche en temps réel
    document.getElementById('searchDoc').addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        const documents = document.querySelectorAll('.document-card');
        
        documents.forEach(doc => {
            const name = doc.querySelector('.document-name').textContent.toLowerCase();
            const type = doc.querySelector('.document-type').textContent.toLowerCase();
            
            if (name.includes(search) || type.includes(search)) {
                doc.style.display = 'flex';
            } else {
                doc.style.display = 'none';
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>