<?php
session_start();

$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch card data
function fetchCardData($conn, $card_id) {
    $stmt = $conn->prepare("SELECT card_id, user_license_plate, user_height AS distance FROM card WHERE card_id = ?");
    $stmt->bind_param("i", $card_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get available parking slot
function getParkingSlot($conn, $distance) {
    if ($distance > 190) {
        // For cars taller than 190 cm
        $lot_sql = "SELECT number, bay_id FROM lot WHERE parking_type_id = 1 AND status_id = 1 LIMIT 1";
    } else {
        // For cars shorter than or equal to 190 cm
        $lot_sql = "SELECT number, bay_id FROM lot WHERE status_id = 1 AND bay_id IN (1, 2, 3, 4, 5, 6, 7, 8)
                    ORDER BY CASE 
                        WHEN number LIKE 'A1%' THEN 1
                        WHEN number LIKE 'B1%' THEN 2
                        WHEN number LIKE 'C1%' THEN 3
                        WHEN number LIKE 'D1%' THEN 4
                        WHEN number LIKE 'E1%' THEN 5
                        WHEN number LIKE 'F1%' THEN 6
                        WHEN number LIKE 'G1%' THEN 7
                        WHEN number LIKE 'H1%' THEN 8
                    END,
                    CAST(SUBSTRING(number, 2) AS UNSIGNED) LIMIT 1";
    }
    return $conn->query($lot_sql);
}

// Handle AJAX request to fetch card data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_id'])) {
    $card_id = $_POST['card_id'];
    $result = fetchCardData($conn, $card_id);

    if ($result->num_rows > 0) {
        $card_data = $result->fetch_assoc();
        $distance = $card_data['distance'];

        $lot_result = getParkingSlot($conn, $distance);

        if ($lot_result->num_rows > 0) {
            $lot_row = $lot_result->fetch_assoc();
            $parking_slot = $lot_row['number'];
            $bay_id = $lot_row['bay_id'];

            // Fetch bay name
            $bay_stmt = $conn->prepare("SELECT bay_name FROM bay WHERE bay_id = ?");
            $bay_stmt->bind_param("i", $bay_id);
            $bay_stmt->execute();
            $bay_result = $bay_stmt->get_result();
            $bay_name = $bay_result->num_rows > 0 ? $bay_result->fetch_assoc()['bay_name'] : "Unknown Bay";

            echo json_encode([
                'status' => 'success',
                'card_id' => $card_data['card_id'],
                'distance' => $card_data['distance'],
                'user_license_plate' => $card_data['user_license_plate'],
                'parking_slot' => $parking_slot,
                'bay_name' => $bay_name
            ]);
        } else {
            echo json_encode(['status' => 'error', 'error' => 'No available parking slots.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'error' => 'ไม่พบข้อมูล Card ID']);
    }
    $conn->close();
    exit;
}

// Handle confirmation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $card_id = $_POST['card_id_confirm'];
    $lot_number = $_POST['lot_number_confirm'];
    $distance = $_POST['distance_confirm'];

    $stmt = $conn->prepare("SELECT lot_id, bay_id FROM lot WHERE number = ?");
    $stmt->bind_param("s", $lot_number);
    $stmt->execute();
    $lot_result = $stmt->get_result();

    if ($lot_result->num_rows > 0) {
        $lot_row = $lot_result->fetch_assoc();
        $lot_id = $lot_row['lot_id'];

        // Update lot status and card details
        $stmt = $conn->prepare("UPDATE lot SET status_id = 6 WHERE number = ?");
        $stmt->bind_param("s", $lot_number);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE card SET lot_id = ?, user_height = ?, status_id = 6 WHERE card_id = ?");
        $stmt->bind_param("idi", $lot_id, $distance, $card_id);
        $stmt->execute();

        // Log update history
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO update_history (card_id, lot_id, distance, user_license_plate, time_in) VALUES (?, ?, ?, ?, ?)");
        $user_license_plate = $conn->query("SELECT user_license_plate FROM card WHERE card_id = $card_id")->fetch_assoc()['user_license_plate'];
        $stmt->bind_param("iiiss", $card_id, $lot_id, $distance, $user_license_plate, $current_time);
        $stmt->execute();

        $_SESSION['card_data'] = [
            'card_id' => $card_id,
            'distance' => $distance,
            'user_license_plate' => $user_license_plate,
            'parking_slot' => $lot_number
        ];

        echo json_encode(['status' => 'success', 'redirect' => 'parking_again.php']);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Lot number not found']);
    }

    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาข้อมูล Card</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@300;400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-image: url('https://cdn.pixabay.com/photo/2024/06/03/17/13/ai-generated-8806905_960_720.jpg');
            background-size: auto; /* Ensures the image is fully visible without cropping */
            background-position: center;
            background-repeat: no-repeat;
            background-color: wheat; /* Adds a fallback background color */
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card-search-container {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 100%;
            max-width: 500px;
            transition: transform 0.3s ease-in-out;
        }

        .card-search-container:hover {
            transform: scale(1.05);
        }

        h1 {
            font-family: 'Teko', sans-serif;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1.5rem;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #333;
            border-radius: 8px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #e63946;
            outline: none;
            background-color: #f1f1f1;
        }

        button {
            background-color: #e63946;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        button:hover {
            background-color: #d62828;
            transform: scale(1.1);
        }

        button:active {
            transform: scale(1);
        }

        .result-card {
            background-color: #1d3557;
            color: #fff;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 1rem;
            display: inline-block;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-10px);
        }

        .result-card p {
            font-size: 1.1rem;
        }

        .result-card p strong {
            font-family: 'Teko', sans-serif;
            font-size: 1.5rem;
        }

        .text-red-500 {
            color: #e63946;
        }

        /* Icon styles */
        .icon {
            font-size: 1.5rem;
            margin-right: 10px;
            vertical-align: middle;
        }
        /* Add this inside your existing <style> tag */
        .notification {
            background-color: rgba(255, 255, 255, 0.9);
            color: #2ecc71; /* Green color */
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            font-size: 1.2rem;
            text-align: center;
            transition: opacity 0.5s ease;
        }

    </style>
    <script>
        async function fetchCardData() {
            const cardId = document.getElementById('card_id').value;
            if (!cardId) {
                alert("กรุณากรอก Card ID");
                return;
            }

            const formData = new FormData();
            formData.append('card_id', cardId);

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const data = await response.json();
                let resultDiv = document.getElementById('result');
                if (data.status === 'success') {
                    resultDiv.innerHTML = `
                        <div class="result-card">
                            <p><span class="icon">🚘</span><strong>Card ID:</strong> ${data.card_id}</p>
                            <p><span class="icon">📏</span><strong>Distance:</strong> ${data.distance} ซม.</p>
                            <p><span class="icon">🔖</span><strong>License Plate:</strong> ${data.user_license_plate}</p>
                            <p><span class="icon">🅿️</span><strong>Parking Slot:</strong> ${data.parking_slot}</p>
                            <p><span class="icon">📍</span><strong>Bay Name:</strong> ${data.bay_name}</p>
                            <button id="confirmButton" class="bg-green-500 text-white rounded px-4 py-2 mt-4" onclick="confirmParking('${data.card_id}', '${data.parking_slot}', '${data.distance}')">ยืนยันการจอดรถ</button>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function confirmParking(cardId, parkingSlot, distance) {
            const formData = new FormData();
            formData.append('confirm', 'true');
            formData.append('card_id_confirm', cardId);
            formData.append('lot_number_confirm', parkingSlot);
            formData.append('distance_confirm', distance);

            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.status === 'success') {
                    showMessage('การเรียกรถสำเร็จ 🚗'); // แสดงข้อความ
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000); // ปิดข้อความและเปลี่ยนหน้าไปยัง redirect
                } else {
                    alert(data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

            // Add this inside your existing <script> tag
        function showMessage(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerText = message;

            // Append notification to the body
            document.body.appendChild(notification);
            
            // Center the notification
            notification.style.display = 'block'; // Show the notification
            notification.style.position = 'fixed';
            notification.style.top = '50%';
            notification.style.left = '50%';
            notification.style.transform = 'translate(-50%, -50%)';
            notification.style.zIndex = '1000'; // Ensure it is above other elements

            // Set timeout to hide the notification
            setTimeout(() => {
                notification.style.display = 'none'; // Hide the notification
                document.body.removeChild(notification); // Remove from DOM
            }, 5000); // Display duration
        }

    </script>
</head>
<body>
    <div class="card-search-container">
        <h1>ค้นหาข้อมูล Card 🚗</h1>
        <input type="text" id="card_id" placeholder="กรุณากรอก Card ID" class="border rounded px-4 py-2" />
        <button onclick="fetchCardData()" class="bg-blue-500 text-white rounded px-4 py-2 mt-3">ค้นหา</button>
        <div id="result" class="mt-5"></div>
    </div>
</body>
</html>