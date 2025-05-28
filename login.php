<?php
require './includes/config.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: chat.php");
        exit;
    } else {
        $errors[] = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&display=swap');

        body {
            font-family: 'Fira Code', monospace;
            background-color: #0a0e12;
            color: #e2e8f0;
        }

        .terminal {
            background-color: #0f1419;
            border: 1px solid #00ff41;
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.3);
        }

        .input-field {
            background-color: #1a1e24;
            border: 1px solid #2d3748;
            color: #00ff41;
            caret-color: #00ff41;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            border-color: #00ff41;
            box-shadow: 0 0 0 1px rgba(0, 255, 65, 0.3);
            outline: none;
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

        .cursor-blink {
            animation: blink 1s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .error-message {
            border-left: 3px solid #ff2b4e;
            background-color: rgba(255, 43, 78, 0.1);
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Scan Line Effect -->
    <div class="scan-line"></div>

    <!-- Login Terminal -->
    <div class="terminal rounded-lg w-full max-w-md p-6 relative">
        <!-- Terminal Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            </div>
            <div class="text-xs text-green-400">secure_login.sh</div>
        </div>

        <!-- Terminal Title -->
        <div class="mb-6">
            <div class="text-green-400 text-sm mb-1">$ ./authenticate.sh</div>
            <h1 class="text-2xl font-bold text-green-400 mb-2">AUTHENTIFICATION</h1>
            <div class="text-xs text-gray-400">Entrez vos identifiants pour accéder au système</div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="error-message p-3 mb-4 rounded text-red-400 text-sm">
                <div class="flex items-center mb-1">
                    <span class="mr-2">⚠️</span>
                    <span>ERREUR D'AUTHENTIFICATION</span>
                </div>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li class="ml-6">$ <?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" class="space-y-4">
            <div>
                <label class="block mb-1 text-xs text-gray-400">IDENTIFIANT</label>
                <div class="flex items-center bg-[#1a1e24] border border-[#2d3748] rounded overflow-hidden">
                    <span class="text-green-400 px-3">$</span>
                    <input type="text" name="username"
                        class="w-full py-2 px-1 bg-transparent text-green-400 focus:outline-none" required>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-xs text-gray-400">MOT DE PASSE</label>
                <div class="flex items-center bg-[#1a1e24] border border-[#2d3748] rounded overflow-hidden">
                    <span class="text-green-400 px-3">*</span>
                    <input type="password" name="password"
                        class="w-full py-2 px-1 bg-transparent text-green-400 focus:outline-none" required>
                </div>
            </div>

            <button type="submit" class="btn-hack w-full py-3 rounded font-medium flex items-center justify-center">
                <span class="mr-2">[</span>
                CONNEXION
                <span class="ml-2">]</span>
            </button>

            <div class="text-center text-xs text-gray-400 pt-2">
                <span>Pas encore d'accès?</span>
                <a href="register.php" class="text-green-400 hover:underline ml-1">Créer un compte</a>
            </div>
        </form>

        <!-- Terminal Footer -->
        <div class="mt-6 pt-4 border-t border-gray-700 flex items-center justify-between text-xs text-gray-500">
            <div>v1.0.0</div>
            <div class="flex items-center">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                <span>Connexion sécurisée</span>
            </div>
        </div>
    </div>

    <script>
        // Add terminal typing effect
        document.addEventListener('DOMContentLoaded', function () {
            // Focus on username field
            document.querySelector('input[name="username"]').focus();

            // Add cursor blink to inputs on focus
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    this.classList.add('cursor-blink');
                });

                input.addEventListener('blur', function () {
                    this.classList.remove('cursor-blink');
                });
            });

            // Add typing sound effect (optional)
            const addTypingSound = () => {
                inputs.forEach(input => {
                    input.addEventListener('keydown', function () {
                        // You could add a subtle typing sound here
                        // For now we'll just add a visual feedback
                        this.style.textShadow = '0 0 5px rgba(0, 255, 65, 0.8)';
                        setTimeout(() => {
                            this.style.textShadow = 'none';
                        }, 100);
                    });
                });
            };

            addTypingSound();
        });
    </script>
</body>

</html>