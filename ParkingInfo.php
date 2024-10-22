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
        @keyframes drive {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        body {
            font-family: 'Kanit', sans-serif;
            background-color: <?php echo $backgroundColor; ?>;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            transition: background-color 1s;
            overflow: hidden;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 40px;
            padding: 20px;
            width: 500px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        
            margin-bottom: 20px;
            transform: translateX(100px);
        }
        .card:hover {

            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.3),
                rgba(255, 255, 255, 0.1)
            );
            transform: rotate(45deg);
            pointer-events: none;
        }
        .card-header {
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            text-align: center;
            font-size: 1.4em;
            font-weight: bold;
            margin: -20px -20px 20px -20px;
            position: relative;
            overflow: hidden;
        }
        .card-header::after {
            content: '🚗';
            position: absolute;
            font-size: 2em;
            right: 20px;
            top: 20%;
            transform: translateY(100%);
            animation: drive 5s linear infinite;
        }
        .info-item {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.3em;
        }
        @keyframes redYellowOrangeGlow {
    0% { text-shadow: 0 0 10px red, 0 0 20px yellow, 0 0 30px orange; }
    50% { text-shadow: 0 0 10px yellow, 0 0 20px orange, 0 0 30px red; }
    100% { text-shadow: 0 0 10px orange, 0 0 20px red, 0 0 30px yellow; }
}

.info-item1 {
    margin-bottom: 15px;
    display: flex;
    justify-content: center; /* ปรับข้อความให้อยู่ตรงกลาง */
    align-items: center;
    font-size: 1.3em;
    color: red; /* ตัวหนังสือสีแดง */
    animation: redYellowOrangeGlow 2s infinite; /* เพิ่มเอฟเฟกต์ไฟแดง-เหลือง-ส้ม */
}


        .badge {
            background-color: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 1.2em;
            animation: float 3s ease-in-out infinite;

        }
        
        .progress-bar {
            background-color: #ecf0f1;
            border-radius: 13px;
            padding: 3px;
            margin-top: 10px;
        }
        .fuel-gauge {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: conic-gradient(
                #3498db 0deg <?php echo $carInfo['fuelLevel'] * 3.6; ?>deg,
                #ecf0f1 <?php echo $carInfo['fuelLevel'] * 3.6; ?>deg 360deg
            );
            margin: 20px auto;
            position: relative;
        }
        .fuel-gauge::before {
            content: '⛽';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2em;
        }
        .road {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background-color: #333;
            overflow: hidden;
            
        }
        .road::after {
            content: '🚗';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 4px;
            background: repeating-linear-gradient(
                to right,
                #fff,
                #fff 20px,
                transparent 20px,
                transparent 40px
                
            );
            
            animation: moveRoad 0.5s linear infinite;
        }
        .parking-info-card {
            background-color: rgba(255, 255, 255, 0.8);
    border-radius: 15px;
    padding: 20px;
    width: 600px; /* ขนาดกว้างสำหรับกรอบรวม */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    backdrop-filter: blur(10px);
    transform: translateX(200px); /* ขยับไปทางขวา */
    margin-bottom: 20px;
}

.parking-card-header {
    background-color: #3498db;
    color: white;
    padding: 15px;
    border-radius: 10px 10px 0 0;
    text-align: center;
    font-size: 1.4em;
    font-weight: bold;
    margin: -20px -20px 20px -20px;
    position: relative;
    overflow: hidden;
}

.info-item {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.2em;
}

.parking-info-group {
    margin-bottom: 20px;
}

.parking-info-group h3 {
    margin-top: 0;
    font-size: 1.2em;
    color: #3498db;
}

        .wheel {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid #333;
            border-radius: 50%;
            bottom: 10px;
            animation: rotate 2s linear infinite;
        }
        .wheel-left {
            left: 20px;
        }
        .wheel-right {
            right: 20px;
        }
        .sidebar {
        position: fixed;
        left: 0; /* ชิดซ้ายสุด */
        width: 250px; /* ปรับขนาดความกว้างของ sidebar ตามที่ต้องการ */
            background-color: #003366;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.7);
      }
      .confirm-button {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .confirm-button:hover {
            background-color: #2980b9;
        }
        .confirm-button1 {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left:120px ;
        }
        .confirm-button1:hover {
            background-color: #2980b9;
        }

        @keyframes moveRoad {
            0% { transform: translateX(0); }
            50% {content: '🚗';}
            100% { transform: translateX(-40px); }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }
          }
    </style>
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
    <!-- <button class="confirm-button">ยืนยัน</button> -->
</div>
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
    <div class="sidebar">
        <div class="logo">
            <h2>APS KU</h2>
        </div>
        <ul>
            <li><a href="ParkingInfo.php" class="menu-item active">Check ID card</a></li>
            <li><a href="accept_return.php" class="menu-item ">Update Status</a></li>
            <li><a href="moniter.php" class="menu-item" target="_blank">Monitor</a></li>
            <li><a href="index.php" class="menu-item">Sign out</a></li>
        </ul>
        <div class="version">
            <p>Version 2.0</p>
        </div>
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