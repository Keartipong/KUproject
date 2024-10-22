<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking History</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Roboto:wght@400&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #111;
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffffff;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }

        .container {
            background: linear-gradient(145deg, rgba(30, 30, 30, 0.8), rgba(50, 50, 50, 0.9));
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.6), 0 0 40px rgba(0, 255, 165, 0.4);
            max-width: 900px;
            width: 100%;
            text-align: center;
            animation: dropDown 1s ease-in-out;
            backdrop-filter: blur(10px);
            position: relative;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00FFFF;
            font-size: 48px;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(0, 255, 255, 0.8), 0 0 40px rgba(0, 165, 255, 1);
            animation: neonGlow 1.5s infinite alternate;
        }

        input[type="text"] {
            width: 80%;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #00FFFF;
            border-radius: 30px;
            background-color: rgba(30, 30, 30, 0.9);
            color: #fff;
            font-size: 18px;
            text-align: center;
            transition: 0.4s ease-in-out;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }

        input[type="text"]:focus {
            outline: none;
            background-color: rgba(50, 50, 50, 1);
            border: 1px solid #00FF00;
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.8), 0 0 25px rgba(0, 255, 255, 0.6);
        }

        button {
            padding: 15px 30px;
            background: linear-gradient(145deg, #00FFFF, #00FF00);
            border: none;
            border-radius: 50px;
            cursor: pointer;
            color: #333;
            font-size: 18px;
            font-weight: bold;
            transition: 0.4s ease-in-out;
            position: relative;
            z-index: 2;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.8), 0 0 40px rgba(0, 255, 0, 0.5);
        }

        button:hover {
            background: linear-gradient(145deg, #00FF00, #FFFF00);
            color: #000;
            box-shadow: 0 0 25px rgba(255, 255, 0, 1), 0 0 50px rgba(255, 255, 0, 0.8);
            transform: scale(1.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            border: 1px solid rgba(0, 255, 255, 0.3);
            padding: 15px;
            text-align: center;
            font-size: 18px;
        }

        th {
            background-color: rgba(30, 30, 30, 0.9);
            color: #00FFFF;
            text-transform: uppercase;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
        }

        td {
            background-color: rgba(15, 15, 15, 0.85);
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
        }

        tr:hover td {
            background-color: rgba(0, 255, 255, 0.8);
            transition: all 0.3s ease-in-out;
            color: #000;
        }

        .pagination {
            margin-top: 30px;
            text-align: center;
        }

        .pagination a {
            color: #00FFFF;
            font-weight: bold;
            padding: 10px 15px;
            margin: 0 5px;
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .pagination a.active {
            background-color: rgba(0, 255, 0, 0.8);
            color: #000;
        }

        .pagination a:hover {
            background-color: rgba(0, 255, 255, 0.9);
            color: #333;
            transform: scale(1.3);
        }

        /* Glowing Neon Effect for Header */
        @keyframes neonGlow {
            0% {
                text-shadow: 0 0 10px rgba(0, 255, 255, 0.8),
                             0 0 20px rgba(0, 255, 255, 1),
                             0 0 30px rgba(0, 165, 255, 1);
            }
            100% {
                text-shadow: 0 0 20px rgba(0, 255, 255, 1),
                             0 0 30px rgba(0, 255, 255, 1),
                             0 0 50px rgba(0, 255, 255, 0.8);
            }
        }

        @keyframes dropDown {
            0% { transform: translateY(-50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .neon-lights {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, rgba(0, 255, 255, 0.6), rgba(0, 255, 0, 0.6));
            animation: glowStripe 2s infinite linear;
        }

        @keyframes glowStripe {
            0% { background-position: 0%; }
            100% { background-position: 100%; }
        }

    </style>
</head>
<body>

<div class="neon-lights"></div>

<div class="container">
    <h1>Access time</h1>
    <input type="text" id="search" placeholder="Search by His ID, Card ID, License Plate...">
    <button onclick="searchData()">Search</button>

    <table id="historyTable">
        <tr>
            <th>ลำดับ</th>
            <th>Card ID</th>
            <th>ความสูง</th> <!-- ปรับเป็นความสูง -->
            <th>ป้ายทะเบียน</th> <!-- ปรับเป็นป้ายทะเบียน -->
            <th>Lot</th> <!-- ปรับเป็น Lot ID -->
            <th>เวลาเข้า</th>
        </tr>   

        <?php
        // สร้างการเชื่อมต่อฐานข้อมูล
        $servername = "151.106.124.154"; // เปลี่ยนเป็นเซิร์ฟเวอร์ของคุณ
        $username = "u583789277_wag7"; // เปลี่ยนเป็นชื่อผู้ใช้ของคุณ
        $password = "2567Concept"; // เปลี่ยนเป็นรหัสผ่านของคุณ
        $dbname = "u583789277_wag7"; // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

        // สร้างการเชื่อมต่อ
        $conn = new mysqli($servername, $username, $password, $dbname);

        // ตรวจสอบการเชื่อมต่อ
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // กำหนดจำนวนรายการต่อหน้า
        $items_per_page = 10;

        // รับหมายเลขหน้าปัจจุบันจาก URL (ถ้าไม่มีให้ตั้งเป็น 1)
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $items_per_page;

        // คำสั่ง SQL เพื่อดึงข้อมูลจากตาราง update_history พร้อมกับข้อมูลจากตาราง lot
        $sql = "
            SELECT uh.card_id, uh.user_license_plate, uh.distance , uh.lot_id, uh.time_in, l.number AS lot_number
            FROM update_history uh
            JOIN lot l ON uh.lot_id = l.lot_id
            ORDER BY uh.time_in DESC
            LIMIT $offset, $items_per_page
        ";
        $result = $conn->query($sql);

        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if ($result->num_rows > 0) {
            // เริ่มลำดับ
            $count = $offset + 1;

            // แสดงข้อมูลที่ดึงมา
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $count . "</td>";
                echo "<td>" . $row["card_id"] . "</td>";
                echo "<td>" . $row["distance"] . "</td>"; // เปลี่ยนให้แสดงความสูง (distance)
                echo "<td>" . $row["user_license_plate"] . "</td>"; // เปลี่ยนให้แสดงป้ายทะเบียน
                echo "<td>" . $row["lot_number"] . "</td>"; // แสดงหมายเลขจากตาราง lot
                echo "<td>" . $row["time_in"] . "</td>";
                echo "</tr>";

                // เพิ่มลำดับ
                $count++;
            }
        } else {
            echo "<tr><td colspan='6'>No results found.</td></tr>";
        }

        echo "</table>";

        // คำสั่ง SQL เพื่อหาจำนวนรวมของข้อมูลในตาราง update_history
        $total_sql = "SELECT COUNT(*) as total FROM update_history";
        $total_result = $conn->query($total_sql);
        $total_row = $total_result->fetch_assoc();
        $total_items = $total_row['total'];

        // คำนวณจำนวนหน้าทั้งหมด
        $total_pages = ceil($total_items / $items_per_page);

        // สร้างลิงก์สำหรับการแบ่งหน้า
        echo "<div class='pagination'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                echo "<a class='active' href='?page=$i'>$i</a>";
            } else {
                echo "<a href='?page=$i'>$i</a>";
            }
        }
        echo "</div>";

        // ปิดการเชื่อมต่อ
        $conn->close();
        ?>
    </div>
</body>

<script>
function searchData() {
    let input = document.getElementById('search').value;
    let table = document.getElementById('historyTable');
    let tr = table.getElementsByTagName('tr');

    // แสดงข้อมูลใหม่เมื่อกดปุ่มค้นหา
    let searchResults = [];
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName('td');
        let found = false;

        for (let j = 1; j < td.length; j++) { // เริ่มจาก 1 เพราะไม่ต้องการตรวจสอบคอลัมน์ที่ 0
            if (td[j]) {
                let txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toLowerCase().indexOf(input.toLowerCase()) > -1) {
                    found = true;
                    break;
                }
            }
        }
        if (found) {
            searchResults.push(tr[i]); // บันทึกแถวที่ตรงกับคำค้นหา
        }
    }

    // ซ่อนทั้งหมดก่อนแล้วจึงแสดงผลลัพธ์ที่ตรงกัน
    for (let i = 1; i < tr.length; i++) {
        tr[i].style.display = "none"; // ซ่อนแถวทั้งหมด
    }

    for (let i = 0; i < searchResults.length; i++) {
        searchResults[i].style.display = ""; // แสดงเฉพาะแถวที่ตรงกัน
    }
}
</script>
</html>
