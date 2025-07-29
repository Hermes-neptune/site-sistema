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

    $user_id = $input['user_id'];
    $filter = isset($input['filter']) ? $input['filter'] : 'Todos';

    $query = "
        SELECT 
            hc.id,
            hc.quantidade,
            hc.categoria as type,
            hc.detalhes as details,
            hc.data_adicao as date,
            u.username as studentName,
            u.nome_completo as fullName,
            u.rm as studentRM
        FROM historico_creditos hc
        JOIN users u ON hc.user_id = u.id
        WHERE hc.user_id = :user_id
    ";

    $params = [':user_id' => $user_id];

    switch ($filter) {
        case 'Doações':
            $query .= " AND hc.categoria = 'Doacao'";
            break;
        case 'Atividades':
            $query .= " AND hc.categoria = 'Atividade'";
            break;
        case 'Últimos 30 dias':
            $query .= " AND hc.data_adicao >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'Todos':
        default:
            break;
    }

    $query .= " ORDER BY hc.data_adicao DESC LIMIT 100"; 

    $stmt = $pdo->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedHistory = [];
    foreach ($history as $item) {
        $formattedHistory[] = [
            'id' => $item['id'],
            'date' => $item['date'],
            'studentName' => $item['fullName'] ?: $item['studentName'], 
            'studentRM' => $item['studentRM'],
            'credits' => (int)$item['quantidade'],
            'type' => $item['type'] === 'Doacao' ? 'Doação' : 'Atividade',
            'details' => $item['details'],
            'quantity' => ''
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Histórico recuperado com sucesso',
        'history' => $formattedHistory,
        'total' => count($formattedHistory)
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