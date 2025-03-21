<?php
require 'processos/db_connect.php';

if (!isset($_POST['username'], $_POST['email'], $_POST['password'])) {
    header('Location: register.php?error=Campos obrigatórios faltando.');
    exit();
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    header('Location: register.php?error=O email já está em uso.');
    exit();
}

function gerarCodigoUnico($pdo) {
    do {
        // Gera um código aleatório de 5 dígitos
        $codigo = rand(10000, 99999);

        // Verifica no banco de dados se o código já existe
        $sql = "SELECT id FROM users WHERE codigo_unico = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $exists = $stmt->fetch();
    } while ($exists); 

    return $codigo;
}

// Gera um código único para o usuário
$codigo_unico = gerarCodigoUnico($pdo);

// Gera os 2 hashes combinando a senha com o username e o email
$hash_username_password = hash('sha256', $username . $password);
$hash_email_password = hash('sha256', $email . $password);

// Insere os dados no banco de dados
$sql = "INSERT INTO users (username, email, hash_username_password, hash_email_password, codigo_unico) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $email, $hash_username_password, $hash_email_password, $codigo_unico]);

header('Location: login.php');
exit();
?>
