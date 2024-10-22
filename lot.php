<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "151.106.124.154";
$username = "u583789277_wag7";
$password = "2567Concept";
$dbname = "u583789277_wag7";

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่าเลือกโซนหรือไม่
$bay_id = isset($_POST['zone']) ? $_POST['zone'] : '';

// โซนที่ต้องการแสดง (1-8)
$valid_zones = ['1', '2', '3', '4', '5', '6', '7', '8'];

// ใช้คำสั่งเตรียมเพื่อป้องกัน SQL Injection
$placeholders = implode(',', array_fill(0, count($valid_zones), '?'));
$sql = "SELECT DISTINCT b.bay_id, b.bay_name 
        FROM lot l
        JOIN bay b ON l.bay_id = b.bay_id
        WHERE b.bay_id IN ($placeholders) 
        ORDER BY b.bay_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($valid_zones)), ...$valid_zones);
$stmt->execute();
$zone_result = $stmt->get_result();

// ตรวจสอบข้อผิดพลาด
if (!$zone_result) {
    die("Query Error: " . $conn->error);
}

// ดึงข้อมูลที่จอดรถตามโซนที่เลือก
$sql = "SELECT l.lot_id, l.number, l.status_id, b.bay_name 
        FROM lot l
        JOIN bay b ON l.bay_id = b.bay_id" . 
       ($bay_id && in_array($bay_id, $valid_zones) ? " WHERE b.bay_id = ?" : "") . 
       " ORDER BY b.bay_name, l.number";

$stmt = $conn->prepare($sql);
if ($bay_id && in_array($bay_id, $valid_zones)) {
    $stmt->bind_param('s', $bay_id);
}
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบข้อผิดพลาด
if (!$result) {
    die("Query Error: " . $conn->error);
}

// ปิดการเชื่อมต่อฐานข้อมูลเมื่อเสร็จ
function closeConnection($conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัพเดทสถานะช่องจอด</title>
    <style>
        /* ปรับปรุงสไตล์ */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #67b26f 0%, #4ca2cd 100%);
            color: #333;
        }
        header {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px 0;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        header h1 {
            margin: 0;
            font-size: 2.5rem;
            color: #4CAF50;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            position: relative;
        }
        .container::before {
            content: '';
            position: absolute;
            top: -10px;
            right: -10px;
            bottom: -10px;
            left: -10px;
            z-index: -1;
            background: linear-gradient(135deg, #4ca2cd 0%, #67b26f 100%);
            border-radius: 15px;
            filter: blur(30px);
        }
        .filter-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
        }
        .filter-form label {
            font-weight: bold;
            font-size: 1.2rem;
        }
        .filter-form select,
        .filter-form input[type="submit"] {
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
        .filter-form select {
            width: 200px;
        }
        .filter-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        .filter-form input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow: hidden;
            border-radius: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: center;
            font-size: 1.1rem;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr.available td {
            background-color: #d4edda;
        }
        tr.full td {
            background-color: #76BC43 ;
        }
        tr.reserved td {
            background-color: #FFFF99;
        }
        tr.broken td {
            background-color: #FF0066;
        }
        .zone-header {
            background-color: #e2e2e2;
            padding: 10px;
            margin-top: 20px;
            font-size: 1.5rem;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-radius: 5px;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            font-size: 1.3rem;
            color: #888;
        }
        /* เพิ่มเอฟเฟกต์เฟดอิน */
        .fade-in {
            opacity: 0;
            animation: fadeIn 1s forwards;
        }
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
        .H1 input[type="submit"]{
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <header>
        <h1>อัพเดทสถานะช่องจอด</h1>
    </header>
    <div class="container">
        <form method="POST" action="" class="filter-form">
            <label for="zone">เลือกโซน:</label>
            <select name="zone" id="zone">
                <option value="">ทั้งหมด</option>
                <?php
                // แสดงตัวเลือกโซนที่ดึงมาจากตาราง bay
                while ($row = $zone_result->fetch_assoc()) {
                    echo "<option value='{$row['bay_id']}'" . ($bay_id == $row['bay_id'] ? ' selected' : '') . ">{$row['bay_name']}</option>";
                }
                ?>
            </select>
            <input type="submit" value="แสดง">
        </form>

        <form method="POST" action="updatelot_status.php" class="H1">
            <?php
            $current_zone = '';
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if the zone has changed
                    if ($current_zone != $row['bay_name']) {
                        if ($current_zone != '') {
                            echo '</table>'; // Close previous table
                        }
                        $current_zone = $row['bay_name'];
                        echo "<div class='zone-header'>ช่องจอด $current_zone</div>";
                        echo "<table><thead>
                                <tr>
                                    <th>หมายเลขที่จอดรถ</th>
                                    <th>สถานะ</th>
                                    <th>อัปเดตสถานะ</th>
                                </tr>
                              </thead>
                              <tbody>";
                    }
                    $status_class = "";
                    $status_text = "";
                    switch ($row["status_id"]) {
                        case 1:
                            $status_class = "available";
                            $status_text = "ว่าง";
                            break;
                        case 7:
                            $status_class = "full";
                            $status_text = "เต็ม";
                            break;
                        case 6:
                            $status_class = "reserved";
                            $status_text = "จอง";
                            break;
                            case 3:
                                $status_class = "broken";
                                $status_text = "เสีย";
                                break;
                    }
                    echo "<tr class='{$status_class}'>
                            <td>{$row['number']}</td>
                            <td>{$status_text}</td>
                            <td>
                                <select name='status_{$row['lot_id']}'>
                                    <option value='1'" . ($row['status_id'] == 1 ? ' selected' : '') . ">ว่าง</option>
                                    <option value='6'" . ($row['status_id'] == 6 ? ' selected' : '') . ">จอง</option>
                                    <option value='7'" . ($row['status_id'] == 7 ? ' selected' : '') . ">เต็ม</option>
                                    <option value='3'" . ($row['status_id'] == 3 ? ' selected' : '') . ">เสีย</option>
                                </select>
                            </td>
                        </tr>";
                }
                echo '</tbody></table>'; // Close the last table
            } else {
                echo "<p class='no-data'>ไม่พบข้อมูลที่จอดรถ</p>";
            }
            ?>
            <input type="submit" value="อัปเดตสถานะ">
        </form>
    </div>
    <?php closeConnection($conn); ?>
</body>
</html>
