<?php
require '../config_api.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $action = $input['action'] ?? '';
    
    if ($action === 'get_invites') {
        $user_id = $input['user_id'] ?? '';
        
        if (empty($user_id)) {
            throw new Exception('ID do usuário é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                gc.id,
                gc.grupo_id,
                gc.convidado_por,
                gc.data_convite,
                gc.expira_em,
                g.nome as grupo_nome,
                g.descricao as grupo_descricao,
                g.foto as grupo_foto,
                g.tipo as grupo_tipo,
                u.username as convidado_por_nome,
                u.photo as convidado_por_foto
            FROM grupo_convites gc
            INNER JOIN grupos g ON gc.grupo_id = g.id
            INNER JOIN users u ON gc.convidado_por = u.id
            WHERE gc.convidado_id = ? 
            AND gc.status = 'pendente'
            AND gc.expira_em > NOW()
            ORDER BY gc.data_convite DESC
        ");
        
        $stmt->execute([$user_id]);
        $invites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($invites as &$invite) {
            if ($invite['data_convite']) {
                $date = new DateTime($invite['data_convite']);
                $invite['data_convite'] = $date->format('d/m/Y H:i');
            }
            if ($invite['expira_em']) {
                $date = new DateTime($invite['expira_em']);
                $invite['expira_em'] = $date->format('d/m/Y H:i');
            }
        }
        
        echo json_encode([
            'success' => true,
            'invites' => $invites
        ]);
        
    } elseif ($action === 'get_count') {
        $user_id = $input['user_id'] ?? '';
        
        if (empty($user_id)) {
            throw new Exception('ID do usuário é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM grupo_convites 
            WHERE convidado_id = ? 
            AND status = 'pendente'
            AND expira_em > NOW()
        ");
        
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'count' => (int)$result['count']
        ]);
        
    } elseif ($action === 'aceitar_convite') {
        $invite_id = $input['invite_id'] ?? '';
        
        if (empty($invite_id)) {
            throw new Exception('ID do convite é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT gc.*, g.nome as grupo_nome, g.max_membros
            FROM grupo_convites gc
            INNER JOIN grupos g ON gc.grupo_id = g.id
            WHERE gc.id = ? 
            AND gc.status = 'pendente'
            AND gc.expira_em > NOW()
        ");
        $stmt->execute([$invite_id]);
        $invite = $stmt->fetch();
        
        if (!$invite) {
            throw new Exception('Convite não encontrado, já processado ou expirado');
        }
        
        $stmt = $pdo->prepare("
            SELECT id FROM grupo_membros 
            WHERE grupo_id = ? AND user_id = ?
        ");
        $stmt->execute([$invite['grupo_id'], $invite['convidado_id']]);
        if ($stmt->fetch()) {
            throw new Exception('Você já é membro deste grupo');
        }
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM grupo_membros 
            WHERE grupo_id = ?
        ");
        $stmt->execute([$invite['grupo_id']]);
        $memberCount = $stmt->fetch()['count'];
        
        if ($memberCount >= $invite['max_membros']) {
            throw new Exception('O grupo atingiu o limite máximo de membros');
        }
        
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("
                UPDATE grupo_convites 
                SET status = 'aceito', data_resposta = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$invite_id]);
            
            $stmt = $pdo->prepare("
                INSERT INTO grupo_membros (grupo_id, user_id, papel, data_entrada, convidado_por)
                VALUES (?, ?, 'membro', NOW(), ?)
            ");
            $stmt->execute([$invite['grupo_id'], $invite['convidado_id'], $invite['convidado_por']]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Convite aceito! Você agora é membro do grupo "' . $invite['grupo_nome'] . '"'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
    } elseif ($action === 'rejeitar_convite') {
        $invite_id = $input['invite_id'] ?? '';
        
        if (empty($invite_id)) {
            throw new Exception('ID do convite é obrigatório');
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM grupo_convites 
            WHERE id = ? AND status = 'pendente'
        ");
        $stmt->execute([$invite_id]);
        $invite = $stmt->fetch();
        
        if (!$invite) {
            throw new Exception('Convite não encontrado ou já processado');
        }
        
        $stmt = $pdo->prepare("
            UPDATE grupo_convites 
            SET status = 'rejeitado', data_resposta = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$invite_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Convite rejeitado'
        ]);
        
    } elseif ($action === 'enviar_convite') {
        $grupo_id = $input['grupo_id'] ?? '';
        $convidado_id = $input['convidado_id'] ?? '';
        $convidado_por = $input['convidado_por'] ?? '';
        
        if (empty($grupo_id) || empty($convidado_id) || empty($convidado_por)) {
            throw new Exception('Dados obrigatórios não fornecidos');
        }
        
        $stmt = $pdo->prepare("
            SELECT papel FROM grupo_membros 
            WHERE grupo_id = ? AND user_id = ?
        ");
        $stmt->execute([$grupo_id, $convidado_por]);
        $memberRole = $stmt->fetch();
        
        if (!$memberRole) {
            throw new Exception('Você não é membro deste grupo');
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$convidado_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Usuário não encontrado');
        }
        
        $stmt = $pdo->prepare("
            SELECT id FROM grupo_membros 
            WHERE grupo_id = ? AND user_id = ?
        ");
        $stmt->execute([$grupo_id, $convidado_id]);
        if ($stmt->fetch()) {
            throw new Exception('Este usuário já é membro do grupo');
        }
        
        $stmt = $pdo->prepare("
            SELECT id FROM grupo_convites 
            WHERE grupo_id = ? AND convidado_id = ? 
            AND status = 'pendente' AND expira_em > NOW()
        ");
        $stmt->execute([$grupo_id, $convidado_id]);
        if ($stmt->fetch()) {
            throw new Exception('Já existe um convite pendente para este usuário');
        }
        
        $stmt = $pdo->prepare("
            SELECT g.max_membros, COUNT(gm.id) as current_members
            FROM grupos g
            LEFT JOIN grupo_membros gm ON g.id = gm.grupo_id
            WHERE g.id = ?
            GROUP BY g.id
        ");
        $stmt->execute([$grupo_id]);
        $groupInfo = $stmt->fetch();
        
        if ($groupInfo && $groupInfo['current_members'] >= $groupInfo['max_membros']) {
            throw new Exception('O grupo atingiu o limite máximo de membros');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO grupo_convites (grupo_id, convidado_id, convidado_por, status, data_convite, expira_em)
            VALUES (?, ?, ?, 'pendente', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        $stmt->execute([$grupo_id, $convidado_id, $convidado_por]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Convite enviado com sucesso!'
        ]);
        
    } elseif ($action === 'limpar_convites_expirados') {
        $stmt = $pdo->prepare("
            UPDATE grupo_convites 
            SET status = 'expirado' 
            WHERE status = 'pendente' AND expira_em <= NOW()
        ");
        $stmt->execute();
        
        $affected = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => "$affected convites expirados foram limpos"
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