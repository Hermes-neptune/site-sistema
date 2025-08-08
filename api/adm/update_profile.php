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
    $nome_completo = isset($input['nome_completo']) ? trim($input['nome_completo']) : null;
    $email = isset($input['email']) ? trim($input['email']) : null;
    $telefone = isset($input['telefone']) ? trim($input['telefone']) : null;

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    if (!empty($nome_completo) && strlen($nome_completo) < 2) {
        throw new Exception('Nome deve ter pelo menos 2 caracteres');
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    if (!$stmt->fetch()) {
        throw new Exception('Usuário não encontrado');
    }

    $updateFields = [];
    $params = ['user_id' => $user_id];

    if ($nome_completo !== null) {
        $updateFields[] = "nome_completo = :nome_completo";
        $params['nome_completo'] = $nome_completo;
    }

    if ($email !== null) {
        $emailCheckStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $emailCheckStmt->bindParam(':email', $email);
        $emailCheckStmt->bindParam(':user_id', $user_id);
        $emailCheckStmt->execute();

        if ($emailCheckStmt->fetch()) {
            throw new Exception('Este email já está sendo usado por outro usuário');
        }

        $updateFields[] = "email = :email";
        $params['email'] = $email;
    }

    if ($telefone !== null) {
        try {
            $columnCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'telefone'");
            if ($columnCheck->rowCount() > 0) {
                $updateFields[] = "telefone = :telefone";
                $params['telefone'] = $telefone;
            }
        } catch (Exception $e) {
        }
    }

    if (empty($updateFields)) {
        throw new Exception('Nenhum campo válido para atualizar');
    }

    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindParam(':' . $key, $params[$key]);
    }

    if (!$stmt->execute()) {
        throw new Exception('Erro ao atualizar dados do usuário');
    }

    $stmt = $pdo->prepare("
        SELECT u.*, COALESCE(c.quantidade, 0) as creditos 
        FROM users u 
        LEFT JOIN creditos c ON u.id = c.username 
        WHERE u.id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$updatedUser) {
        throw new Exception('Erro ao recuperar dados atualizados');
    }

    unset($updatedUser['password']);

    echo json_encode([
        'success' => true,
        'message' => 'Perfil atualizado com sucesso',
        'user' => $updatedUser
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