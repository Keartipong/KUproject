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
    <title>Latest Parking Info</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        h1 {
            text-align: center;
            color: #00ff6a;
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 30px;
            text-shadow: 2px 2px 5px rgba(0, 255, 106, 0.7);
        }

        .section-header {
            font-size: 1.8em;
            font-weight: 600;
            color: #00ff6a;
            margin: 20px 0;
            text-align: center;
            width: 85%;
            max-width: 1000px;
            text-shadow: 2px 2px 5px rgba(0, 255, 106, 0.5);
        }

        .latest-info, table {
            width: 85%;
            max-width: 1000px;
            margin: 0 auto 20px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 255, 106, 0.2);
            background-color: #1c1c1c;
        }

        .latest-info {
            display: flex;
            justify-content: space-around;
            padding: 15px;
            background: #1f1f1f;
            border: 2px solid #00ff6a;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-box {
            flex: 1;
            margin: 0 10px;
            padding: 15px;
            border: 2px solid #00ff6a;
            border-radius: 8px;
            background: #1a1a1a;
            text-align: center;
        }

        .info-box h2 {
            font-size: 1.2em;
            color: #00ff6a;
            margin-bottom: 8px;
        }

        .info-box p {
            font-size: 1em;
            color: #fff;
            margin: 5px 0;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        th, td {
            padding: 20px;
            text-align: left;
            font-size: 1.4em;
            color: #fff;
        }

        th {
            background-color: #00ff6a;
            color: black;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        td {
            color: #b8fcb8;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #2a2a2a;
        }

        tr:hover {
            background-color: #1f3e30;
            transform: scale(1.01);
            transition: all 0.3s ease-in-out;
        }

        .starfield {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #00ff6a;
            border-radius: 50%;
            animation: twinkling 3s infinite;
        }

        @keyframes twinkling {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        @media only screen and (max-width: 768px) {
            .latest-info {
                flex-direction: column;
                align-items: center;
            }

            .info-box {
                margin: 10px 0;
                width: 90%;
            }

            th, td {
                font-size: 1em;
                padding: 15px;
            }

            h1 {
                font-size: 24px;
            }

            .section-header {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>

<div class="starfield" id="starfield"></div>

<h1>Bigmoniter</h1>

<div class="section-header">Latest Parking Info</div>
<div class="latest-info">
    <div class="info-box">
        <h2>Card ID</h2>
        <p><?= htmlspecialchars($result['card_id'] ?? 'No Data') ?></p>
    </div>
    <div class="info-box">
        <h2>License Plate</h2>
        <p><?= htmlspecialchars($result['user_license_plate'] ?? 'No Data') ?></p>
    </div>
    <div class="info-box">
        <h2>Zone</h2>
        <p><?= htmlspecialchars($result['parked_zone'] ?? 'No Data') ?></p>
    </div>
    <div class="info-box">
        <h2>Parking Spot</h2>
        <p><?= htmlspecialchars($result['number'] ?? 'No Data') ?></p>
    </div>
</div>

<div class="section-header">Parking Information</div>
<table>
    <thead>
        <tr>
            <th>NO.</th>
            <th>Card ID</th>
            <th>License Plate</th>
            <th>Zone</th>
            <th>Parking Slot</th>
        </tr>
    </thead>
    <tbody>
        <?php $index = 1; ?>
        <?php foreach ($cards as $card): ?>
        <tr>
            <td><?= $index++ ?></td>
            <td><?= htmlspecialchars($card['card_id']) ?></td>
            <td><?= htmlspecialchars($card['user_license_plate']) ?></td>
            <td><?= htmlspecialchars($card['parked_zone']) ?></td>
            <td><?= htmlspecialchars($card['number']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    setTimeout(function() {
        window.location.reload(1);
    }, 10000);

    function createStar() {
        const star = document.createElement("div");
        star.classList.add("star");
        star.style.width = `${Math.random() * 3}px`;
        star.style.height = `${Math.random() * 3}px`;
        star.style.top = `${Math.random() * 100}vh`;
        star.style.left = `${Math.random() * 100}vw`;
        document.getElementById("starfield").appendChild(star);

        setTimeout(() => {
            star.remove();
        }, 3000);
    }

    setInterval(createStar, 100);
</script>
    <!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
