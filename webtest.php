<?php
session_start();

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

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch parking data
$sql = "SELECT number, status_id, bay_id FROM lot";
$result = $conn->query($sql);

if (!$result) {
    die("Error retrieving data: " . $conn->error);
}

// Function to get status class and status text in Thai
function getStatusClassAndText($status_id) {
    switch ($status_id) {
        case 1: return ['class' => 'bg-blue-500 neon-glow fa fa-car', 'text' => 'ว่าง']; // Empty
        case 6: return ['class' => 'bg-yellow-400 text-black neon-glow fa fa-parking', 'text' => 'จอง']; // Reserved
        case 7: return ['class' => 'bg-green-500 neon-glow fa fa-check-circle', 'text' => 'จอด']; // Parked
        case 3: return ['class' => 'bg-red-500 neon-glow fa fa-exclamation-triangle', 'text' => 'พัง']; // Broken
        default: return ['class' => 'bg-gray-300 neon-glow fa fa-question-circle', 'text' => 'ไม่ทราบ']; // Unknown
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚗 Racing Track Parking Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Bungee&display=swap');
        
        /* Global Racing Theme */
        body {
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(45deg, #000000, #1c1c1c);
            background-size: cover;
            color: white;
            overflow-x: hidden;
            min-height: 100vh;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        /* Title with racing flags */
        h2, h1 {
            font-family: 'Bungee', cursive; 
            letter-spacing: 2px;
            position: relative;
        }
        h2:before, h1:before {
            content: "🏁";
            position: absolute;
            left: -40px;
            animation: flag-wave 2s infinite;
        }
        @keyframes flag-wave {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(10deg); }
            100% { transform: rotate(0deg); }
        }

        /* Animated Car */
        .car-bg {
            background: url('https://i.imgur.com/pxgZ5RA.png') no-repeat center;
            background-size: cover;
            animation: drift 5s ease-in-out infinite alternate;
            position: relative;
            z-index: 0;
            opacity: 0.3;
        }
        @keyframes drift {
            0% { transform: translateX(-10px) rotate(0deg); }
            100% { transform: translateX(10px) rotate(2deg); }
        }

        /* Neon Glow Effects */
        .neon-glow {
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.9), 0 0 30px rgba(0, 255, 255, 0.9);
            border-radius: 5px;
        }

        /* Button hover with engine roar animation */
        .neon-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, #282828, #383838);
            transition: background 0.3s ease, transform 0.3s ease;
            box-shadow: 0 0 10px #00f, 0 0 15px #00f;
        }
        .neon-btn:hover {
            background: #1a1a1a;
            transform: translateY(-5px) scale(1.1); 
            box-shadow: 0 0 30px #f0f, 0 0 40px #00f;
            animation: engine-roar 0.7s ease;
        }
        @keyframes engine-roar {
            0%, 100% { transform: scale(1.05); }
            50% { transform: scale(1.15); }
        }

        /* Loader as Spinning Tires */
        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #e74c3c;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            animation: spin 1.5s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Racing Car Icon */
        .zone-icon {
            transition: transform 0.3s ease, color 0.3s;
            color: #FFD700; 
        }
        .zone-icon:hover {
            transform: scale(1.5) rotate(15deg); 
            color: #00FF00; 
        }

        /* Details Slide-in Animation */
        .zone-details-enter {
            opacity: 0;
            transform: translateX(100%) scale(0.7) rotate(0deg);
            transition: transform 0.4s ease, opacity 0.4s ease;
        }
        .zone-details-enter-active {
            opacity: 1;
            transform: translateX(0) scale(1) rotate(360deg);
        }

        /* Neon Glow for details */
        .neon-glow {
            animation: neonPulse 1.5s infinite;
        }
        @keyframes neonPulse {
            0%, 100% { box-shadow: 0 0 15px rgba(255, 0, 0, 0.8), 0 0 30px rgba(0, 0, 255, 1); }
            50% { box-shadow: 0 0 30px rgba(255, 0, 255, 1), 0 0 50px rgba(0, 255, 255, 1); }
        }
    </style>
    <script>
    let currentZoneId = null; // เก็บ ID ของโซนที่เลือก

    function fetchZoneDetails(zoneId) {
        currentZoneId = zoneId; // ตั้งค่า ID ของโซนที่เลือก
        document.getElementById('zone-details').innerHTML = '<div class="loader"></div>';
        
        fetch(`get_zone_data.php?zone_id=${zoneId}`)
            .then(response => response.json())
            .then(data => {
                const detailsTable = document.getElementById('zone-details');
                detailsTable.classList.add('zone-details-enter');
                setTimeout(() => {
                    detailsTable.classList.add('zone-details-enter-active');
                }, 100);
                
                detailsTable.innerHTML = `
                    <h2 class='text-4xl font-bold mb-4 neon-glow'><i class="fa fa-map-signs"></i> Zone ${data.zone}</h2>
                    <table class='min-w-full bg-gray-800'>
                        <thead>
                            <tr>
                                <th class='py-2 px-4 border-b text-left text-gray-300'><i class="fa fa-list-ol"></i> Slot Number</th>
                                <th class='py-2 px-4 border-b text-left text-gray-300'><i class="fa fa-car"></i> Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.slots.map(slot => `
                                <tr>
                                    <td class='py-2 px-4 border-b text-gray-200'>${slot.number}</td>
                                    <td class='py-2 px-4 border-b text-gray-200'>
                                        <span class='${slot.class} neon-glow rounded px-2 py-1 text-white'>
                                            <i class='${slot.class}'></i> ${slot.status}
                                        </span>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            })
            .catch(error => {
                document.getElementById('zone-details').innerHTML = `<p>Error loading data. Please try again.</p>`;
                console.error('Error:', error);
            });
    }

    // เรียกใช้ fetchZoneDetails ทุก 5 วินาทีสำหรับโซนที่เลือก
    setInterval(() => {
        if (currentZoneId !== null) {
            fetchZoneDetails(currentZoneId);
        }
    }, 5000);
</script>

</head>
<body class="min-h-screen flex">
    <div class="w-1/4 bg-gray-900 p-6 shadow-lg">
        <h2 class="text-3xl font-bold mb-4 neon-glow"><i class="fa fa-parking"></i> Parking Zones</h2>
        <ul>
            <?php
            $zones = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            foreach ($zones as $zoneIndex => $zone) {
                echo "<li class='mb-4'>
                        <button class='w-full text-left py-3 px-5 bg-gray-700 hover:bg-gray-600 neon-btn' onclick='fetchZoneDetails(" . ($zoneIndex + 1) . ")'>
                            <i class='fas fa-road zone-icon'></i> Zone {$zone}
                        </button>
                      </li>";
            }
            ?>
        </ul>
    </div>
    <div class="w-3/4 p-6" id="zone-details">
        <div class="text-center"><i class="fas fa-info-circle"></i> Please select a zone to view details.</div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
