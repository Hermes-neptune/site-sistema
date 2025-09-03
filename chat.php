<?php 
session_start(); 
require 'process/db_connect.php';  

if (!isset($_SESSION['id'])) {     
    header('Location: login.php');     
    exit; 
}  

$current_user_id = $_SESSION['id'];  

$user_id_from_url = isset($_GET['friend_id']) ? $_GET['friend_id'] : null;
$selected_friend = null;

$stmt_amigos = $pdo->prepare("     
    SELECT u.id, u.username, u.photo     
    FROM amizades a     
    JOIN users u ON (a.user_id = u.id OR a.friend_id = u.id)     
    WHERE (a.user_id = ? OR a.friend_id = ?) AND a.status = 'aceito' AND u.id != ?     
    GROUP BY u.id 
"); 
$stmt_amigos->execute([$current_user_id, $current_user_id, $current_user_id]); 
$amigos = $stmt_amigos->fetchAll(); 

if ($user_id_from_url) {
    foreach ($amigos as $amigo) {
        if ($amigo['id'] === $user_id_from_url) {
            $selected_friend = $amigo;
            break;
        }
    }
}
?>  

<!DOCTYPE html> 
<html lang="pt-br"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>Chat Privado</title> 
    <link rel="shortcut icon" type="image/x-icon" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
    <link rel="stylesheet" href="css/chat.css">
    <link rel="stylesheet" href="css/emoji-styles.css">
</head> 
<body>  

<div class="chat-container">     
    <aside class="sidebar">         
        <div class="sidebar-header">             
            <h3>Conversas</h3>             
            <button class="btn-back" onclick="window.location.href='index.php'">Voltar</button>         
        </div>
        
        <!-- Campo de Pesquisa -->
        <div class="search-container">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input 
                    type="text" 
                    id="search-friends" 
                    class="search-input" 
                    placeholder="Buscar conversas..."
                    autocomplete="off"
                >
                <button type="button" class="clear-search" id="clear-search" style="display: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <ul class="friend-list" id="friend-list">             
            <li class="friend-item add-friends-option" onclick="window.location.href='friends.php'">
                <div class="add-friends-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span>Adicionar Amigos</span>
            </li>
            
            <?php if (empty($amigos)): ?>                 
                <li class="no-friends-message" id="no-friends-default">Nenhum amigo para conversar.</li>             
            <?php else: ?>                 
                <?php foreach ($amigos as $amigo): ?>                     
                    <li class="friend-item <?= ($selected_friend && $selected_friend['id'] === $amigo['id']) ? 'active' : '' ?>"                          
                        data-friend-id="<?= htmlspecialchars($amigo['id']) ?>"
                        data-friend-name="<?= htmlspecialchars(strtolower($amigo['username'])) ?>"
                        onclick="startChat('<?= htmlspecialchars($amigo['id']) ?>', '<?= htmlspecialchars($amigo['username']) ?>', '<?= htmlspecialchars($amigo['photo']) ?>')">                         
                        <img src="<?= htmlspecialchars($amigo['photo']) ?>" alt="Foto">                         
                        <span><?= htmlspecialchars($amigo['username']) ?></span>                     
                    </li>                 
                <?php endforeach; ?>             
            <?php endif; ?>
        </ul>
        
        <!-- Mensagem quando não há resultados na pesquisa -->
        <div class="no-search-results" id="no-search-results" style="display: none;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <h4>Nenhuma conversa encontrada</h4>
            <p>Não encontramos conversas com esse nome.</p>
        </div>
    </aside>      

    <main class="chat-window">         
        <div id="chat-welcome" class="chat-area" style="display: <?= $selected_friend ? 'none' : 'block' ?>;">             
            <div class="welcome-message">                 
                <h2>Bem-vindo ao Chat</h2>                 
                <p>Selecione um amigo na lista para começar a conversar ou adicione novos amigos.</p>             
            </div>         
        </div>          

        <div id="chat-conversation" class="chat-area" style="display: <?= $selected_friend ? 'flex' : 'none' ?>;">             
            <div class="chat-header">                 
                <img id="chat-friend-photo" src="<?= $selected_friend ? htmlspecialchars($selected_friend['photo']) : '' ?>" alt="Foto">                 
                <h3 id="chat-friend-name"><?= $selected_friend ? htmlspecialchars($selected_friend['username']) : '' ?></h3>             
            </div>             
            <div class="message-area" id="message-area">             
            </div>             
            <form class="message-input-area" id="message-form">                 
                <input type="hidden" id="chat-friend-id" name="friend_id" value="<?= $selected_friend ? htmlspecialchars($selected_friend['id']) : '' ?>">                 
                <input type="text" id="message-input" name="message" placeholder="Digite sua mensagem..." autocomplete="off">                 
                <button type="submit">Enviar</button>             
            </form>         
        </div>     
    </main> 
</div>  

<script>     
    const LOGGED_IN_USER_ID = '<?= htmlspecialchars($current_user_id); ?>';
    const SELECTED_FRIEND = <?= $selected_friend ? json_encode($selected_friend) : 'null' ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-friends');
        const clearButton = document.getElementById('clear-search');
        const friendList = document.getElementById('friend-list');
        const noSearchResults = document.getElementById('no-search-results');
        const noFriendsDefault = document.getElementById('no-friends-default');
        
        let totalFriends = 0;
        let visibleFriends = 0;
        
        function countFriends() {
            const friendItems = friendList.querySelectorAll('.friend-item[data-friend-name]');
            totalFriends = friendItems.length;
            visibleFriends = totalFriends;
        }
        
        function searchFriends(searchTerm) {
            const friendItems = friendList.querySelectorAll('.friend-item[data-friend-name]');
            const addFriendsOption = friendList.querySelector('.add-friends-option');
            visibleFriends = 0;
            let hasResults = false;
            
            searchTerm = searchTerm.toLowerCase().trim();
            
            if (addFriendsOption) {
                if (searchTerm === '' || 'adicionar amigos'.includes(searchTerm)) {
                    addFriendsOption.style.display = 'flex';
                } else {
                    addFriendsOption.style.display = 'none';
                }
            }
            
            friendItems.forEach(item => {
                const friendName = item.dataset.friendName;
                const shouldShow = friendName.includes(searchTerm);
                
                if (shouldShow) {
                    item.style.display = 'flex';
                    item.style.animation = 'fadeInChat 0.3s ease forwards';
                    visibleFriends++;
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            if (searchTerm !== '' && !hasResults && totalFriends > 0) {
                noSearchResults.style.display = 'flex';
                if (noFriendsDefault) noFriendsDefault.style.display = 'none';
            } else {
                noSearchResults.style.display = 'none';
                if (noFriendsDefault && totalFriends === 0 && searchTerm === '') {
                    noFriendsDefault.style.display = 'block';
                }
            }
        }
        
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value;
            
            if (searchTerm.trim() !== '') {
                clearButton.style.display = 'flex';
            } else {
                clearButton.style.display = 'none';
            }
            
            searchFriends(searchTerm);
        });
        
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            clearButton.style.display = 'none';
            searchFriends('');
            searchInput.focus();
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                clearButton.style.display = 'none';
                searchFriends('');
            }
        });
        
        countFriends();
        
        if (SELECTED_FRIEND) {
            startChat(SELECTED_FRIEND.id, SELECTED_FRIEND.username, SELECTED_FRIEND.photo);
        }
    });
</script>
<script src="js/emoji-system.js"></script>
<script src="js/friends.js"></script> 
<script src="js/thema.js"></script>

</body> 
</html>