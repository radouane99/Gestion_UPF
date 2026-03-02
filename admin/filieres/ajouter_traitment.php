<?php
// admin/filieres/check_code.php
require_once '../../config/database.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if (empty($code)) {
    echo json_encode(['available' => false]);
    exit();
}

$pdo = getConnexion();
$stmt = $pdo->prepare("SELECT CodeF FROM filieres WHERE CodeF = ?");
$stmt->execute([$code]);

if ($stmt->fetch()) {
    echo json_encode(['available' => false]);
} else {
    echo json_encode(['available' => true]);
}
?>