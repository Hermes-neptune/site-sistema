<?php
require 'db_connect.php';

$limite = 3;  

$sql = "SELECT descricao, status FROM pendencias WHERE user_id = ? AND status = 'pendente' LIMIT ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['id'], $limite]);
$pendencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
