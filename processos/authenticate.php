<?php
    session_start();

    require 'db_connect.php';

    if (!isset($_POST['login'], $_POST['password'])) {
        header('Location: login.php?error=true');
        exit();
    }

    $login = $_POST['login'];
    $password = $_POST['password'];

    $hash_rm_password = hash('sha256', $login . $password);

    $sql = "SELECT * FROM users WHERE (hash_rm_password = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hash_rm_password]);

    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['id'] = $user['id'];
        header('Location: ../protected.php');
        exit();
    } else {
        header('Location: ../login.php?error=true');
        exit();
    }
?>