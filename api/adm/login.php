<?php
    require '../config_api.php';

if (file_exists(__DIR__ . '/../../.env')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['rm']) || !isset($input['password'])) {
        throw new Exception('RM e senha são obrigatórios');
    }
    
    $rm = trim($input['rm']);
    $password = $input['password'];
    
    if (empty($rm) || strlen($rm) < 3) {
        throw new Exception('RM deve ter pelo menos 3 caracteres');
    }
    
    $rm = preg_replace('/[^a-zA-Z0-9]/', '', $rm);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE rm = :rm");
    $stmt->bindParam(':rm', $rm);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Usuário não encontrado');
    }
    
    if (!isset($user['tipo']) || $user['tipo'] !== 'adm') {
        throw new Exception('Acesso negado. Apenas administradores podem fazer login.');
    }
    
    $stmt = $pdo->prepare("SELECT quantidade FROM creditos WHERE username = :id");
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();
    
    $creditos = $stmt->fetch(PDO::FETCH_ASSOC);
    $user['creditos'] = $creditos ? $creditos['quantidade'] : 0;
    
    if (!isset($_ENV['ENCRYPTION_KEY'])) {
        throw new Exception('Chave de criptografia não encontrada');
    }
    
    $expectedHash = hash('sha256', $user['id'] . $_ENV['ENCRYPTION_KEY'] . hash('sha256', $rm . $password));
    if ($user['password'] !== $expectedHash) {
        throw new Exception('Senha incorreta');
    }
    
    unset($user['password']);
    
    if ($user['creditos'] === null) {
        $user['creditos'] = 0;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'user' => $user
    ]);
    
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