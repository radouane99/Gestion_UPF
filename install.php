<?php
// install.php - À exécuter UNE SEULE FOIS au début
// Ce fichier crée toute la structure automatiquement

require_once 'config/database.php';

// Style pour l'affichage
echo "<!DOCTYPE html>
<html>
<head>
    <title>Installation UPF Gestion</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 10px; max-width: 800px; margin: auto; }
        .success { color: green; padding: 5px; }
        .warning { color: orange; }
        h1 { color: #294898; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🚀 Installation automatique de l'application UPF</h1>";

try {
    $pdo = getConnexion();
    
    // 1. CRÉATION DES TABLES
    echo "<h2>📦 Création des tables...</h2>";
    
    // Table filieres
    $pdo->exec("CREATE TABLE IF NOT EXISTS filieres (
        CodeF VARCHAR(10) PRIMARY KEY,
        IntituleF VARCHAR(100) NOT NULL,
        responsable VARCHAR(100),
        nbPlaces INT,
        created_at DATETIME NOT NULL
    )");
    echo "<p class='success'>✅ Table filières créée</p>";
    
    // Table etudiants
    $pdo->exec("CREATE TABLE IF NOT EXISTS etudiants (
        Code VARCHAR(10) PRIMARY KEY,
        Nom VARCHAR(50) NOT NULL,
        Prenom VARCHAR(50) NOT NULL,
        Filiere VARCHAR(10) NULL,
        FNote DECIMAL(4,2) NULL,
        Photo VARCHAR(255) NULL,
        date_naissance DATE NULL,
        email VARCHAR(100) UNIQUE NULL,
        telephone VARCHAR(20) NULL,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (Filiere) REFERENCES filieres(CodeF) ON DELETE SET NULL
    )");
    echo "<p class='success'>✅ Table étudiants créée</p>";
    
    // Table utilisateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        login VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL,
        etudiant_id VARCHAR(10) UNIQUE NULL,
        derniere_connexion DATETIME NULL,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (etudiant_id) REFERENCES etudiants(Code) ON DELETE SET NULL
    )");
    echo "<p class='success'>✅ Table utilisateurs créée</p>";
    
    // Table documents
    $pdo->exec("CREATE TABLE IF NOT EXISTS documents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        etudiant_id VARCHAR(10) NOT NULL,
        type_doc ENUM('releve_notes', 'attestation', 'autre') NOT NULL,
        nom_fichier VARCHAR(255) NOT NULL,
        chemin VARCHAR(255) NOT NULL,
        taille INT NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        uploaded_by INT NOT NULL,
        uploaded_at DATETIME NOT NULL,
        FOREIGN KEY (etudiant_id) REFERENCES etudiants(Code) ON DELETE CASCADE,
        FOREIGN KEY (uploaded_by) REFERENCES utilisateurs(id) ON DELETE RESTRICT
    )");
    echo "<p class='success'>✅ Table documents créée</p>";
    
    // 2. VÉRIFIER SI DES DONNÉES EXISTENT DÉJÀ
    $count = $pdo->query("SELECT COUNT(*) FROM filieres")->fetchColumn();
    
    if ($count == 0) {
        echo "<h2>📝 Insertion des données de test...</h2>";
        
        // Insérer les filières
        $sql = "INSERT INTO filieres (CodeF, IntituleF, responsable, nbPlaces, created_at) VALUES
                ('GINFO', 'Génie Informatique', 'Pr. KZADRI', 50, NOW()),
                ('GINDUS', 'Génie Industriel', 'Pr. EL FALLAH', 45, NOW()),
                ('GSTR', 'Gestion', 'Pr. BENALI', 60, NOW())";
        $pdo->exec($sql);
        echo "<p class='success'>✅ 3 filières ajoutées</p>";
        
        // Insérer les étudiants
        $sql = "INSERT INTO etudiants (Code, Nom, Prenom, Filiere, FNote, date_naissance, email, telephone, created_at) VALUES
                ('E001', 'ALAOUI', 'Ahmed', 'GINFO', 15.5, '2000-01-15', 'ahmed.alaoui@example.com', '0612345678', NOW()),
                ('E002', 'BENNANI', 'Fatima', 'GINFO', 8.5, '2001-03-22', 'fatima.bennani@example.com', '0623456789', NOW()),
                ('E003', 'CHRAIBI', 'Youssef', 'GINDUS', 12.0, '2000-11-10', 'youssef.chraibi@example.com', '0634567890', NOW()),
                ('E004', 'DAOUDI', 'Khadija', 'GINDUS', 17.0, '2001-07-18', 'khadija.daoudi@example.com', '0645678901', NOW()),
                ('E005', 'EL AMRANI', 'Omar', 'GSTR', NULL, '2000-09-05', 'omar.elamrani@example.com', '0656789012', NOW())";
        $pdo->exec($sql);
        echo "<p class='success'>✅ 5 étudiants ajoutés</p>";
        
        // Insérer les utilisateurs AVEC HASH AUTOMATIQUE
        echo "<p>🔐 Génération des hashs des mots de passe...</p>";
        
        // Admin: admin123
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $userHash = password_hash('password123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO utilisateurs (login, password, role, etudiant_id, created_at) VALUES
                ('admin', :adminHash, 'admin', NULL, NOW()),
                ('ahmed.alaoui', :userHash, 'user', 'E001', NOW()),
                ('fatima.bennani', :userHash, 'user', 'E002', NOW()),
                ('youssef.chraibi', :userHash, 'user', 'E003', NOW()),
                ('khadija.daoudi', :userHash, 'user', 'E004', NOW()),
                ('omar.elamrani', :userHash, 'user', 'E005', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':adminHash' => $adminHash,
            ':userHash' => $userHash
        ]);
        echo "<p class='success'>✅ 6 utilisateurs ajoutés avec mots de passe hashés</p>";
        
        echo "<h2 class='success'>🎉 Installation terminée avec succès !</h2>";
        
    } else {
        echo "<p class='warning'>⚠️ Des données existent déjà, insertion ignorée.</p>";
    }
    
    // 3. CRÉER LES DOSSIERS UPLOADS
    if (!file_exists('uploads/photos')) {
        mkdir('uploads/photos', 0777, true);
        echo "<p class='success'>✅ Dossier uploads/photos créé</p>";
    }
    if (!file_exists('uploads/documents')) {
        mkdir('uploads/documents', 0777, true);
        echo "<p class='success'>✅ Dossier uploads/documents créé</p>";
    }
    
    // 4. INFORMATIONS DE CONNEXION
    echo "<h3>🔑 Informations de connexion :</h3>";
    echo "<ul>";
    echo "<li><strong>Admin</strong> → login: <code>admin</code> | password: <code>admin123</code></li>";
    echo "<li><strong>Ahmed</strong> → login: <code>ahmed.alaoui</code> | password: <code>password123</code></li>";
    echo "<li><strong>Fatima</strong> → login: <code>fatima.bennani</code> | password: <code>password123</code></li>";
    echo "<li><strong>Youssef</strong> → login: <code>youssef.chraibi</code> | password: <code>password123</code></li>";
    echo "<li><strong>Khadija</strong> → login: <code>khadija.daoudi</code> | password: <code>password123</code></li>";
    echo "<li><strong>Omar</strong> → login: <code>omar.elamrani</code> | password: <code>password123</code></li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' style='background: #294898; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➡️ Aller à l'application</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "</div></body></html>";
?>