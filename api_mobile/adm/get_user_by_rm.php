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

    if (!$input || !isset($input['rm'])) {
        throw new Exception('RM é obrigatório');
    }

    $rm = (int)$input['rm'];

    if ($rm <= 0) {
        throw new Exception('RM deve ser um número válido');
    }

    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.nome_completo,
            u.email,
            u.rm,
            u.photo,
            u.tipo,
            COALESCE(c.quantidade, 0) as creditos_atuais
        FROM users u 
        LEFT JOIN creditos c ON u.id = c.username 
        WHERE u.rm = :rm
    ");
    $stmt->bindParam(':rm', $rm);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Usuário não encontrado com este RM');
    }

    $historico = [];
    try {
        $stmt = $pdo->prepare("
            SELECT 
                quantidade,
                tipo,
                detalhes,
                data_adicao
            FROM historico_creditos 
            WHERE user_id = :user_id 
            ORDER BY data_adicao DESC 
            LIMIT 10
        ");
        $stmt->bindParam(':user_id', $user['id']);
        $stmt->execute();
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $historico = [];
    }

    unset($user['id']); 

    echo json_encode([
        'success' => true,
        'message' => 'Usuário encontrado',
        'user' => $user,
        'historico_creditos' => $historico
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