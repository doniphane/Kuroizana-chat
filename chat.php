<?php
require './includes/config.php';
require './includes/session.php';

$userId = $_SESSION['user_id'];

// Met à jour la dernière activité
$pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$userId]);

// Utilisateurs en ligne
$stmtOnline = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
$stmtOnline->execute([$userId]);
$onlineUsers = $stmtOnline->fetchAll();

// Utilisateurs avec qui il y a déjà eu des messages
$stmtConv = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN sender_id = :me THEN receiver_id 
            ELSE sender_id 
        END AS other_user_id
    FROM messages
    WHERE sender_id = :me OR receiver_id = :me
");
$stmtConv->execute(['me' => $userId]);
$conversationUserIds = array_column($stmtConv->fetchAll(), 'other_user_id');

// Noms des utilisateurs liés aux conversations
$conversations = [];
if ($conversationUserIds) {
    $placeholders = implode(',', array_fill(0, count($conversationUserIds), '?'));
    $stmtNames = $pdo->prepare("SELECT id, username FROM users WHERE id IN ($placeholders)");
    $stmtNames->execute($conversationUserIds);
    $conversations = $stmtNames->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURE_CHAT - Terminal</title>
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

        .chat-window {
            background-color: #1a1e24;
            border: 1px solid #2d3748;
        }

        /* Styles des bulles de message */
        .message-bubble-sent {
            background-color: #00ff41;
            color: #000000;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            max-width: 80%;
            align-self: flex-end;
            border: 1px solid #00cc33;
            box-shadow: 0 2px 5px rgba(0, 255, 65, 0.2);
        }

        .message-bubble-received {
            background-color: #e2e8f0;
            color: #000000;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            max-width: 80%;
            align-self: flex-start;
            border: 1px solid #a0aec0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message-time {
            font-size: 0.7rem;
            color: #4a5568;
            margin-top: 0.25rem;
            text-align: right;
        }

        .message-username {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .user-conversation {
            background-color: #0f1419;
            border: 1px solid #00ccff;
            color: #00ccff;
            transition: all 0.2s ease;
        }

        .user-conversation:hover {
            background-color: #00ccff;
            color: #0f1419;
        }

        .select-hack {
            background-color: #1a1e24;
            border: 1px solid #00ff41;
            color: #00ff41;
        }

        .select-hack:focus {
            border-color: #00ff41;
            box-shadow: 0 0 0 1px rgba(0, 255, 65, 0.3);
            outline: none;
        }

        .message-input {
            background-color: #1a1e24;
            border: 1px solid #2d3748;
            color: #00ff41;
            resize: none;
        }

        .message-input:focus {
            border-color: #00ff41;
            box-shadow: 0 0 0 1px rgba(0, 255, 65, 0.3);
            outline: none;
        }

        .btn-send {
            background-color: #0f1419;
            border: 1px solid #00ff41;
            color: #00ff41;
            transition: all 0.2s ease;
        }

        .btn-send:hover {
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
            animation: scan 4s linear infinite;
        }

        @keyframes scan {
            0% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(100vh);
            }
        }

        .typing-indicator {
            color: #00ff41;
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

        .file-input {
            background-color: #1a1e24;
            border: 1px solid #2d3748;
            color: #00ff41;
        }

        .file-input:focus {
            border-color: #00ff41;
            outline: none;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-online {
            background-color: #00ff41;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Style pour les images et vidéos dans les messages */
        .message-media {
            max-width: 100%;
            max-height: 200px;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
            border: 1px solid #4a5568;
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navbar.php'; ?>

    <!-- Scan Line Effect -->
    <div class="scan-line"></div>

    <div class="max-w-7xl mx-auto p-4 mt-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 h-[calc(100vh-8rem)]">

            <!-- Sidebar - Users & Conversations -->
            <div class="lg:col-span-1 space-y-4">

                <!-- Terminal Header -->
                <div class="terminal rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="text-xs text-green-400">chat_terminal.sh</div>
                    </div>

                    <div class="text-green-400 text-sm mb-2">$ ./secure_chat.sh</div>
                    <h2 class="text-xl font-bold text-green-400 mb-2">MESSAGERIE</h2>
                    <div class="text-xs text-gray-400">Interface de communication sécurisée</div>
                </div>

                <!-- Sélection utilisateur -->
                <div class="terminal rounded-lg p-4">
                    <h3 class="text-sm font-bold text-green-400 mb-3 flex items-center">
                        <span class="mr-2">👥</span>
                        DESTINATAIRE
                    </h3>
                    <div class="flex items-center mb-2">
                        <span class="text-green-400 mr-2">></span>
                        <select id="receiver_id" class="select-hack flex-1 p-2 rounded text-sm">
                            <option value="">Sélectionner un utilisateur...</option>
                            <?php foreach ($onlineUsers as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-xs text-gray-400">
                        <span class="status-indicator status-online"></span>
                        <?= count($onlineUsers) ?> utilisateur(s) en ligne
                    </div>
                </div>

                <!-- Conversations récentes -->
                <?php if (!empty($conversations)): ?>
                    <div class="terminal rounded-lg p-4">
                        <h3 class="text-sm font-bold text-cyan-400 mb-3 flex items-center">
                            <span class="mr-2">💬</span>
                            CONVERSATIONS
                        </h3>
                        <div class="space-y-2">
                            <?php foreach ($conversations as $conv): ?>
                                <button onclick="selectUser(<?= $conv['id'] ?>, '<?= htmlspecialchars($conv['username']) ?>')"
                                    class="user-conversation w-full px-3 py-2 rounded text-xs text-left flex items-center">
                                    <span class="status-indicator status-online"></span>
                                    <?= htmlspecialchars($conv['username']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Status -->
                <div class="terminal rounded-lg p-4">
                    <div class="text-xs text-gray-400 space-y-1">
                        <div class="flex justify-between">
                            <span>Connexions actives:</span>
                            <span class="text-green-400"><?= count($onlineUsers) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Chiffrement:</span>
                            <span class="text-green-400">AES-256</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <span class="text-green-400">SÉCURISÉ</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="lg:col-span-3 flex flex-col">

                <!-- Chat Header -->
                <div class="terminal rounded-t-lg p-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="status-indicator status-online"></span>
                        <span class="text-green-400 font-bold" id="current-user">Aucun utilisateur sélectionné</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        <span id="connection-status">En attente...</span>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="chat-window flex-1 p-4 overflow-y-auto" id="chat-box">
                    <div class="text-center text-gray-500 text-sm">
                        <div class="mb-2">📡</div>
                        <div>Sélectionnez un utilisateur pour commencer la conversation</div>
                        <div class="text-xs mt-2">Toutes les communications sont chiffrées end-to-end</div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="terminal rounded-b-lg p-4">
                    <form id="chat-form" enctype="multipart/form-data" class="space-y-3">

                        <!-- Message Input -->
                        <div class="flex items-center space-x-2">
                            <span class="text-green-400">></span>
                            <textarea name="message" id="message" class="message-input flex-1 p-2 rounded text-sm"
                                rows="1" placeholder="Tapez votre message..." disabled></textarea>
                        </div>

                        <!-- File Input -->
                        <div class="flex items-center space-x-2">
                            <span class="text-cyan-400 text-xs">FICHIER:</span>
                            <input type="file" name="file" id="file" class="file-input flex-1 p-2 rounded text-xs"
                                accept="image/*,video/*" disabled>
                        </div>

                        <!-- Send Button -->
                        <button type="submit" class="btn-send w-full py-2 rounded font-medium text-sm" disabled
                            id="send-btn">
                            [ SÉLECTIONNER UN DESTINATAIRE ]
                        </button>

                    </form>

                    <!-- Typing Indicator -->
                    <div class="mt-2 text-xs">
                        <span class="typing-indicator" id="typing-status"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('chat-form');
        const chatBox = document.getElementById('chat-box');
        const receiverSelect = document.getElementById('receiver_id');
        const currentUserSpan = document.getElementById('current-user');
        const connectionStatus = document.getElementById('connection-status');
        const messageInput = document.getElementById('message');
        const fileInput = document.getElementById('file');
        const sendBtn = document.getElementById('send-btn');
        const typingStatus = document.getElementById('typing-status');

        let selectedUserId = null;
        let selectedUsername = null;

        // Function called by conversation buttons
        function selectUser(userId, username) {
            selectedUserId = userId;
            selectedUsername = username;
            receiverSelect.value = userId;

            updateUI();
            loadMessages();
        }

        // Handle select change
        receiverSelect.addEventListener('change', function () {
            selectedUserId = this.value;
            const option = this.options[this.selectedIndex];
            selectedUsername = option.textContent;

            updateUI();

            if (selectedUserId) {
                loadMessages();
            } else {
                resetChat();
            }
        });

        function updateUI() {
            if (selectedUserId) {
                currentUserSpan.textContent = selectedUsername;
                connectionStatus.textContent = 'Connexion établie';

                // Enable inputs
                messageInput.disabled = false;
                fileInput.disabled = false;
                sendBtn.disabled = false;
                sendBtn.textContent = '[ ENVOYER MESSAGE ]';

                messageInput.focus();
            } else {
                resetChat();
            }
        }

        function resetChat() {
            currentUserSpan.textContent = 'Aucun utilisateur sélectionné';
            connectionStatus.textContent = 'En attente...';

            // Disable inputs
            messageInput.disabled = true;
            fileInput.disabled = true;
            sendBtn.disabled = true;
            sendBtn.textContent = '[ SÉLECTIONNER UN DESTINATAIRE ]';

            chatBox.innerHTML = `
                <div class="text-center text-gray-500 text-sm">
                    <div class="mb-2">📡</div>
                    <div>Sélectionnez un utilisateur pour commencer la conversation</div>
                    <div class="text-xs mt-2">Toutes les communications sont chiffrées end-to-end</div>
                </div>
            `;
        }

        async function loadMessages() {
            if (!selectedUserId) return;

            console.log("Chargement des messages pour l'utilisateur ID :", selectedUserId);

            try {
                const res = await fetch('fetch_messages.php?receiver_id=' + selectedUserId);
                let html = await res.text();

                // Appliquer les styles aux messages
                html = formatMessages(html);

                chatBox.innerHTML = html;
                chatBox.scrollTop = chatBox.scrollHeight;
            } catch (error) {
                console.error('Erreur lors du chargement des messages:', error);
                chatBox.innerHTML = '<div class="text-red-400 text-center">Erreur de connexion</div>';
            }
        }

        // Fonction pour formater les messages avec nos styles
        function formatMessages(html) {
            // Cette fonction est une solution temporaire
            // Idéalement, vous devriez modifier fetch_messages.php pour appliquer ces styles directement

            // Remplacer les classes de message envoyé
            html = html.replace(/class="[^"]*bg-blue-100[^"]*"/g, 'class="message-bubble-sent"');

            // Remplacer les classes de message reçu
            html = html.replace(/class="[^"]*bg-gray-100[^"]*"/g, 'class="message-bubble-received"');

            // Ajouter des classes pour les médias
            html = html.replace(/<img[^>]*>/g, function (match) {
                return match.replace(/class="[^"]*"/g, 'class="message-media"');
            });

            html = html.replace(/<video[^>]*>/g, function (match) {
                return match.replace(/class="[^"]*"/g, 'class="message-media"');
            });

            return html;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!selectedUserId) {
                alert('Veuillez sélectionner un destinataire');
                return;
            }

            const formData = new FormData(form);
            formData.append('receiver_id', selectedUserId);

            try {
                const res = await fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                });

                if (res.ok) {
                    form.reset();
                    loadMessages();
                    typingStatus.textContent = '';
                } else {
                    alert("Erreur lors de l'envoi.");
                }
            } catch (error) {
                console.error('Erreur lors de l\'envoi:', error);
                alert("Erreur de connexion.");
            }
        });

        // Auto-resize textarea
        messageInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';

            // Typing indicator
            if (this.value.length > 0) {
                typingStatus.textContent = 'Frappe en cours...';
            } else {
                typingStatus.textContent = '';
            }
        });

        // Enter to send (Shift+Enter for new line)
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey && !this.disabled) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Auto-refresh messages every 3 seconds
        setInterval(() => {
            if (selectedUserId) {
                loadMessages();
            }
        }, 3000);

        // Initialize
        window.onload = () => {
            resetChat();
        };
    </script>
</body>

</html>