<?php
    session_start();

    require 'process/db_connect.php';

    if (!isset($_POST['login'], $_POST['password'])) {
        header('Location: login.php?error=true');
        exit();
    }

    $login = $_POST['login'];
    $password = $_POST['password'];

    $password = hash('sha256', $login . $password);

    $sql = "SELECT * FROM users WHERE (password = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$password]);

    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['id'] = $user['id'];
        header('Location: protected.php');
        exit();
    } else {
        header('Location: login.php?error=true');
        exit();
    }
?>