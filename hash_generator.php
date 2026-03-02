<?php
// hash_generator.php (À la racine de gestion_upf/)
// ⚠️ À SUPPRIMER APRÈS UTILISATION ⚠️

require_once 'config/database.php';

// Style pour un affichage propre
echo "<!DOCTYPE html>
<html>
<head>
    <title>Générateur de mots de passe</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #294898, #C72C82);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #294898;
            margin-bottom: 20px;
            text-align: center;
        }
        h2 {
            color: #C72C82;
            margin-top: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #294898;
            color: white;
            padding: 10px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .hash {
            font-family: monospace;
            font-size: 12px;
            background: #f5f5f5;
            padding: 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔐 Générateur de mots de passe UPF</h1>";

try {
    // Connexion à la BDD
    $pdo = getConnexion();
    echo "<div class='success'>✅ Connexion à la base de données réussie</div>";
    
    // 1. GÉNÉRER LES MOTS DE PASSE HASHÉS
    echo "<h2>📝 Génération des hashs</h2>";
    
    // Admin: admin123
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    echo "<p>🔑 Admin: <strong>admin123</strong> → <span class='hash'>" . $adminPassword . "</span></p>";
    
    // User: password123
    $userPassword = password_hash('password123', PASSWORD_DEFAULT);
    echo "<p>🔑 User: <strong>password123</strong> → <span class='hash'>" . $userPassword . "</span></p>";
    
    // 2. VIDER LA TABLE (optionnel - décommente si besoin)
    // echo "<h2>🧹 Nettoyage</h2>";
    // $pdo->exec("DELETE FROM utilisateurs");
    // echo "<p>✅ Table utilisateurs vidée</p>";
    
    // 3. INSÉRER LES UTILISATEURS
    echo "<h2>💾 Insertion des utilisateurs</h2>";
    
    // Admin
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password, role, created_at) 
                           VALUES (:login, :pass, 'admin', NOW())
                           ON DUPLICATE KEY UPDATE password = :pass");
    $stmt->execute([':login' => 'admin', ':pass' => $adminPassword]);
    echo "<p>✅ Admin inséré/mis à jour: <strong>admin</strong></p>";
    
    // Ahmed
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                           VALUES (:login, :pass, 'user', 'E001', NOW())
                           ON DUPLICATE KEY UPDATE password = :pass");
    $stmt->execute([':login' => 'ahmed.alaoui', ':pass' => $userPassword]);
    echo "<p>✅ Ahmed inséré/mis à jour: <strong>ahmed.alaoui</strong> (E001)</p>";
    
    // Fatima
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                           VALUES (:login, :pass, 'user', 'E002', NOW())
                           ON DUPLICATE KEY UPDATE password = :pass");
    $stmt->execute([':login' => 'fatima.bennani', ':pass' => $userPassword]);
    echo "<p>✅ Fatima insérée/mise à jour: <strong>fatima.bennani</strong> (E002)</p>";
    
    // Ajout d'un 3ème étudiant si tu veux
    $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                           VALUES ('youssef.chraibi', :pass, 'user', 'E003', NOW())");
    $stmt->execute([':pass' => $userPassword]);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                           VALUES ('khadija.daoudi', :pass, 'user', 'E004', NOW())");
    $stmt->execute([':pass' => $userPassword]);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                           VALUES ('omar.elamrani', :pass, 'user', 'E005', NOW())");
    $stmt->execute([':pass' => $userPassword]);
    
    // 4. VÉRIFICATION
    echo "<h2>🔍 Vérification des données</h2>";
    
    $stmt = $pdo->query("SELECT u.*, e.Nom, e.Prenom 
                         FROM utilisateurs u 
                         LEFT JOIN etudiants e ON u.etudiant_id = e.Code 
                         ORDER BY u.id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Login</th>
            <th>Role</th>
            <th>Étudiant</th>
            <th>Hash</th>
            <th>Test</th>
          </tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td><strong>" . $user['login'] . "</strong></td>";
        echo "<td>" . $user['role'] . "</td>";
        
        $etudiant = ($user['Nom'] ?? '') ? $user['Prenom'] . ' ' . $user['Nom'] : '—';
        echo "<td>" . $etudiant . " (" . ($user['etudiant_id'] ?? '') . ")</td>";
        
        echo "<td><span class='hash'>" . substr($user['password'], 0, 30) . "...</span></td>";
        
        // Test password_verify
        if ($user['login'] == 'admin') {
            $test = password_verify('admin123', $user['password']);
            $passTest = 'admin123';
        } else {
            $test = password_verify('password123', $user['password']);
            $passTest = 'password123';
        }
        
        if ($test) {
            echo "<td style='color:green; font-weight:bold;'>✅ OK (pass: $passTest)</td>";
        } else {
            echo "<td style='color:red; font-weight:bold;'>❌ ÉCHEC</td>";
        }
        
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. RÉCAPITULATIF
    echo "<div class='success'>";
    echo "<h3>🎯 Récapitulatif des comptes</h3>";
    echo "<ul>";
    echo "<li><strong>Admin</strong> → login: <code>admin</code> | password: <code>admin123</code></li>";
    echo "<li><strong>Ahmed Alaoui</strong> → login: <code>ahmed.alaoui</code> | password: <code>password123</code></li>";
    echo "<li><strong>Fatima Bennani</strong> → login: <code>fatima.bennani</code> | password: <code>password123</code></li>";
    echo "<li><strong>Youssef Chraibi</strong> → login: <code>youssef.chraibi</code> | password: <code>password123</code></li>";
    echo "<li><strong>Khadija Daoudi</strong> → login: <code>khadija.daoudi</code> | password: <code>password123</code></li>";
    echo "<li><strong>Omar El Amrani</strong> → login: <code>omar.elamrani</code> | password: <code>password123</code></li>";
    echo "</ul>";
    echo "</div>";
    
    // 6. AVERTISSEMENT
    echo "<div class='warning'>";
    echo "⚠️ <strong>ATTENTION: Ce fichier doit être SUPPRIMÉ maintenant !</strong><br>";
    echo "Il contient des mots de passe en clair et des hashs. Supprime-le avant de continuer.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da; color:#721c24; padding:15px; border-radius:5px;'>";
    echo "❌ Erreur: " . $e->getMessage();
    echo "</div>";
}

echo "</div></body></html>";
?>