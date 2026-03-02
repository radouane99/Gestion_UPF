<?php
// admin/filieres/ajouter.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';
require_once '../../includes/header.php';
?>

<style>
    /* ============================================= */
    /* STYLES POUR AJOUTER FILIÈRE */
    /* ============================================= */
    :root {
        --upf-blue: #294898;
        --upf-pink: #C72C82;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --dark: #1e293b;
        --light: #f8fafc;
        --gray: #64748b;
        --gradient: linear-gradient(135deg, var(--upf-blue), var(--upf-pink));
    }

    .form-page {
        padding: 20px;
        max-width: 700px;
        margin: 0 auto;
    }

    .form-header {
        background: white;
        border-radius: 30px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, rgba(41,72,152,0.05), rgba(199,44,130,0.05));
    }

    .form-header h1 {
        font-size: 2.2rem;
        color: var(--dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .form-header h1 i {
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

    .modern-form {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 25px;
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
    .form-group textarea {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: var(--upf-pink);
        box-shadow: 0 0 0 4px rgba(199,44,130,0.1);
        outline: none;
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        color: var(--gray);
        font-size: 0.8rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f1f5f9;
    }

    .btn {
        padding: 14px 30px;
        border-radius: 16px;
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
        background: var(--gradient);
        color: white;
        box-shadow: 0 5px 15px rgba(199,44,130,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(199,44,130,0.4);
    }

    .btn-secondary {
        background: white;
        color: var(--dark);
        border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
    }

    .code-hint {
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 12px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        color: var(--gray);
    }

    .code-hint strong {
        color: var(--upf-pink);
    }

    #codeStatus {
        margin-top: 5px;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
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
            <i class="fas fa-plus-circle"></i>
            Nouvelle filière
        </h1>
        <div class="breadcrumb">
            <a href="../dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="liste.php"><i class="fas fa-building"></i> Filières</a>
            <i class="fas fa-chevron-right"></i>
            <span>Ajouter</span>
        </div>
    </div>

    <!-- Formulaire -->
    <form class="modern-form" action="ajouter_traitement.php" method="POST" id="filiereForm">
        
        <!-- Code filière -->
        <div class="form-group">
            <label>
                <i class="fas fa-id-card"></i>
                Code filière <span style="color: var(--danger);">*</span>
            </label>
            <div class="code-hint">
                <i class="fas fa-info-circle"></i>
                Exemple: <strong>GINFO</strong>, <strong>GINDUS</strong>, <strong>GSTR</strong>
            </div>
            <input type="text" 
                   name="code" 
                   id="code" 
                   required 
                   placeholder="Ex: GINFO"
                   maxlength="10"
                   style="text-transform: uppercase;"
                   onkeyup="this.value = this.value.toUpperCase(); checkCode();">
            <div id="codeStatus"></div>
            <small>Code unique, 2-10 caractères, lettres et chiffres uniquement</small>
        </div>

        <!-- Intitulé -->
        <div class="form-group">
            <label>
                <i class="fas fa-heading"></i>
                Intitulé complet <span style="color: var(--danger);">*</span>
            </label>
            <input type="text" 
                   name="intitule" 
                   id="intitule" 
                   required 
                   placeholder="Ex: Génie Informatique">
        </div>

        <!-- Responsable et Places (2 colonnes) -->
        <div class="form-row">
            <div class="form-group">
                <label>
                    <i class="fas fa-user-tie"></i>
                    Responsable pédagogique
                </label>
                <input type="text" 
                       name="responsable" 
                       placeholder="Ex: Pr. KZADRI">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-users"></i>
                    Nombre de places
                </label>
                <input type="number" 
                       name="nbPlaces" 
                       min="1" 
                       max="500" 
                       placeholder="Ex: 50">
            </div>
        </div>

        <!-- Notes (optionnel) -->
        <div class="form-group">
            <label>
                <i class="fas fa-sticky-note"></i>
                Description / Notes
            </label>
            <textarea name="notes" rows="3" placeholder="Informations supplémentaires..."></textarea>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <a href="liste.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i>
                Créer la filière
            </button>
        </div>
    </form>
</div>

<script>
    function checkCode() {
        const code = document.getElementById('code').value;
        const status = document.getElementById('codeStatus');
        
        if (code.length >= 2) {
            // Vérifier format (lettres et chiffres uniquement)
            if (!/^[A-Z0-9]+$/.test(code)) {
                status.innerHTML = '❌ Caractères non autorisés (utilisez lettres et chiffres)';
                status.style.color = '#ef4444';
                return;
            }
            
            // Vérifier disponibilité
            fetch('check_code.php?code=' + encodeURIComponent(code))
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        status.innerHTML = '✅ Code disponible';
                        status.style.color = '#10b981';
                    } else {
                        status.innerHTML = '❌ Ce code existe déjà';
                        status.style.color = '#ef4444';
                    }
                });
        } else {
            status.innerHTML = '';
        }
    }

    document.getElementById('filiereForm').addEventListener('submit', function(e) {
        const code = document.getElementById('code').value;
        const intitule = document.getElementById('intitule').value;
        
        if (!code || !intitule) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }
        
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
    });
</script>

<?php require_once '../../includes/footer.php'; ?>