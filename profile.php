<?php
require './includes/config.php';
require './includes/session.php';
require './includes/functions.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: login.php');
    exit;
}

// Traitement du formulaire de mise à jour du profil
$message = '';
$error = '';

if ($_POST['action'] ?? '' === 'update_profile') {
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, bio = ? WHERE id = ?");
            $stmt->execute([$email, $bio, $user_id]);
            $message = 'Profil mis à jour avec succès!';
            
            // Recharger les données utilisateur
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour du profil.';
        }
    } else {
        $error = 'Email invalide.';
    }
}

// Statistiques utilisateur
$stmt = $pdo->prepare("SELECT COUNT(*) as message_count FROM messages WHERE sender_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT receiver_id) as conversation_count FROM messages WHERE sender_id = ?");
$stmt->execute([$user_id]);
$conversations = $stmt->fetch();

$defaultAvatar = getDefaultAvatar($username);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?= htmlspecialchars($username) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#0a0e13] text-white min-h-screen font-mono">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header du profil -->
        <div class="terminal-window bg-[#0f1419] border border-[#00ff41] rounded-lg shadow-lg shadow-[#00ff41]/20 mb-8">
            <div class="terminal-header bg-[#1a1e24] px-4 py-3 border-b border-[#2d3748] flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <div class="text-xs text-green-400">user_profile.sh</div>
                <div class="text-xs text-gray-400">SECURE MODE</div>
            </div>

            <div class="p-6">
                <div class="flex flex-col md:flex-row items-start space-y-6 md:space-y-0 md:space-x-8">
                    <!-- Avatar Section -->
                    <div class="flex flex-col items-center space-y-4">
                        <div class="relative">
                            <?php if ($user['avatar']): ?>
                                <img src="<?= htmlspecialchars($user['avatar']) ?>" 
                                     alt="Avatar" 
                                     class="w-32 h-32 rounded-full border-2 border-[#00ff41] shadow-lg shadow-[#00ff41]/30">
                            <?php else: ?>
                                <div class="w-32 h-32 rounded-full border-2 border-[#00ff41] shadow-lg shadow-[#00ff41]/30 flex items-center justify-center text-4xl font-bold text-white"
                                     style="background: linear-gradient(135deg, <?= $defaultAvatar['color'] ?>, <?= $defaultAvatar['color'] ?>88);">
                                    <?= $defaultAvatar['letter'] ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Bouton de modification d'avatar -->
                            <button onclick="openAvatarModal()" 
                                    class="absolute -bottom-2 -right-2 bg-[#00ff41] text-black w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#00cc33] transition-all duration-300 shadow-lg">
                                📷
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <h1 class="text-2xl font-bold text-[#00ff41] mb-1">@<?= htmlspecialchars($username) ?></h1>
                            <div class="text-xs text-gray-400">ID: #<?= str_pad($user_id, 6, '0', STR_PAD_LEFT) ?></div>
                            <div class="text-xs text-green-400 mt-2">
                                <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                                EN LIGNE
                            </div>
                        </div>
                    </div>

                    <!-- Informations du profil -->
                    <div class="flex-1 space-y-6">
                        <!-- Statistiques -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-[#1a1e24] border border-[#2d3748] rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-[#00ff41]"><?= $stats['message_count'] ?></div>
                                <div class="text-xs text-gray-400">MESSAGES ENVOYÉS</div>
                            </div>
                            <div class="bg-[#1a1e24] border border-[#2d3748] rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-[#00ccff]"><?= $conversations['conversation_count'] ?></div>
                                <div class="text-xs text-gray-400">CONVERSATIONS</div>
                            </div>
                            <div class="bg-[#1a1e24] border border-[#2d3748] rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-[#ff6b6b]"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                                <div class="text-xs text-gray-400">MEMBRE DEPUIS</div>
                            </div>
                        </div>

                        <!-- Informations personnelles -->
                        <div class="bg-[#1a1e24] border border-[#2d3748] rounded-lg p-4">
                            <h3 class="text-lg font-bold text-[#00ff41] mb-4">$ cat user_info.txt</h3>
                            
                            <?php if ($message): ?>
                                <div class="bg-green-900/30 border border-green-500 text-green-400 px-4 py-2 rounded mb-4 text-sm">
                                    ✓ <?= htmlspecialchars($message) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="bg-red-900/30 border border-red-500 text-red-400 px-4 py-2 rounded mb-4 text-sm">
                                    ✗ <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div>
                                    <label class="block text-sm text-gray-400 mb-2">Email:</label>
                                    <input type="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                           class="w-full bg-[#0f1419] border border-[#2d3748] rounded px-3 py-2 text-white focus:border-[#00ff41] focus:outline-none transition-colors"
                                           placeholder="votre@email.com">
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-gray-400 mb-2">Bio:</label>
                                    <textarea name="bio" 
                                              rows="3"
                                              class="w-full bg-[#0f1419] border border-[#2d3748] rounded px-3 py-2 text-white focus:border-[#00ff41] focus:outline-none transition-colors resize-none"
                                              placeholder="Parlez-nous de vous..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                </div>
                                
                                <button type="submit" 
                                        class="bg-[#00ff41] text-black px-6 py-2 rounded font-semibold hover:bg-[#00cc33] transition-all duration-300 shadow-lg shadow-[#00ff41]/30">
                                    [METTRE À JOUR]
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="chat.php" class="block bg-[#0f1419] border border-[#00ff41] rounded-lg p-6 hover:bg-[#1a1e24] transition-all duration-300 group">
                <div class="flex items-center space-x-4">
                    <div class="text-3xl">💬</div>
                    <div>
                        <h3 class="text-lg font-bold text-[#00ff41] group-hover:text-white">Accéder au Chat</h3>
                        <p class="text-sm text-gray-400">Rejoindre les conversations</p>
                    </div>
                </div>
            </a>
            
            <a href="logout.php" class="block bg-[#0f1419] border border-[#ff2b4e] rounded-lg p-6 hover:bg-[#1a1e24] transition-all duration-300 group">
                <div class="flex items-center space-x-4">
                    <div class="text-3xl">🚪</div>
                    <div>
                        <h3 class="text-lg font-bold text-[#ff2b4e] group-hover:text-white">Déconnexion</h3>
                        <p class="text-sm text-gray-400">Fermer la session sécurisée</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Modal d'upload d'avatar -->
    <div id="avatar-modal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[#0f1419] border border-[#00ff41] rounded-lg max-w-md w-full">
            <div class="terminal-header bg-[#1a1e24] px-4 py-3 border-b border-[#2d3748] flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <div class="text-xs text-green-400">avatar_upload.sh</div>
                <button onclick="closeAvatarModal()" class="text-red-500 hover:text-red-400">✕</button>
            </div>
            
            <div class="p-6">
                <h3 class="text-lg font-bold text-[#00ff41] mb-4">$ upload_avatar --secure</h3>
                
                <form id="avatar-form" enctype="multipart/form-data" class="space-y-4">
                    <div class="border-2 border-dashed border-[#2d3748] rounded-lg p-8 text-center hover:border-[#00ff41] transition-colors">
                        <input type="file" 
                               id="avatar-input" 
                               name="avatar" 
                               accept="image/*" 
                               class="hidden"
                               onchange="previewAvatar(this)">
                        <label for="avatar-input" class="cursor-pointer">
                            <div class="text-4xl mb-2">📷</div>
                            <div class="text-sm text-gray-400">Cliquez pour sélectionner une image</div>
                            <div class="text-xs text-gray-500 mt-1">JPG, PNG, GIF (max 2MB)</div>
                        </label>
                    </div>
                    
                    <div id="avatar-preview" class="hidden text-center">
                        <img id="preview-image" src="/placeholder.svg" alt="Aperçu" class="w-24 h-24 rounded-full mx-auto border-2 border-[#00ff41] mb-4">
                        <div class="space-x-4">
                            <button type="button" 
                                    onclick="uploadAvatar()" 
                                    class="bg-[#00ff41] text-black px-4 py-2 rounded hover:bg-[#00cc33] transition-colors">
                                [CONFIRMER]
                            </button>
                            <button type="button" 
                                    onclick="cancelPreview()" 
                                    class="bg-[#ff2b4e] text-white px-4 py-2 rounded hover:bg-[#ff1a3d] transition-colors">
                                [ANNULER]
                            </button>
                        </div>
                    </div>
                </form>
                
                <div id="upload-progress" class="hidden mt-4">
                    <div class="text-sm text-gray-400 mb-2">Upload en cours...</div>
                    <div class="w-full bg-[#2d3748] rounded-full h-2">
                        <div id="progress-bar" class="bg-[#00ff41] h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .terminal-window {
        font-family: 'Fira Code', monospace;
    }
    
    .terminal-header {
        font-family: 'Fira Code', monospace;
    }
    </style>

    <script>
    function openAvatarModal() {
        document.getElementById('avatar-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAvatarModal() {
        document.getElementById('avatar-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        cancelPreview();
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Vérifier la taille du fichier (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Taille maximum: 2MB');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('avatar-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    function cancelPreview() {
        document.getElementById('avatar-input').value = '';
        document.getElementById('avatar-preview').classList.add('hidden');
        document.getElementById('upload-progress').classList.add('hidden');
    }

    function uploadAvatar() {
        const formData = new FormData();
        const fileInput = document.getElementById('avatar-input');
        
        if (!fileInput.files[0]) {
            alert('Veuillez sélectionner un fichier');
            return;
        }
        
        formData.append('avatar', fileInput.files[0]);
        
        // Afficher la barre de progression
        document.getElementById('upload-progress').classList.remove('hidden');
        
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                document.getElementById('progress-bar').style.width = percentComplete + '%';
            }
        });
        
        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert('Avatar mis à jour avec succès!');
                    location.reload();
                } else {
                    alert('Erreur: ' + response.message);
                }
            } else {
                alert('Erreur lors de l\'upload');
            }
            document.getElementById('upload-progress').classList.add('hidden');
        });
        
        xhr.addEventListener('error', function() {
            alert('Erreur de connexion');
            document.getElementById('upload-progress').classList.add('hidden');
        });
        
        xhr.open('POST', 'upload_avatar.php');
        xhr.send(formData);
    }

    // Fermer le modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAvatarModal();
        }
    });
    </script>
</body>
</html>
