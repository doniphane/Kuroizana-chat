<?php
require './includes/config.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Tous les champs sont requis.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $errors[] = "Nom d'utilisateur déjà pris.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Access</title>
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

        .error-message {
            border-left: 3px solid #ff2b4e;
            background-color: rgba(255, 43, 78, 0.1);
        }

        .success-message {
            border-left: 3px solid #00ff41;
            background-color: rgba(0, 255, 65, 0.1);
        }

        .password-strength {
            height: 4px;
            background-color: #2d3748;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 4px;
        }

        .strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak {
            background-color: #ff2b4e;
            width: 25%;
        }

        .strength-medium {
            background-color: #ffa500;
            width: 50%;
        }

        .strength-good {
            background-color: #ffff00;
            width: 75%;
        }

        .strength-strong {
            background-color: #00ff41;
            width: 100%;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Scan Line Effect -->
    <div class="scan-line"></div>

    <!-- Register Terminal -->
    <div class="terminal rounded-lg w-full max-w-md p-6 relative">
        <!-- Terminal Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex space-x-2">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            </div>
            <div class="text-xs text-green-400">create_user.sh</div>
        </div>

        <!-- Terminal Title -->
        <div class="mb-6">
            <div class="text-green-400 text-sm mb-1">$ ./new_account.sh</div>
            <h1 class="text-2xl font-bold text-green-400 mb-2">CRÉER UN ACCÈS</h1>
            <div class="text-xs text-gray-400">Générer de nouveaux identifiants système</div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="error-message p-3 mb-4 rounded text-red-400 text-sm">
                <div class="flex items-center mb-1">
                    <span class="mr-2">⚠️</span>
                    <span>ERREUR DE CRÉATION</span>
                </div>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li class="ml-6">$ <?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form method="POST" class="space-y-4" id="registerForm">
            <div>
                <label class="block mb-1 text-xs text-gray-400">NOUVEL IDENTIFIANT</label>
                <div class="flex items-center bg-[#1a1e24] border border-[#2d3748] rounded overflow-hidden">
                    <span class="text-green-400 px-3">@</span>
                    <input type="text" name="username" id="username"
                        class="w-full py-2 px-1 bg-transparent text-green-400 focus:outline-none" required>
                </div>
                <div class="text-xs text-gray-500 mt-1" id="usernameStatus"></div>
            </div>

            <div>
                <label class="block mb-1 text-xs text-gray-400">MOT DE PASSE SÉCURISÉ</label>
                <div class="flex items-center bg-[#1a1e24] border border-[#2d3748] rounded overflow-hidden">
                    <span class="text-green-400 px-3">#</span>
                    <input type="password" name="password" id="password"
                        class="w-full py-2 px-1 bg-transparent text-green-400 focus:outline-none" required>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="text-xs mt-1" id="strengthText">Force du mot de passe</div>
            </div>

            <div>
                <label class="block mb-1 text-xs text-gray-400">CONFIRMER MOT DE PASSE</label>
                <div class="flex items-center bg-[#1a1e24] border border-[#2d3748] rounded overflow-hidden">
                    <span class="text-green-400 px-3">#</span>
                    <input type="password" name="confirm_password" id="confirmPassword"
                        class="w-full py-2 px-1 bg-transparent text-green-400 focus:outline-none" required>
                </div>
                <div class="text-xs mt-1" id="confirmStatus"></div>
            </div>

            <button type="submit" class="btn-hack w-full py-3 rounded font-medium flex items-center justify-center"
                id="submitBtn">
                <span class="mr-2">[</span>
                CRÉER ACCÈS
                <span class="ml-2">]</span>
            </button>

            <div class="text-center text-xs text-gray-400 pt-2">
                <span>Déjà un accès?</span>
                <a href="login.php" class="text-green-400 hover:underline ml-1">Se connecter</a>
            </div>
        </form>

        <!-- Security Info -->
        <div class="mt-6 p-3 bg-black/30 rounded text-xs text-gray-400">
            <div class="flex items-center mb-2">
                <span class="text-green-400 mr-2">🔒</span>
                <span>SÉCURITÉ</span>
            </div>
            <ul class="space-y-1 ml-4">
                <li>• Chiffrement AES-256</li>
                <li>• Hachage sécurisé</li>
                <li>• Connexion anonyme</li>
            </ul>
        </div>

        <!-- Terminal Footer -->
        <div class="mt-6 pt-4 border-t border-gray-700 flex items-center justify-between text-xs text-gray-500">
            <div>v1.0.0</div>
            <div class="flex items-center">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                <span>Système sécurisé</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const usernameStatus = document.getElementById('usernameStatus');
            const confirmStatus = document.getElementById('confirmStatus');
            const submitBtn = document.getElementById('submitBtn');

            // Focus on username field
            usernameInput.focus();

            // Username validation
            usernameInput.addEventListener('input', function () {
                const username = this.value;
                if (username.length < 3) {
                    usernameStatus.textContent = '⚠️ Minimum 3 caractères';
                    usernameStatus.className = 'text-xs text-red-400 mt-1';
                } else if (username.length >= 3) {
                    usernameStatus.textContent = '✓ Identifiant valide';
                    usernameStatus.className = 'text-xs text-green-400 mt-1';
                }
            });

            // Password strength checker
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                const strength = calculatePasswordStrength(password);

                strengthBar.className = 'strength-bar';

                if (strength < 2) {
                    strengthBar.classList.add('strength-weak');
                    strengthText.textContent = 'Faible';
                    strengthText.className = 'text-xs mt-1 text-red-400';
                } else if (strength < 3) {
                    strengthBar.classList.add('strength-medium');
                    strengthText.textContent = 'Moyen';
                    strengthText.className = 'text-xs mt-1 text-orange-400';
                } else if (strength < 4) {
                    strengthBar.classList.add('strength-good');
                    strengthText.textContent = 'Bon';
                    strengthText.className = 'text-xs mt-1 text-yellow-400';
                } else {
                    strengthBar.classList.add('strength-strong');
                    strengthText.textContent = 'Fort';
                    strengthText.className = 'text-xs mt-1 text-green-400';
                }
            });

            // Confirm password validation
            confirmPasswordInput.addEventListener('input', function () {
                const password = passwordInput.value;
                const confirmPassword = this.value;

                if (confirmPassword === '') {
                    confirmStatus.textContent = '';
                } else if (password === confirmPassword) {
                    confirmStatus.textContent = '✓ Mots de passe identiques';
                    confirmStatus.className = 'text-xs mt-1 text-green-400';
                } else {
                    confirmStatus.textContent = '⚠️ Mots de passe différents';
                    confirmStatus.className = 'text-xs mt-1 text-red-400';
                }
            });

            function calculatePasswordStrength(password) {
                let strength = 0;

                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;

                return strength;
            }

            // Form submission with loading effect
            document.getElementById('registerForm').addEventListener('submit', function () {
                submitBtn.innerHTML = '<span class="mr-2">[</span>CRÉATION...<span class="ml-2">]</span>';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>

</html>