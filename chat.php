<?php
session_start();
require 'process/db_connect.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['id'];

$stmt_amigos = $pdo->prepare("
    SELECT u.id, u.username, u.photo
    FROM amizades a
    JOIN users u ON (a.user_id = u.id OR a.friend_id = u.id)
    WHERE (a.user_id = ? OR a.friend_id = ?) AND a.status = 'aceito' AND u.id != ?
    GROUP BY u.id
");
$stmt_amigos->execute([$current_user_id, $current_user_id, $current_user_id]);
$amigos = $stmt_amigos->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Privado</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>

<div class="chat-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Amigos</h3>
            <button class="btn-back" onclick="window.location.href='friends.php'">Voltar</button>
        </div>
        <ul class="friend-list">
            <?php if (empty($amigos)): ?>
                <li>Nenhum amigo para conversar.</li>
            <?php else: ?>
                <?php foreach ($amigos as $amigo): ?>
                    <li class="friend-item" onclick="startChat(<?= $amigo['id'] ?>, '<?= htmlspecialchars($amigo['username']) ?>', '<?= htmlspecialchars($amigo['photo']) ?>')">
                        <img src="<?= htmlspecialchars($amigo['photo']) ?>" alt="Foto">
                        <span><?= htmlspecialchars($amigo['username']) ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </aside>

    <main class="chat-window">
        <div id="chat-welcome" class="chat-area">
            <div class="welcome-message">
                <h2>Bem-vindo ao Chat</h2>
                <p>Selecione um amigo na lista para começar a conversar.</p>
            </div>
        </div>

        <div id="chat-conversation" class="chat-area" style="display: none;">
            <div class="chat-header">
                <img id="chat-friend-photo" src="" alt="Foto">
                <h3 id="chat-friend-name"></h3>
            </div>
            <div class="message-area" id="message-area">
            </div>
            <form class="message-input-area" id="message-form">
                <input type="hidden" id="chat-friend-id" name="friend_id">
                <input type="text" id="message-input" name="message" placeholder="Digite sua mensagem..." autocomplete="off">
                <button type="submit">Enviar</button>
            </form>
        </div>
    </main>
</div>

<script>
    const LOGGED_IN_USER_ID = <?= $current_user_id; ?>;
</script>
<script src="js/friends.js"></script>
<script src="js/thema.js"></script>
</body>
</html>
