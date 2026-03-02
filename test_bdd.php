<?php
require_once 'config/database.php';

try {
    $pdo = getConnexion();
    echo "✅ Connexion BDD réussie!";
    
    // Test si la table utilisateurs existe
    $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
    $count = $stmt->fetchColumn();
    echo "<br>📊 Nombre d'utilisateurs: " . $count;
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>