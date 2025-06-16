<?php
$host = 'localhost';
$db   = 'modern_pos'; // Is your database name EXACTLY 'modern_pos'?
$user = 'root';     // This is the default WAMP username.
$pass = '';         // This is the default WAMP password (empty).
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // This will stop the script if the connection fails.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>