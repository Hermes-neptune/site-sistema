<?php
require '../config_api.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'get_requests') {
        $user_id = $input['user_id'] ?? '';
        
        if (empty($user_id)) {
            throw new Exception('ID do usuário é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.user_id,
                a.data_solicitacao,
                u.username,
                u.photo,
                u.email
            FROM amizades a
            INNER JOIN users u ON a.user_id = u.id
            WHERE a.friend_id = ? 
            AND a.status = 'pendente'
            ORDER BY a.data_solicitacao DESC
        ");
        
        $stmt->execute([$user_id]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($requests as &$request) {
            if ($request['data_solicitacao']) {
                $date = new DateTime($request['data_solicitacao']);
                $request['data_solicitacao'] = $date->format('d/m/Y H:i');
            }
        }
        
        echo json_encode([
            'success' => true,
            'requests' => $requests
        ]);
        
    } elseif ($action === 'get_count') {
        $user_id = $input['user_id'] ?? '';
        
        if (empty($user_id)) {
            throw new Exception('ID do usuário é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM amizades 
            WHERE friend_id = ? AND status = 'pendente'
        ");
        
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'count' => (int)$result['count']
        ]);
        
    } elseif ($action === 'aceitar_solicitacao') {
        $request_id = $input['request_id'] ?? '';
        
        if (empty($request_id)) {
            throw new Exception('ID da solicitação é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM amizades 
            WHERE id = ? AND status = 'pendente'
        ");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();
        
        if (!$request) {
            throw new Exception('Solicitação não encontrada ou já processada');
        }
        
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("
                UPDATE amizades 
                SET status = 'aceito', data_aceite = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$request_id]);
            
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO amizades (user_id, friend_id, status, data_solicitacao, data_aceite)
                VALUES (?, ?, 'aceito', NOW(), NOW())
            ");
            $stmt->execute([$request['friend_id'], $request['user_id']]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Solicitação de amizade aceita com sucesso!'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
    } elseif ($action === 'rejeitar_solicitacao') {
        $request_id = $input['request_id'] ?? '';
        
        if (empty($request_id)) {
            throw new Exception('ID da solicitação é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM amizades 
            WHERE id = ? AND status = 'pendente'
        ");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch();
        
        if (!$request) {
            throw new Exception('Solicitação não encontrada ou já processada');
        }
        
        $stmt = $pdo->prepare("
            UPDATE amizades 
            SET status = 'rejeitado' 
            WHERE id = ?
        ");
        $stmt->execute([$request_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Solicitação de amizade rejeitada'
        ]);
        
    } elseif ($action === 'enviar_solicitacao') {
        $user_id = $input['user_id'] ?? '';
        $friend_id = $input['friend_id'] ?? '';
        
        if (empty($user_id) || empty($friend_id)) {
            throw new Exception('IDs dos usuários são obrigatórios');
        }
        
        if ($user_id === $friend_id) {
            throw new Exception('Você não pode enviar solicitação para si mesmo');
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM amizades 
            WHERE (user_id = ? AND friend_id = ?) 
            OR (user_id = ? AND friend_id = ?)
        ");
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['status'] === 'pendente') {
                throw new Exception('Já existe uma solicitação pendente');
            } elseif ($existing['status'] === 'aceito') {
                throw new Exception('Vocês já são amigos');
            }
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$friend_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Usuário não encontrado');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO amizades (user_id, friend_id, status, data_solicitacao)
            VALUES (?, ?, 'pendente', NOW())
        ");
        $stmt->execute([$user_id, $friend_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Solicitação de amizade enviada com sucesso!'
        ]);
        
    } else {
        throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>