<?php
// admin/etudiants/check_code.php
require_once '../../config/database.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if (empty($code)) {
    echo json_encode(['available' => false]);
    exit();
}

$pdo = getConnexion();
$stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE Code = ?");
$stmt->execute([$code]);

if ($stmt->fetch()) {
    echo json_encode(['available' => false]);
} else {
    echo json_encode(['available' => true]);
}
?>