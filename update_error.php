<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Connect to MySQL database
$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$redirectToTest = false;

// Car submission logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carId'])) {
    $carId = $_POST['carId'];
    $submittedCars = isset($_SESSION['submittedCars']) ? $_SESSION['submittedCars'] : [];
    $submittedCars[$carId] = true;
    $_SESSION['submittedCars'] = $submittedCars;

    // If "Yes" button is pressed
    if (isset($_POST['confirm'])) {
        $conn->begin_transaction();
        try {
            $updateCardQuery = "UPDATE card SET status_id = 7 WHERE card_id = ?";
            $stmt = $conn->prepare($updateCardQuery);
            $stmt->bind_param('s', $carId);
            $stmt->execute();

            $selectLotIdQuery = "SELECT lot_id FROM card WHERE card_id = ?";
            $stmt = $conn->prepare($selectLotIdQuery);
            $stmt->bind_param('s', $carId);
            $stmt->execute();
            $stmt->bind_result($lotId);
            $stmt->fetch();
            $stmt->close();

            if ($lotId) {
                $updateLotQuery = "UPDATE lot SET status_id = 7 WHERE lot_id = ?";
                $stmt = $conn->prepare($updateLotQuery);
                $stmt->bind_param('s', $lotId);
                $stmt->execute();
            }

            $conn->commit();
            $message = 'จอดรถเรียบร้อยแล้ว';
            $redirectToTest = true;
        } catch (Exception $e) {
            $conn->rollback();
        }
    }

    // If "No" button is pressed
    if (isset($_POST['cancel'])) {
        $conn->begin_transaction();
        try {
            $updateCardQuery = "UPDATE card SET status_id = 1 WHERE card_id = ?";
            $stmt = $conn->prepare($updateCardQuery);
            $stmt->bind_param('s', $carId);
            $stmt->execute();

            $selectLotIdQuery = "SELECT lot_id FROM card WHERE card_id = ?";
            $stmt = $conn->prepare($selectLotIdQuery);
            $stmt->bind_param('s', $carId);
            $stmt->execute();
            $stmt->bind_result($lotId);
            $stmt->fetch();
            $stmt->close();

            if ($lotId) {
                $updateLotQuery = "UPDATE lot SET status_id = 3 WHERE lot_id = ?";
                $stmt = $conn->prepare($updateLotQuery);
                $stmt->bind_param('s', $lotId);
                $stmt->execute();
            }

            $conn->commit();
            $message = 'ช่องจอดไม่สามารถใช้งานได้';
            $redirectToTest = true;
        } catch (Exception $e) {
            $conn->rollback();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการส่งข้อมูล</title>
    
    <!-- Car-themed fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS for fast, modern design -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Custom styles for car-themed look */
        body {
            background: linear-gradient(135deg, #2b2e4a, #1f2235);
            color: #f5f5f5;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: slideIn 0.5s ease-in-out;
        }

        .form-title {
            font-family: 'Anton', sans-serif;
            font-size: 32px;
            margin-bottom: 20px;
            color: #ff6f61;
        }

        .car-icon {
            width: 70px;
            height: auto;
            margin-bottom: 20px;
            animation: bounce 1s infinite;
        }

        .confirm-button, .cancel-button {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            font-size: 18px;
            cursor: pointer;
            border-radius: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s;
        }

        .confirm-button {
            background: #4CAF50;
        }

        .cancel-button {
            background: #f44336;
        }

        .confirm-button:hover, .cancel-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .info-text {
            color: #ff6f61;
            text-shadow: 0 0 5px #ffb3a7;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($redirectToTest): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold"><?php echo $message; ?></strong>
                <div class="mt-4">
                    <a href="update.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">ตกลง</a>
                </div>
            </div>
        <?php else: ?>
            <img src="https://cdn-icons-png.flaticon.com/512/54/54263.png" class="car-icon" alt="Car Icon">
            <h1 class="form-title info-text">ยืนยันการจอดรถ 🚗</h1>
            <p><strong>Car ID:</strong> <?php echo htmlspecialchars($_POST['carId']); ?></p>
            <p><strong>ป้ายทะเบียน:</strong> <?php echo htmlspecialchars($_POST['licensePlate']); ?></p>
            <p><strong>ความสูง:</strong> <?php echo htmlspecialchars($_POST['height']); ?> cm</p>
            <p><strong>Bay:</strong> <?php echo htmlspecialchars($_POST['zone']); ?></p>
            <p><strong>ช่องจอด:</strong> <?php echo htmlspecialchars($_POST['parkingSlot']); ?></p>
            <p>ช่องจอดทำงาน ?</p>
            <form id="confirmForm" method="POST">
                <input type="hidden" name="carId" value="<?php echo htmlspecialchars($_POST['carId']); ?>">
                <input type="hidden" name="licensePlate" value="<?php echo htmlspecialchars($_POST['licensePlate']); ?>">
                <input type="hidden" name="height" value="<?php echo htmlspecialchars($_POST['height']); ?>">
                <input type="hidden" name="zone" value="<?php echo htmlspecialchars($_POST['zone']); ?>">
                <input type="hidden" name="parkingSlot" value="<?php echo htmlspecialchars($_POST['parkingSlot']); ?>">

                <button type="submit" name="confirm" class="confirm-button text-white">Yes, ทำงาน</button>
                <button type="submit" name="cancel" class="cancel-button text-white">No, ไม่ทำ</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
