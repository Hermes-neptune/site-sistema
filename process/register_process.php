<?php
require 'db_connect.php';

if (!isset($_POST['rm'], $_POST['username'], $_POST['email'], $_POST['password'])) {
    header('Location: register.php?error=Campos obrigatórios faltando.');
    exit();
}

$rm = $_POST["rm"];
$username = $_POST["username"];
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
if (strlen($rm) != 5) {
    header('Location: ../register.php?error=RM deve ter 5 caracteres.');
    exit();
}
if (strlen($username) < 3 || strlen($username) > 20) {
    header('Location: ../register.php?error=O nome de usuário deve ter entre 3 e 20 caracteres.');
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../register.php?error=Email inválido.');
    exit();
}
if (strlen($password) < 6 || strlen($password) > 20) {
    header('Location: ../register.php?error=A senha deve ter entre 6 e 20 caracteres.');
    exit();
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    header('Location: ../register.php?error=O nome de usuário só pode conter letras, números e sublinhados.');
    exit();
}

$password = hash('sha256', $rm . $password);

$sql = "INSERT INTO users (username, email, password, rm) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $email, $password, $rm]);

header('Location: ../login.php');
exit();
?>
