<?php
// admin/utilisateurs/supprimer.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

// Empêcher suppression de soi-même
if ($id == $_SESSION['user_id']) {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Vous ne pouvez pas supprimer votre propre compte"
    ];
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

try {
    $pdo->beginTransaction();

    // Récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Utilisateur introuvable");
    }

    // Vérifier si l'utilisateur a uploadé des documents
    if ($user['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE uploaded_by = ?");
        $stmt->execute([$id]);
        $docCount = $stmt->fetchColumn();
        
        if ($docCount > 0) {
            throw new Exception("Cet administrateur a uploadé $docCount document(s). Impossible de supprimer.");
        }
    }

    // Supprimer l'utilisateur
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);

    // Log l'action
    error_log("Admin {$_SESSION['login']} a supprimé l'utilisateur {$user['login']} (ID: $id)");

    $pdo->commit();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => "✅ Utilisateur supprimé avec succès"
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