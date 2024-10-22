<?php
// Database connection
$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to select the latest record from update_history and join with lot table
$sql = "SELECT uh.card_id, uh.lot_id, uh.user_license_plate, uh.time_out, l.number 
        FROM update_history uh 
        JOIN lot l ON uh.lot_id = l.lot_id 
        ORDER BY uh.time_out DESC LIMIT 1";

$result = $conn->query($sql);

// Prepare an array to hold the data
$data = [];
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Adjust time_out to current time + 7 hours
    if (isset($data['time_out'])) {
        $dateTime = new DateTime($data['time_out']);
        $dateTime->modify('+7 hours');
        $data['time_out'] = $dateTime->format('Y-m-d H:i:s'); // Format as 24-hour time
    }
} else {
    $data = null; // No data found
}

$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
