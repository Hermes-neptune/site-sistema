<?php
session_start();
require 'db_connect.php';
require __DIR__ . '/config.php'; 

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

if (!isset($_SESSION['id'])) {
    http_response_code(401 );
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

$current_user_id = $_SESSION['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'fetch_messages' && isset($_GET['friend_id'])) {
        $friend_id = $_GET['friend_id'];

        $stmt = $pdo->prepare("
            SELECT id, remetente_id, mensagem, data_envio
            FROM mensagens_privadas
            WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?)
            ORDER BY data_envio ASC
        ");
        $stmt->execute([$current_user_id, $friend_id, $friend_id, $current_user_id]);
        $encrypted_messages = $stmt->fetchAll();

        $decrypted_messages = array_map(function($msg) {
            $msg['mensagem'] = decrypt_message($msg['mensagem']);
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

    } else {
        http_response_code(400 );
        echo json_encode(['status' => 'error', 'message' => 'Ação ou parâmetros inválidos.']);
    }

} catch (Exception $e) {
    http_response_code(500 );
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
