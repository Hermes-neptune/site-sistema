<?php
require '../config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['user_id']) || !isset($input['nome'])) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    $user_id = $input['user_id'];
    $nome = trim($input['nome']);
    $descricao = trim($input['descricao'] ?? '');
    $tipo = $input['tipo'] ?? 'privado';
    $max_membros = $input['max_membros'] ?? 50;
    $foto = $input['foto'] ?? 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/group_default.png';
    
    if (strlen($nome) < 3) {
        throw new Exception('Nome do grupo deve ter pelo menos 3 caracteres');
    }
    
    if (strlen($nome) > 100) {
        throw new Exception('Nome do grupo deve ter no máximo 100 caracteres');
    }
    
    $stmt_user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    if (!$stmt_user->fetch()) {
        throw new Exception('Usuário não encontrado');
    }
    
    $grupo_id = md5(uniqid(rand(), true));
    
    $pdo->beginTransaction();
    
    try {
        $stmt_grupo = $pdo->prepare("
            INSERT INTO grupos (id, nome, descricao, foto, criador_id, tipo, max_membros) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_grupo->execute([$grupo_id, $nome, $descricao, $foto, $user_id, $tipo, $max_membros]);
        
        $stmt_membro = $pdo->prepare("
            INSERT INTO grupo_membros (grupo_id, user_id, papel) 
            VALUES (?, ?, 'admin')
        ");
        $stmt_membro->execute([$grupo_id, $user_id]);
        
        $stmt_leitura = $pdo->prepare("
            INSERT INTO grupo_mensagens_lidas (grupo_id, user_id, ultima_mensagem_lida) 
            VALUES (?, ?, 0)
        ");
        $stmt_leitura->execute([$grupo_id, $user_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Grupo criado com sucesso',
            'grupo' => [
                'id' => $grupo_id,
                'nome' => $nome,
                'descricao' => $descricao,
                'foto' => $foto,
                'tipo' => $tipo,
                'papel' => 'admin',
                'total_membros' => 1
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>