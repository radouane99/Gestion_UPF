<?php
// admin/etudiants/supprimer_document.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;
$code = $_GET['code'] ?? '';

if (empty($id) || empty($code)) {
    header('Location: ../liste.php');
    exit();
}

$pdo = getConnexion();

try {
    $pdo->beginTransaction();

    // Récupérer le document
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND etudiant_id = ?");
    $stmt->execute([$id, $code]);
    $doc = $stmt->fetch();

    if (!$doc) {
        throw new Exception("Document introuvable");
    }

    // Supprimer fichier physique
    $filepath = '../../' . $doc['chemin'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    // Supprimer de la BDD
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => "✅ Document supprimé avec succès"
    ];

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Erreur : " . $e->getMessage()
    ];
}

header('Location: detail.php?code=' . $code);
exit();
?>