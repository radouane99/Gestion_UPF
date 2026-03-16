<?php
// login_traitement.php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($login) || empty($password)) {
    header('Location: login.php?erreur=1');
    exit();
}

require_once __DIR__ . '/../config/database.php';
$pdo = getConnexion();

try {
    $sql = "SELECT * FROM utilisateurs WHERE login = :login";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['etudiant_id'] = $user['etudiant_id'];
        $_SESSION['heure_connexion'] = date('Y-m-d H:i:s');
        $_SESSION['photo'] = $user['photo']; // après avoir défini les autres variables de session

        setcookie('last_login', $login, time() + (30 * 24 * 3600), '/');
        
        $sqlUpdate = "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([':id' => $user['id']]);
        
        if ($user['role'] === 'admin') {
header('Location: ' . BASE_URL . 'admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'user/profil.php');
        }
        exit();
    } else {
        header('Location: ' . BASE_URL . 'auth/login.php?erreur=1');
        exit();
    }
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'auth/login.php?erreur=1');
    exit();
}
?>