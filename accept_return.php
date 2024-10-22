<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS KU - Accept Return</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #003366;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.7);
        }

        .logo h2 {
            margin: 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin-top: 50px;
        }

        .menu-item {
            display: block;
            padding: 10px 20px;
            margin: 10px 0;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .menu-item.active,
        .menu-item:hover {
            background-color: #0059b3;
        }

        .version p {
            text-align: center;
            font-size: 14px;
            color: #ccc;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #003366;
        }

        .return-section,
        .status-update-section {
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.7);
            margin-bottom: 30px;
        }

        .return-section label,
        .status-update-section label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
            color: #003366;
        }

        .return-section input[type="text"],
        .status-update-section button {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .return-section button,
        .status-update-section button {
            background-color: #0059b3;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 18px;
        }

        .return-section button:hover,
        .status-update-section button:hover {
            background-color: #003366;
        }

        .return-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.7);
            margin: 20px 0;
        }

        .return-details h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #003366;
        }

        .update-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .history-section {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #003366;
            color: #fff;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }

            .main-content {
                padding: 10px;
            }

            table thead {
                display: none;
            }

            table, table tbody, table tr, table td {
                display: block;
                width: 100%;
            }

            table tr {
                margin-bottom: 15px;
            }

            table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }

            table td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-left: 15px;
                font-weight: bold;
                text-align: left;
                background-color: #003366;
                color: #fff;
                border-right: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
        <div class="logo">
            <h2>APS KU</h2>
        </div>
        <ul>
            <li><a href="ParkingInfo.php" class="menu-item">Check ID card</a></li>
            <li><a href="accept_return.php" class="menu-item active">Update Status</a></li>
            <li><a href="moniter.php" class="menu-item" target="_blank">Monitor</a></li>
            <li><a href="index.php" class="menu-item">Sign out</a></li>
        </ul>
        <div class="version">
            <p>Version 2.0</p>
        </div>
    </div>
    <div class="main-content">
        <h1>Accept Return</h1>
        <div class="return-section">
            <!-- <form action="accept_return.php" method="POST">
                <label>ไอดีการ์ด:</label>
                <input type="text" name="card_id" placeholder="กรอกไอดีการ์ด" required>
                <button type="submit">ยืนยัน</button>
            </form> -->
            <label>ตัวเรียกช่องจอด:</label>
            <a href="update.php" target="_blank">
            <button type="button">เรียกช่องจอด</button>
            </a>
            <a href="retrieve_parking.php" target="_blank">
            <button type="button">คืนช่องจอด</button>
            </a><br><br>
            <label>ประวัติการใช้งาน:</label>
            <a href="his_in.php" target="_blank">
                <button type="button">เข้าใช้งาน</button>
            </a>
            <a href="his_out.php" target="_blank">
                <button type="button">คืนช่องจอด</button>
            </a>
            <label>คืนการ์ด:</label>
            <a href="showout.php" target="_blank">
            <button type="button">คืนCard ID</button>
            </a>
        </div>

        <h1>Update Status</h1>
        <div class="status-update-section">
            <form action="accept_return.php" method="POST">
                <label>อัพเดทการ์ดไอดีว่าง:</label>
                <button type="submit" name="update_card_status">อัพเดท</button>
            </form>

            <form action="accept_return.php" method="POST">
                <label>อัพเดทสถานะช่องจอดความสูงปกติเป็นว่าง:</label>
                <button type="submit" name="update_normal_empty">อัพเดทช่องจอดปกติ (ว่าง)</button>
            </form>

            <form action="accept_return.php" method="POST">
                <label>อัพเดทสถานะช่องจอดความสูงปกติเป็นเต็ม:</label>
                <button type="submit" name="update_normal_full">อัพเดทช่องจอดปกติ (เต็ม)</button>
            </form>

            <form action="accept_return.php" method="POST">
                <label>อัพเดทสถานะช่องจอดความสูงเกินเป็นว่าง:</label>
                <button type="submit" name="update_high_empty">อัพเดทช่องจอดสูงเกิน (ว่าง)</button>
            </form>

            <form action="accept_return.php" method="POST">
                <label>อัพเดทสถานะช่องจอดความสูงเกินเป็นเต็ม:</label>
                <button type="submit" name="update_high_full">อัพเดทช่องจอดสูงเกิน (เต็ม)</button>
            </form>
        </div>

        <?php
        // // ฟังก์ชันคืนช่องจอดรถ
        // function return_lot($id_no) {
        //     $servername = "localhost";
        //     $username = "root";
        //     $password = "";
        //     $dbname = "carlot";

        //     // สร้างการเชื่อมต่อ
        //     $conn = new mysqli($servername, $username, $password, $dbname);

        //     // ตรวจสอบการเชื่อมต่อ
        //     if ($conn->connect_error) {
        //         die("Connection failed: " . $conn->connect_error);
        //     }

        //     // คิวรีช่องจอดรถที่มีสถานะ 7 (หมายถึงถูกจอดแล้ว)
        //     $sql = "SELECT number, status_id FROM lot WHERE status_id=7";
        //     $result = $conn->query($sql);

        //     $parked_lot = array();
        //     while ($row = $result->fetch_assoc()) {
        //         $parked_lot[] = $row['number'];
        //     }

        //     // คิวรีข้อมูลการ์ดจาก lot ที่ต้องการคืน
        //     $stmt = $conn->prepare('SELECT card.card_id, card.status_id, card.user_height, lot.parked_zone, bay.bay_name, lot.number, lot.lot_id
        //                             FROM lot
        //                             INNER JOIN card ON lot.lot_id = card.lot_id                  
        //                             INNER JOIN bay ON lot.bay_id = bay.bay_id
        //                             WHERE card.card_id=?');
        //     $stmt->bind_param("s", $id_no);
        //     $stmt->execute();
        //     $result = $stmt->get_result();
        //     $old_data = $result->fetch_assoc();

        //     if ($old_data) {
        //         $card_id = $old_data['card_id'];
        //         $status_id = $old_data['status_id'];
        //         $user_height = $old_data['user_height'];
        //         $parked_zone = $old_data['parked_zone'];
        //         $bay_name = $old_data['bay_name'];
        //         $number = $old_data['number'];
        //         $lot_id = $old_data['lot_id'];

        //         if (in_array($number, $parked_lot)) {
        //             // อัปเดตสถานะ lot เป็นว่าง (status_id = 1)
        //             $stmt = $conn->prepare("UPDATE lot SET status_id = '1' WHERE number = ?");
        //             $stmt->bind_param("s", $number);
        //             $stmt->execute();

        //             // อัปเดตการ์ดเป็นค่าว่าง (status_id = 1)
        //             $stmt = $conn->prepare("UPDATE card SET user_height=NULL, lot_id=NULL, status_id='1' WHERE lot_id=?");
        //             $stmt->bind_param("s", $lot_id);
        //             $stmt->execute();

        //             // อัปเดตเวลาออกในตาราง history
        //             $current_time_out = date('Y-m-d H:i:s');
        //             $stmt = $conn->prepare("UPDATE update_history SET time_out = ? WHERE card_id = ? AND time_out IS NULL");
        //             $stmt->bind_param("ss", $current_time_out, $card_id);
        //             $stmt->execute();

        //             echo "<div class='return-details'>
        //                     <h2>การคืนสำเร็จ</h2>
        //                     <p>ไอดีการ์ด: " . htmlspecialchars($id_no) . "</p>
        //                     <p>โซน: " . htmlspecialchars($parked_zone) . "</p>
        //                     <p>หมายเลขช่องจอด: " . htmlspecialchars($number) . "</p>
        //                   </div>";
        //         } else {
        //             echo "<div class='return-details'><p>การ์ดที่ระบุไม่มีการจอด</p></div>";
        //         }
        //     } else {
        //         echo "<div class='return-details'><p>ไม่พบข้อมูลการ์ดที่ระบุ</p></div>";
        //     }
        //     $conn->close();
        // }

        // // ตรวจสอบว่ามีการโพสต์ข้อมูลจากฟอร์มหรือไม่
        // if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //     if (isset($_POST["card_id"])) {
        //         $card_id = $_POST["card_id"];
        //         return_lot($card_id);
    //    }

            // อัปเดตสถานะการ์ดไอดีเป็นว่าง
            if (isset($_POST['update_card_status'])) {
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

                $sql = "UPDATE card SET lot_id=NULL, user_height=NULL ,status_id=1";

                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('อัพเดทสถานะสำเร็จ!');</script>";
                } else {
                    echo "<script>alert('Error updating record: " . $conn->error . "');</script>";
                }
                $conn->close();
            }

            // อัปเดต status_id ของทุกช่องจอดรถในตาราง lot เป็นว่าง
            if (isset($_POST['update_lot_status_empty'])) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "carlot";

                // สร้างการเชื่อมต่อ
                $conn = new mysqli($servername, $username, $password, $dbname);

                // ตรวจสอบการเชื่อมต่อ
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // อัปเดตทุกช่องจอดรถให้ status_id เป็น 1 (ว่าง)
                $sql = "UPDATE lot SET status_id = 1";

                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('อัพเดทสถานะช่องจอดรถสำเร็จ!');</script>";
                } else {
                    echo "<script>alert('Error updating lot status: " . $conn->error . "');</script>";
                }
                $conn->close();
            }

            // อัปเดต status_id ของทุกช่องจอดรถในตาราง lot เป็นเต็ม
            if (isset($_POST['update_lot_status_full'])) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "carlot";

                // สร้างการเชื่อมต่อ
                $conn = new mysqli($servername, $username, $password, $dbname);

                // ตรวจสอบการเชื่อมต่อ
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // อัปเดตทุกช่องจอดรถให้ status_id เป็น 7 (เต็ม)
                $sql = "UPDATE lot SET status_id = 7";

                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('อัพเดทสถานะช่องจอดรถเต็มสำเร็จ!');</script>";
                } else {
                    echo "<script>alert('Error updating lot status: " . $conn->error . "');</script>";
                }
                $conn->close();
            }
        // }
        // อัปเดตสถานะช่องจอดความสูงปกติเป็นว่าง
        if (isset($_POST['update_normal_empty'])) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "carlot";

            // สร้างการเชื่อมต่อ
            $conn = new mysqli($servername, $username, $password, $dbname);

            // ตรวจสอบการเชื่อมต่อ
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // อัปเดตช่องจอดความสูงปกติ (parking_type_id = 0) ให้ status_id เป็น 1 (ว่าง)
            $sql = "UPDATE lot SET status_id = 1 WHERE parking_type_id = 0";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('อัพเดทสถานะช่องจอดปกติเป็นว่างสำเร็จ!');</script>";
            } else {
                echo "<script>alert('Error updating normal lot status: " . $conn->error . "');</script>";
            }
            $conn->close();
        }

        // อัปเดตสถานะช่องจอดความสูงปกติเป็นเต็ม
        if (isset($_POST['update_normal_full'])) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "carlot";

            // สร้างการเชื่อมต่อ
            $conn = new mysqli($servername, $username, $password, $dbname);

            // ตรวจสอบการเชื่อมต่อ
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // อัปเดตช่องจอดความสูงปกติ (parking_type_id = 0) ให้ status_id เป็น 7 (เต็ม)
            $sql = "UPDATE lot SET status_id = 7 WHERE parking_type_id = 0";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('อัพเดทสถานะช่องจอดปกติเป็นเต็มสำเร็จ!');</script>";
            } else {
                echo "<script>alert('Error updating normal lot status: " . $conn->error . "');</script>";
            }
            $conn->close();
        }

        // อัปเดตสถานะช่องจอดความสูงเกินเป็นว่าง
        if (isset($_POST['update_high_empty'])) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "carlot";

            // สร้างการเชื่อมต่อ
            $conn = new mysqli($servername, $username, $password, $dbname);

            // ตรวจสอบการเชื่อมต่อ
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // อัปเดตช่องจอดความสูงเกิน (parking_type_id = 1) ให้ status_id เป็น 1 (ว่าง)
            $sql = "UPDATE lot SET status_id = 1 WHERE parking_type_id = 1";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('อัพเดทสถานะช่องจอดสูงเกินเป็นว่างสำเร็จ!');</script>";
            } else {
                echo "<script>alert('Error updating high lot status: " . $conn->error . "');</script>";
            }
            $conn->close();
        }

        // อัปเดตสถานะช่องจอดความสูงเกินเป็นเต็ม
        if (isset($_POST['update_high_full'])) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "carlot";

            // สร้างการเชื่อมต่อ
            $conn = new mysqli($servername, $username, $password, $dbname);

            // ตรวจสอบการเชื่อมต่อ
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // อัปเดตช่องจอดความสูงเกิน (parking_type_id = 1) ให้ status_id เป็น 7 (เต็ม)
            $sql = "UPDATE lot SET status_id = 7 WHERE parking_type_id = 1";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('อัพเดทสถานะช่องจอดสูงเกินเป็นเต็มสำเร็จ!');</script>";
            } else {
                echo "<script>alert('Error updating high lot status: " . $conn->error . "');</script>";
            }
            $conn->close();
        }
    
        ?>
    </div>
</body>
</html>
