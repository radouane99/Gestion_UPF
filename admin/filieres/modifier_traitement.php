<?php
// admin/filieres/modifier_traitement.php
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: liste.php');
    exit();
}

$pdo = getConnexion();

$original_code = $_POST['original_code'] ?? '';
$intitule = trim($_POST['intitule'] ?? '');
$responsable = trim($_POST['responsable'] ?? '');
$nbPlaces = !empty($_POST['nbPlaces']) ? intval($_POST['nbPlaces']) : null;

$errors = [];

if (empty($original_code)) {
    $errors[] = "Code original manquant";
}

if (empty($intitule)) {
    $errors[] = "L'intitulé est requis";
}

if ($nbPlaces !== null && ($nbPlaces < 1 || $nbPlaces > 500)) {
    $errors[] = "Le nombre de places doit être entre 1 et 500";
}

if (empty($errors)) {
    try {
        $pdo->beginTransaction();

        // Vérifier que la filière existe
        $stmt = $pdo->prepare("SELECT * FROM filieres WHERE CodeF = ?");
        $stmt->execute([$original_code]);
        if (!$stmt->fetch()) {
            throw new Exception("Filière introuvable");
        }

        // Mettre à jour
        $sql = "UPDATE filieres SET IntituleF = ?, responsable = ?, nbPlaces = ? WHERE CodeF = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$intitule, $responsable, $nbPlaces, $original_code]);

        $pdo->commit();

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Filière modifiée avec succès !"
        ];

        header('Location: liste.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur : " . $e->getMessage()
        ];
        header('Location: modifier.php?code=' . $original_code);
        exit();
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
    header('Location: modifier.php?code=' . $original_code);
    exit();
}
?>