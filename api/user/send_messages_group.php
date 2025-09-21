<?php 
require '../config_api.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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

function decrypt_message(string $encrypted_text): string {
    $key = hex2bin($_ENV['ENCRYPTION_KEY']);
    $iv = hex2bin($_ENV['ENCRYPTION_IV']);
    $encrypted = base64_decode($encrypted_text);
    return openssl_decrypt($encrypted, $_ENV['CIPHER_ALGO'], $key, OPENSSL_RAW_DATA, $iv);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['grupo_id']) || !isset($input['remetente_id']) || !isset($input['mensagem'])) {
    echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não fornecidos']);
    exit();
}

$grupo_id = trim($input['grupo_id']);
$remetente_id = trim($input['remetente_id']);
$mensagem = trim($input['mensagem']);
$tipo = isset($input['tipo']) ? $input['tipo'] : 'texto';
$arquivo_url = isset($input['arquivo_url']) ? $input['arquivo_url'] : null;

if (empty($mensagem)) {
    echo json_encode(['success' => false, 'message' => 'Mensagem não pode estar vazia']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupo_membros WHERE grupo_id = ? AND user_id = ?");
    $stmt->execute([$grupo_id, $remetente_id]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Usuário não é membro deste grupo']);
        exit();
    }
    
    $stmt = $pdo->prepare("SELECT ativo FROM grupos WHERE id = ?");
    $stmt->execute([$grupo_id]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$grupo) {
        echo json_encode(['success' => false, 'message' => 'Grupo não encontrado']);
        exit();
    }
    
    if (!$grupo['ativo']) {
        echo json_encode(['success' => false, 'message' => 'Grupo inativo']);
        exit();
    }
    
    try {
        $mensagem_criptografada = encrypt_message($mensagem);
    } catch (Exception $e) {
        error_log("Erro na criptografia: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro ao processar mensagem']);
        exit();
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO mensagens_grupos (grupo_id, remetente_id, mensagem, tipo, arquivo_url, data_envio) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$grupo_id, $remetente_id, $mensagem_criptografada, $tipo, $arquivo_url]);
    
    $mensagem_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("
        INSERT INTO grupo_mensagens_lidas (grupo_id, user_id, ultima_mensagem_lida, data_leitura) 
        VALUES (?, ?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE 
        ultima_mensagem_lida = VALUES(ultima_mensagem_lida), 
        data_leitura = VALUES(data_leitura)
    ");
    $stmt->execute([$grupo_id, $remetente_id, $mensagem_id]);
    
    $stmt = $pdo->prepare("
        SELECT 
            mg.id,
            mg.grupo_id,
            mg.remetente_id,
            mg.mensagem,
            mg.tipo,
            mg.arquivo_url,
            mg.data_envio,
            mg.editada,
            mg.data_edicao,
            u.username as remetente_nome,
            u.photo as remetente_photo,
            DATE_FORMAT(mg.data_envio, '%H:%i') as horario_formatado,
            DATE_FORMAT(mg.data_envio, '%d/%m/%Y') as data_formatada
        FROM mensagens_grupos mg
        LEFT JOIN users u ON mg.remetente_id = u.id
        WHERE mg.id = ?
    ");
    
    $stmt->execute([$mensagem_id]);
    $mensagem_enviada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mensagem_enviada) {
        try {
            $mensagem_enviada['mensagem'] = decrypt_message($mensagem_enviada['mensagem']);
        } catch (Exception $e) {
            error_log("Erro na descriptografia: " . $e->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mensagem enviada com sucesso',
        'mensagem' => $mensagem_enviada
    ]);
     
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()]);
}
?>