<?php
require 'db_connect.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

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

$key = bin2hex(openssl_random_pseudo_bytes(16));
$password = hash('sha256', $key . $_ENV['ENCRYPTION_KEY'] . hash('sha256', $rm . $password));

try {
    $pdo->beginTransaction();
    
    $sql = "INSERT INTO users (id, username, email, password, rm, nome_completo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$key, $username, $email, $password, $rm, $username]);
    
    $sql_preferences = "INSERT INTO user_preferences (
        user_id, 
        email_notifications, 
        push_notifications, 
        credit_alerts, 
        message_notifications, 
        mobile_notif
    ) VALUES (?, 1, 1, 1, 1, 1)";
    $stmt_preferences = $pdo->prepare($sql_preferences);
    $stmt_preferences->execute([$key]);
    
    $sql_privacy = "INSERT INTO user_privacy (
        user_id, 
        public_profile, 
        show_online_status, 
        allow_direct_messages, 
        share_activity
    ) VALUES (?, 1, 0, 1, 0)";
    $stmt_privacy = $pdo->prepare($sql_privacy);
    $stmt_privacy->execute([$key]);
    
    $pdo->commit();
    
    header('Location: ../login.php');
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../register.php?error=Erro ao criar usuário. Tente novamente.');
    exit();
}
?>