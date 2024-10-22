<?php
session_start();

// Check if the user is logged in
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

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Car submission logic
$message = '';
$showModal = false;  // Modal only shows on true

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carId'])) {
    $carId = htmlspecialchars($_POST['carId']);
    $submittedCars = $_SESSION['submittedCars'] ?? [];
    $submittedCars[$carId] = true;
    $_SESSION['submittedCars'] = $submittedCars;

    // Confirm action (Yes button)
    if (isset($_POST['confirm'])) {
        $conn->begin_transaction();
        try {
            // Update card table
            $updateCardQuery = "UPDATE card SET user_height = NULL, lot_id = NULL, status_id = 1 WHERE card_id = ?";
            $stmt = $conn->prepare($updateCardQuery);
            $stmt->bind_param('s', $carId);
            $stmt->execute();

            // Find lot_id for the car
            $selectLotIdQuery = "SELECT lot_id FROM card WHERE card_id = ?";
            $stmt = $conn->prepare($selectLotIdQuery);
            $stmt->bind_param('s', $carId);
            $stmt->execute();
            $stmt->bind_result($lotId);
            $stmt->fetch();
            $stmt->close();

            // Update the lot if lot_id is found
            if ($lotId) {
                $updateLotQuery = "UPDATE lot SET status_id = 1 WHERE lot_id = ?";
                $stmt = $conn->prepare($updateLotQuery);
                $stmt->bind_param('s', $lotId);
                $stmt->execute();
            }

            // Update time_out in history table
            $current_time_out = date('Y-m-d H:i:s');
            $updateHistoryQuery = "UPDATE update_history SET time_out = ? WHERE card_id = ? AND time_out IS NULL";
            $stmt = $conn->prepare($updateHistoryQuery);
            $stmt->bind_param("ss", $current_time_out, $carId);
            $stmt->execute();

            $conn->commit();
            $message = 'การเรียกรถสำเร็จ 🚗';
           
            $showModal = true;  // Show success modal
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'เกิดข้อผิดพลาดในการเรียกรถ กรุณาลองใหม่';
            $showModal = true;  // Show error modal
        }
    }

    // Cancel action (No button)
    if (isset($_POST['cancel'])) {
        $message = 'ช่องจอดไม่ทำงานกรุณาติดต่อผู้ดูแล';
        $showModal = true;  // Show cancel modal
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการส่งข้อมูล</title>

    <!-- Custom Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* General page style */
        body {
            background: linear-gradient(45deg, #1b2735, #090a0f);
            color: #e0f7fa;
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background: #212a36;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 550px;
            width: 100%;
            animation: slideIn 0.8s ease-in-out;
        }

        .form-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 32px;
            color: #00e5ff;
            margin-bottom: 20px;
        }

        .car-icon {
            width: 90px;
            margin-bottom: 25px;
            animation: spinWheel 1.5s linear infinite;
        }

        .confirm-button, .cancel-button {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s;
        }

        .confirm-button {
            background: linear-gradient(90deg, #00e676, #00c853);
            color: white;
            border: none;
        }

        .cancel-button {
            background: linear-gradient(90deg, #ff5252, #e53935);
            color: white;
            border: none;
        }

        .confirm-button:hover, .cancel-button:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 255, 135, 0.4), 0 6px 20px rgba(255, 82, 82, 0.4);
        }

        @keyframes spinWheel {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .car-details {
            background: rgba(0, 0, 0, 0.4);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            color: #00e5ff;
        }

        .car-details p {
            font-size: 16px;
            margin: 8px 0;
        }

        .fa-car {
            color: #00e5ff;
            margin-right: 10px;
        }

        /* New Notification Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.85);
            padding-top: 60px;
            transition: opacity 0.5s ease-in-out;
        }

        .modal-content {
            background-color: #1c2938;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            width: 50%;
            max-width: 600px;
            text-align: center;
            color: #00e5ff;
            box-shadow: 0 15px 30px rgba(0, 255, 135, 0.5), 0 15px 30px rgba(0, 255, 200, 0.5);
            animation: modalFadeIn 0.8s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .close {
            color: #00e5ff;
            font-size: 28px;
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .close:hover {
            color: #e0f7fa;
        }

        .modal-icon {
            font-size: 48px;
            color: #00e5ff;
            margin-bottom: 20px;
            animation: iconPulse 1.5s infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .modal-message {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }

        .modal-buttons button {
            padding: 12px 25px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .modal-buttons .confirm {
            background-color: #00e676;
            color: white;
        }

        .modal-buttons .confirm:hover {
            background-color: #00c853;
        }

        .modal-buttons .cancel {
            background-color: #ff5252;
            color: white;
        }

        .modal-buttons .cancel:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <img src="https://cdn-icons-png.flaticon.com/512/54/54263.png" class="car-icon" alt="Car Icon">
        <h1 class="form-title">ยืนยันการเรียกรถ</h1>

        <div class="car-details">
            <p><i class="fas fa-car"></i><strong>Car ID:</strong> <?php echo htmlspecialchars($_POST['carId']); ?></p>
            <p><i class="fas fa-id-card"></i><strong>ป้ายทะเบียน:</strong> <?php echo htmlspecialchars($_POST['licensePlate']); ?></p>
            <p><i class="fas fa-arrows-alt-v"></i><strong>ความสูง:</strong> <?php echo htmlspecialchars($_POST['height']); ?> cm</p>
            <p><i class="fas fa-map-marker-alt"></i><strong>Bay:</strong> <?php echo htmlspecialchars($_POST['zone']); ?></p>
            <p><i class="fas fa-parking"></i><strong>ช่องจอด:</strong> <?php echo htmlspecialchars($_POST['parkingSlot']); ?></p>
        </div>

        <form method="POST">
            <input type="hidden" name="carId" value="<?php echo htmlspecialchars($_POST['carId']); ?>">
            <input type="hidden" name="licensePlate" value="<?php echo htmlspecialchars($_POST['licensePlate']); ?>">
            <input type="hidden" name="height" value="<?php echo htmlspecialchars($_POST['height']); ?>">
            <input type="hidden" name="zone" value="<?php echo htmlspecialchars($_POST['zone']); ?>">
            <input type="hidden" name="parkingSlot" value="<?php echo htmlspecialchars($_POST['parkingSlot']); ?>">

            <button type="submit" name="confirm" class="confirm-button">Yes, ทำงาน</button>
            <button type="submit" name="cancel" class="cancel-button">No, ไม่ทำงาน</button>
        </form>
    </div>

    <div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <i class="fas fa-check-circle modal-icon"></i>
        <p id="modalMessage" class="modal-message"></p>
        <div class="modal-buttons">
            <!-- Updated the onclick behavior to redirect to retrieve_parking.php -->
            <button class="confirm" onclick="window.location.href='retrieve_parking.php';">ตกลง</button>
        </div>
    </div>
</div>

    <script>
        // Modal handling logic
        const modal = document.getElementById("myModal");
        const span = document.getElementsByClassName("close")[0];

        // Show modal if necessary (PHP controlled)
        <?php if ($showModal): ?>
            document.getElementById("modalMessage").innerText = '<?php echo addslashes($message); ?>';
            modal.style.display = "block";
        <?php endif; ?>

        // Close modal on 'X' click
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Close modal if clicking outside the modal content
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
