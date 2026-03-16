<?php
// index.php
session_start();

// Vérifier si l'application est installée
try {
    require_once 'config/database.php';
    $pdo = getConnexion();
    
    // Vérifier si la table utilisateurs existe et a des données
    $result = $pdo->query("SHOW TABLES LIKE 'utilisateurs'");
    if ($result->rowCount() == 0) {
        // Pas de tables, rediriger vers install.php
        header('Location: install.php');
        exit();
    }
    
    $count = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    if ($count == 0) {
        // Tables vides, rediriger vers install.php
        header('Location: install.php');
        exit();
    }
    
} catch (Exception $e) {
    // Erreur de connexion, probablement BDD n'existe pas
    header('Location: install.php');
    exit();
}

// Si tout est bon, continuer normalement
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/profil.php');
    }
    exit();
} else {
    header('Location: ./auth/login.php');
    exit();
}
?>