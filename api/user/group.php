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
    
    if (!$input || !isset($input['user_id'])) {
        throw new Exception('ID do usuário é obrigatório');
    }
    
    $user_id = $input['user_id'];
    
    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.nome,
            g.descricao,
            g.foto,
            g.tipo,
            g.data_criacao,
            gm.papel,
            COUNT(DISTINCT gm2.user_id) as total_membros,
            (
                SELECT mg.mensagem 
                FROM mensagens_grupos mg 
                WHERE mg.grupo_id = g.id 
                ORDER BY mg.data_envio DESC 
                LIMIT 1
            ) as ultima_mensagem,
            (
                SELECT u.username 
                FROM mensagens_grupos mg2 
                JOIN users u ON mg2.remetente_id = u.id
                WHERE mg2.grupo_id = g.id 
                ORDER BY mg2.data_envio DESC 
                LIMIT 1
            ) as ultimo_remetente,
            (
                SELECT DATE_FORMAT(mg3.data_envio, '%H:%i') 
                FROM mensagens_grupos mg3 
                WHERE mg3.grupo_id = g.id 
                ORDER BY mg3.data_envio DESC 
                LIMIT 1
            ) as horario_ultima_mensagem,
            COALESCE(
                (
                    SELECT COUNT(*) 
                    FROM mensagens_grupos mg4 
                    LEFT JOIN grupo_mensagens_lidas gml ON (
                        gml.grupo_id = g.id 
                        AND gml.user_id = ?
                        AND mg4.id <= gml.ultima_mensagem_lida
                    )
                    WHERE mg4.grupo_id = g.id 
                    AND mg4.remetente_id != ?
                    AND gml.id IS NULL
                ), 0
            ) as mensagens_nao_lidas
        FROM grupos g
        JOIN grupo_membros gm ON g.id = gm.grupo_id
        LEFT JOIN grupo_membros gm2 ON g.id = gm2.grupo_id
        WHERE gm.user_id = ? AND g.ativo = 1
        GROUP BY g.id, g.nome, g.descricao, g.foto, g.tipo, g.data_criacao, gm.papel
        ORDER BY 
            CASE 
                WHEN ultima_mensagem IS NOT NULL
                THEN (SELECT MAX(data_envio) FROM mensagens_grupos WHERE grupo_id = g.id)
                ELSE g.data_criacao
            END DESC
    ");
    
    $stmt->execute([$user_id, $user_id, $user_id]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($grupos as &$grupo) {
        if (empty($grupo['foto'])) {
            $grupo['foto'] = 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/group_default.png';
        }
        
        if ($grupo['ultima_mensagem']) {
            try {
                $decrypted_message = decrypt_message($grupo['ultima_mensagem']);
                $mensagem = $grupo['ultimo_remetente'] . ': ' . $decrypted_message;
            } catch (Exception $e) {
                $mensagem = $grupo['ultimo_remetente'] . ': [Mensagem não pôde ser descriptografada]';
            }
            
            if (strlen($mensagem) > 50) {
                $mensagem = substr($mensagem, 0, 47) . '...';
            }
            $grupo['ultima_mensagem_formatada'] = $mensagem;
        } else {
            $grupo['ultima_mensagem_formatada'] = 'Grupo criado';
        }
        
        $grupo['horario_formatado'] = $grupo['horario_ultima_mensagem'] ?? '';
    }
    
    echo json_encode([
        'success' => true,
        'grupos' => $grupos,
        'count' => count($grupos)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>