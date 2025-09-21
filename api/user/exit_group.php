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
    $membro = $stmt_membro->fetch();
    if (!$membro) {
        throw new Exception('Você não é membro deste grupo');
    }
    
    $stmt_criador = $pdo->prepare("
        SELECT criador_id FROM grupos WHERE id = ?
    ");
    $stmt_criador->execute([$grupo_id]);
    $grupo = $stmt_criador->fetch();
    
    if ($grupo['criador_id'] === $user_id) {
        $stmt_admins = $pdo->prepare("
            SELECT COUNT(*) as total_admins 
            FROM grupo_membros 
            WHERE grupo_id = ? AND papel = 'admin' AND user_id != ?
        ");
        $stmt_admins->execute([$grupo_id, $user_id]);
        $admins = $stmt_admins->fetch();
        
        if ($admins['total_admins'] == 0) {
            $stmt_promover = $pdo->prepare("
                SELECT user_id FROM grupo_membros 
                WHERE grupo_id = ? AND user_id != ? 
                ORDER BY data_entrada ASC 
                LIMIT 1
            ");
            $stmt_promover->execute([$grupo_id, $user_id]);
            $novo_admin = $stmt_promover->fetch();
            
            if ($novo_admin) {
                $stmt_update = $pdo->prepare("
                    UPDATE grupo_membros 
                    SET papel = 'admin' 
                    WHERE grupo_id = ? AND user_id = ?
                ");
                $stmt_update->execute([$grupo_id, $novo_admin['user_id']]);
                
                $stmt_update_grupo = $pdo->prepare("
                    UPDATE grupos 
                    SET criador_id = ? 
                    WHERE id = ?
                ");
                $stmt_update_grupo->execute([$novo_admin['user_id'], $grupo_id]);
            }
        }
    }
    
    $stmt_remover = $pdo->prepare("
        DELETE FROM grupo_membros 
        WHERE grupo_id = ? AND user_id = ?
    ");
    $stmt_remover->execute([$grupo_id, $user_id]);
    
    $stmt_leitura = $pdo->prepare("
        DELETE FROM grupo_mensagens_lidas 
        WHERE grupo_id = ? AND user_id = ?
    ");
    $stmt_leitura->execute([$grupo_id, $user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Você saiu do grupo com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
