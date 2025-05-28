<?php
/**
 * Fonctions communes pour l'application de chat
 */

/**
 * Génère un avatar par défaut basé sur la première lettre du nom d'utilisateur
 */
function getDefaultAvatar($username)
{
    $letter = strtoupper(substr($username, 0, 1));
    $colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57', '#ff9ff3', '#54a0ff'];
    $color = $colors[ord($letter) % count($colors)];
    return [
        'letter' => $letter,
        'color' => $color
    ];
}

/**
 * Formate une date pour l'affichage
 */
function formatDate($date, $format = 'H:i')
{
    return date($format, strtotime($date));
}

/**
 * Sécurise une chaîne pour l'affichage HTML
 */
function secure($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Génère un nom de fichier unique pour les uploads
 */
function generateUniqueFilename($originalName, $prefix = '')
{
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return $prefix . uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Vérifie si un fichier est une image valide
 */
function isValidImage($filePath)
{
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    return in_array($mimeType, $allowedTypes) && getimagesize($filePath) !== false;
}

/**
 * Convertit la taille d'un fichier en format lisible
 */
function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
}
?>