<?php
require '../config_api.php';

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
    
    $stmt_membro = $pdo->prepare("
        SELECT papel FROM grupo_membros 
        WHERE grupo_id = ? AND user_id = ?
    ");
    $stmt_membro->execute([$grupo_id, $user_id]);
    $user_role = $stmt_membro->fetch();
    if (!$user_role) {
        throw new Exception('Você não tem acesso a este grupo');
    }
    
    $stmt_grupo = $pdo->prepare("
        SELECT 
            g.*,
            u.username as criador_nome,
            COUNT(gm.id) as total_membros
        FROM grupos g
        JOIN users u ON g.criador_id = u.id
        LEFT JOIN grupo_membros gm ON g.id = gm.grupo_id
        WHERE g.id = ? AND g.ativo = 1
        GROUP BY g.id
    ");
    $stmt_grupo->execute([$grupo_id]);
    $grupo = $stmt_grupo->fetch(PDO::FETCH_ASSOC);
    
    if (!$grupo) {
        throw new Exception('Grupo não encontrado');
    }
    
    $grupo['user_role'] = $user_role['papel'];
    
    echo json_encode([
        'success' => true,
        'grupo' => $grupo
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>