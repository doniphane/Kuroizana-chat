<?php
require './includes/config.php';
require './includes/session.php';

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');
$file_path = null;
$file_type = null;

// Gestion du fichier si présent
if (!empty($_FILES['file']['name'])) {
    require 'upload.php'; // Fournit $file_path et $file_type
}

if ($receiver_id && ($message !== '' || $file_path)) {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content, file_path, file_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $message, $file_path, $file_type]);
    http_response_code(200);
    echo "Message envoyé";
} else {
    http_response_code(400);
    echo "Message vide ou destinataire manquant";
}
