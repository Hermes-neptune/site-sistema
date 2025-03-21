<?php
require 'db_connect.php';
$limite = 8; 

$sql = "SELECT mensagem, data_criacao FROM mensagens WHERE user_id = ? ORDER BY data_criacao DESC LIMIT ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['id'], $limite]);
$mensagens = $stmt->fetchAll();
?>