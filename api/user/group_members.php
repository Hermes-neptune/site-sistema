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
    $membro_info = $stmt_membro->fetch();
    if (!$membro_info) {
        throw new Exception('Você não tem acesso a este grupo');
    }
    
    $stmt_membros = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.photo,
            gm.papel,
            gm.data_entrada,
            DATE_FORMAT(gm.data_entrada, '%d/%m/%Y às %H:%i') as data_entrada_formatada
        FROM grupo_membros gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.grupo_id = ?
        ORDER BY 
            CASE gm.papel 
                WHEN 'admin' THEN 1 
                WHEN 'moderador' THEN 2 
                ELSE 3 
            END,
            u.username
    ");
    $stmt_membros->execute([$grupo_id]);
    $membros = $stmt_membros->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($membros as &$membro) {
        if (empty($membro['photo'])) {
            $membro['photo'] = 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/user.png';
        }
    }
    
    echo json_encode([
        'success' => true,
        'membros' => $membros,
        'count' => count($membros),
        'user_role' => $membro_info['papel']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>