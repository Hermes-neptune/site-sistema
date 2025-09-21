<?php
require '../config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['grupo_id']) || !isset($input['user_id']) || !isset($input['convidado_id'])) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    $grupo_id = $input['grupo_id'];
    $user_id = $input['user_id'];
    $convidado_id = $input['convidado_id'];
    
    $stmt_permissao = $pdo->prepare("
        SELECT papel FROM grupo_membros 
        WHERE grupo_id = ? AND user_id = ? AND papel IN ('admin', 'moderador')
    ");
    $stmt_permissao->execute([$grupo_id, $user_id]);
    if (!$stmt_permissao->fetch()) {
        throw new Exception('Você não tem permissão para convidar membros');
    }
    
    $stmt_convidado = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt_convidado->execute([$convidado_id]);
    $convidado = $stmt_convidado->fetch();
    if (!$convidado) {
        throw new Exception('Usuário não encontrado');
    }
    
    $stmt_membro = $pdo->prepare("SELECT id FROM grupo_membros WHERE grupo_id = ? AND user_id = ?");
    $stmt_membro->execute([$grupo_id, $convidado_id]);
    if ($stmt_membro->fetch()) {
        throw new Exception('Usuário já é membro do grupo');
    }
    
    $stmt_convite = $pdo->prepare("
        SELECT id FROM grupo_convites 
        WHERE grupo_id = ? AND convidado_id = ? AND status = 'pendente'
    ");
    $stmt_convite->execute([$grupo_id, $convidado_id]);
    if ($stmt_convite->fetch()) {
        throw new Exception('Já existe um convite pendente para este usuário');
    }
    
    $stmt_limite = $pdo->prepare("
        SELECT g.max_membros, COUNT(gm.id) as total_membros
        FROM grupos g
        LEFT JOIN grupo_membros gm ON g.id = gm.grupo_id
        WHERE g.id = ?
        GROUP BY g.id, g.max_membros
    ");
    $stmt_limite->execute([$grupo_id]);
    $limite = $stmt_limite->fetch();
    
    if ($limite['total_membros'] >= $limite['max_membros']) {
        throw new Exception('Grupo atingiu o limite máximo de membros');
    }
    
    $stmt_criar = $pdo->prepare("
        INSERT INTO grupo_convites (grupo_id, convidado_id, convidado_por) 
        VALUES (?, ?, ?)
    ");
    $stmt_criar->execute([$grupo_id, $convidado_id, $user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Convite enviado com sucesso',
        'convidado' => $convidado['username']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>