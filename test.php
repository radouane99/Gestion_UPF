<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "auth_check_user.php : ";
if (file_exists(__DIR__ . '/../includes/auth_check_user.php')) {  // ← chemin absolu
    echo "✅ Fichier OK<br>";
} else {
    echo "❌ Fichier manquant<br>";
    echo "Chemin cherché : " . __DIR__ . '/../includes/auth_check_user.php';
}   