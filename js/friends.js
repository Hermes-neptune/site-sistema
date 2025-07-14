async function sendRequest(friendId) {
    const formData = new FormData();
    formData.append('action', 'enviar_solicitacao');
    formData.append('friend_id', friendId);

    try {
        const response = await fetch('process/friends_process.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            document.getElementById(`user-${friendId}`).remove();
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
    formData.append('request_id', requestId);

    try {
        const response = await fetch('process/friends_process.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            document.getElementById(`request-${requestId}`).remove();
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
    activeChatFriendId = friendId;

    document.getElementById('chat-welcome').style.display = 'none';
    document.getElementById('chat-conversation').style.display = 'flex';

    document.getElementById('chat-friend-name').innerText = friendName;
    document.getElementById('chat-friend-photo').src = friendPhoto;
    document.getElementById('chat-friend-id').value = friendId;

    document.querySelectorAll('.friend-item').forEach(item => item.classList.remove('active'));
    document.querySelector(`.friend-item[onclick*="startChat(${friendId},"]`).classList.add('active');

    fetchMessages(friendId);

    if (messagePollingInterval) clearInterval(messagePollingInterval);
    messagePollingInterval = setInterval(() => fetchMessages(friendId), 3000);
}

async function fetchMessages(friendId) {
    try {
        const response = await fetch(`process/chat_process.php?action=fetch_messages&friend_id=${friendId}`);
        const result = await response.json();

        if (result.status === 'success') {
            const messageArea = document.getElementById('message-area');
            const shouldScroll = messageArea.scrollTop + messageArea.clientHeight === messageArea.scrollHeight;

            if (messageArea.children.length !== result.messages.length) {
                messageArea.innerHTML = ''; 
                result.messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message');
                    messageDiv.classList.add(msg.remetente_id == LOGGED_IN_USER_ID ? 'sent' : 'received');
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
        formData.append('friend_id', friendId);
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
    window.location.href = `chat.php?friend_id=${userId}`;
}

