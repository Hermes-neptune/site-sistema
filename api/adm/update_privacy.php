<?php
require '../config_api.php';

if (file_exists(__DIR__ . '/../../../../../.env')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../');
    $dotenv->load();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['user_id'])) {
        throw new Exception('ID do usuário é obrigatório');
    }

    $user_id = trim($input['user_id']);

    if (empty($user_id)) {
        throw new Exception('ID do usuário não pode estar vazio');
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if (!$stmt->fetch()) {
        throw new Exception('Usuário não encontrado');
    }

    $updateFields = [];
    $params = [':user_id' => $user_id];

    $allowedFields = [
        'public_profile',
        'show_online_status', 
        'allow_direct_messages',
        'share_activity'
    ];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $input[$field] ? 1 : 0;
        }
    }

    if (empty($updateFields)) {
        throw new Exception('Nenhum campo válido para atualização foi fornecido');
    }

    $stmt = $pdo->prepare("SELECT id FROM user_privacy WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO user_privacy (user_id) VALUES (:user_id)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    $sql = "UPDATE user_privacy SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $stmt = $pdo->prepare("
        SELECT 
            public_profile,
            show_online_status,
            allow_direct_messages,
            share_activity
        FROM user_privacy 
        WHERE user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $privacy = $stmt->fetch(PDO::FETCH_ASSOC);

    $privacy['public_profile'] = (bool)$privacy['public_profile'];
    $privacy['show_online_status'] = (bool)$privacy['show_online_status'];
    $privacy['allow_direct_messages'] = (bool)$privacy['allow_direct_messages'];
    $privacy['share_activity'] = (bool)$privacy['share_activity'];

    echo json_encode([
        'success' => true,
        'message' => 'Configurações de privacidade atualizadas com sucesso',
        'privacy' => $privacy
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>