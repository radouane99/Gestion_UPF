<?php
// admin/etudiants/supprimer_traitement.php
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

    // Récupérer toutes les infos de l'étudiant
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE Code = ?");
    $stmt->execute([$code]);
    $etudiant = $stmt->fetch();

    if (!$etudiant) {
        throw new Exception("Étudiant introuvable");
    }

    // 1. Récupérer tous les documents pour suppression physique
    $stmt = $pdo->prepare("SELECT chemin FROM documents WHERE etudiant_id = ?");
    $stmt->execute([$code]);
    $documents = $stmt->fetchAll();

    // 2. Supprimer les fichiers physiques (documents)
    foreach ($documents as $doc) {
        $filepath = '../../' . $doc['chemin'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // 3. Supprimer les documents de la BDD
    $stmt = $pdo->prepare("DELETE FROM documents WHERE etudiant_id = ?");
    $stmt->execute([$code]);

    // 4. Supprimer le compte utilisateur s'il existe
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE etudiant_id = ?");
    $stmt->execute([$code]);

    // 5. Supprimer la photo physique
    if (!empty($etudiant['Photo'])) {
        $photopath = '../../' . $etudiant['Photo'];
        if (file_exists($photopath)) {
            unlink($photopath);
        }
    }

    // 6. Supprimer l'étudiant
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE Code = ?");
    $stmt->execute([$code]);

    $pdo->commit();

    // Log
    error_log("Admin {$_SESSION['login']} a supprimé l'étudiant {$etudiant['Prenom']} {$etudiant['Nom']} ($code)");

    $_SESSION['flash'] = [  
        'type' => 'success',
        'message' => "✅ Étudiant supprimé avec succès"
    ];

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ Erreur lors de la suppression : " . $e->getMessage()
    ];
}

header('Location: liste.php');
exit();
?>