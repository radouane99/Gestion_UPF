<?php
// includes/functions.php

/**
 * Génère un login à partir du nom et prénom
 */
function generateLogin($nom, $prenom) {
    // Nettoyer les caractères spéciaux
    $nom = strtolower(trim($nom));
    $prenom = strtolower(trim($prenom));
    
    // Supprimer les accents
    $nom = removeAccents($nom);
    $prenom = removeAccents($prenom);
    
    // Supprimer les caractères non alphabétiques
    $nom = preg_replace('/[^a-z]/', '', $nom);
    $prenom = preg_replace('/[^a-z]/', '', $prenom);
    
    return $prenom . '.' . $nom;
}

/**
 * Supprime les accents d'une chaîne
 */
function removeAccents($str) {
    $accents = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'ç' => 'c', 'ñ' => 'n'
    ];
    return strtr($str, $accents);
}

/**
 * Génère un mot de passe aléatoire
 */
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Formate une date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Calcule l'âge à partir d'une date de naissance
 */
function calculateAge($birthdate) {
    if (empty($birthdate)) return null;
    $today = new DateTime();
    $diff = $today->diff(new DateTime($birthdate));
    return $diff->y;
}

/**
 * Détermine la mention à partir d'une note
 */
function getMention($note) {
    if ($note === null) return ['mention' => 'Non évalué', 'class' => 'note-non-evalue'];
    if ($note >= 16) return ['mention' => 'Très Bien', 'class' => 'note-excellente'];
    if ($note >= 14) return ['mention' => 'Bien', 'class' => 'note-bien'];
    if ($note >= 12) return ['mention' => 'Assez Bien', 'class' => 'note-assez-bien'];
    if ($note >= 10) return ['mention' => 'Passable', 'class' => 'note-passable'];
    return ['mention' => 'Insuffisant', 'class' => 'note-insuffisant'];
}

/**
 * Détermine le statut à partir d'une note
 */
function getStatus($note) {
    if ($note === null) return ['status' => 'En attente', 'class' => 'status-attente'];
    if ($note >= 10) return ['status' => 'Reçu', 'class' => 'status-reçu'];
    return ['status' => 'Ajourné', 'class' => 'status-ajourné'];
}
?>