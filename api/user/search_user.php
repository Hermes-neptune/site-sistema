<?php
require '../config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['current_user_id'])) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    $current_user_id = $input['current_user_id'];
    $search = $input['search'] ?? '';
    
    $stmt_usuarios = $pdo->prepare("
        SELECT DISTINCT 
            u.id, 
            u.username, 
            u.nome_completo,
            u.photo,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM amizades 
                    WHERE ((user_id = ? AND friend_id = u.id) OR (user_id = u.id AND friend_id = ?)) 
                    AND status = 'aceito'
                ) THEN 1 ELSE 0 
            END as is_friend,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM amizades 
                    WHERE user_id = ? AND friend_id = u.id AND status = 'pendente'
                ) THEN 1 ELSE 0 
            END as request_sent,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM amizades 
                    WHERE user_id = u.id AND friend_id = ? AND status = 'pendente'
                ) THEN 1 ELSE 0 
            END as pending_request
        FROM users u 
        WHERE u.id != ?
        AND (? = '' OR u.username LIKE ? OR u.nome_completo LIKE ?)
        ORDER BY u.username
        LIMIT 50
    ");
    
    $searchParam = '%' . $search . '%';
    $stmt_usuarios->execute([
        $current_user_id, $current_user_id, $current_user_id, $current_user_id, 
        $current_user_id, $search, $searchParam, $searchParam
    ]);
    
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usuarios as &$usuario) {
        if (empty($usuario['photo'])) {
            $usuario['photo'] = 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/user.png';
        }
    }
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios,
        'count' => count($usuarios)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>