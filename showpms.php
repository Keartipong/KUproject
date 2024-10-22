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
    <title>Car Dashboard - Car Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@300;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
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

        #threeJsCanvas {
            position: fixed;
            top: 0;
            left: 0;
            z-index: -1;
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

        @keyframes pulsate {
            0% { text-shadow: 0 0 15px var(--neon-yellow); }
            100% { text-shadow: 0 0 40px var(--neon-blue); }
        }

        .car-data {
    display: flex;
    flex-direction: column; /* เปลี่ยนจาก grid เป็น flexbox และตั้งค่าให้แสดงในแนวตั้ง */
    gap: 20px; /* ระยะห่างระหว่างการ์ด */
    width: 100%;
    max-width: 400px; /* ปรับขนาดให้เหมาะสมกับการแสดงในแนวตั้ง */
    background: var(--dashboard-bg);
    border: 2px solid var(--neon-yellow);
    border-radius: 25px;
    padding: 30px;
    box-shadow: 0 0 30px var(--neon-yellow), 0 0 50px var(--neon-purple);
    position: relative;
    z-index: 10;
    animation: fadeIn 1.5s ease-in-out;
}

        @keyframes fadeIn {
            0% { opacity: 0; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }

        .data-card {
            text-align: center;
            transition: transform 0.3s ease;
            padding: 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--neon-blue);
            position: relative;
            animation: flicker 2s infinite alternate;
        }

        .data-card:hover {
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

        .gauge {
            position: absolute;
            top: -15px;
            right: -15px;
            height: 50px;
            width: 50px;
            border-radius: 50%;
            border: 3px solid var(--neon-yellow);
            box-shadow: 0 0 15px var(--neon-yellow);
            animation: spinGauge 2s linear infinite;
        }

        @keyframes flicker {
            0% { box-shadow: 0 0 10px var(--neon-green); }
            100% { box-shadow: 0 0 20px var(--neon-blue); }
        }

        @keyframes spinGauge {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Tire track animation */
        .tire-track {
            position: fixed;
            bottom: 10%;
            left: 0;
            width: 100%;
            height: 5px;
            background-image: url('https://example.com/tire_tracks.png');
            background-size: contain;
            animation: tireTrackMove 5s linear infinite;
            z-index: 1;
        }

        .rpm-visualizer {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @keyframes tireTrackMove {
            0% { background-position: 0; }
            100% { background-position: 100%; }
        }

        .rpm-value {
            font-size: 1.5rem;
            color: var(--neon-yellow);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: opacity 0.5s ease-out;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid var(--neon-yellow);
            border-top: 5px solid var(--neon-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* RPM slider */
        .rpm-slider {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 20;
        }

        .rpm-slider input[type="range"] {
            width: 300px;
        }

        .rpm-value {
            font-size: 1.5rem;
            color: var(--neon-yellow);
            margin-left: 10px;
        }
    </style>
</head>
<body onload="fetchData()"> <!-- เรียก fetchData เมื่อหน้าโหลด -->
    <canvas id="threeJsCanvas"></canvas>
    <div class="tire-track"></div>
    <div class="dashboard">
        <h1>Car Details</h1>
        <div class="car-data">
            <div class="data-card">
                <div class="data-label">Card ID</div>
                <div class="data-value" id="card_id">Not Found</div>
                <div class="gauge"></div>
            </div>
            <div class="data-card">
                <div class="data-label">ป้ายทะเบียน</div>
                <div class="data-value" id="user_license_plate">Not Found</div>
                <div class="gauge"></div>
            </div>
            <div class="data-card">
                <div class="data-label">เวลา</div>
                <div class="data-value" id="timestamp">Not Found</div>
                <div class="gauge"></div>
            </div>
        </div>
    </div>

    <!-- RPM Slider -->
    <div class="rpm-slider">
        <input type="range" min="0" max="7000" step="100" id="rpmRange">
        <span class="rpm-value" id="rpmValue">0 RPM</span>
    </div>

    <script>
        function fetchData() {
    fetch('showdata.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('card_id').textContent = data.card_id || 'Not Found'; 
            document.getElementById('user_license_plate').textContent = data.user_license_plate || 'Not Found';  
            document.getElementById('timestamp').textContent = data.timestamp || 'Not Found';                
            
            // อัพเดท currentData ให้เป็นค่าที่เพิ่งดึงมา
            currentData = JSON.stringify(data); // ใช้ JSON.stringify เพื่อเปลี่ยนข้อมูลให้เป็น string
            showData(currentData); // เรียกใช้ showData หลังจากดึงข้อมูลใหม่
        })
        .catch(error => console.error('Error fetching data:', error));
}

let currentData = "No new data";

function showData(newData) {
    const displayElement = document.getElementById('data-display');
    // แสดงข้อมูลใหม่
    displayElement.textContent = newData;

    // หลังจาก 5 วินาที เปลี่ยนเป็น "on data" ถ้าไม่มีข้อมูลใหม่
    setTimeout(() => {
        if (displayElement.textContent === newData) {
            displayElement.textContent = "on data";
        }
    }, 5000);
}

// เรียกใช้ fetchData ทุกๆ 1 วินาที
setInterval(fetchData, 1000); // ทุกๆ 1 วินาที


        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas: document.getElementById('threeJsCanvas'), alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);

        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0x00ffff, 1);
        directionalLight.position.set(5, 5, 5);
        scene.add(directionalLight);

        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.25;
        controls.enableZoom = false;

        const particlesGeometry = new THREE.BufferGeometry();
        const particlesCount = 5000;
        const posArray = new Float32Array(particlesCount * 3);

        for (let i = 0; i < particlesCount * 3; i++) {
            posArray[i] = (Math.random() - 0.5) * 10;
        }

        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
        const particlesMaterial = new THREE.PointsMaterial({
            size: 0.005,
            color: 0x00ffff,
        });

        const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
        scene.add(particlesMesh);

        camera.position.z = 5;

        function animate() {
            requestAnimationFrame(animate);
            particlesMesh.rotation.y += 0.001;
            controls.update();
            renderer.render(scene, camera);
        }

        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Engine RPM Sound Control
        const engineSound = new Audio('https://example.com/car_engine.mp3');
        const rpmRange = document.getElementById('rpmRange');
        const rpmValue = document.getElementById('rpmValue');

        rpmRange.addEventListener('input', () => {
            const rpm = rpmRange.value;
            rpmValue.textContent = `${rpm} RPM`;
            engineSound.playbackRate = rpm / 7000 + 0.5; // Adjust playback speed based on RPM
        });

        window.onload = () => engineSound.play();
    </script>
</body>
</html>