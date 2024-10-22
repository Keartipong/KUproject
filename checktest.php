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

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบสถานะของช่องจอด over_height (parking_type_id = 1)
$over_height_status_sql = "SELECT COUNT(*) as total_lots, SUM(CASE WHEN status_id IN (6, 7, 3) THEN 1 ELSE 0 END) as full_lots FROM lot WHERE parking_type_id = 1";
$over_height_status_result = $conn->query($over_height_status_sql);
$over_height_status_row = $over_height_status_result->fetch_assoc();

if ($over_height_status_row['total_lots'] == $over_height_status_row['full_lots']) {
    $over_height_status = "เต็ม";
} else {
    $over_height_status = "ว่าง";
}

// ตรวจสอบสถานะของช่องจอด normal (parking_type_id = 0)
$normal_status_sql = "SELECT COUNT(*) as total_lots, SUM(CASE WHEN status_id IN (6, 7, 3) THEN 1 ELSE 0 END) as full_lots FROM lot WHERE parking_type_id = 0";
$normal_status_result = $conn->query($normal_status_sql);
$normal_status_row = $normal_status_result->fetch_assoc();

if ($normal_status_row['total_lots'] == $normal_status_row['full_lots']) {
    $normal_status = "เต็ม";
} else {
    $normal_status = "ว่าง";
}

$message = null; // เพิ่มตัวแปรสำหรับข้อความแจ้งเตือน

// ตรวจสอบว่าทั้งสองสถานะเป็น "เต็ม"
if ($over_height_status === "เต็ม" && $normal_status === "เต็ม") {
    $message = "ช่องจอดเต็มกรุณาจอดแบบลาน!!!"; // ข้อความแจ้งเตือน
}

$conn->close();

// ส่งข้อมูลเป็น JSON
echo json_encode([
    'over_height_status' => $over_height_status,
    'normal_status' => $normal_status,
    'message' => $message // เพิ่มการส่งข้อความแจ้งเตือน
]);
?>
