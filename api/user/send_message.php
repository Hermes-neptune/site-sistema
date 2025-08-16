<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config_api.php';

if (file_exists(__DIR__ . '../../../../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '../../../../..');
    $dotenv->load();
}

function encrypt_message(string $plaintext): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $encrypted = openssl_encrypt($plaintext, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encrypted);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $sender_id = $input['sender_id'] ?? '';
    $recipient_id = $input['recipient_id'] ?? '';
    $message = $input['message'] ?? '';

    if (empty($sender_id) || empty($recipient_id) || empty($message)) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Todos os campos são obrigatórios'
        ]);
        exit;
    }

    $message = trim($message);
    
    if (empty($message)) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'A mensagem não pode estar vazia'
        ]);
        exit;
    }

    if (strlen($message) > 1000) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Mensagem muito longa'
        ]);
        exit;
    }

    $senderCheck = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $senderCheck->execute([$sender_id]);
    if (!$senderCheck->fetch()) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Remetente não encontrado'
        ]);
        exit;
    }

    $recipientCheck = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $recipientCheck->execute([$recipient_id]);
    if (!$recipientCheck->fetch()) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Destinatário não encontrado'
        ]);
        exit;
    }

    try {
        $encrypted_message = encrypt_message($message);
    } catch (Exception $e) {
        error_log("Erro na criptografia: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Erro ao processar mensagem'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO mensagens_privadas (remetente_id, destinatario_id, mensagem, data_envio, lida) 
        VALUES (?, ?, ?, NOW(), 0)
    ");
    
    $result = $stmt->execute([$sender_id, $recipient_id, $encrypted_message]);
    
    if ($result) {
        $message_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'status' => 'success',
            'message' => 'Mensagem enviada com sucesso',
            'message_id' => $message_id
        ]);
    } else {
        throw new Exception('Erro ao inserir mensagem no banco');
    }

} catch (Exception $e) {
    error_log("Erro em send_message.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Erro interno do servidor'
    ]);
}
?>