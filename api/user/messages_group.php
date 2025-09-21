<?php 
require '../config_api.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

function decrypt_message(string $ciphertext): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $decoded_ciphertext = base64_decode($ciphertext);
    return openssl_decrypt($decoded_ciphertext, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['grupo_id']) || !isset($input['user_id'])) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    $grupo_id = $input['grupo_id'];
    $user_id = $input['user_id'];
    $limit = $input['limit'] ?? 50;
    $offset = $input['offset'] ?? 0;
    
    $stmt_membro = $pdo->prepare("
        SELECT id FROM grupo_membros 
        WHERE grupo_id = ? AND user_id = ?
    ");
    $stmt_membro->execute([$grupo_id, $user_id]);
    if (!$stmt_membro->fetch()) {
        throw new Exception('Você não tem acesso a este grupo');
    }
    
    $stmt_mensagens = $pdo->prepare("
        SELECT 
            mg.id,
            mg.mensagem,
            mg.tipo,
            mg.arquivo_url,
            mg.data_envio,
            mg.editada,
            u.id as remetente_id,
            u.username as remetente_nome,
            u.photo as remetente_photo,
            DATE_FORMAT(mg.data_envio, '%H:%i') as horario_formatado,
            DATE_FORMAT(mg.data_envio, '%d/%m/%Y') as data_formatada
        FROM mensagens_grupos mg
        JOIN users u ON mg.remetente_id = u.id
        WHERE mg.grupo_id = ?
        ORDER BY mg.data_envio DESC
        LIMIT ? OFFSET ?
    ");
    $stmt_mensagens->execute([$grupo_id, $limit, $offset]);
    $mensagens = $stmt_mensagens->fetchAll(PDO::FETCH_ASSOC);
    
    $mensagens_descriptografadas = [];
    foreach ($mensagens as $mensagem) {
        try {
            if ($mensagem['tipo'] === 'texto' || $mensagem['tipo'] === null) {
                $mensagem['mensagem'] = decrypt_message($mensagem['mensagem']);
            }
        } catch (Exception $e) {
            $mensagem['mensagem'] = '[Mensagem não pôde ser descriptografada]';
        }
        
        $mensagens_descriptografadas[] = $mensagem;
    }
    
    $mensagens_descriptografadas = array_reverse($mensagens_descriptografadas);
    
    if (!empty($mensagens_descriptografadas)) {
        $ultima_mensagem_id = end($mensagens_descriptografadas)['id'];
        
        $stmt_update = $pdo->prepare("
            INSERT INTO grupo_mensagens_lidas (grupo_id, user_id, ultima_mensagem_lida) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                ultima_mensagem_lida = GREATEST(ultima_mensagem_lida, ?),
                data_leitura = CURRENT_TIMESTAMP
        ");
        $stmt_update->execute([$grupo_id, $user_id, $ultima_mensagem_id, $ultima_mensagem_id]);
    }
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens_descriptografadas,
        'count' => count($mensagens_descriptografadas)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>