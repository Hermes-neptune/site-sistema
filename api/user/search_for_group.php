<?php
require '../config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['user_id']) || !isset($input['grupo_id'])) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    $user_id = $input['user_id'];
    $grupo_id = $input['grupo_id'];
    $search = $input['search'] ?? '';
    
    $stmt_permissao = $pdo->prepare("
        SELECT papel FROM grupo_membros 
        WHERE grupo_id = ? AND user_id = ? AND papel IN ('admin', 'moderador')
    ");
    $stmt_permissao->execute([$grupo_id, $user_id]);
    if (!$stmt_permissao->fetch()) {
        throw new Exception('Você não tem permissão para convidar membros');
    }
    
    $stmt_amigos = $pdo->prepare("
        SELECT DISTINCT u.id, u.username, u.photo 
        FROM amizades a 
        JOIN users u ON (
            CASE 
                WHEN a.user_id = ? THEN u.id = a.friend_id
                WHEN a.friend_id = ? THEN u.id = a.user_id
            END
        )
        WHERE (a.user_id = ? OR a.friend_id = ?) 
        AND a.status = 'aceito' 
        AND u.id != ?
        AND u.id NOT IN (
            SELECT gm.user_id 
            FROM grupo_membros gm 
            WHERE gm.grupo_id = ?
        )
        AND u.id NOT IN (
            SELECT gc.convidado_id 
            FROM grupo_convites gc 
            WHERE gc.grupo_id = ? AND gc.status = 'pendente'
        )
        AND (? = '' OR u.username LIKE ?)
        ORDER BY u.username
        LIMIT 20
    ");
    
    $searchParam = '%' . $search . '%';
    $stmt_amigos->execute([
        $user_id, $user_id, $user_id, $user_id, $user_id,
        $grupo_id, $grupo_id, $search, $searchParam
    ]);
    $amigos = $stmt_amigos->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($amigos as &$amigo) {
        if (empty($amigo['photo'])) {
            $amigo['photo'] = 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/user.png';
        }
    }
    
    echo json_encode([
        'success' => true,
        'usuarios' => $amigos,
        'count' => count($amigos)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>