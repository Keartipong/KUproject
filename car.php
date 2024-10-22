<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Car Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #000000 0%, #434343 100%);
            background-size: 200% 200%;
            animation: backgroundAnimation 10s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        @keyframes backgroundAnimation {
            0% {
                background-position: 0% 0%;
            }
            100% {
                background-position: 100% 100%;
            }
        }
        
        .container {
            background-color: #1a1a1a;
            border: 5px solid #4CAF50;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            padding: 30px;
            width: 400px;
            max-width: 90%;
            text-align: center;
        }
        
        .license_plate {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 15px;
            padding: 15px;
            border: 3px solid #ffffff;
            border-radius: 10px;
            background-color: #333333;
        }
        
        .id {
            font-size: 28px;
            color: #ffffff;
            padding: 15px;
            border-top: 3px solid #444444;
        }
    </style>
</head>
<body>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;700&display=swap" rel="stylesheet">
    <div class="container">
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

        if (!isset($_SESSION['shown_ids'])) {
            $_SESSION['shown_ids'] = [];
        }

        $totalRecordsQuery = "SELECT COUNT(*) AS total FROM license_data";
        $totalResult = $conn->query($totalRecordsQuery);
        $totalRow = $totalResult->fetch_assoc();
        $totalRecords = $totalRow['total'];

        if (count($_SESSION['shown_ids']) >= $totalRecords) {
            $_SESSION['shown_ids'] = []; 
        }

do {
            $sql = "SELECT id, license_plate FROM license_data ORDER BY RAND() LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id = $row["id"];
                $license_plate = $row["license_plate"];
                
                if (!in_array($id, $_SESSION['shown_ids'])) {
                    $_SESSION['shown_ids'][] = $id;
                    break;
                }
            }
        } while (true);

        $conn->close();
        ?>
        <div class='license_plate'><?php echo htmlspecialchars($license_plate); ?></div>
        <div class='id'>ID: <?php echo htmlspecialchars($id); ?></div>
    </div>
</body>
</html>
