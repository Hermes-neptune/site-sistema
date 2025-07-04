<?php
require 'db_connect.php';

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
    header('Location: ../register.php?error=O email já está em uso.');
    exit();
}

function gerarCodigoUnico($pdo) {
    do {
        $codigo = rand(10000, 99999);

        $sql = "SELECT id FROM users WHERE rm = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $exists = $stmt->fetch();
    } while ($exists); 

    return $codigo;
}

$codigo_unico = gerarCodigoUnico($pdo);

$password = hash('sha256', $rm . $password);

$sql = "INSERT INTO users (username, email, password, rm) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$rm, $email, $password, $codigo_unico]);

header('Location: ../login.php');
exit();
?>
