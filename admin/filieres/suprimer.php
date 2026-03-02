<?php
// admin/filieres/supprimer_traitement.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit();
}

$code = $_POST['code'] ?? '';

if (empty($code)) {
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

try {
    $pdo->beginTransaction();

    // Vérifier que la filière existe
    $stmt = $pdo->prepare("SELECT * FROM filieres WHERE CodeF = ?");
    $stmt->execute([$code]);
    $filiere = $stmt->fetch();

    if (!$filiere) {
        throw new Exception("Filière introuvable");
    }

    // Dissocier les étudiants (ON DELETE SET NULL dans la BDD)
    // Pas besoin de faire quoi que ce soit, la contrainte FK s'en charge

    // Supprimer la filière
    $stmt = $pdo->prepare("DELETE FROM filieres WHERE CodeF = ?");
    $stmt->execute([$code]);

    $pdo->commit();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => "✅ Filière supprimée avec succès"
    ];

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Erreur : " . $e->getMessage()
    ];
}

header('Location: liste.php');
exit();
?>