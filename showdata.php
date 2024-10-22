<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Database configuration
$host = '151.106.124.154'; // เปลี่ยนเป็นค่าจริงถ้าจำเป็น
$dbname = 'u583789277_wag7';
$username = 'u583789277_wag7';
$password = '2567Concept';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Retrieve the latest card ID where status_id is 6
$sql = "
 SELECT 
    dd.card_id, 
    dd.distance, 
    dd.timestamp, 
    c.user_license_plate
FROM 
    distance_data dd
INNER JOIN 
    card c ON dd.card_id = c.card_id  -- JOIN ตาราง card กับ distance_data โดยใช้ card_id
ORDER BY 
    dd.timestamp DESC
LIMIT 1;
";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute();
    $carData = $stmt->fetch(PDO::FETCH_ASSOC);

    // เพิ่มเวลา 7 ชั่วโมง
    if ($carData && isset($carData['timestamp'])) {
        $dateTime = new DateTime($carData['timestamp']);
        $dateTime->modify('+7 hours');
        $carData['timestamp'] = $dateTime->format('Y-m-d H:i:s'); // แปลงกลับเป็นรูปแบบ timestamp
    }
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// If no data is found, set default values
if (!$carData) {
    $carData = [
        'card_id' => 'Not Found',
        'user_license_plate' => 'Not Found',
        'distance' => 'Not Found',
        'timestamp' => 'Not Found'
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($carData);