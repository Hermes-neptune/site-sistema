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

async function fetchMessages(friendId) {
    try {
        const response = await fetch(`process/chat_process.php?action=fetch_messages&friend_id=${encodeURIComponent(friendId)}`);
        const result = await response.json();

        if (result.status === 'success') {
            const messageArea = document.getElementById('message-area');
            const shouldScroll = messageArea.scrollTop + messageArea.clientHeight === messageArea.scrollHeight;

            if (messageArea.children.length !== result.messages.length) {
                messageArea.innerHTML = ''; 
                result.messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message');
                    messageDiv.classList.add(String(msg.remetente_id) === String(LOGGED_IN_USER_ID) ? 'sent' : 'received');
                    messageDiv.textContent = msg.mensagem;
                    messageArea.appendChild(messageDiv);
                });
            }
            
            if(shouldScroll || messageArea.children.length <= 1) {
                messageArea.scrollTop = messageArea.scrollHeight;
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Erro ao buscar mensagens:', error);
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
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
            alert('Erro: ' + error.message);
        }
    });
}

function openChat(userId) {
    window.location.href = `chat.php?friend_id=${encodeURIComponent(userId)}`;
}

function isValidHexId(id) {
    return /^[0-9a-fA-F]{32}$/.test(id);
}

function escapeSelector(selector) {
    return selector.replace(/[!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~]/g, '\\$&');
}