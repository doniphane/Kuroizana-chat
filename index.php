<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuroizana-chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;600&family=Orbitron:wght@500;700&display=swap');

        body {
            font-family: 'Fira Code', monospace;
            background-color: #0a0e12;
            color: #e2e8f0;
        }

        .cyber-font {
            font-family: 'Orbitron', sans-serif;
        }

        .terminal {
            background-color: #0f1419;
            border: 1px solid #00ff41;
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.3);
        }

        .glow-text {
            text-shadow: 0 0 5px rgba(0, 255, 65, 0.8);
        }

        .btn-hack {
            background-color: #0f1419;
            border: 1px solid #00ff41;
            color: #00ff41;
            transition: all 0.2s ease;
        }

        .btn-hack:hover {
            background-color: #00ff41;
            color: #0f1419;
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.5);
        }

        .btn-alt {
            background-color: #0f1419;
            border: 1px solid #ff2b4e;
            color: #ff2b4e;
            transition: all 0.2s ease;
        }

        .btn-alt:hover {
            background-color: #ff2b4e;
            color: #0f1419;
            box-shadow: 0 0 15px rgba(255, 43, 78, 0.5);
        }

        .matrix-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .matrix-char {
            position: absolute;
            color: #00ff41;
            font-size: 14px;
            opacity: 0;
            animation: fall linear infinite;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100%);
                opacity: 1;
            }

            80% {
                opacity: 0.5;
            }

            100% {
                transform: translateY(1000%);
                opacity: 0;
            }
        }

        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background-color: rgba(0, 255, 65, 0.5);
            opacity: 0.7;
            z-index: 10;
            animation: scan 3s linear infinite;
        }

        @keyframes scan {
            0% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(100vh);
            }
        }

        .glitch {
            position: relative;
        }

        .glitch::before,
        .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.8;
        }

        .glitch::before {
            animation: glitch-1 0.4s infinite;
            color: #ff2b4e;
            z-index: -1;
            clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%);
            transform: translate(-2px);
        }

        .glitch::after {
            animation: glitch-2 0.4s infinite;
            color: #00ccff;
            z-index: -2;
            clip-path: polygon(0 60%, 100% 60%, 100% 100%, 0 100%);
            transform: translate(2px);
        }

        @keyframes glitch-1 {

            0%,
            100% {
                opacity: 0.8;
            }

            50% {
                opacity: 0.3;
            }
        }

        @keyframes glitch-2 {

            0%,
            100% {
                opacity: 0.8;
            }

            50% {
                opacity: 0.3;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Matrix Background -->
    <div class="matrix-bg" id="matrixBg"></div>

    <!-- Scan Line Effect -->
    <div class="scan-line"></div>

    <!-- Main Content -->
    <div class="terminal rounded-lg w-full max-w-md p-6 relative z-10">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            </div>
            <div class="text-xs text-green-400">ENCRYPTED</div>
        </div>

        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <h1 class="cyber-font text-4xl font-bold text-green-400 mb-2 glitch" data-text="Kuroizana-chat">
                Kuroizana-chat
            </h1>
            <p class="text-sm text-gray-400">Messagerie sécurisée</p>
        </div>

        <!-- Features -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="text-center">
                <div class="text-2xl mb-2 text-green-400">🔒</div>
                <div class="text-xs text-gray-400">Chiffré</div>
            </div>
            <div class="text-center">
                <div class="text-2xl mb-2 text-green-400">⚡</div>
                <div class="text-xs text-gray-400">Rapide</div>
            </div>
            <div class="text-center">
                <div class="text-2xl mb-2 text-green-400">👤</div>
                <div class="text-xs text-gray-400">Anonyme</div>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-black/30 p-3 rounded mb-8 flex items-center justify-between">
            <div class="text-xs text-gray-400">Status:</div>
            <div class="text-xs text-green-400 flex items-center">
                <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                ONLINE
            </div>
        </div>

        <!-- Authentication Buttons -->
        <div class="space-y-0">
            <a href="register.php" class="btn-hack block w-full py-3 text-center rounded-t font-medium">
                CRÉER UN COMPTE
            </a>
            <a href="login.php" class="btn-alt block w-full py-3 text-center rounded-b font-medium">
                CONNEXION
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-gray-500">
            v1.0 • Kuroizana-chat
        </div>
    </div>

    <script>
        // Matrix Background Effect
        document.addEventListener('DOMContentLoaded', function () {
            const matrixBg = document.getElementById('matrixBg');
            const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';

            // Create initial characters
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    createMatrixChar();
                }, i * 100);
            }

            // Continue creating characters
            setInterval(createMatrixChar, 200);

            function createMatrixChar() {
                const char = document.createElement('div');
                char.className = 'matrix-char';
                char.textContent = chars[Math.floor(Math.random() * chars.length)];
                char.style.left = Math.random() * 100 + '%';
                char.style.animationDuration = (Math.random() * 3 + 2) + 's';

                matrixBg.appendChild(char);

                // Remove after animation completes
                setTimeout(() => {
                    char.remove();
                }, 5000);
            }

            // Glitch effect on title
            setInterval(() => {
                const title = document.querySelector('.glitch');
                title.style.animation = 'none';
                setTimeout(() => {
                    title.style.animation = '';
                }, 100);
            }, 3000);
        });
    </script>
</body>

</html>