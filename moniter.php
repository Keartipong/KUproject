<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
$buttons = [
    ['text' => 'Big Monitor', 'url' => 'https://wag7.bowlab.net/webcarlot/bigmoniter.php', 'icon' => 'car'],
    ['text' => 'Small Monitor', 'url' => 'https://wag7.bowlab.net/webcarlot/small.php', 'icon' => 'crown'],
    ['text' => 'Manage parking slots', 'url' => 'https://wag7.bowlab.net/webcarlot/lot.php', 'icon' => 'clock'],
    ['text' => 'Parking slot status', 'url' => 'https://wag7.bowlab.net/webcarlot/webtest.php', 'icon' => 'zap'],
];

function getIcon($name) {
    $icons = [
        'car' => '<img src="img/monitor2.png" alt="car icon" width="30" height="30">',
        'crown' => '<img src="img/monitor3.png" alt="car icon" width="30" height="30">',
        'zap' => '<img src="img/sedan.png" alt="car icon" width="30" height="30">',
        'clock' => '<img src="img/settings.png" alt="car icon" width="30" height="30">',
    ];
    return $icons[$name] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://unpkg.com/alpinejs@3.13.5/dist/cdn.min.js" defer></script>
    <style>
        @keyframes ledAnimation {
            0%, 100% { box-shadow: 0 0 5px #ff0000, 0 0 10px #ff0000, 0 0 15px #ff0000, 0 0 20px #ff0000; }
            25% { box-shadow: 0 0 5px #00ff00, 0 0 10px #00ff00, 0 0 15px #00ff00, 0 0 20px #00ff00; }
            50% { box-shadow: 0 0 5px #0000ff, 0 0 10px #0000ff, 0 0 15px #0000ff, 0 0 20px #0000ff; }
            75% { box-shadow: 0 0 5px #ffff00, 0 0 10px #ffff00, 0 0 15px #ffff00, 0 0 20px #ffff00; }
        }
        .led-effect {
            position: relative;
        }
        .led-effect::before {
            content: '';
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border-radius: inherit;
            animation: ledAnimation 5s linear infinite;
            z-index: -1;
        }
        body, html {
    margin: 0;
    padding: 0;
    font-family: 'Orbitron', sans-serif;
    background-color: #1a1a1a;
    color: white;
}

.container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
    background-size: cover;
    background-position: center;
    position: relative;
}

.container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
}

.content {
    background-color: rgba(0, 0, 0, 0.8);
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
    position: relative;
    z-index: 1;
    box-shadow: 0 0 5px rgba(255, 255, 255, 0.1);
}

h1 {
    font-size: 3rem;
    margin-bottom: 2rem;
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
}
.sidebar {
        position: fixed;
        left: 0; /* ชิดซ้ายสุด */
        width: 250px; /* ปรับขนาดความกว้างของ sidebar ตามที่ต้องการ */
            background-color: #003366;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.7);
                width: 250px;

    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100vh;
      }
      
.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    margin: 20px 0;
}

.button-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.car-button {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.car-button h3 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.car-button a {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #333;
    color: white;
    text-decoration: none;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.car-button a:hover {
    background-color: #555;
    transform: scale(1.05);
}

.car-button svg {
    width: 24px;
    height: 24px;
    margin-right: 0.5rem;
}

@keyframes rgb-glow {
    0% { box-shadow: 0 0 5px #ff0000; }
    33% { box-shadow: 0 0 5px #00ff00; }
    66% { box-shadow: 0 0 5px #0000ff; }
    100% { box-shadow: 0 0 5px #ff0000; }
}

.content {
    animation: rgb-glow 5s linear infinite;
}

@keyframes tilt {
    0%, 50%, 100% {
        transform: rotate(0deg);
    }
    25% {
        transform: rotate(0.5deg);
    }
    75% {
        transform: rotate(-0.5deg);
    }
}
@media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }
          }

.animate-tilt {
    animation: tilt 10s infinite linear;
}
    </style>
</head>


<body class="bg-gray-900 text-white">
    
    <div class="min-h-screen flex items-center justify-center bg-opacity-75 relative overflow-hidden">
    
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Car background" class="w-full h-full object-cover opacity-30">
        </div>
        <div x-data="{ show: false }" 
             x-init="setTimeout(() => show = true, 100)"
             :class="{ 'opacity-0 scale-90': !show, 'opacity-100 scale-100': show }"
             class="text-center z-10 p-12 bg-black bg-opacity-90 rounded-3xl shadow-2xl relative transition-all duration-500 led-effect">
            <div class="relative bg-black p-6 rounded-3xl">
                <h1 class="text-6xl font-black mb-12 text-white shadow-text relative z-10" style="font-family: 'Orbitron', sans-serif; text-shadow: 0 0 5px rgba(255,255,255,0.5);">
                Control  Monitor
                </h1>
                
                <div class="grid grid-cols-2 gap-12 relative z-10">
                    <?php foreach ($buttons as $index => $button): ?>
                        <div x-data="{ hover: false }" 
                             @mouseenter="hover = true" 
                             @mouseleave="hover = false"
                             class="flex flex-col items-center mb-8">
                            <h3 class="text-xl font-bold mb-2 text-white" style="font-family: 'Orbitron', sans-serif; text-shadow: 0 0 5px rgba(255,255,255,0.5);">
                                <?php echo $button['text']; ?>
                            </h3>
                            <div class="relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 rounded-xl blur opacity-75 transition duration-1000 animate-tilt"></div>
                                <button 
                                    class="relative bg-black text-white font-bold py-6 px-8 rounded-xl shadow-lg transform transition duration-300 ease-in-out hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-white overflow-hidden"
                                    :class="{ 'scale-105': hover, 'scale-100': !hover }"
                                    onclick="window.open('<?php echo $button['url']; ?>', '_blank')"
                                    style="font-family: 'Orbitron', sans-serif">
                                    <span class="relative z-10 flex items-center justify-center">
                                        <?php echo getIcon($button['icon']); ?>
                                        <span class="ml-2"><?php echo $button['text']; ?></span>
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-20"
                                         x-bind:style="{ transform: hover ? 'translateX(100%)' : 'translateX(-100%)' }"
                                         style="transition: transform 1.5s linear;"></div>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>