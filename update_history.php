<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_id = $_POST['card_id'];
    $lot_number = $_POST['lot_number'];
    $distance = $_POST['distance'];

    // ดึงข้อมูล lot_id จาก lot number
    $stmt = $conn->prepare("SELECT lot_id FROM lot WHERE number = ?");
    $stmt->bind_param("s", $lot_number);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Error executing lot query: ' . $stmt->error]);
        exit;
    }
    $lot_result = $stmt->get_result();

    if ($lot_result->num_rows > 0) {
        $lot_row = $lot_result->fetch_assoc();
        $lot_id = $lot_row['lot_id'];

        // ดึงข้อมูล license_plate จาก card
        $stmt = $conn->prepare("SELECT license_plate FROM card WHERE card_id = ?");
        $stmt->bind_param("i", $card_id);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Error executing card query: ' . $stmt->error]);
            exit;
        }
        $card_result = $stmt->get_result();

        if ($card_result->num_rows > 0) {
            $card_row = $card_result->fetch_assoc();
            $license_plate = $card_row['license_plate'];

            // Insert ลงในตาราง history
            $stmt = $conn->prepare("INSERT INTO history (his_id, card_id, height, license_plate, lot_id, time_in, time_out) VALUES (NULL, ?, ?, ?, ?, CURRENT_TIMESTAMP, NULL)");
            $stmt->bind_param("isss", $card_id, $distance, $license_plate, $lot_id);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Error inserting into history: ' . $stmt->error]);
                exit;
            }

            $data = ['status' => 'success'];
        } else {
            $data = ['error' => 'Card not found'];
        }
    } else {
        $data = ['error' => 'Lot number not found'];
    }

    echo json_encode($data);
    $conn->close();
    exit;
}
?>
