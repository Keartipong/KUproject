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
    <title>ข้อมูลช่องจอดล่าสุด</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --neon-blue: #00ffff;
            --neon-yellow: #ffd700;
            --neon-red: #ff0000;
            --dark-bg: #0a0a0a;
            --dashboard-bg: rgba(0, 0, 0, 0.7);
            --neon-green: #39ff14;
            --neon-purple: #9400d3;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Rajdhani', sans-serif;
            background-color: var(--dark-bg);
            color: #ffffff;
            overflow: hidden;
            height: 100vh;
            background-image: url('https://example.com/garage_background.jpg');
            background-size: cover;
            background-position: center;
            animation: backgroundZoom 10s linear infinite alternate;
        }

        @keyframes backgroundZoom {
            0% { background-size: 100%; }
            100% { background-size: 110%; }
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 5px solid var(--neon-blue);
            border-radius: 20px;
            box-shadow: 0 0 20px var(--neon-blue), 0 0 40px var(--neon-purple);
            animation: neonGlow 2s ease-in-out infinite alternate;
        }

        @keyframes neonGlow {
            0% { box-shadow: 0 0 15px var(--neon-purple); }
            100% { box-shadow: 0 0 30px var(--neon-yellow); }
        }

        .dashboard {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
        }
        
        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            margin-bottom: 40px;
            color: var(--neon-yellow);
            text-shadow: 0 0 20px var(--neon-blue), 0 0 40px var(--neon-red);
            animation: pulsate 2s infinite alternate;
        }

        .card {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
            max-width: 400px;
            background: var(--dashboard-bg);
            border: 2px solid var(--neon-yellow);
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 0 30px var(--neon-yellow), 0 0 50px var(--neon-purple);
            animation: fadeIn 1.5s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }

        .info-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--neon-blue);
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px var(--neon-green);
        }

        .data-label {
            font-size: 1.4rem;
            color: var(--neon-green);
        }

        .data-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--neon-yellow);
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h1>Parking Slot Details</h1>
    <div class="card">
        <div class="info-item">
            <strong class="data-label">การ์ดไอดี:</strong>
            <span id="card_id" class="data-value">Loading...</span>
        </div>
        <div class="info-item">
            <strong class="data-label">ป้ายทะเบียน:</strong>
            <span id="user_license_plate" class="data-value">Loading...</span>
        </div>
        <div class="info-item">
            <strong class="data-label">โซน:</strong>
            <span id="zone" class="data-value">Loading...</span>
        </div>
        <div class="info-item">
            <strong class="data-label">Bay:</strong>
            <span id="bay_name" class="data-value">Loading...</span>
        </div>
        <div class="info-item">
            <strong class="data-label">ช่องจอด:</strong>
            <span id="parking_slot" class="data-value">Loading...</span>
        </div>
    </div>
</div>

<script>
    let latestCardId = null; // To keep track of the last card ID
    let showDataTimeout;

    function fetchCarData() {
        // Pass lastCardId as a query parameter
        fetch(`fetch_data.php?lastCardId=${latestCardId}`)
            .then(response => response.json())
            .then(data => {
                if (data.card_id !== latestCardId && data.card_id !== 'No Data') {
                    latestCardId = data.card_id; // Update latestCardId

                    // Show new data
                    document.getElementById('card_id').textContent = data.card_id;
                    document.getElementById('user_license_plate').textContent = data.user_license_plate;
                    document.getElementById('zone').textContent = data.zone;
                    document.getElementById('bay_name').textContent = data.bay_name;
                    document.getElementById('parking_slot').textContent = data.parking_slot;

                    // Clear the previous timeout if any
                    clearTimeout(showDataTimeout);

                    // Set timeout to revert back to loading state after 5 seconds
                    showDataTimeout = setTimeout(() => {
                        document.getElementById('card_id').textContent = 'Loading...';
                        document.getElementById('user_license_plate').textContent = 'Loading...';
                        document.getElementById('zone').textContent = 'Loading...';
                        document.getElementById('bay_name').textContent = 'Loading...';
                        document.getElementById('parking_slot').textContent = 'Loading...';
                    }, 5000); // Show data for 5 seconds
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    setInterval(fetchCarData, 1000); // Fetch data every second
</script>



</body>
</html>
