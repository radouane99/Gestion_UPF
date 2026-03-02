<?php
// user/profil.php
require_once '../includes/auth_check_user.php';  // ← Devrait marcher !
require_once '../config/database.php';
require_once '../includes/header.php';



$pdo = getConnexion();

// Récupérer les informations de l'étudiant connecté
$stmt = $pdo->prepare("
    SELECT e.*, f.IntituleF, f.CodeF,
           (SELECT AVG(FNote) FROM etudiants WHERE Filiere = e.Filiere AND FNote IS NOT NULL) as moyenne_filiere,
           (SELECT COUNT(*) FROM etudiants WHERE Filiere = e.Filiere) as total_filiere,
           (SELECT COUNT(*) FROM etudiants WHERE Filiere = e.Filiere AND FNote > e.FNote) as mieux_classe
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

// Récupérer les statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE etudiant_id = ?");
$stmt->execute([$_SESSION['etudiant_id']]);
$nbDocuments = $stmt->fetchColumn();

// Récupérer la dernière connexion
$stmt = $pdo->prepare("SELECT derniere_connexion FROM utilisateurs WHERE etudiant_id = ?");
$stmt->execute([$_SESSION['etudiant_id']]);
$derniereConnexion = $stmt->fetchColumn();

// Déterminer la mention
$mention = '';
$couleurMention = '';
if ($etudiant['FNote'] !== null) {
    if ($etudiant['FNote'] >= 16) {
        $mention = 'Très Bien';
        $couleurMention = 'mention-excellente';
    } elseif ($etudiant['FNote'] >= 14) {
        $mention = 'Bien';
        $couleurMention = 'mention-bien';
    } elseif ($etudiant['FNote'] >= 12) {
        $mention = 'Assez Bien';
        $couleurMention = 'mention-assez-bien';
    } elseif ($etudiant['FNote'] >= 10) {
        $mention = 'Passable';
        $couleurMention = 'mention-passable';
    } else {
        $mention = 'Insuffisant';
        $couleurMention = 'mention-insuffisant';
    }
} else {
    $mention = 'Non évalué';
    $couleurMention = 'mention-non-evalue';
}
?>

<style>
    /* ============================================= */
    /* STYLES MODERNES POUR LE PROFIL ÉTUDIANT */
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
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 200px;
        background: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
        opacity: 0.1;
    }

    .profile-cover {
        position: relative;
        display: flex;
        align-items: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    /* Avatar avec upload */
    .avatar-container {
        position: relative;
        width: 180px;
        height: 180px;
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
        overflow: hidden;
        position: relative;
        border: 4px solid white;
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
    }

    .avatar-upload:hover {
        transform: scale(1.1);
        background: var(--gradient);
        color: white;
    }

    .avatar-upload input {
        display: none;
    }

    /* Informations principales */
    .profile-title {
        flex: 1;
    }

    .profile-title h1 {
        font-size: 3rem;
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

    .badge-filiere {
        background: rgba(41,72,152,0.1);
        color: var(--upf-blue);
    }

    .badge-code {
        background: rgba(199,44,130,0.1);
        color: var(--upf-pink);
    }

    .badge-online {
        background: rgba(16,185,129,0.1);
        color: #10b981;
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

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f5f9;
    }

    .info-card-header i {
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

    .info-card-header h3 {
        font-size: 1.2rem;
        color: var(--dark);
    }

    /* Liste d'informations */
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
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-value i {
        color: var(--upf-pink);
    }

    /* Carte de note */
    .note-card {
        background: var(--gradient);
        border-radius: 25px;
        padding: 30px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .note-card::before {
        content: '🎓';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 150px;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .note-label {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        opacity: 0.9;
        margin-bottom: 10px;
    }

    .note-value {
        font-size: 5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 10px;
    }

    .note-mention {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .note-stats {
        display: flex;
        gap: 20px;
        font-size: 0.95rem;
        opacity: 0.9;
    }

    /* Mentions */
    .mention-excellente { color: #10b981; }
    .mention-bien { color: #3b82f6; }
    .mention-assez-bien { color: #f59e0b; }
    .mention-passable { color: #f97316; }
    .mention-insuffisant { color: #ef4444; }
    .mention-non-evalue { color: var(--gray); }

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

    /* Section documents récents */
    .documents-section {
        background: white;
        border-radius: 25px;
        padding: 30px;
        margin-top: 30px;
    }

    .documents-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .documents-header h3 {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .document-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 15px;
        background: #f8fafc;
        margin-bottom: 10px;
        transition: all 0.3s;
    }

    .document-item:hover {
        background: var(--gradient);
        color: white;
    }

    .document-icon {
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: var(--upf-pink);
        font-size: 1.5rem;
    }

    .document-info {
        flex: 1;
    }

    .document-name {
        font-weight: 600;
        margin-bottom: 3px;
    }

    .document-meta {
        font-size: 0.8rem;
        color: var(--gray);
    }

    .document-download {
        padding: 8px 15px;
        border-radius: 10px;
        background: white;
        color: var(--upf-blue);
        text-decoration: none;
        transition: all 0.3s;
    }

    .document-item:hover .document-download {
        background: var(--upf-pink);
        color: white;
    }

    /* Modal d'upload */
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

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-cover {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-title h1 {
            font-size: 2rem;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .note-value {
            font-size: 3rem;
        }
    }
</style>

<div class="profile-page">
    
    <!-- En-tête du profil -->
    <div class="profile-header">
        <div class="profile-cover">
            <!-- Avatar -->
            <div class="avatar-container">
                <div class="profile-avatar">
                    <?php if (!empty($etudiant['Photo']) && file_exists('../' . $etudiant['Photo'])): ?>
                        <img src="../<?php echo htmlspecialchars($etudiant['Photo']); ?>" alt="Photo profil">
                    <?php else: ?>
                        <?php echo strtoupper(substr($etudiant['Prenom'], 0, 1) . substr($etudiant['Nom'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Bouton upload photo -->
                <label class="avatar-upload">
                    <i class="fas fa-camera"></i>
                    <input type="file" id="photoUpload" accept="image/jpeg,image/png" form="uploadForm">
                </label>
            </div>

            <!-- Informations principales -->
            <div class="profile-title">
                <h1><?php echo htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']); ?></h1>
                <div class="profile-badges">
                    <span class="badge badge-filiere">
                        <i class="fas fa-graduation-cap"></i>
                        <?php echo htmlspecialchars($etudiant['IntituleF'] ?? 'Non assigné'); ?>
                    </span>
                    <span class="badge badge-code">
                        <i class="fas fa-id-card"></i>
                        <?php echo $etudiant['Code']; ?>
                    </span>
                    <span class="badge badge-online">
                        <i class="fas fa-circle"></i>
                        En ligne
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille d'informations -->
    <div class="info-grid">
        <!-- Carte Note -->
        <div class="note-card">
            <div class="note-label">Note</div>
            <div class="note-value">
                <?php echo $etudiant['FNote'] !== null ? number_format($etudiant['FNote'], 2) : '---'; ?>
            </div>
            <div class="note-mention <?php echo $couleurMention; ?>">
                <?php echo $mention; ?>
            </div>
            <div class="note-stats">
                <span><i class="fas fa-chart-line"></i> Moy. filière: <?php echo number_format($etudiant['moyenne_filiere'] ?? 0, 2); ?></span>
                <span><i class="fas fa-trophy"></i> Rang: <?php echo ($etudiant['mieux_classe'] ?? 0) + 1; ?>/<?php echo $etudiant['total_filiere']; ?></span>
            </div>
        </div>

        <!-- Informations personnelles -->
        <div class="info-card">
            <div class="info-card-header">
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
            <div class="info-card-header">
                <i class="fas fa-envelope"></i>
                <h3>Contact</h3>
            </div>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($etudiant['email'] ?? 'Non renseigné'); ?>
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value">
                        <i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($etudiant['telephone'] ?? 'Non renseigné'); ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- Filière -->
        <div class="info-card">
            <div class="info-card-header">
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
            </ul>
        </div>

        <!-- Statistiques -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-chart-bar"></i>
                <h3>Statistiques</h3>
            </div>
            <ul class="info-list">
                <li class="info-item">
                    <span class="info-label">Documents</span>
                    <span class="info-value">
                        <i class="fas fa-file-pdf"></i>
                        <?php echo $nbDocuments; ?> document(s)
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">Dernière connexion</span>
                    <span class="info-value">
                        <i class="fas fa-clock"></i>
                        <?php echo $derniereConnexion ? date('d/m/Y H:i', strtotime($derniereConnexion)) : 'Première connexion'; ?>
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">IP actuelle</span>
                    <span class="info-value">
                        <i class="fas fa-network-wired"></i>
                        <?php echo $_SERVER['REMOTE_ADDR']; ?>
                    </span>
                </li>
                <li class="info-item">
                    <span class="info-label">Navigateur</span>
                    <span class="info-value">
                        <i class="fas fa-globe"></i>
                        <?php 
                        $ua = $_SERVER['HTTP_USER_AGENT'];
                        if (strpos($ua, 'Chrome')) echo 'Chrome';
                        elseif (strpos($ua, 'Firefox')) echo 'Firefox';
                        elseif (strpos($ua, 'Safari')) echo 'Safari';
                        else echo 'Autre';
                        ?>
                    </span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
        <a href="notes.php" class="action-card">
            <i class="fas fa-graduation-cap"></i>
            <h4>Mes notes</h4>
            <p>Consulter mes résultats</p>
        </a>
        <a href="documents.php" class="action-card">
            <i class="fas fa-file-pdf"></i>
            <h4>Mes documents</h4>
            <p><?php echo $nbDocuments; ?> document(s) disponible(s)</p>
        </a>
        <a href="changer_password.php" class="action-card">
            <i class="fas fa-key"></i>
            <h4>Changer mot de passe</h4>
            <p>Sécuriser mon compte</p>
        </a>
        <a href="../logout.php" class="action-card" style="color: var(--danger);">
            <i class="fas fa-sign-out-alt"></i>
            <h4>Déconnexion</h4>
            <p>Quitter l'application</p>
        </a>
    </div>

    <!-- Documents récents -->
    <div class="documents-section">
        <div class="documents-header">
            <h3><i class="fas fa-history"></i> Documents récents</h3>
            <a href="documents.php" style="color: var(--upf-pink);">Voir tous <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php
        // Récupérer les 3 derniers documents
        $stmt = $pdo->prepare("
            SELECT * FROM documents 
            WHERE etudiant_id = ? 
            ORDER BY uploaded_at DESC 
            LIMIT 3
        ");
        $stmt->execute([$_SESSION['etudiant_id']]);
        $documentsRecents = $stmt->fetchAll();
        
        if (count($documentsRecents) > 0):
            foreach ($documentsRecents as $doc):
        ?>
            <div class="document-item">
                <div class="document-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="document-info">
                    <div class="document-name"><?php echo htmlspecialchars($doc['nom_fichier']); ?></div>
                    <div class="document-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($doc['uploaded_at'])); ?></span>
                        <span><i class="fas fa-weight"></i> <?php echo round($doc['taille'] / 1024, 2); ?> Ko</span>
                    </div>
                </div>
                <a href="../<?php echo $doc['chemin']; ?>" class="document-download" download>
                    <i class="fas fa-download"></i> Télécharger
                </a>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <p style="text-align: center; color: var(--gray); padding: 30px;">
                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                Aucun document disponible
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Formulaire caché pour upload photo -->
<form id="uploadForm" action="upload_photo.php" method="POST" enctype="multipart/form-data" style="display: none;">
    <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png">
</form>

<!-- Modal Upload -->
<div class="modal" id="uploadModal">
    <div class="modal-content">
        <h2 style="margin-bottom: 20px;">📸 Changer photo de profil</h2>
        <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="photoUploadForm">
            <div style="border: 2px dashed #e2e8f0; border-radius: 20px; padding: 40px; text-align: center; margin-bottom: 20px;">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--upf-pink); margin-bottom: 15px;"></i>
                <p>Glissez votre photo ici ou</p>
                <label style="background: var(--gradient); color: white; padding: 10px 20px; border-radius: 10px; cursor: pointer; display: inline-block; margin-top: 10px;">
                    <i class="fas fa-folder-open"></i> Parcourir
                    <input type="file" name="photo" accept="image/jpeg,image/png" style="display: none;" required>
                </label>
                <p style="margin-top: 15px; font-size: 0.8rem; color: var(--gray);">
                    Formats acceptés: JPG, PNG (Max: 2 Mo)
                </p>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Uploader</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Upload photo
    document.getElementById('photoUpload').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            document.getElementById('uploadForm').submit();
        }
    });

    // Preview avant upload (optionnel)
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Afficher preview dans l'avatar
                document.querySelector('.profile-avatar').innerHTML = 
                    `<img src="${e.target.result}" alt="Preview">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Ouvrir modal
    function openModal() {
        document.getElementById('uploadModal').classList.add('active');
    }

    // Fermer modal
    function closeModal() {
        document.getElementById('uploadModal').classList.remove('active');
    }
</script>

<?php require_once '../includes/footer.php'; ?>