<?php
session_start();
require '../../process/db_connect.php';

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

header('Content-Type: application/json');

function decrypt_message(string $ciphertext): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $decoded_ciphertext = base64_decode($ciphertext);
    return openssl_decrypt($decoded_ciphertext, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $current_user_id = '23a4f7e8c94633d4f33dbd787edb76ed';

    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN mp.remetente_id = ? THEN mp.destinatario_id 
                ELSE mp.remetente_id 
            END as contact_id,
            u.username,
            u.photo,
            mp.mensagem as last_message,
            mp.data_envio as last_message_time,
            mp.remetente_id = ? as is_sent_by_me,
            mp.lida,
            COUNT(CASE WHEN mp.destinatario_id = ? AND mp.lida = 0 THEN 1 END) as unread_count
        FROM mensagens_privadas mp
        INNER JOIN users u ON u.id = (
            CASE 
                WHEN mp.remetente_id = ? THEN mp.destinatario_id 
                ELSE mp.remetente_id 
            END
        )
        WHERE (mp.remetente_id = ? OR mp.destinatario_id = ?)
        AND mp.id IN (
            SELECT MAX(id) 
            FROM mensagens_privadas mp2
            WHERE (mp2.remetente_id = ? AND mp2.destinatario_id = u.id) 
            OR (mp2.remetente_id = u.id AND mp2.destinatario_id = ?)
        )
        GROUP BY contact_id, u.username, u.photo, mp.mensagem, mp.data_envio, mp.remetente_id, mp.lida
        ORDER BY mp.data_envio DESC
    ");
    
    $stmt->execute([
        $current_user_id, // CASE condition
        $current_user_id, // is_sent_by_me check
        $current_user_id, // unread count
        $current_user_id, // CASE condition in JOIN
        $current_user_id, // WHERE remetente_id
        $current_user_id, // WHERE destinatario_id
        $current_user_id, // subquery remetente_id
        $current_user_id  // subquery destinatario_id
    ]);
    
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted_conversations = [];
    foreach ($conversations as $conv) {
        try {
            $decrypted_message = decrypt_message($conv['last_message']);
        } catch (Exception $e) {
            $decrypted_message = '[Mensagem não pôde ser descriptografada]';
        }
        
        $message_time = new DateTime($conv['last_message_time']);
        $now = new DateTime();
        $diff = $now->diff($message_time);
        
        if ($diff->days > 365) {
            $formatted_time = $diff->y . ' ano(s) atrás';
        } elseif ($diff->days > 30) {
            $formatted_time = floor($diff->days / 30) . ' mês(es) atrás';
        } elseif ($diff->days > 0) {
            $formatted_time = $diff->days . 'd atrás';
        } elseif ($diff->h > 0) {
            $formatted_time = $diff->h . 'h atrás';
        } elseif ($diff->i > 0) {
            $formatted_time = $diff->i . 'min atrás';
        } else {
            $formatted_time = 'agora';
        }
        
        $formatted_conversations[] = [
            'contact_id' => $conv['contact_id'],
            'username' => $conv['username'],
            'photo' => $conv['photo'],
            'last_message' => $conv['is_sent_by_me'] ? 'Você: ' . $decrypted_message : $decrypted_message,
            'formatted_time' => $formatted_time,
            'unread_count' => (int)$conv['unread_count'],
            'is_sent_by_me' => (bool)$conv['is_sent_by_me'],
            'is_read' => (bool)$conv['lida']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'conversations' => $formatted_conversations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar conversas: ' . $e->getMessage()
    ]);
}
?>