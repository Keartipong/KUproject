<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// เชื่อมต่อกับฐานข้อมูล MySQL
$servername = "151.106.124.154"; 
$username = "u583789277_wag7"; 
$password = "2567Concept"; 
$dbname = "u583789277_wag7"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ฟังก์ชันรีเซ็ตสถานะการ submit ของรถ
if (isset($_POST['reset'])) {
    unset($_SESSION['submittedCars']);
}

// จัดการการ submit ของรถ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carId'])) {
    $carId = $_POST['carId'];
    $submittedCars = isset($_SESSION['submittedCars']) ? $_SESSION['submittedCars'] : [];
    $submittedCars[$carId] = true;
    $_SESSION['submittedCars'] = $submittedCars;

    echo json_encode(['status' => 'success']);
    exit;
}

// ดึงข้อมูลจากฐานข้อมูล
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$submittedCars = isset($_SESSION['submittedCars']) ? $_SESSION['submittedCars'] : [];

$query = "SELECT c.card_id, c.user_height, c.user_license_plate, l.lot_id, l.number, l.bay_id, b.bay_name, l.parked_zone 
          FROM card c
          JOIN lot l ON c.lot_id = l.lot_id
          JOIN bay b ON l.bay_id = b.bay_id
          WHERE c.status_id = 6";

if ($searchTerm) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $query .= " AND (c.user_license_plate LIKE '%$searchTerm%' OR c.card_id LIKE '%$searchTerm%')";
}

$result = $conn->query($query);

$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = [
            'id' => $row['card_id'],
            'licensePlate' => $row['user_license_plate'],
            'height' => $row['user_height'],
            'zone' => $row['bay_name'], // แสดงชื่อ Bay
            'parked_zone' => $row['parked_zone'], // ใช้ parked_zone ตรงนี้
            'parkingSlot' => $row['lot_id'],
            'number' => $row['number']
        ];
    }
}
// ลบการกรองรถที่ยังไม่ถูกส่งออกไป
$filteredCars = $cars;
// กรองเฉพาะรถที่ยังไม่ถูกส่ง
// $filteredCars = array_filter($cars, function($car) use ($submittedCars) {
//     return !isset($submittedCars[$car['id']]);
// });

if (isset($_GET['ajax'])) {
    echo json_encode(array_values($filteredCars));
    exit;
}

$conn->close();
?>

<!-- HTML ด้านล่างนี้ไม่เปลี่ยนแปลง -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการที่จอดรถอัจฉริยะ</title>
    
    <!-- ลิงก์ไปที่ Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai+Looped:wght@100;200;300;400;500;600;700&family=Itim&family=Luckiest+Guy&family=Mali:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script> 
    <style>
            
 @keyframes textGlow {
    0% {
        text-shadow: 0 0 5px #ff1744, 0 0 0px #ff1744, 0 0 0px #ff1744;
    }
    50% {
        text-shadow: 0 0 0px #ff1744, 0 0 5px #ff1744, 0 0 0px #ff1744;
    }
    100% {
        text-shadow: 0 0 0px #ff1744, 0 0 0px #ff1744, 0 0 5px #ff1744;
    }
}

        /* ใช้ฟอนต์ Poppins */
        body {
            background-image: url('https://i.pinimg.com/originals/06/43/34/064334a850251d0e3b63f915d138eb67.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            font-family: 'IBM Plex Sans Thai Looped', sans-serif; /* ใช้ฟอนต์ IBM Plex Sans Thai Looped */
            color: #f7fafc;
        }
        .car-card table {
            font-family: 'Mali', sans-serif; /* ใช้ฟอนต์ Mali */
        }
        input, button {
            background-color: rgba(255, 255, 255, 0.2); /* ความโปร่งแสงสำหรับ input และปุ่ม */
            color: #fff;
            border-color: #ff1744; /* ใช้สีแดง */
        }
        input::placeholder {
            color: #ff5252; /* ปรับสี placeholder ให้เป็นสีแดง */
        }
        input:focus, button:hover {
            background-color: rgba(255, 255, 255, 0.4); /* เพิ่มความโปร่งแสงเมื่อ hover */
        }
        h1 {
          color: #ff1744; /* สีแดงสด */
          font-size: 3.5rem; /* ขยายขนาดตัวอักษร */
          font-weight: 800; /* เพิ่มความหนาของตัวอักษร */
          text-align: center;
          text-transform: uppercase; /* ทำให้ข้อความเป็นตัวพิมพ์ใหญ่ทั้งหมด */
          margin-bottom: 20px; /* เพิ่มระยะห่างด้านล่าง */
          animation: textGlow 1.5s infinite alternate; /* เพิ่มการทำงานของ animation */
          
        }
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }
        .bg-red-custom {
            background-color: #ff1744; /* สีแดงสำหรับปุ่ม */
        }
        .text-red-custom {
            color:black; /* สีแดงสำหรับข้อความ */
        }
        .border-red-custom {
            border-color: #ff1744; /* เส้นขอบสีแดง */
        }

        /* กรอบ Card ID */
        .card-id {
    border: 3px solid #ff1744; /* ขยายขนาดของเส้นขอบ */
    padding: 12px; /* เพิ่ม padding */
    border-radius: 12px; /* เพิ่มมุมโค้ง */
    background-color: rgba(0, 0, 0, 0.5); /* ปรับความโปร่งแสงและพื้นหลังสีดำ */
    text-align: center;
    margin-bottom: 15px; /* เพิ่มระยะห่างด้านล่าง */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* เพิ่มเงา */
    backdrop-filter: blur(8px); /* เพิ่ม blur */
    
    
}
.card-id h3 {
    color: #ff1744; /* สีแดงสด */
    font-size: 1.5rem; /* ขยายขนาดตัวอักษร */
    margin: 0; /* เอาระยะห่างออก */
    font-weight: 700; /* เพิ่มความหนาของตัวอักษร */
}
        .card-id h2{
          z-index: 1;
          font-size: 2em;
        }
        .car-management-system {
          background: rgba(0, 0, 0, 0.8); /* เพิ่มความมืด */
          padding: 40px; /* เพิ่ม padding */
          border-radius: 15px; /* เพิ่มมุมโค้ง */
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4); /* เพิ่มเงา */
          backdrop-filter: blur(15px); /* เพิ่ม blur ของพื้นหลัง */
          width: 80%; /* กำหนดความกว้างของกล่อง */
          max-width: 100%; /* กำหนดความกว้างสูงสุด */
          margin: 50px auto; /* จัดตำแหน่งตรงกลางและเพิ่มระยะห่างด้านบน/ล่าง */
          text-align: center; /* จัดตำแหน่งข้อความตรงกลาง */
        }
        .car-card {
    transition: all 0.3s ease;
    position: relative;
}
.car-card:hover .card-id {
    animation: rgbGlow 1.5s infinite alternate;
}
.submit-car-form button {
    background: linear-gradient(100deg, #ff1744, #ff5252); /* เพิ่ม gradient สี */
    border: none; /* เอาเส้นขอบออก */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงา */
    transition: background 0.3s ease, box-shadow 0.3s ease; /* เพิ่มการทำงานของ transition */
}
.submit-car-form button:hover {
    background: linear-gradient(90deg, #ff5252, #ff1744); /* สลับสี gradient เมื่อ hover */
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.7); /* เพิ่มเงา */
}

.car-management-system h1 {
    color: #ff1744; /* สีแดงสด */
    font-size: 2rem; /* ขยายขนาดตัวอักษร */
    font-weight: 800; /* เพิ่มความหนาของตัวอักษร */
    text-transform: uppercase; /* ตัวพิมพ์ใหญ่ทั้งหมด */
    margin-bottom: 20px; /* เพิ่มระยะห่างด้านล่าง */
    animation: textGlow 1.5s infinite alternate; /* เพิ่มการทำงานของ animation */
}
        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        .blurred-background {
          backdrop-filter: blur(8px); /* ระดับความเบลอของพื้นหลัง */
            -webkit-backdrop-filter: blur(8px); /* สำหรับ Safari */
            background-color: rgba(0, 0, 0, 0.5); /* สีพื้นหลังที่โปร่งแสง */
            padding: 1.5rem; /* เพิ่ม padding เพื่อให้ข้อความไม่ชนกับขอบ */
            border-radius: 1rem; /* เพิ่มมุมโค้ง */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* เพิ่มเงาที่พื้นหลัง */
            display: inline-block; /* ทำให้พื้นหลังอยู่รอบข้อความ */
            margin-bottom: 1rem; /* เพิ่มระยะห่างด้านล่าง */
        }
        .text-shadow {
          text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8); /* เพิ่มเงาให้กับข้อความ */
        }
       
        @keyframes rgbGlow {
    0% {
        text-shadow: 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 15px #ff1744, 0 0 20px #ff1744, 0 0 25px #ff1744, 0 0 30px #ff1744, 0 0 35px #ff1744;
        box-shadow: 0 0 0px #ff1744, 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 10px #ff1744, 0 0 5px #ff1744, 0 0 5px #ff1744, 0 0 0px #ff1744;
    }
    50% {
        text-shadow: 0 0 5px #ff5252, 0 0 10px #ff5252, 0 0 15px #ff5252, 0 0 20px #ff5252, 0 0 25px #ff5252, 0 0 30px #ff5252, 0 0 35px #ff5252;
        box-shadow: 0 0 0px #ff5252, 0 0 5px #ff5252, 0 0 10px #ff5252, 0 0 10px #ff5252, 0 0 5px #ff5252, 0 0 5px #ff5252, 0 0 0px #ff5252;
    }
    100% {
        text-shadow: 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 15px #ff1744, 0 0 20px #ff1744, 0 0 25px #ff1744, 0 0 30px #ff1744, 0 0 35px #ff1744;
        box-shadow: 0 0 0px #ff1744, 0 0 5px #ff1744, 0 0 10px #ff1744, 0 0 10px #ff1744, 0 0 5px #ff1744, 0 0 5px #ff1744, 0 0 0px #ff1744;
    }
}
.menu-item {
    background: linear-gradient(100deg, #ff1744, #ff5252); /* เพิ่ม gradient สี */
    border: none; /* เอาเส้นขอบออก */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* เพิ่มเงา */
    transition: background 0.3s ease, box-shadow 0.3s ease; /* เพิ่มการทำงานของ transition */
    font-size: 1rem; /* ขยายขนาดตัวอักษร */
    padding: 15px; /* เพิ่ม padding */
    border-radius: 15px; /* เพิ่มมุมโค้ง */
    position: fixed; /* ตรึงไว้กับตำแหน่งของหน้าเว็บ */
    bottom: 10px; /* ตำแหน่งจากด้านล่าง */
    right: -600px; /* ตำแหน่งจากด้านขวา */
    text-align: center; /* จัดข้อความให้อยู่กลาง */
}

.menu-item:hover {
    background: linear-gradient(90deg, #ff5252, #ff1744); /* สลับสี gradient เมื่อ hover */
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.7); /* เพิ่มเงา */
}
    </style>
</head>
<body>
    <div class="car-management-system p-6">
  
        <div class="blurred-background">
        <li><a href="parking_again.php" class="menu-item" target="_blank">คำนวนหาช่องจอดใหม่กรณีช่องจอดพัง</a></li>
            <h1 class="text-shadow">ตัวเรียกช่องจอด(จอดรถ)</h1>
        </div>
        
        <div class="absolute right-0 top-10 mr-7">
            <input type="text" id="search" class="w-full max-w-wd p-3 bg-gray-500 border-red-500 text-white rounded" placeholder="ค้นหา">
            <svg class="absolute top-1/2 right-2 transform -translate-y-1/2 w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a7 7 0 0 1 7 7 7 7 0 0 1-7 7 7 7 0 0 1-7-7 7 7 0 0 1 7-7zm0 0l6 6" />
            </svg>
        </div>
        
        <div id="car-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- รายการรถจะถูกแทรกที่นี่โดย JavaScript -->
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search');
        const carGrid = document.getElementById('car-grid');

        function updateCarList(searchTerm) {
            fetch(`?search=${encodeURIComponent(searchTerm)}&ajax=true`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(cars => {
                    carGrid.innerHTML = '';
                    cars.forEach(car => {
    const carCard = document.createElement('div');
    carCard.className = 'car-card bg-gray-800 border-red-custom p-4 rounded-lg';
    carCard.dataset.carId = car.id;

    carCard.innerHTML = `
    <div class="card-id">
        <h3 class="text-xl font-bold text-red-custom">Card ID: ${car.id}</h3>
    </div>
    <table class="w-full text-left text-gray-300">
        <tr>
            <td class="py-1">ทะเบียน:</td>
            <td>${car.licensePlate}</td>
        </tr>
        <tr>
            <td class="py-1">ความสูง:</td>
            <td>${car.height} ซม.</td>
        </tr>
         <tr>
            <td class="py-1">Zone:</td>
            <td><span class="bay-circle"></span>${car.parked_zone}</td> <!-- ใช้ car.parked_zone แทน car.bay -->
        </tr>
        <tr>
            <td class="py-1">Bay:</td>
            <td><span class="zone-circle"></span>${car.zone}</td>
        </tr>
        <tr>
            <td class="py-1">ช่องจอด:</td>
            <td><span class="parking-circle"></span>${car.number}</td>
        </tr>
    </table>
    <form class="submit-car-form" method="POST" action="update_error.php">
        <input type="hidden" name="carId" value="${car.id}">
        <input type="hidden" name="licensePlate" value="${car.licensePlate}">
        <input type="hidden" name="height" value="${car.height}">
        <input type="hidden" name="bay" value="${car.bay}">
        <input type="hidden" name="zone" value="${car.zone}">
        <input type="hidden" name="parkingSlot" value="${car.number}">
        <button type="submit" name="submit" class="mt-4 w-full bg-red-custom hover:bg-red-600 text-white p-2 rounded">
            Submit
        </button>
    </form>
    `;

    carGrid.appendChild(carCard);
});

                })
                .catch(error => console.error('There was a problem with the fetch operation:', error));
        }

        searchInput.addEventListener('input', () => {
            updateCarList(searchInput.value);
        });

        // Initial load
        updateCarList('');
        setInterval(() => {
            location.reload();
        }, 5000);
    </script>
</body>
</html>
