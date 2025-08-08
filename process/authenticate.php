<?php
session_start();
require 'db_connect.php';

if (file_exists(__DIR__ . '/../../../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../');
    $dotenv->load();
}

if (!isset($_POST['login'], $_POST['password'])) {
    header('Location: login.php?error=true');
    exit();
}

$login = $_POST['login'];
$password = $_POST['password'];

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';

$allowed_redirects = ['index.php', 'config.php'];
if (!in_array($redirect, $allowed_redirects)) {
    $redirect = 'index.php';
}

$sql = "SELECT id FROM users WHERE (rm = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$login]);

$user = $stmt->fetch();

$password = hash('sha256', $user['id'] . $_ENV['ENCRYPTION_KEY'] . hash('sha256', $login . $password));

$sql = "SELECT * FROM users WHERE (password = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$password]);

$user = $stmt->fetch();

if ($user) {
    $_SESSION['id'] = $user['id'];
    header('Location: ../' . $redirect);
    exit();
} else {
    $redirect_param = ($redirect !== 'index.php') ? '&redirect=' . urlencode($redirect) : '';
    header('Location: ../login.php?error=true' . $redirect_param);
    exit();
}
?>