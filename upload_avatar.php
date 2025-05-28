<?php
require './includes/config.php';
require './includes/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur d\'upload']);
    exit;
}

$file = $_FILES['avatar'];

// Vérifications de sécurité
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (max 2MB)']);
    exit;
}

// Vérifier que c'est vraiment une image
$imageInfo = getimagesize($file['tmp_name']);
if ($imageInfo === false) {
    echo json_encode(['success' => false, 'message' => 'Le fichier n\'est pas une image valide']);
    exit;
}

// Créer le dossier uploads s'il n'existe pas
$uploadDir = 'uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Générer un nom de fichier unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Déplacer le fichier uploadé
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier']);
    exit;
}

// Redimensionner l'image (optionnel)
try {
    $resizedPath = resizeImage($filepath, 200, 200);
    if ($resizedPath && $resizedPath !== $filepath) {
        unlink($filepath); // Supprimer l'original
        $filepath = $resizedPath;
    }
} catch (Exception $e) {
    // Si le redimensionnement échoue, on garde l'image originale
}

// Supprimer l'ancien avatar s'il existe
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$oldAvatar = $stmt->fetchColumn();

if ($oldAvatar && file_exists($oldAvatar)) {
    unlink($oldAvatar);
}

// Mettre à jour la base de données
try {
    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->execute([$filepath, $user_id]);

    echo json_encode(['success' => true, 'message' => 'Avatar mis à jour avec succès', 'avatar_url' => $filepath]);
} catch (PDOException $e) {
    // Supprimer le fichier en cas d'erreur de base de données
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}

/**
 * Redimensionner une image
 */
function resizeImage($sourcePath, $maxWidth, $maxHeight)
{
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo)
        return false;

    list($originalWidth, $originalHeight, $imageType) = $imageInfo;

    // Calculer les nouvelles dimensions en gardant le ratio
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $newWidth = round($originalWidth * $ratio);
    $newHeight = round($originalHeight * $ratio);

    // Créer l'image source
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    if (!$sourceImage)
        return false;

    // Créer l'image de destination
    $destImage = imagecreatetruecolor($newWidth, $newHeight);

    // Préserver la transparence pour PNG et GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
        $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
        imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Redimensionner
    imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    // Sauvegarder l'image redimensionnée
    $pathInfo = pathinfo($sourcePath);
    $resizedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_resized.' . $pathInfo['extension'];

    $success = false;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($destImage, $resizedPath, 90);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($destImage, $resizedPath, 9);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($destImage, $resizedPath);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($destImage, $resizedPath, 90);
            break;
    }

    // Nettoyer la mémoire
    imagedestroy($sourceImage);
    imagedestroy($destImage);

    return $success ? $resizedPath : false;
}
?>