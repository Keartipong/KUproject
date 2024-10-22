<?php
session_start(); 

if (isset($_SESSION['logged_in'])) {
    unset($_SESSION['logged_in']); // Remove logged in state
}

$host = '151.106.124.154';
$db = 'u583789277_wag7';
$user = 'u583789277_wag7'; 
$pass = '2567Concept';     
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $employee_id = trim($_POST['employee_id'] ?? '');


    if (empty($username) || empty($password) || empty($employee_id)) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    } else {

        $user_ip = $_SERVER['REMOTE_ADDR'];
    
        if ($user_ip === '::1') {
            $user_ip = '192.168.1.55'; 
        }
        // แสดง IP Address
        echo "<script>alert('IP ของคุณคือ: $user_ip');</script>";
 
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND employee_id = ?');
        $stmt->execute([$username, $employee_id]);
        $user = $stmt->fetch();
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            echo "<script>alert('คุณล็อกอินแล้ว');</script>";
        
            exit;
        }


        if ($user && hash('sha256', $password) === $user['password'] && $user_ip === $user['ip_address']) {
            $_SESSION['logged_in'] = true;
            echo "<script>alert('Login successful');</script>";
            header("Location: ParkingInfo.php"); // ไปยังหน้าถัดไป
            exit;
        } else {
            echo "<script>alert('แจ้งเตือน: ข้อมูลเข้าสู่ระบบไม่ถูกต้อง');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantum Car Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.7.14/lottie.min.js"></script>
    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background: radial-gradient(circle at 20% 20%, #000000, #1a1a1a, #000000);
            margin: 0;
            overflow: hidden;
            color: #00f7ff;
        }
        .alert {
            color: red; /* เปลี่ยนสีข้อความ */
            background-color: #f8d7da; /* สีพื้นหลัง */
            border: 1px solid #f5c6cb; /* ขอบสี */
            padding: 10px; /* ขนาด padding */
            border-radius: 5px; /* ขอบมน */
            margin-bottom: 15px; /* ระยะห่างด้านล่าง */
            display: none; /* ซ่อนเมื่อไม่มีข้อความ */
        }
        #matrix-background {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -10;
            background-color: black;
            overflow: hidden;
        }

        canvas {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -2;
            pointer-events: none;
        }

        #particles-js, #quantum-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, rgba(0, 0, 0, 0.8), rgba(20, 20, 20, 0.9)), url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: blur(2px);
        }
        @keyframes login-container {
    0% {
        transform: translateX(-100%); /* Start off-screen to the left */
        opacity: 0; /* Invisible at start */
    }
    100% {
        transform: translateX(0); /* End at its original position */
        opacity: 1; /* Fully visible at the end */
    }
}

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        100% {
            transform: scale(1.05); /* Slightly scale up */
        }
    }
    .container {
    position: relative;
    width: 300px;
    height: 200px;
    overflow: hidden;
    border: 2px solid #000;
}

.small-frame {
    width: 100%;
    height: 100%;
    position: absolute;
    background: #f0f0f0;
    transition: transform 0.5s ease;
}

.lift-control {
    position: absolute;
    width: 100%;
    background: #4CAF50;
    color: white;
    text-align: center;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    transform: translateY(100%); /* เริ่มจากด้านล่าง */
    transition: transform 0.5s ease;
}

    .login-container {
        position: relative; 
        z-index: 10;
        backdrop-filter: blur(12px);
        background-color: rgba(0, 0, 0, 0.85);
        border: 2px solid #00f7ff;
        box-shadow: 0 0 25px #00f7ff, inset 0 0 20px #ff00ff;
        padding: 40px;
        border-radius: 20px;
        transition: 0.5s ease;
        perspective: 1000px;
        animation: login-container 0.6s ease-out, pulse 2.5s infinite alternate; /* Combine animations */
    }


        @keyframes glitch {
            0% { text-shadow: 2px 2px 0px #ff00ff, -2px -2px 0px #00f7ff; }
            20% { text-shadow: -2px 2px 0px #ff00ff, 2px -2px 0px #00f7ff; }
            40% { text-shadow: 2px -2px 0px #ff00ff, -2px 2px 0px #00f7ff; }
            60% { text-shadow: -2px -2px 0px #ff00ff, 2px 2px 0px #00f7ff; }
            80% { text-shadow: 2px 2px 0px #ff00ff, -2px -2px 0px #00f7ff; }
            100% { text-shadow: 0 0 8px #ff00ff, 0 0 15px #00f7ff; }
        }

        .glitch:hover {
            animation: glitch 1s infinite;
        }

        .login-title {
            text-align: center;
            font-size: 3rem;
            animation: neonPulse 2s ease-in-out infinite;
        }

        .input-group input:hover {
            filter: contrast(2);
            animation: inputDistort 0.5s ease-out forwards;
        }

        @keyframes inputDistort {
            0% { transform: scale(1); filter: hue-rotate(0deg); }
            50% { transform: scale(1.1); filter: hue-rotate(360deg); }
            100% { transform: scale(1); filter: hue-rotate(0deg); }
        }

        .cyber-button:hover {
            background: linear-gradient(45deg, #ff00ff, #00f7ff);
            box-shadow: 0 0 30px #ff00ff, 0 0 60px #00f7ff, 0 0 90px #00f7ff;
            transform: translateZ(10px);
            filter: contrast(1.5);
            animation: buttonDistort 0.5s ease-out forwards;
        }

        @keyframes buttonDistort {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }


        body::before {
            content: '';
            position: fixed;
            width: 15px;
            height: 15px;
            background-color: #00f7ff;
            border-radius: 50%;
            pointer-events: none;
            transform: translate(-50%, -50%);
            transition: all 0.1s ease;
        }

        body:hover::before {
            transition: 0s;
        }

        /* Flying particles and 3D hologram car */
        @keyframes fly {
            0% { left: -120px; top: 80%; }
            50% { left: 50%; top: 20%; }
            100% { left: 110%; top: 80%; }
        }

        /* Shake error message */
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
        #particles-js, #quantum-particles { 
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(45deg, rgba(0, 0, 0, 0.8), rgba(20, 20, 20, 0.9)), url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: blur(2px);
        }

        /* Enhanced login container */
        .login-container {
            position: relative; 
            z-index: 10; 
            backdrop-filter: blur(12px); 
            background-color: rgba(0, 0, 0, 0.85); 
            border: 2px solid #00f7ff; 
            box-shadow: 0 0 25px #00f7ff, inset 0 0 20px #ff00ff; 
            animation: pulse 2.5s infinite alternate; 
            padding: 40px; 
            border-radius: 20px; 
            transition: 0.5s ease;
        }

        .login-container:hover {
            transform: scale(1.05); 
            box-shadow: 0 0 40px #ff00ff, inset 0 0 30px #00f7ff;
        }

        @keyframes pulse {
            0%, 100% { 
                box-shadow: 0 0 25px #00f7ff, inset 0 0 15px #ff00ff; 
            }
            50% { 
                box-shadow: 0 0 40px #00f7ff, inset 0 0 25px #ff00ff; 
            }
        }

        @keyframes neonPulse {
            0%, 100% {
                text-shadow: 0 0 8px #ff00ff, 0 0 15px #00f7ff;
            }
            50% {
                text-shadow: 0 0 18px #ff00ff, 0 0 35px #00f7ff;
            }
        }
        .login-title {
            text-align: center;
            font-size: 3rem;
            animation: neonPulse 2s ease-in-out infinite;
        }
        .input-group { 
            position: relative; 
            margin-bottom: 30px; 
        }

        .input-group input { 
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: none;
            border-bottom: 3px solid #00f7ff;
            background-color: rgba(0, 247, 255, 0.15);
            color: #fff;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.4s ease;
            border-radius: 5px;
            box-shadow: 0 0 12px #00f7ff, inset 0 0 15px #ff00ff;
        }

        .input-group input:focus { 
            outline: none; 
            border-color: #ff00ff; 
            box-shadow: 0 0 20px #ff00ff, inset 0 0 20px #00f7ff; 
        }

        .input-group label { 
            position: absolute; 
            top: 0; 
            left: 10px; 
            padding: 12px; 
            color: #00f7ff; 
            transition: all 0.4s ease; 
            pointer-events: none; 
        }

        .input-group input:focus + label, 
        .input-group input:not(:placeholder-shown) + label { 
            top: -25px; 
            font-size: 14px; 
            color: #ff00ff; 
        }

        .input-group i { 
            position: absolute; 
            right: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #00f7ff; 
            transition: all 0.4s; 
        }

        .input-group input:focus + i { 
            color: #ff00ff; 
        }

        /* Enhanced button with 3D effect */
        .cyber-button { 
            position: relative; 
            padding: 16px 40px; 
            border: none; 
            background: linear-gradient(45deg, #00f7ff, #ff00ff); 
            color: #fff; 
            font-family: 'Orbitron', sans-serif; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            overflow: hidden; 
            transition: 0.5s; 
            cursor: pointer; 
            border-radius: 50px; 
            box-shadow: 0 0 25px #00f7ff, 0 0 50px #ff00ff, 0 0 75px #ff00ff; 
            transform: translateZ(0);
        }

        .cyber-button:hover { 
            background: linear-gradient(45deg, #ff00ff, #00f7ff); 
            box-shadow: 0 0 30px #ff00ff, 0 0 60px #00f7ff, 0 0 90px #00f7ff; 
            transform: translateZ(10px);
        }

        .cyber-button span { 
            position: absolute; 
            display: block; 
        }

        .cyber-button span:nth-child(1) { 
            top: 0; left: -100%; 
            width: 100%; 
            height: 2px; 
            background: linear-gradient(90deg, transparent, #00f7ff); 
            animation: btn-anim1 1s linear infinite; 
        }

        @keyframes btn-anim1 { 
            0% { left: -100%; } 
            50%, 100% { left: 100%; } 
        }

        .cyber-button span:nth-child(2) { 
            top: -100%; right: 0; 
            width: 2px; 
            height: 100%; 
            background: linear-gradient(180deg, transparent, #00f7ff); 
            animation: btn-anim2 1s linear infinite; 
            animation-delay: .25s; 
        }

        @keyframes btn-anim2 { 
            0% { top: -100%; } 
            50%, 100% { top: 100%; } 
        }

        .cyber-button span:nth-child(3) { 
            bottom: 0; right: -100%; 
            width: 100%; 
            height: 2px; 
            background: linear-gradient(270deg, transparent, #00f7ff); 
            animation: btn-anim3 1s linear infinite; 
            animation-delay: .5s; 
        }

        @keyframes btn-anim3 { 
            0% { right: -100%; } 
            50%, 100% { right: 100%; } 
        }

        .cyber-button span:nth-child(4) { 
            bottom: -100%; left: 0; 
            width: 2px; 
            height: 100%; 
            background: linear-gradient(360deg, transparent, #00f7ff); 
            animation: btn-anim4 1s linear infinite; 
            animation-delay: .75s; 
        }

        /* Error message */
        #error-message { 
            color: #ff0000; 
            text-align: center; 
            margin-top: 10px; 
            animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both; 
        }

        /* Special animations for icons */
        .flying-car { 
            position: absolute; 
            width: 120px; 
            height: 60px; 
            background: url('car-icon.png') no-repeat center center; 
            background-size: contain; 
            animation: fly 8s linear infinite; 
        }

    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div id="particles-js"></div>
    <div class="flying-car"></div>
    <div class="min-h-screen flex items-center justify-center">
        <div class="login-container">
            <h1 class="text-3xl text-center mb-8 glitch">Parking Lift Control System</h1>
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder=" " required>
                    <label for="username">Username</label>
                    <i class="fa fa-user"></i>
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder=" " required>
                    <label for="password">Password</label>
                    <i class="fa fa-lock"></i>
                </div>

                <div class="input-group">
                    <input type="text" name="employee_id" placeholder=" " required>
                    <label for="employee_id">Employee ID</label>
                    <i class="fa fa-id-badge"></i>
                </div>

                <button class="cyber-button" type="submit">
                    <span></span><span></span><span></span><span></span>
                    Login
                </button>
            </form>
        </div>
    </div>

    <div id="lottie-container"></div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script>
        
// Matrix Rain Effect (Code Rain)
const canvas = document.getElementById('matrix-canvas');
const ctx = canvas.getContext('2d');

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

const matrixChars = "01"; // Characters to be displayed in rain
const fontSize = 16;
const columns = canvas.width / fontSize;
const drops = [];

// Initialize drops for each column
for (let x = 0; x < columns; x++) {
    drops[x] = 1;
}

// Draw the rain
function drawMatrix() {
    ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#00f7ff"; // Matrix text color
    ctx.font = `${fontSize}px 'Orbitron'`;

    for (let i = 0; i < drops.length; i++) {
        const text = matrixChars[Math.floor(Math.random() * matrixChars.length)];
        ctx.fillText(text, i * fontSize, drops[i] * fontSize);

        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
            drops[i] = 0; // Reset drop
        }

        drops[i]++;
    }
}

// Animate the matrix rain effect
setInterval(drawMatrix, 35);

// Light trail following the mouse
document.addEventListener('mousemove', (e) => {
    const lightTrail = document.querySelector('body::before');
    gsap.to(lightTrail, { top: `${e.clientY}px`, left: `${e.clientX}px`, duration: 0.2 });
});

</script>

<script>
    particlesJS('particles-js', {
        particles: { 
            number: { value: 80, density: { enable: true, value_area: 800 } }, 
            color: { value: "#00f7ff" }, 
            shape: { type: "circle" }, 
            opacity: { value: 0.5, random: true },
            size: { value: 3, random: true }, 
            line_linked: { enable: true, distance: 150, color: "#00f7ff", opacity: 0.4, width: 1 },
            move: { enable: true, speed: 6, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
        },
        interactivity: { 
            detect_on: "canvas", 
            events: { 
                onhover: { enable: true, mode: "repulse" }, 
                onclick: { enable: true, mode: "push" }, 
                resize: true 
            },
            modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
        },
        retina_detect: true
    });

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, 1, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ alpha: true });
    renderer.setSize(300, 300);
    document.getElementById('carHologram').appendChild(renderer.domElement);

    const geometry = new THREE.BoxGeometry(1, 0.5, 2);
    const material = new THREE.MeshBasicMaterial({ color: 0x00f7ff, wireframe: true });
    const car = new THREE.Mesh(geometry, material);
    scene.add(car);

    camera.position.z = 3;

    function animate() {
        requestAnimationFrame(animate);
        car.rotation.y += 0.01;
        car.rotation.x += 0.005;
        renderer.render(scene, camera);
    }
    animate();

    gsap.from(".login-container", { duration: 1, y: 50, opacity: 0, ease: "power3.out" });
    gsap.from("input, button", { duration: 0.5, y: 20, opacity: 0, stagger: 0.2, ease: "power2.out", delay: 0.5 });

    const lottieAnimation = lottie.loadAnimation({
        container: document.getElementById('lottie-container'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
        path: 'https://assets2.lottiefiles.com/packages/lf20_yyitq4uc.json'
    });

    const hologram = document.getElementById('carHologram');
    hologram.addEventListener('mousemove', (e) => {
        const rect = hologram.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width * 2 - 1;
        const y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        gsap.to(car.rotation, { x: y * 0.5, y: x * 0.5, duration: 0.5 });
    });

    hologram.addEventListener('mouseleave', () => {
        gsap.to(car.rotation, { x: 0, y: 0, duration: 0.5 });
    });

    const button = document.querySelector('.cyber-button');
    button.addEventListener('click', function(e) {
        let x = e.clientX - e.target.offsetLeft;
        let y = e.clientY - e.target.offsetTop;
        
        let ripples = document.createElement('span');
        ripples.style.left = x + 'px';
        ripples.style.top = y + 'px';
        this.appendChild(ripples);

        setTimeout(() => {
            ripples.remove();
        }, 1000);
    });

    gsap.to('.flying-car', {
        y: '+=20',
        duration: 2,
        repeat: -1,
        yoyo: true,
        ease: 'power1.inOut'
    });

    document.addEventListener('mousemove', (e) => {
        const loginContainer = document.querySelector('.login-container');
        const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
        const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
        gsap.to(loginContainer, { rotationY: xAxis, rotationX: yAxis, duration: 0.5 });
    });
    document.addEventListener("DOMContentLoaded", function() {
    const liftControl = document.getElementById('liftControl');

    // เริ่มต้นการแสดงเมื่อโหลดหน้า
    setTimeout(() => {
        liftControl.style.transform = 'translateY(0)'; // ขยับขึ้นมา
    }, 500); // รอ 0.5 วินาที
});
</script>
</body>
</html>
