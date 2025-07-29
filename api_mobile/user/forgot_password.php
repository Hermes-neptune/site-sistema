<?php
    require '../config_api.php';

if (file_exists(__DIR__ . '/../../.env')) {
    require_once __DIR__ . '/../../process/send_email.php';
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'])) {
        throw new Exception('Email é obrigatório');
    }
    
    $email = trim($input['email']);
    
    if (empty($email)) {
        throw new Exception('Por favor, digite seu email');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Por favor, digite um email válido');
    }
    
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("SELECT id FROM password_resets WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE password_resets SET token = :token, expiry = :expiry WHERE email = :email");
        } else {
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (:email, :token, :expiry)");
        }
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->execute();
        
        $resetLink = $_ENV['SITE_URL_API'] . "reset_password.php?token=" . $token;

        $emailSent = sendEmail($email, $resetLink, 'Recuperação de Senha', $user['username']);
        
        if ($emailSent) {
            echo json_encode([
                'success' => true,
                'message' => 'Instruções enviadas para seu email.',
            ]);
        } else {
            throw new Exception('Erro ao enviar email. Tente novamente.');
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Se o email estiver cadastrado, você receberá as instruções.'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>