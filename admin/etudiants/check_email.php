<?php
// admin/etudiants/check_email.php
require_once '../../config/database.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['available' => true]);
    exit();
}

$pdo = getConnexion();
$stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    echo json_encode(['available' => false]);
} else {
    echo json_encode(['available' => true]);
}
?>