<?php
require 'processos/db_connect.php';

if (!isset($_POST['rm'], $_POST['email'], $_POST['password'])) {
    header('Location: register.php?error=Campos obrigatórios faltando.');
    exit();
}

$rm = $_POST["rm"];
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
        $codigo = rand(10000, 99999);

        $sql = "SELECT id FROM users WHERE codigo_unico = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $exists = $stmt->fetch();
    } while ($exists); 

    return $codigo;
}

$codigo_unico = gerarCodigoUnico($pdo);

$hash_rm_password = hash('sha256', $rm . $password);

$sql = "INSERT INTO users (username, email, hash_rm_password, codigo_unico) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$rm, $email, $hash_rm_password, $codigo_unico]);

header('Location: login.php');
exit();
?>
