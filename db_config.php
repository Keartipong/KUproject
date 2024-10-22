<?php
// db_config.php
$host = '151.106.124.154';
$username = 'u583789277_wag7';
$password = '2567Concept';
$dbname = 'u583789277_wag7';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>