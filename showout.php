<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Dashboard - Return card</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }

        .car-details {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
            margin-top: 20px;
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 4rem;
            margin-bottom: 40px;
            color: #ffd700;
            text-shadow: 0 0 20px #39ff14, 0 0 40px #ffd700;
            animation: pulsate 2s infinite alternate;
        }

        .data-card {
            margin-bottom: 20px;
            padding: 15px;
            border: 2px solid #ffd700;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .data-label {
            font-size: 1.4rem;
            color: #39ff14;
        }

        .data-value {
            font-size: 2rem;
            font-weight: bold;
            color: #ffd700;
        }
    </style>
</head>
<body>
    <h1>Return card</h1>
    <div class='car-details' id='car-details'>
        <!-- Data will be injected here via JavaScript -->
    </div>

    <script>
        // Function to fetch data and update the UI
        function fetchData() {
            fetch('showoutdata.php')
                .then(response => response.json())
                .then(data => {
                    const carDetails = document.getElementById('car-details');
                    
                    if (data) {
                        carDetails.innerHTML = `
                            <div class='data-card'>
                                <div class='data-label'>Card ID</div>
                                <div class='data-value'>${data.card_id}</div>
                            </div>
                            <div class='data-card'>
                                <div class='data-label'>ช่องจอด</div>
                                <div class='data-value'>${data.number}</div>
                            </div>
                            <div class='data-card'>
                                <div class='data-label'>User License Plate</div>
                                <div class='data-value'>${data.user_license_plate}</div>
                            </div>
                            <div class='data-card'>
                                <div class='data-label'>Time Out</div>
                                <div class='data-value'>${data.time_out}</div>
                            </div>
                        `;
                    } else {
                        carDetails.innerHTML = "<div class='data-card'>ไม่พบข้อมูล</div>";
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }

        // Fetch data initially
        fetchData();
        // Set interval to fetch data every 5 seconds
        setInterval(fetchData, 1000); // 5000 ms = 5 seconds
    </script>
</body>
</html>
