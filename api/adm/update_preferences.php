<?php
require '../config_api.php';

if (file_exists(__DIR__ . '/../../.env')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
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
        'email_notifications',
        'push_notifications',
        'message_notifications',
        'credit_alerts',
        'mobile_notif'
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

    $stmt = $pdo->prepare("SELECT id FROM user_preferences WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (:user_id)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    $sql = "UPDATE user_preferences SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $stmt = $pdo->prepare("
        SELECT 
            email_notifications,
            push_notifications,
            message_notifications,
            credit_alerts,
            mobile_notif
        FROM user_preferences 
        WHERE user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);

    $preferences['email_notifications'] = (bool)$preferences['email_notifications'];
    $preferences['push_notifications'] = (bool)$preferences['push_notifications'];
    $preferences['message_notifications'] = (bool)$preferences['message_notifications'];
    $preferences['credit_alerts'] = (bool)$preferences['credit_alerts'];
    $preferences['mobile_notif'] = (bool)$preferences['mobile_notif'];

    echo json_encode([
        'success' => true,
        'message' => 'Preferências atualizadas com sucesso',
        'preferences' => $preferences
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