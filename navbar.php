<?php
session_start();
require_once './includes/functions.php';

$isLogged = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? null;

// Récupérer l'avatar de l'utilisateur si connecté
$userAvatar = null;
if ($isLogged) {
    try {
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userAvatar = $stmt->fetchColumn();
    } catch (Exception $e) {
        // En cas d'erreur, on continue sans avatar
    }
}

?>

<nav
    class="terminal-nav bg-[#0f1419] border-b border-[#00ff41] shadow-lg shadow-[#00ff41]/20 py-3 px-4 md:px-6 flex justify-between items-center sticky top-0 z-50">
    <!-- Logo et titre -->
    <div class="flex items-center space-x-3">
        <div class="flex space-x-1">
            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
        </div>
        <div class="text-xl font-bold text-[#00ff41] flex items-center">
            <span class="hidden md:inline text-xs text-gray-400 mr-2">$</span>
            <span class="cyber-font">Kuroizana_ CHAT</span>
            <span class="typing-cursor ml-1">_</span>
        </div>
    </div>

    <!-- Status et menu -->
    <div class="flex items-center space-x-4">
        <?php if ($isLogged): ?>
            <!-- Indicateur de connexion -->
            <div class="hidden md:flex items-center text-xs text-gray-400">
                <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                <span>CONNEXION SÉCURISÉE</span>
            </div>

            <!-- Menu utilisateur avec avatar -->
            <div class="flex items-center space-x-4">
                <!-- Avatar et nom d'utilisateur -->
                <div class="flex items-center space-x-3">
                    <!-- Avatar -->
                    <div class="relative">
                        <?php if ($userAvatar && file_exists($userAvatar)): ?>
                            <img src="<?= htmlspecialchars($userAvatar) ?>" alt="Avatar"
                                class="w-8 h-8 rounded-full border border-[#00ff41] shadow-sm">
                        <?php else:
                            $defaultAvatar = getDefaultAvatar($username);
                            ?>
                            <div class="w-8 h-8 rounded-full border border-[#00ff41] shadow-sm flex items-center justify-center text-sm font-bold text-white"
                                style="background: linear-gradient(135deg, <?= $defaultAvatar['color'] ?>, <?= $defaultAvatar['color'] ?>88);">
                                <?= $defaultAvatar['letter'] ?>
                            </div>
                        <?php endif; ?>

                        <!-- Indicateur en ligne -->
                        <div
                            class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-[#0f1419] rounded-full">
                        </div>
                    </div>

                    <!-- Nom d'utilisateur -->
                    <span class="text-cyan-400 text-sm">
                        <span class="hidden md:inline">@</span><?= htmlspecialchars($username) ?>
                    </span>
                </div>

                <!-- Menu déroulant -->
                <div class="relative">
                    <button onclick="toggleUserMenu()" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="user-menu"
                        class="absolute right-0 mt-2 w-48 bg-[#1a1e24] border border-[#2d3748] rounded-lg shadow-lg shadow-black/50 hidden z-50">
                        <div class="py-2">
                            <a href="profile.php"
                                class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#2d3748] hover:text-white transition-colors">
                                <span class="mr-3">👤</span>
                                Mon Profil
                            </a>
                            <a href="chat.php"
                                class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-[#2d3748] hover:text-white transition-colors">
                                <span class="mr-3">💬</span>
                                Terminal Chat
                            </a>
                            <div class="border-t border-[#2d3748] my-1"></div>
                            <a href="logout.php"
                                class="flex items-center px-4 py-2 text-sm text-red-400 hover:bg-[#2d3748] hover:text-red-300 transition-colors">
                                <span class="mr-3">🚪</span>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Liens d'authentification -->
            <div class="flex items-center space-x-3">
                <a href="login.php"
                    class="nav-link text-[#00ff41] hover:text-white hover:bg-[#00ff41] px-3 py-1 rounded text-sm transition-all duration-300">
                    [CONNEXION]
                </a>
                <a href="register.php"
                    class="nav-link text-[#00ccff] hover:text-white hover:bg-[#00ccff] px-3 py-1 rounded text-sm transition-all duration-300">
                    [INSCRIPTION]
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Orbitron:wght@500;700&display=swap');

    .terminal-nav {
        font-family: 'Fira Code', monospace;
    }

    .cyber-font {
        font-family: 'Orbitron', sans-serif;
    }

    .typing-cursor {
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

    .nav-link {
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .nav-link:hover::before {
        left: 100%;
    }

    /* Scan line effect */
    .terminal-nav::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background-color: rgba(0, 255, 65, 0.5);
        opacity: 0.7;
        z-index: 10;
        animation: scan 3s linear infinite;
    }

    @keyframes scan {
        0% {
            transform: translateY(0);
        }

        100% {
            transform: translateY(100%);
        }
    }
</style>

<script>
    // Effet de glitch aléatoire sur le titre
    document.addEventListener('DOMContentLoaded', function () {
        const title = document.querySelector('.cyber-font');

        function glitchEffect() {
            if (Math.random() > 0.97) {
                title.style.textShadow = `
                -1px -1px 0 rgba(0, 255, 65, 0.7),
                1px 1px 0 rgba(255, 0, 65, 0.7)
            `;

                setTimeout(() => {
                    title.style.textShadow = '';
                }, 100);
            }
        }

        // Appliquer l'effet de glitch à intervalles aléatoires
        setInterval(glitchEffect, 100);
    });

    // Gestion du menu déroulant utilisateur
    function toggleUserMenu() {
        const menu = document.getElementById('user-menu');
        menu.classList.toggle('hidden');
    }

    // Fermer le menu si on clique ailleurs
    document.addEventListener('click', function (event) {
        const menu = document.getElementById('user-menu');
        const button = event.target.closest('button[onclick="toggleUserMenu()"]');

        if (!button && !menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>