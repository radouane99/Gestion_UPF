<?php
// admin/utilisateurs/ajouter_traitement.php - CORRIGÉ
require_once '../../includes/auth_check_admin.php';
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ajouter.php');
    exit();
}

$pdo = getConnexion();

// Récupération des données
$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'user';
$etudiant_id = $_POST['etudiant_id'] ?? null;
$send_email = isset($_POST['send_email']);
$force_change = isset($_POST['force_password_change']);

// Si c'est un admin, on force etudiant_id à NULL
if ($role === 'admin') {
    $etudiant_id = null;
}

// Validation
$errors = [];

if (empty($login)) {
    $errors[] = "Le login est requis";
} elseif (strlen($login) < 3) {
    $errors[] = "Le login doit contenir au moins 3 caractères";
}

if (empty($password)) {
    $errors[] = "Le mot de passe est requis";
} elseif (strlen($password) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
}

if ($password !== $confirm_password) {
    $errors[] = "Les mots de passe ne correspondent pas";
}

if (!in_array($role, ['admin', 'user'])) {
    $errors[] = "Rôle invalide";
}

if ($role === 'user' && empty($etudiant_id)) {
    $errors[] = "Veuillez sélectionner un étudiant";
}

// Pour admin, on vérifie juste que c'est NULL (pas d'erreur)
// Pour user, on vérifie que l'étudiant existe
if ($role === 'user' && !empty($etudiant_id)) {
    $stmt = $pdo->prepare("SELECT Code FROM etudiants WHERE Code = ?");
    $stmt->execute([$etudiant_id]);
    if (!$stmt->fetch()) {
        $errors[] = "L'étudiant sélectionné n'existe pas";
    }
}

if (empty($errors)) {
    try {
        $pdo->beginTransaction();

        // Vérifier si login existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            throw new Exception("Ce login existe déjà");
        }

        // Si rôle user, vérifier que l'étudiant n'a pas déjà de compte
        if ($role === 'user' && !empty($etudiant_id)) {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE etudiant_id = ?");
            $stmt->execute([$etudiant_id]);
            if ($stmt->fetch()) {
                throw new Exception("Cet étudiant a déjà un compte utilisateur");
            }
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insérer l'utilisateur - IMPORTANT: Pour admin, etudiant_id est NULL
        $sql = "INSERT INTO utilisateurs (login, password, role, etudiant_id, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login, $hashedPassword, $role, $etudiant_id]);

        $userId = $pdo->lastInsertId();

        // Envoyer email si demandé (seulement pour les users)
        if ($send_email && $role === 'user' && !empty($etudiant_id)) {
            $stmt = $pdo->prepare("SELECT email, Prenom, Nom FROM etudiants WHERE Code = ?");
            $stmt->execute([$etudiant_id]);
            $etudiant = $stmt->fetch();
            
            if ($etudiant && !empty($etudiant['email'])) {
                $to = $etudiant['email'];
                $subject = "Vos identifiants de connexion - UPF Gestion";
                $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .header { background: linear-gradient(135deg, #294898, #C72C82); color: white; padding: 20px; }
                        .content { padding: 20px; }
                        .credentials { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h2>Université Privée de Fès</h2>
                    </div>
                    <div class='content'>
                        <h3>Bonjour " . htmlspecialchars($etudiant['Prenom'] . ' ' . $etudiant['Nom']) . ",</h3>
                        <p>Votre compte utilisateur a été créé sur l'application de gestion UPF.</p>
                        
                        <div class='credentials'>
                            <p><strong>Login :</strong> " . htmlspecialchars($login) . "</p>
                            <p><strong>Mot de passe :</strong> " . htmlspecialchars($password) . "</p>
                        </div>
                        
                        <p><strong>Lien de connexion :</strong> <a href='http://localhost/Gestion%20UPF/login.php'>Cliquez ici</a></p>
                        
                        <p style='color: orange;'><strong>⚠️ Important :</strong> Nous vous recommandons de changer votre mot de passe après votre première connexion.</p>
                        
                        <p>Cordialement,<br>L'équipe administrative</p>
                    </div>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: noreply@upf.ac.ma\r\n";

                mail($to, $subject, $message, $headers);
            }
        }

        $pdo->commit();

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => "✅ Utilisateur créé avec succès !"
        ];

        header('Location: liste.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => "❌ Erreur : " . $e->getMessage()
        ];
        header('Location: ajouter.php');
        exit();
    }
} else {
    $_SESSION['flash'] = [
        'type' => 'danger',
        'message' => "❌ " . implode("<br>", $errors)
    ];
    header('Location: ajouter.php');
    exit();
}
?>