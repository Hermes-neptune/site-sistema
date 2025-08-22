async function sendRequest(friendId) {
    const formData = new FormData();
    formData.append('action', 'enviar_solicitacao');
    formData.append('friend_id', String(friendId)); 

    try {
        const response = await fetch('process/friends_process.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            const userElement = document.querySelector(`[id="user-${friendId}"]`);
            if (userElement) {
                userElement.remove();
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao enviar solicitação: ' + error.message);
    }
}

async function handleRequest(requestId, action) {
    const formData = new FormData();
    formData.append('action', action === 'aceitar' ? 'aceitar_solicitacao' : 'rejeitar_solicitacao');
    formData.append('request_id', String(requestId)); 

    try {
        const response = await fetch('process/friends_process.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            const requestElement = document.querySelector(`[id="request-${requestId}"]`);
            if (requestElement) {
                requestElement.remove();
            }
            location.reload();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao processar solicitação: ' + error.message);
    }
}

let activeChatFriendId = null;
let messagePollingInterval = null;
let lastMessageCount = 0;

function startChat(friendId, friendName, friendPhoto) {
    activeChatFriendId = String(friendId); 

    document.getElementById('chat-welcome').style.display = 'none';
    document.getElementById('chat-conversation').style.display = 'flex';

    document.getElementById('chat-friend-name').innerText = friendName;
    document.getElementById('chat-friend-photo').src = friendPhoto;
    document.getElementById('chat-friend-id').value = String(friendId);

    document.querySelectorAll('.friend-item').forEach(item => item.classList.remove('active'));
    
    const friendItem = document.querySelector(`.friend-item[data-friend-id="${friendId}"]`);
    if (friendItem) {
        friendItem.classList.add('active');
    }

    const newUrl = new URL(window.location);
    newUrl.searchParams.set('friend_id', friendId);
    window.history.pushState({}, '', newUrl);

    fetchMessages(friendId);

    if (messagePollingInterval) clearInterval(messagePollingInterval);
    messagePollingInterval = setInterval(() => fetchMessages(friendId), 3000);
}

function createMessageElement(msg) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message');
    messageDiv.classList.add(String(msg.remetente_id) === String(LOGGED_IN_USER_ID) ? 'sent' : 'received');
    messageDiv.setAttribute('data-message-id', msg.id);
    
    // Process emojis in message
    const processedMessage = window.emojiSystem ? 
        window.emojiSystem.processMessageEmojis(msg.mensagem) : 
        msg.mensagem;
    
    // Create message content
    const messageContent = document.createElement('div');
    messageContent.classList.add('message-content');
    messageContent.innerHTML = processedMessage;
    
    // Create timestamp
    const timestamp = document.createElement('div');
    timestamp.classList.add('message-time');
    timestamp.setAttribute('title', msg.data_envio_full);
    timestamp.textContent = msg.data_envio_formatted;
    
    messageDiv.appendChild(messageContent);
    messageDiv.appendChild(timestamp);
    
    // Add read receipt for sent messages
    if (String(msg.remetente_id) === String(LOGGED_IN_USER_ID)) {
        const readReceipt = document.createElement('div');
        readReceipt.classList.add('read-receipt');
        
        if (msg.visualizada) {
            readReceipt.innerHTML = `<span class="read-icon">✓✓</span><span class="read-time">Visto ${msg.visualizada_formatted}</span>`;
            readReceipt.classList.add('read');
        } else if (msg.lida) {
            readReceipt.innerHTML = `<span class="delivered-icon">✓</span><span class="delivered-time">Entregue</span>`;
            readReceipt.classList.add('delivered');
        } else {
            readReceipt.innerHTML = `<span class="sent-icon">○</span><span class="sent-time">Enviando...</span>`;
            readReceipt.classList.add('sent');
        }
        
        messageDiv.appendChild(readReceipt);
    }
    
    return messageDiv;
}

async function fetchMessages(friendId) {
    try {
        const response = await fetch(`process/chat_process.php?action=fetch_messages&friend_id=${encodeURIComponent(friendId)}`);
        const result = await response.json();

        if (result.status === 'success') {
            const messageArea = document.getElementById('message-area');
            const shouldScroll = messageArea.scrollTop + messageArea.clientHeight >= messageArea.scrollHeight - 100;

            if (messageArea.children.length !== result.messages.length) {
                messageArea.innerHTML = ''; 
                result.messages.forEach(msg => {
                    const messageElement = createMessageElement(msg);
                    messageArea.appendChild(messageElement);
                });
                
                lastMessageCount = result.messages.length;
            }
            
            if (shouldScroll || messageArea.children.length <= 1) {
                messageArea.scrollTop = messageArea.scrollHeight;
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Erro ao buscar mensagens:', error);
    }
}

async function markMessagesAsRead(friendId) {
    try {
        const formData = new FormData();
        formData.append('action', 'mark_as_read');
        formData.append('friend_id', String(friendId));

        const response = await fetch('process/chat_process.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.status !== 'success') {
            console.error('Erro ao marcar mensagens como lidas:', result.message);
        }
    } catch (error) {
        console.error('Erro ao marcar mensagens como lidas:', error);
    }
}

const messageForm = document.getElementById('message-form');
if (messageForm) {
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('message-input');
        const friendIdInput = document.getElementById('chat-friend-id');
        
        const message = messageInput.value.trim();
        const friendId = friendIdInput.value;

        if (!message || !friendId) return;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('friend_id', String(friendId)); 
        formData.append('message', message);

        try {
            const response = await fetch('process/chat_process.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.status === 'success') {
                messageInput.value = ''; 
                fetchMessages(friendId); 
                
                // Close emoji picker if open
                if (window.emojiSystem) {
                    window.emojiSystem.closePicker();
                }
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
            alert('Erro: ' + error.message);
        }
    });
}

// Focus event to mark messages as read when user focuses on chat
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && activeChatFriendId) {
        markMessagesAsRead(activeChatFriendId);
    }
});

// Mark messages as read when starting a chat
const originalStartChat = startChat;
startChat = function(friendId, friendName, friendPhoto) {
    originalStartChat(friendId, friendName, friendPhoto);
    setTimeout(() => {
        markMessagesAsRead(friendId);
    }, 1000);
};

function openChat(userId) {
    window.location.href = `chat.php?friend_id=${encodeURIComponent(userId)}`;
}

function isValidHexId(id) {
    return /^[0-9a-fA-F]{32}$/.test(id);
}

function escapeSelector(selector) {
    return selector.replace(/[!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~]/g, '\\$&');
}