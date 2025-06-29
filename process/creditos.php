<?php
    require 'db_connect.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sql = "SELECT quantidade FROM creditos WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);
    $creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_creditos = 0;
    foreach ($creditos as $credito) {
        $total_creditos += $credito['quantidade'];
}
?>