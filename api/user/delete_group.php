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
    
    $stmt_admin = $pdo->prepare("
        SELECT papel FROM grupo_membros 
        WHERE grupo_id = ? AND user_id = ? AND papel = 'admin'
    ");
    $stmt_admin->execute([$grupo_id, $user_id]);
    if (!$stmt_admin->fetch()) {
        throw new Exception('Apenas administradores podem deletar o grupo');
    }
    
    $pdo->beginTransaction();
    
    try {
        $stmt_deletar = $pdo->prepare("
            UPDATE grupos 
            SET ativo = 0 
            WHERE id = ?
        ");
        $stmt_deletar->execute([$grupo_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Grupo deletado com sucesso'
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