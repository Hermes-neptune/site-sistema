<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input['action'] === 'update_profile') {
    $username = trim($input['username']);
    $nome_completo = trim($input['nome_completo']);
    $email = trim($input['email']);
    $user_id = $_SESSION['id'];
    
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username e email são obrigatórios']);
        exit();
    }
    
    
    try {
        $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $user_id]);
        
        if ($check_email->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Este email já está sendo usado por outro usuário']);
            exit();
        }
        
        $sql = "UPDATE users SET username = ?, nome_completo = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $nome_completo, $email, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()]);
    }
}

if ($input['action'] === 'change_password') {
    $current_password = $input['current_password'];
    $new_password = $input['new_password'];
    $confirm_password = $input['confirm_password'];
    $user_id = $_SESSION['id'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Nova senha e confirmação não coincidem']);
        exit();
    }
    
    if (strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres']);
        exit();
    }
    
    try {
        $sql = "SELECT password,rm FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        $password = hash('sha256', $user['rm'] . $current_password);

        if (!$user || $password !== $user['password']) {
            echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
            exit();
        }
        
        $new_hash = hash('sha256', $user['rm'] . $new_password);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_hash, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha: ' . $e->getMessage()]);
    }
}

?>