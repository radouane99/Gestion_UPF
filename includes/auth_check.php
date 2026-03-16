<?php
// includes/auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion
    $redirect = '../login.php?erreur=acces';
    header('Location: ' . $redirect);
    exit();
}
?>