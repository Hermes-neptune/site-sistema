<?php
session_start();
require 'db_connect.php';

if (file_exists(__DIR__ . '/../../../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../..');
    $dotenv->load();
}

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit;
}

function encrypt_message(string $plaintext): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $encrypted = openssl_encrypt($plaintext, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encrypted);
}

function decrypt_message(string $ciphertext): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $decoded_ciphertext = base64_decode($ciphertext);
    return openssl_decrypt($decoded_ciphertext, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
}

function formatTimeAgo($datetime) {
    $now = new DateTime();
    $time = new DateTime($datetime);
    $diff = $now->diff($time);

    if ($diff->y > 0) {
        return $diff->y . ' ano' . ($diff->y > 1 ? 's' : '') . ' atrás';
    } elseif ($diff->m > 0) {
        return $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '') . ' atrás';
    } elseif ($diff->d > 0) {
        return $diff->d . ' dia' . ($diff->d > 1 ? 's' : '') . ' atrás';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') . ' atrás';
    } else {
        return 'Agora mesmo';
    }
}

$current_user_id = $_SESSION['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'fetch_messages' && isset($_GET['friend_id'])) {
        $friend_id = $_GET['friend_id'];

        $stmt = $pdo->prepare("
            SELECT id, remetente_id, mensagem, data_envio, lida, visualizada, data_visualizacao
            FROM mensagens_privadas
            WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?)
            ORDER BY data_envio ASC
        ");
        $stmt->execute([$current_user_id, $friend_id, $friend_id, $current_user_id]);
        $encrypted_messages = $stmt->fetchAll();

        $stmt_mark_read = $pdo->prepare("
            UPDATE mensagens_privadas 
            SET lida = 1, visualizada = NOW(), data_visualizacao = NOW() 
            WHERE remetente_id = ? AND destinatario_id = ? AND lida = 0
        ");
        $stmt_mark_read->execute([$friend_id, $current_user_id]);

        $decrypted_messages = array_map(function($msg) {
            $msg['mensagem'] = decrypt_message($msg['mensagem']);
            $msg['data_envio_formatted'] = formatTimeAgo($msg['data_envio']);
            $msg['data_envio_full'] = date('d/m/Y H:i', strtotime($msg['data_envio']));
            $msg['visualizada_formatted'] = $msg['visualizada'] ? formatTimeAgo($msg['visualizada']) : null;
            return $msg;
        }, $encrypted_messages);

        echo json_encode(['status' => 'success', 'messages' => $decrypted_messages]);

    } elseif ($action === 'send_message' && isset($_POST['friend_id']) && isset($_POST['message'])) {
        $friend_id = $_POST['friend_id'];
        $plaintext_message = trim($_POST['message']);

        if (empty($plaintext_message)) {
            throw new Exception('A mensagem não pode estar vazia.');
        }

        $encrypted_message = encrypt_message($plaintext_message);

        $stmt = $pdo->prepare("
            INSERT INTO mensagens_privadas (remetente_id, destinatario_id, mensagem)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$current_user_id, $friend_id, $encrypted_message]);

        echo json_encode(['status' => 'success', 'message' => 'Mensagem enviada.']);

    } elseif ($action === 'mark_as_read' && isset($_POST['friend_id'])) {
        $friend_id = $_POST['friend_id'];
        
        $stmt = $pdo->prepare("
            UPDATE mensagens_privadas 
            SET lida = 1, visualizada = NOW(), data_visualizacao = NOW() 
            WHERE remetente_id = ? AND destinatario_id = ? AND lida = 0
        ");
        $stmt->execute([$friend_id, $current_user_id]);

        echo json_encode(['status' => 'success', 'message' => 'Mensagens marcadas como lidas.']);

    } elseif ($action === 'get_read_status' && isset($_GET['message_ids'])) {
        $message_ids = explode(',', $_GET['message_ids']);
        $placeholders = str_repeat('?,', count($message_ids) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT id, lida, visualizada, data_visualizacao 
            FROM mensagens_privadas 
            WHERE id IN ($placeholders) AND remetente_id = ?
        ");
        $stmt->execute(array_merge($message_ids, [$current_user_id]));
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'read_status' => $results]);

    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ação ou parâmetros inválidos.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>