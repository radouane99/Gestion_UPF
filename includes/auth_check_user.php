<?php
// includes/auth_check_user.php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php?erreur=acces');
    exit();
}

if ($_SESSION['role'] !== 'user') {
    header('Location: ../login.php?erreur=acces');
    exit();
}

if (!isset($_SESSION['etudiant_id'])) {
    header('Location: ../logout.php');
    exit();
}
?>