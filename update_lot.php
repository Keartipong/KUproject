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

    // เรียกข้อมูล lot_id จาก lot number
    $stmt = $conn->prepare("SELECT lot_id FROM lot WHERE number = ?");
    $stmt->bind_param("s", $lot_number);
    $stmt->execute();
    $lot_result = $stmt->get_result();

    if ($lot_result->num_rows > 0) {
        $lot_row = $lot_result->fetch_assoc();
        $lot_id = $lot_row['lot_id'];

        // อัปเดต status_id ของ lot เป็น 6
        $stmt = $conn->prepare("UPDATE lot SET status_id = 6 WHERE number = ?");
        $stmt->bind_param("s", $lot_number);
        $stmt->execute();

        // อัปเดต lot_id ในตาราง card
        $stmt = $conn->prepare("UPDATE card SET lot_id = ? WHERE card_id = ?");
        $stmt->bind_param("ii", $lot_id, $card_id);
        $stmt->execute();

        // อัปเดต user_height ในตาราง card
        $stmt = $conn->prepare("UPDATE card SET user_height = ? WHERE card_id = ?");
        $stmt->bind_param("di", $distance, $card_id);
        $stmt->execute();

        // อัปเดต status_id ในตาราง card
        $stmt = $conn->prepare("UPDATE card SET status_id = 6 WHERE card_id = ?");
        $stmt->bind_param("i", $card_id);
        $stmt->execute();

        // ดึง user_license_plate จากตาราง card
        $stmt = $conn->prepare("SELECT user_license_plate FROM card WHERE card_id = ?");
        $stmt->bind_param("i", $card_id);
        $stmt->execute();
        $license_result = $stmt->get_result();

        if ($license_result->num_rows > 0) {
            $license_row = $license_result->fetch_assoc();
            $user_license_plate = $license_row['user_license_plate'];
        } else {
            $user_license_plate = null; // หรือกำหนดค่าเริ่มต้นถ้าหาไม่เจอ
        }

        // เพิ่มข้อมูลลงในตาราง update_history
        $current_time = date('Y-m-d H:i:s'); // เวลาในปัจจุบัน
        $stmt = $conn->prepare("INSERT INTO update_history (card_id, lot_id,distance, user_license_plate, time_in, time_out) VALUES (?, ?, ?, ?, ? , NULL)");
        $stmt->bind_param("iiiss", $card_id, $lot_id,$distance, $user_license_plate, $current_time);
        $stmt->execute();

        $data = ['status' => 'success'];
    } else {
        $data = ['error' => 'Lot number not found'];
    }

    echo json_encode($data);
    $conn->close();
    exit;
}
?>