<?php
$maxImageSize = 3 * 1024 * 1024; // 3 Mo
$maxVideoSize = 100 * 1024 * 1024; // 100 Mo

$uploadDir = 'assets/uploads/';
if (!is_dir($uploadDir))
    mkdir($uploadDir, 0777, true);

$type = mime_content_type($_FILES['file']['tmp_name']);
$filename = uniqid() . "_" . basename($_FILES['file']['name']);
$targetPath = $uploadDir . $filename;

if (str_starts_with($type, 'image/')) {
    if ($_FILES['file']['size'] > $maxImageSize) {
        exit('Image trop grande');
    }
    $file_type = 'image';
} elseif (str_starts_with($type, 'video/')) {
    if ($_FILES['file']['size'] > $maxVideoSize) {
        exit('Vidéo trop grande');
    }
    $file_type = 'video';
} else {
    exit('Type de fichier non supporté');
}

if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    $file_path = $targetPath;
} else {
    exit('Erreur lors de l’envoi du fichier');
}
