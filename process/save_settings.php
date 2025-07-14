<?php
session_start();
require './vendor/autoload.php';
require 'db_connect.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit();
}

if ($data['action'] === 'update_profile') {
    if (!isset($data['username']) || !isset($data['nome_completo']) || !isset($data['email'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$data['email'], $_SESSION['id']]);
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso por outro usuário']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users SET username = ?, nome_completo = ?, email = ? WHERE id = ?");
    $result = $stmt->execute([
        $data['username'],
        $data['nome_completo'],
        $data['email'],
        $_SESSION['id']
    ]);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil']);
        exit();
    }
}

if ($data['action'] === 'change_password') {
    if (!isset($data['current_password']) || !isset($data['new_password']) || !isset($data['confirm_password'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
        exit();
    }

    if ($data['new_password'] !== $data['confirm_password']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nova senha e confirmação não coincidem']);
        exit();
    }

    if (strlen($data['new_password']) < 6) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nova senha deve ter pelo menos 6 caracteres']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        exit();
    }

    if (!hash_equals($user['password'], hash( 'sha256',  $user['id'] . $_ENV['ENCRYPTION_KEY'] . hash('sha256', $user['rm'] . $current_password)))) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
        exit();
    }

    $new_password_hash = hash( 'sha256',  $user['id'] . $_ENV['ENCRYPTION_KEY'] . hash('sha256', $user['rm'] . $data['new_password']));
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $result = $stmt->execute([$new_password_hash, $_SESSION['id']]);

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha']);
        exit();
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
exit();
