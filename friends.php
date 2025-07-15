<?php
session_start();
require 'process/db_connect.php';

if (!isset($_SESSION['id'])) {
    header('Location: login.php'); 
    exit;
}

$user_id = $_SESSION['id'];

$stmt_pendentes = $pdo->prepare("
    SELECT a.id, u.username, u.photo
    FROM amizades a
    JOIN users u ON a.user_id = u.id
    WHERE a.friend_id = ? AND a.status = 'pendente'
");
$stmt_pendentes->execute([$user_id]);
$solicitacoes_pendentes = $stmt_pendentes->fetchAll();

$stmt_amigos = $pdo->prepare("
    SELECT u.id, u.username, u.photo
    FROM amizades a
    JOIN users u ON (a.user_id = u.id OR a.friend_id = u.id)
    WHERE (a.user_id = ? OR a.friend_id = ?) AND a.status = 'aceito' AND u.id != ?
");
$stmt_amigos->execute([$user_id, $user_id, $user_id]);
$amigos = $stmt_amigos->fetchAll();

$stmt_outros = $pdo->prepare("
    SELECT id, username, photo FROM users WHERE id != ?
    AND id NOT IN (
        SELECT friend_id FROM amizades WHERE user_id = ?
    ) AND id NOT IN (
        SELECT user_id FROM amizades WHERE friend_id = ?
    )
");
$stmt_outros->execute([$user_id, $user_id, $user_id]);
$outros_usuarios = $stmt_outros->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Amizade</title>
    <link rel="stylesheet" href="css/friends.css">
</head>
<body>

<div class="container">
    <h1>Minhas Amizades</h1>

    <div class="section">
        <h2>Solicitações Pendentes (<?= count($solicitacoes_pendentes) ?>)</h2>
        <div id="pending-requests" class="user-list">
            <?php if (empty($solicitacoes_pendentes)): ?>
                <p>Nenhuma solicitação pendente.</p>
            <?php else: ?>
                <?php foreach ($solicitacoes_pendentes as $req): ?>
                    <div class="user-card" id="request-<?= $req['id'] ?>">
                        <img src="<?= htmlspecialchars($req['photo']) ?>" alt="Foto de <?= htmlspecialchars($req['username']) ?>">
                        <span><?= htmlspecialchars($req['username']) ?></span>
                        <div class="actions">
                            <button class="btn-accept" onclick="handleRequest('<?= $req['id'] ?>', 'aceitar')">Aceitar</button>
                            <button class="btn-reject" onclick="handleRequest('<?= $req['id'] ?>', 'rejeitar')">Rejeitar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>Meus Amigos (<?= count($amigos) ?>)</h2>
        <div class="user-list">
                <?php if (empty($amigos)): ?>
                <p>Você ainda não tem amigos.</p>
            <?php else: ?>
                <?php foreach ($amigos as $amigo): ?>
                    <div class="user-card">
                        <img src="<?= htmlspecialchars($amigo['photo']) ?>" alt="Foto de <?= htmlspecialchars($amigo['username']) ?>">
                        <span><?= htmlspecialchars($amigo['username']) ?></span>
                        <div class="actions">
                            <button class="btn-message" onclick="openChat('<?= $amigo['id'] ?>')">Mensagem</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>Adicionar Amigos</h2>
        <div class="user-list">
            <?php if (empty($outros_usuarios)): ?>
                <p>Não há novos usuários para adicionar.</p>
            <?php else: ?>
                <?php foreach ($outros_usuarios as $user): ?>
                    <div class="user-card" id="user-<?= $user['id'] ?>">
                        <img src="<?= htmlspecialchars($user['photo']) ?>" alt="Foto de <?= htmlspecialchars($user['username']) ?>">
                        <span><?= htmlspecialchars($user['username']) ?></span>
                        <div class="actions">
                            <button class="btn-add" onclick="sendRequest('<?= $user['id'] ?>')">Adicionar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/friends.js"></script>
<script src="js/thema.js"></script>
</body>
</html>
