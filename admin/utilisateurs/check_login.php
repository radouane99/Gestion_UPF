<?php
// admin/utilisateurs/check_login.php
require_once '../../config/database.php';

header('Content-Type: application/json');

$login = $_GET['login'] ?? '';

if (strlen($login) < 3) {
    echo json_encode(['available' => false, 'message' => 'Trop court']);
    exit();
}

$pdo = getConnexion();

$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
$stmt->execute([$login]);

if ($stmt->fetch()) {
    echo json_encode(['available' => false, 'message' => 'Login déjà utilisé']);
} else {
    echo json_encode(['available' => true, 'message' => 'Login disponible']);
}
?>