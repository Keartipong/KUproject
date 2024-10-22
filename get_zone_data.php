<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Database connection
$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if zone_id is set in the request
if (!isset($_GET['zone_id'])) {
    echo json_encode([
        'zone' => 0,
        'slots' => []
    ]);
    exit;
}

// Get zone ID from request
$zoneId = intval($_GET['zone_id']);

// Map bay_id values to zones
$zoneMap = [
    1 => 'A',
    2 => 'B',
    3 => 'C',
    4 => 'D',
    5 => 'E',
    6 => 'F',
    7 => 'G',
    8 => 'H'
];

// Check if the given zoneId is valid
if (!array_key_exists($zoneId, $zoneMap)) {
    echo json_encode([
        'zone' => 'Invalid Zone',
        'slots' => []
    ]);
    exit;
}

// Fetch parking data for the specified zone
$sql = "SELECT number, status_id FROM lot WHERE bay_id = $zoneId";
$result = $conn->query($sql);

if (!$result) {
    die("Error retrieving data: " . $conn->error);
}

// Function to get status class and status text in Thai
function getStatusClassAndText($status_id) {
    switch ($status_id) {
        case 1: return ['class' => 'bg-blue-500', 'text' => '🚗ว่าง']; // Empty
        case 6: return ['class' => 'bg-yellow-400 text-black', 'text' => '🛑จอง']; // Reserved
        case 7: return ['class' => 'bg-green-500', 'text' => '🅿️จอด']; // Parked
        case 3: return ['class' => 'bg-red-500', 'text' => '⚠️พัง']; // Broken
        default: return ['class' => 'bg-gray-500', 'text' => 'ไม่ทราบ']; // Unknown
    }
}

// Prepare data
$slots = [];
while ($row = $result->fetch_assoc()) {
    $status = getStatusClassAndText($row['status_id']);
    $slots[] = [
        'number' => $row['number'],
        'class' => $status['class'],
        'status' => $status['text']
    ];
}

// Return JSON data
echo json_encode([
    'zone' => $zoneMap[$zoneId],
    'slots' => $slots
]);

$conn->close();
?>
