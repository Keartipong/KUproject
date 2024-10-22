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

function getLatestCardId($conn) {
    $sql_latest_card_id = "SELECT card_id FROM distance_data ORDER BY distance_id DESC LIMIT 1";
    $result_latest_card_id = $conn->query($sql_latest_card_id);

    if ($result_latest_card_id->num_rows > 0) {
        $row_latest_card_id = $result_latest_card_id->fetch_assoc();
        return $row_latest_card_id['card_id'];
    } else {
        return null;
    }
}

$card_id = getLatestCardId($conn);

if (!$card_id) {
    $data = ['error' => 'No data available'];
} else {
    $stmt = $conn->prepare("SELECT distance FROM distance_data WHERE card_id = ? ORDER BY distance_id DESC LIMIT 1");
    $stmt->bind_param("i", $card_id);
    $stmt->execute();
    $result_distance = $stmt->get_result();

    if ($result_distance->num_rows > 0) {
        $row_distance = $result_distance->fetch_assoc();
        $distance = $row_distance['distance'];
    } else {
        $data = ['error' => 'No distance data available'];
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("SELECT user_license_plate FROM card WHERE card_id = ?");
    $stmt->bind_param("i", $card_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_license_plate = $row['user_license_plate'];
    } else {
        $data = ['error' => 'No user found'];
        $conn->close();
        exit;
    }

    $status_check_sql = "SELECT COUNT(*) as total_lots, SUM(CASE WHEN status_id IN (6, 7, 3) THEN 1 ELSE 0 END) as full_lots FROM lot";
    $status_check_result = $conn->query($status_check_sql);

    if ($status_check_result === false) {
        $data = ['error' => 'Failed to check parking slot status'];
        $conn->close();
        exit;
    }

    $status_check_row = $status_check_result->fetch_assoc();
    $parked_zone = "N/A";
    if ($status_check_row['total_lots'] == $status_check_row['full_lots']) {
        $parking_slot = "All parking slots are full.";
        $bay_name = "N/A";
    } else {
       if ($status_check_row['total_lots'] == $status_check_row['full_lots']) {
    $parking_slot = "All parking slots are full.";
    $bay_name = "N/A";
} else {
        if ($distance > 190) {
            // สำหรับรถที่ความสูงเกิน 190
            $lot_sql = "SELECT number, bay_id FROM lot WHERE parking_type_id = 1 AND status_id = 1 LIMIT 1";
        } else {
            // สำหรับรถที่ความสูงไม่เกิน 190
            // ค้นหาช่องจอดใน bay ที่กำหนด โดยใช้การจัดเรียงลำดับที่คล้ายกับ JavaScript
            $lot_sql = "
                SELECT number, bay_id 
                FROM lot 
                WHERE status_id = 1 
                AND bay_id IN (1, 2, 3, 4, 5, 6, 7, 8)  -- ระบุ bay_id ที่ต้องการ
                ORDER BY 
                CASE 
                    WHEN number LIKE 'A1%' THEN 1
                    WHEN number LIKE 'B1%' THEN 2
                    WHEN number LIKE 'C1%' THEN 3
                    WHEN number LIKE 'D1%' THEN 4
                    WHEN number LIKE 'E1%' THEN 5
                    WHEN number LIKE 'F1%' THEN 6
                    WHEN number LIKE 'G1%' THEN 7
                    WHEN number LIKE 'H1%' THEN 8
                END,
                CAST(SUBSTRING(number, 2) AS UNSIGNED)  -- แยกตัวเลขจากตัวอักษรเพื่อเรียงลำดับ
                LIMIT 1";
        }

        // ดึงข้อมูลที่จอดจากฐานข้อมูล
        $result = $conn->query($lot_sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $parking_slot = $row['number'];
            $bay_name = $row['bay_id'];  // หรือคุณสามารถใช้ชื่อโซนที่เหมาะสม
        } else {
            $parking_slot = "No available parking slots.";
            $bay_name = "N/A";
        }
        // อัปเดตสถานะของช่องจอดรถ
        if ($parking_slot !== "No available parking slots.") {
            $stmt = $conn->prepare("SELECT lot_id FROM lot WHERE number = ?");
            $stmt->bind_param("s", $parking_slot);
            $stmt->execute();
            $lot_result = $stmt->get_result();

            if ($lot_result->num_rows > 0) {
                $lot_row = $lot_result->fetch_assoc();
                $lot_id = $lot_row['lot_id'];

                // อัปเดต status_id ของ lot เป็น 6
                $stmt = $conn->prepare("UPDATE lot SET status_id = 6 WHERE number = ?");
                $stmt->bind_param("s", $parking_slot);
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

                // เพิ่มข้อมูลลงในตาราง update_history
                $current_time = date('Y-m-d H:i:s'); // เวลาในปัจจุบัน
                $stmt = $conn->prepare("INSERT INTO update_history (card_id, lot_id, distance, user_license_plate, time_in, time_out) VALUES (?, ?, ?, ?, ?, NULL)");
                $stmt->bind_param("iiiss", $card_id, $lot_id, $distance, $user_license_plate, $current_time);
                $stmt->execute();
            }
        }
    

            // ค้นหาช่องจอดตามที่กำหนด
            $lot_result = $conn->query($lot_sql);
    
            // ตรวจสอบว่ามีช่องจอดในชั้นที่ 2 ถึง 7 หรือไม่
            if ($lot_result->num_rows == 0) {
                // ถ้าไม่มีให้ค้นหาช่องจอดในชั้นที่ 1
                $lot_sql = "SELECT number, bay_id FROM lot WHERE parking_type_id = 0 AND status_id = 1 AND number BETWEEN 'A101' AND 'H104' LIMIT 1"; 
                $lot_result = $conn->query($lot_sql);
            }
        }
    
        if ($lot_result->num_rows > 0) {
            $lot_row = $lot_result->fetch_assoc();
            $lot_number = $lot_row['number'];
            $bay_id = $lot_row['bay_id'];
    
            // ดึงชื่อโซนจากตาราง bay
            $bay_sql = "SELECT bay_name FROM bay WHERE bay_id = ?";
            $stmt = $conn->prepare($bay_sql);
            $stmt->bind_param("i", $bay_id);
            $stmt->execute();
            $bay_result = $stmt->get_result();
            
            if ($bay_result->num_rows > 0) {
                $bay_row = $bay_result->fetch_assoc();
                $bay_name = $bay_row['bay_name'];
            } else {
                $bay_name = "Unknown zone";
            }
            
            if (in_array($bay_name, ['A', 'B'])) { 
                $parked_zone = 1;
            } else if (in_array($bay_name, ['C', 'D', 'E', 'F', 'G', 'H'])) { 
                $parked_zone = 2;
            } else {
                $parked_zone = "Unknown";
            }            
            $parking_slot = "Parking slot: " . $lot_number;
        } else {
            $parking_slot = "No available parking slots.";
            $bay_name = "N/A";
        }
    }
    

    $data = [
        'card_id' => $card_id,
        'distance' => $distance,
        'user_license_plate' => $user_license_plate,
        'parked_zone' => $parked_zone,
        'parking_slot' => $parking_slot,
        'bay_name' => $bay_name,
        'last_update' => time()
    ];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS KU </title>
    <link rel="stylesheet" href="styles.css">
    <style>

    </style>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS KU</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="card">
    <div class="card-header">ข้อมูลรถ</div>
    <div class="info-item">
        <span>การ์ดไอดี:</span>
        <span id="card_id"><?= htmlspecialchars($data['card_id'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>ป้ายทะเบียน:</span>
        <span id="user_license_plate"><?= htmlspecialchars($data['user_license_plate'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>ความสูง:</span>
        <span id="distance"><?= htmlspecialchars($data['distance'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>Zone:</span>
        <span id="parked_zone"><?= htmlspecialchars($data['parked_zone'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>Bay:</span>
        <span id="bay_name"><?= htmlspecialchars($data['bay_name'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>ช่องจอด:</span>
        <span id="parking_slot"><?= htmlspecialchars($data['parking_slot'] ?? 'N/A') ?></span>
    </div>
    <button class="confirm-button">ยืนยัน</button>
</div>
<div class="road">
    <div class="wheel wheel-left"></div>
    <div class="wheel wheel-right"></div>
</div>

    </div>
    <div class="container">
    <div class="parking-info-card">
        <div class="parking-card-header">
            ข้อมูลช่องจอด
        </div>
        <div class="info-item">
            <div class="parking-info-group">
                <h3>ช่องจอดความสูงเกิน</h3>
                <div class="info-item">
                <span>สถานะ:</span>
                <span id="over-height-status"></span>
                    
                </div>
            </div>
            <div class="parking-info-group">
                <h3>ช่องจอดความสูงปกติ</h3>
                <div class="info-item">
                <span>สถานะ:</span>
                <span id="normal-status"></span>
                    
                </div>
            </div>
                </div>
                <div class="parking-info-group">
                <div class="info-item1">
                <div>
            <span id="message"></span>
            </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

</div>
    <div class="road">
        <div class="wheel wheel-left"></div>
        <div class="wheel wheel-right"></div>
    </div>
    
 <script>
const ws = new WebSocket('ws://localhost:8080');

ws.onopen = function() {
    console.log('WebSocket connection established');
};

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);

    document.getElementById('card_id').textContent = data.card_id || 'N/A';
    document.getElementById('distance').textContent = data.distance || 'N/A';
    document.getElementById('user_license_plate').textContent = data.user_license_plate || 'N/A';
    document.getElementById('parked_zone').textContent = data.parked_zone || 'N/A';
    document.getElementById('parking_slot').textContent = data.parking_slot || 'N/A';
    document.getElementById('bay_name').textContent = data.bay_name || 'N/A';
};

ws.onerror = function(error) {
    console.error('WebSocket error:', error);
};

ws.onclose = function() {
    console.log('WebSocket connection closed');
};

// Handle the confirm button click event
document.querySelector('.confirm-button').addEventListener('click', function() {
    const card_id = document.getElementById('card_id').textContent;
    const parking_slot = document.getElementById('parking_slot').textContent;

    // Extract lot number from parking_slot (e.g., E404)
    const lot_number = parking_slot.split(': ')[1];

    // Get distance value
    const distance = document.getElementById('distance').textContent;

    // Send data to the server
    fetch('update_lot.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'card_id': card_id,
            'lot_number': lot_number,
            'distance': distance
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Update successful');
        } else {
            alert('Update failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateStatus() {
            $.ajax({
                url: 'checktest.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#over-height-status').text(data.over_height_status);
                    $('#normal-status').text(data.normal_status);
                    $('#message').text(data.message);
                },
                error: function() {
                    $('#over-height-status').text('Error retrieving status.');
                    $('#normal-status').text('Error retrieving status.');
                    $('#message').text('Error retrieving status.');
                }
            });
        }

        // เรียกใช้ฟังก์ชัน updateStatus ทุก 5 วินาที
        setInterval(updateStatus, 5000);

        // เรียกใช้ updateStatus ครั้งแรกเมื่อโหลดหน้า
        $(document).ready(function() {
            updateStatus();
        });
    </script>
</script>
    
</body>
</html>