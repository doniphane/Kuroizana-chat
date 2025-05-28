<?php
require './includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Met à jour le timestamp de l'utilisateur actuel
$pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")
    ->execute([$currentUserId]);

// Sélectionne les utilisateurs en ligne (dernière activité < 2 min)
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ? AND last_seen >= NOW() - INTERVAL 2 MINUTE");
$stmt->execute([$currentUserId]);

$onlineUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retourne le tout au format JSON
header('Content-Type: application/json');
echo json_encode($onlineUsers);
