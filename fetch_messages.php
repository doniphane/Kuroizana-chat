<?php
require './includes/config.php';
require './includes/session.php';

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? null;

if (!$receiver_id) {
    http_response_code(400);
    exit('ID destinataire manquant');
}

// Marquer les messages comme lus
$pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0")
    ->execute([$user_id, $receiver_id]);

// Récupérer la conversation
$stmt = $pdo->prepare("SELECT m.*, u.username as sender_name FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (m.sender_id = ? AND m.receiver_id = ?) 
       OR (m.sender_id = ? AND m.receiver_id = ?)
    ORDER BY m.created_at ASC");

$stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
$messages = $stmt->fetchAll();

if (empty($messages)) {
    echo '<div class="text-center text-gray-400 text-sm py-8">
            <div class="mb-2">📡</div>
            <div>Aucun message. Commencez la conversation!</div>
            <div class="text-xs mt-2 text-green-400">Connexion sécurisée établie</div>
          </div>';
} else {
    echo '<div class="flex flex-col space-y-3 p-2">';

    foreach ($messages as $msg) {
        $isOwn = $msg['sender_id'] == $user_id;
        $alignClass = $isOwn ? 'self-end' : 'self-start';
        $bubbleClass = $isOwn ? 'message-bubble-sent' : 'message-bubble-received';

        $date = date('H:i', strtotime($msg['created_at']));
        $fullDate = date('d/m/Y H:i', strtotime($msg['created_at']));

        echo '<div class="' . $alignClass . ' max-w-[80%]">';

        // Nom de l'expéditeur (seulement pour les messages reçus)
        if (!$isOwn) {
            echo '<div class="text-xs text-cyan-400 mb-1 px-2">
                    <span class="mr-1">@</span>' . htmlspecialchars($msg['sender_name']) . '
                  </div>';
        }

        // Bulle de message
        echo '<div class="' . $bubbleClass . '">';

        // Contenu du message
        if ($msg['content']) {
            echo '<div class="text-black font-medium">' . nl2br(htmlspecialchars($msg['content'])) . '</div>';
        }

        // Média (si présent)
        if ($msg['file_path']) {
            $filePath = htmlspecialchars($msg['file_path']);

            if ($msg['file_type'] === 'image') {
                echo '<div class="mt-2">
                        <img src="' . $filePath . '" 
                             class="image-thumbnail rounded border border-gray-600 max-w-full h-auto max-h-48 cursor-pointer hover:opacity-80 transition-all duration-300 hover:scale-105" 
                             alt="Image" 
                             data-lightbox="true"
                             data-src="' . $filePath . '"
                             data-sender="' . htmlspecialchars($msg['sender_name']) . '"
                             data-date="' . $fullDate . '">
                        <div class="text-xs text-gray-600 mt-1 flex items-center">
                            <span class="mr-1">🖼️</span>
                            <span>Cliquer pour agrandir</span>
                        </div>
                      </div>';
            } elseif ($msg['file_type'] === 'video') {
                echo '<div class="mt-2">
                        <video controls 
                               class="rounded border border-gray-600 max-w-full h-auto max-h-48">
                            <source src="' . $filePath . '" type="video/mp4">
                            Votre navigateur ne supporte pas la vidéo.
                        </video>
                      </div>';
            } else {
                // Autres types de fichiers
                $fileName = basename($filePath);
                echo '<div class="mt-2 p-2 bg-gray-700 rounded border border-gray-600">
                        <a href="' . $filePath . '" 
                           target="_blank" 
                           class="text-cyan-400 hover:text-cyan-300 text-sm flex items-center">
                            <span class="mr-2">📎</span>
                            ' . htmlspecialchars($fileName) . '
                        </a>
                      </div>';
            }
        }

        // Horodatage et statut
        echo '<div class="flex items-center justify-between mt-2 text-xs">';
        echo '<span class="text-gray-600" title="' . $fullDate . '">' . $date . '</span>';

        // Indicateur de lecture pour les messages envoyés
        if ($isOwn) {
            $readStatus = $msg['is_read'] ? '✓✓' : '✓';
            $readColor = $msg['is_read'] ? 'text-green-600' : 'text-gray-500';
            echo '<span class="' . $readColor . ' ml-2">' . $readStatus . '</span>';
        }

        echo '</div>';

        echo '</div>'; // Fin bulle
        echo '</div>'; // Fin conteneur
    }

    echo '</div>';
}
?>

<style>
    /* Styles pour les bulles de message */
    .message-bubble-sent {
        background: linear-gradient(145deg, #00ff41, #00cc33);
        color: #000000;
        border-radius: 1rem 1rem 0.25rem 1rem;
        padding: 0.75rem 1rem;
        border: 1px solid #00cc33;
        box-shadow: 0 2px 8px rgba(0, 255, 65, 0.3);
        position: relative;
    }

    .message-bubble-sent::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: -8px;
        width: 0;
        height: 0;
        border-left: 8px solid #00ff41;
        border-bottom: 8px solid transparent;
    }

    .message-bubble-received {
        background: linear-gradient(145deg, #f1f5f9, #e2e8f0);
        color: #000000;
        border-radius: 1rem 1rem 1rem 0.25rem;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .message-bubble-received::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: -8px;
        width: 0;
        height: 0;
        border-right: 8px solid #f1f5f9;
        border-bottom: 8px solid transparent;
    }

    /* Animation d'apparition des messages */
    .message-bubble-sent,
    .message-bubble-received {
        animation: messageAppear 0.3s ease-out;
    }

    @keyframes messageAppear {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>

<script>
    // Initialiser la lightbox après le chargement du contenu
    document.addEventListener('DOMContentLoaded', function () {
        initializeLightbox();
    });

    function initializeLightbox() {
        // Créer la lightbox si elle n'existe pas
        if (!document.getElementById('lightbox-modal')) {
            createLightboxModal();
        }

        // Ajouter les event listeners aux images
        const images = document.querySelectorAll('img[data-lightbox="true"]');
        images.forEach(img => {
            img.addEventListener('click', function () {
                const src = this.getAttribute('data-src');
                const sender = this.getAttribute('data-sender');
                const date = this.getAttribute('data-date');
                openLightbox(src, sender, date);
            });
        });
    }

    function createLightboxModal() {
        const lightboxHTML = `
        <div id="lightbox-modal" class="lightbox-overlay" onclick="closeLightbox()">
            <div class="lightbox-container">
                <!-- Header de la lightbox -->
                <div class="lightbox-header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="text-xs text-green-400">image_viewer.sh</div>
                        <button onclick="closeLightbox()" class="lightbox-close">✕</button>
                    </div>
                </div>
                
                <!-- Contenu de l'image -->
                <div class="lightbox-content" onclick="event.stopPropagation()">
                    <img id="lightbox-image" src="/placeholder.svg" alt="Image agrandie" class="lightbox-image">
                    
                    <!-- Informations de l'image -->
                    <div class="lightbox-info">
                        <div class="text-green-400 text-sm mb-1">$ cat image_metadata.txt</div>
                        <div class="text-xs text-gray-300 space-y-1">
                            <div class="flex justify-between">
                                <span>Envoyé par:</span>
                                <span class="text-cyan-400" id="lightbox-sender">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Date:</span>
                                <span class="text-yellow-400" id="lightbox-date">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Statut:</span>
                                <span class="text-green-400">SÉCURISÉ</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contrôles -->
                <div class="lightbox-controls">
                    <button onclick="downloadImage()" class="lightbox-btn">
                        <span class="mr-2">💾</span>
                        TÉLÉCHARGER
                    </button>
                    <button onclick="copyImageUrl()" class="lightbox-btn">
                        <span class="mr-2">📋</span>
                        COPIER URL
                    </button>
                    <button onclick="closeLightbox()" class="lightbox-btn lightbox-btn-close">
                        <span class="mr-2">❌</span>
                        FERMER
                    </button>
                </div>
            </div>
        </div>
    `;

        document.body.insertAdjacentHTML('beforeend', lightboxHTML);

        // Ajouter les styles CSS
        const lightboxStyles = `
        <style id="lightbox-styles">
        /* Styles pour la lightbox */
        .lightbox-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            display: none;
            z-index: 9999;
            backdrop-filter: blur(5px);
            animation: lightboxFadeIn 0.3s ease-out;
        }

        .lightbox-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        @keyframes lightboxFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .lightbox-container {
            background-color: #0f1419;
            border: 2px solid #00ff41;
            border-radius: 0.5rem;
            max-width: 90vw;
            max-height: 90vh;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.5);
            overflow: hidden;
            animation: lightboxSlideIn 0.3s ease-out;
            font-family: 'Fira Code', monospace;
        }

        @keyframes lightboxSlideIn {
            from { 
                opacity: 0;
                transform: scale(0.8) translateY(50px);
            }
            to { 
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .lightbox-header {
            background-color: #1a1e24;
            padding: 1rem;
            border-bottom: 1px solid #2d3748;
        }

        .lightbox-close {
            background-color: #ff2b4e;
            color: white;
            border: none;
            border-radius: 50%;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .lightbox-close:hover {
            background-color: #ff1a3d;
            transform: scale(1.1);
        }

        .lightbox-content {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-height: 70vh;
            overflow: auto;
        }

        .lightbox-image {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 0.5rem;
            border: 1px solid #00ff41;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
            margin-bottom: 1rem;
        }

        .lightbox-info {
            background-color: #1a1e24;
            border: 1px solid #2d3748;
            border-radius: 0.5rem;
            padding: 1rem;
            width: 100%;
            max-width: 400px;
        }

        .lightbox-controls {
            background-color: #1a1e24;
            padding: 1rem;
            border-top: 1px solid #2d3748;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .lightbox-btn {
            background-color: #0f1419;
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-family: 'Fira Code', monospace;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        .lightbox-btn:hover {
            background-color: #00ff41;
            color: #0f1419;
            box-shadow: 0 0 10px rgba(0, 255, 65, 0.5);
        }

        .lightbox-btn-close {
            border-color: #ff2b4e;
            color: #ff2b4e;
        }

        .lightbox-btn-close:hover {
            background-color: #ff2b4e;
            color: white;
            box-shadow: 0 0 10px rgba(255, 43, 78, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .lightbox-container {
                max-width: 95vw;
                max-height: 95vh;
            }
            
            .lightbox-controls {
                flex-direction: column;
            }
            
            .lightbox-btn {
                width: 100%;
                justify-content: center;
            }
        }
        </style>
    `;

        document.head.insertAdjacentHTML('beforeend', lightboxStyles);
    }

    let currentImageUrl = '';

    // Fonction pour ouvrir la lightbox
    function openLightbox(imageSrc, senderName, date) {
        currentImageUrl = imageSrc;

        const modal = document.getElementById('lightbox-modal');
        const image = document.getElementById('lightbox-image');
        const sender = document.getElementById('lightbox-sender');
        const dateElement = document.getElementById('lightbox-date');

        if (!modal || !image || !sender || !dateElement) {
            console.error('Éléments de lightbox non trouvés');
            return;
        }

        image.src = imageSrc;
        sender.textContent = senderName;
        dateElement.textContent = date;

        modal.classList.add('active');

        // Empêcher le scroll du body
        document.body.style.overflow = 'hidden';

        console.log('%c[LIGHTBOX] Image ouverte: ' + imageSrc, 'color: #00ff41; font-family: monospace;');
    }

    // Fonction pour fermer la lightbox
    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        if (modal) {
            modal.classList.remove('active');
        }

        // Restaurer le scroll du body
        document.body.style.overflow = 'auto';

        console.log('%c[LIGHTBOX] Fermée', 'color: #ff2b4e; font-family: monospace;');
    }

    // Fonction pour télécharger l'image
    function downloadImage() {
        if (!currentImageUrl) return;

        const link = document.createElement('a');
        link.href = currentImageUrl;
        link.download = 'image_' + Date.now() + '.jpg';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('%c[DOWNLOAD] Image téléchargée', 'color: #00ff41; font-family: monospace;');
    }

    // Fonction pour copier l'URL de l'image
    function copyImageUrl() {
        if (!currentImageUrl) return;

        navigator.clipboard.writeText(currentImageUrl).then(() => {
            // Feedback visuel
            const btn = event.target.closest('.lightbox-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="mr-2">✅</span>COPIÉ!';
            btn.style.borderColor = '#00ff41';
            btn.style.color = '#00ff41';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.borderColor = '';
                btn.style.color = '';
            }, 2000);

            console.log('%c[CLIPBOARD] URL copiée: ' + currentImageUrl, 'color: #00ff41; font-family: monospace;');
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
        });
    }

    // Fermer avec la touche Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });

    // Réinitialiser la lightbox quand de nouveaux messages sont chargés
    window.addEventListener('load', function () {
        initializeLightbox();
    });

    // Observer pour les nouveaux messages chargés dynamiquement
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'childList') {
                initializeLightbox();
            }
        });
    });

    // Observer le chat-box pour les nouveaux messages
    const chatBox = document.getElementById('chat-box');
    if (chatBox) {
        observer.observe(chatBox, { childList: true, subtree: true });
    }
</script>