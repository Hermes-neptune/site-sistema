<?php 
session_start(); 
require 'process/db_connect.php';  

if (!isset($_SESSION['id'])) {     
    header('Location: login.php');     
    exit; 
}  

$current_user_id = $_SESSION['id'];  

$user_id_from_url = isset($_GET['user_id']) ? $_GET['user_id'] : null;
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
</head> 
<body>  

<div class="chat-container">     
    <aside class="sidebar">         
        <div class="sidebar-header">             
            <h3>Conversas</h3>             
            <button class="btn-back" onclick="window.location.href='index.php'">Voltar</button>         
        </div>         
        <ul class="friend-list">             
            <li class="friend-item add-friends-option" onclick="window.location.href='friends.php'">
                <div class="add-friends-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span>Adicionar Amigos</span>
            </li>
            
            <?php if (empty($amigos)): ?>                 
                <li class="no-friends-message">Nenhum amigo para conversar.</li>             
            <?php else: ?>                 
                <?php foreach ($amigos as $amigo): ?>                     
                    <li class="friend-item <?= ($selected_friend && $selected_friend['id'] === $amigo['id']) ? 'active' : '' ?>"                          
                        data-friend-id="<?= htmlspecialchars($amigo['id']) ?>"                          
                        onclick="startChat('<?= htmlspecialchars($amigo['id']) ?>', '<?= htmlspecialchars($amigo['username']) ?>', '<?= htmlspecialchars($amigo['photo']) ?>')">                         
                        <img src="<?= htmlspecialchars($amigo['photo']) ?>" alt="Foto">                         
                        <span><?= htmlspecialchars($amigo['username']) ?></span>                     
                    </li>                 
                <?php endforeach; ?>             
            <?php endif; ?>         
        </ul>     
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
</script> 
<script src="js/friends.js"></script> 
<script src="js/thema.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (SELECTED_FRIEND) {
            startChat(SELECTED_FRIEND.id, SELECTED_FRIEND.username, SELECTED_FRIEND.photo);
        }
    });
</script>

</body> 
</html>