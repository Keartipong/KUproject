<!-- <?php
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

    $status_check_sql = "SELECT COUNT(*) as total_lots, SUM(CASE WHEN status_id IN (6, 3, 7) THEN 1 ELSE 0 END) as full_lots FROM lot";
    $status_check_result = $conn->query($status_check_sql);

    if ($status_check_result === false) {
        $data = ['error' => 'Failed to check parking slot status'];
        $conn->close();
        exit;
    }

    $status_check_row = $status_check_result->fetch_assoc();

    if ($status_check_row['total_lots'] == $status_check_row['full_lots']) {
        $parking_slot = "All parking slots are full.";
        $bay_name = "N/A";
    } else {
        if ($distance > 190) {
            $lot_sql = "SELECT number, bay_id FROM lot WHERE parking_type_id = 1 AND status_id = 1";
        } else {
            $lot_sql = "SELECT number, bay_id FROM lot WHERE parking_type_id = 0 AND status_id = 1";
        }

        $lot_result = $conn->query($lot_sql);

        if ($lot_result->num_rows > 0) {
            $available_lots = [];
            while ($lot_row = $lot_result->fetch_assoc()) {
                $available_lots[] = $lot_row;
            }
            $random_lot = $available_lots[array_rand($available_lots)];
            $lot_number = $random_lot['number'];
            $bay_id = $random_lot['bay_id'];

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
    <title>APS KU</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Your existing styles */
    </style>
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
        <span>ระยะทาง:</span>
        <span id="distance"><?= htmlspecialchars($data['distance'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>ช่องจอด:</span>
        <span id="parking_slot"><?= htmlspecialchars($data['parking_slot'] ?? 'N/A') ?></span>
    </div>
    <div class="info-item">
        <span>โซน:</span>
        <span id="bay_name"><?= htmlspecialchars($data['bay_name'] ?? 'N/A') ?></span>
    </div>
    <button class="confirm-button">ยืนยัน</button>
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

</body>
</html> -->
