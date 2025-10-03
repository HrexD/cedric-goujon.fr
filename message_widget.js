// Widget de notification des messages (à inclure dans vos pages d'admin)
function createMessageNotificationWidget() {
    const widget = document.createElement('div');
    widget.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--accent);
        color: white;
        padding: 10px 15px;
        border-radius: 25px;
        box-shadow: var(--shadow);
        cursor: pointer;
        z-index: 1000;
        font-weight: bold;
        transition: all 0.3s;
        display: none;
    `;
    
    widget.onclick = () => window.open('admin_messages', '_blank');
    document.body.appendChild(widget);
    
    // Vérifier les nouveaux messages
    function checkMessages() {
        fetch('api_messages')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.unread_count > 0) {
                    widget.textContent = `📧 ${data.unread_count} nouveau${data.unread_count > 1 ? 'x' : ''} message${data.unread_count > 1 ? 's' : ''}`;
                    widget.style.display = 'block';
                    widget.onmouseover = () => widget.style.transform = 'scale(1.1)';
                    widget.onmouseout = () => widget.style.transform = 'scale(1)';
                } else {
                    widget.style.display = 'none';
                }
            })
            .catch(err => console.log('Vérification des messages:', err));
    }
    
    // Vérifier au chargement et toutes les 30 secondes
    checkMessages();
    setInterval(checkMessages, 30000);
}

// Démarrer le widget si on est en mode admin (détection simple)
if (window.location.href.includes('admin') || document.body.classList.contains('admin-page')) {
    document.addEventListener('DOMContentLoaded', createMessageNotificationWidget);
}