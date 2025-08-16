<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config_api.php';

if (file_exists(__DIR__ . '../../../../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '../../../../..');
    $dotenv->load();
}

function decrypt_message(string $ciphertext): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $decoded_ciphertext = base64_decode($ciphertext);
    return openssl_decrypt($decoded_ciphertext, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $user_id = $_GET['user_id'] ?? '';
        $contact_id = $_GET['friend_id'] ?? $_GET['contact_id'] ?? '';
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? '';
        $contact_id = $input['contact_id'] ?? $input['friend_id'] ?? '';
    }

    if (empty($user_id) || empty($contact_id)) {
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'user_id e contact_id são obrigatórios'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT 
            mp.id,
            mp.remetente_id,
            mp.destinatario_id,
            mp.mensagem,
            mp.data_envio,
            mp.lida,
            u1.username as sender_name
        FROM mensagens_privadas mp
        LEFT JOIN users u1 ON mp.remetente_id = u1.id
        WHERE (mp.remetente_id = ? AND mp.destinatario_id = ?) 
           OR (mp.remetente_id = ? AND mp.destinatario_id = ?)
        ORDER BY mp.data_envio ASC
    ");
    
    $stmt->execute([$user_id, $contact_id, $contact_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $decrypted_messages = [];
    foreach ($messages as $message) {
        $decrypted_message = $message;
        
        try {
            $decrypted_text = decrypt_message($message['mensagem']);
            
            if (empty($decrypted_text)) {
                $decrypted_text = base64_decode($message['mensagem']) ?: $message['mensagem'];
            }
            
            $decrypted_message['mensagem'] = $decrypted_text;
        } catch (Exception $e) {
            $decrypted_message['mensagem'] = base64_decode($message['mensagem']) ?: $message['mensagem'];
            error_log("Erro na descriptografia da mensagem ID {$message['id']}: " . $e->getMessage());
        }
        
        $decrypted_messages[] = $decrypted_message;
    }

    $updateStmt = $pdo->prepare("
        UPDATE mensagens_privadas 
        SET lida = 1 
        WHERE destinatario_id = ? AND remetente_id = ? AND lida = 0
    ");
    $updateStmt->execute([$user_id, $contact_id]);

    echo json_encode([
        'success' => true,
        'status' => 'success',
        'messages' => $decrypted_messages
    ]);

} catch (Exception $e) {
    error_log("Erro em fetch_messages.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Erro interno do servidor'
    ]);
}
?>