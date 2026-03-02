<?php
// includes/auth_check_admin.php
session_start();

// Debug - à enlever après
error_log("Auth check admin - Session: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    error_log("Auth check admin - Pas de session");
    header('Location: ../login.php?erreur=acces');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    error_log("Auth check admin - Role incorrect: " . $_SESSION['role']);
    header('Location: ../login.php?erreur=acces');
    exit();
}

// Optionnel : définir des variables pour les pages
$user_id = $_SESSION['user_id'];
$user_login = $_SESSION['login'];
?>