<?php
// Informations de connexion MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'messaging');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Mets ton mot de passe ici si besoin

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
