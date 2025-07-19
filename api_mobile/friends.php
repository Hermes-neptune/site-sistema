<?php
    require 'config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['user_id'])) {
        throw new Exception('ID do usuário é obrigatório');
    }
    
    $user_id = $input['user_id'];
    
    $stmt_amigos = $pdo->prepare("
        SELECT u.id, u.username, u.photo 
        FROM amizades a 
        JOIN users u ON (a.user_id = u.id OR a.friend_id = u.id) 
        WHERE (a.user_id = ? OR a.friend_id = ?) AND a.status = 'aceito' AND u.id != ?
        ORDER BY u.username
    ");
    $stmt_amigos->execute([$user_id, $user_id, $user_id]);
    $amigos = $stmt_amigos->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($amigos as &$amigo) {
        if (empty($amigo['photo'])) {
            $amigo['photo'] = 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser//user.png';
        }
    }
    
    echo json_encode([
        'success' => true,
        'friends' => $amigos,
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