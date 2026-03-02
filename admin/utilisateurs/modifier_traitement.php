<?php
// admin/utilisateurs/modifier_traitement.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit();
}

$id = $_POST['id'] ?? 0;
$role = $_POST['role'] ?? '';
$etudiant_id = $_POST['etudiant_id'] ?? null;

$pdo = getConnexion();

try {
    $pdo->beginTransaction();

    // Vérifier que l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("Utilisateur introuvable");
    }

    // Vérifications pour le rôle user
    if ($role === 'user' && !empty($etudiant_id)) {
        // Vérifier que l'étudiant n'est pas déjà lié à un autre compte
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE etudiant_id = ? AND id != ?");
        $stmt->execute([$etudiant_id, $id]);
        if ($stmt->fetch()) {
            throw new Exception("Cet étudiant est déjà lié à un autre compte");
        }

        // Vérifier que l'étudiant existe
        $stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE Code = ?");
        $stmt->execute([$etudiant_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Étudiant introuvable");
        }
    }

    // Mise à jour
    $sql = "UPDATE utilisateurs SET role = ?, etudiant_id = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$role, $etudiant_id, $id]);

    // Log l'action
    error_log("Admin {$_SESSION['login']} a modifié l'utilisateur ID: $id");

    $pdo->commit();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => "✅ Utilisateur modifié avec succès"
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