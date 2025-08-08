<?php
require '../config_api.php';

header('Content-Type: application/json; charset=utf-8');

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

    if (!$input) {
        throw new Exception('Dados não fornecidos');
    }

    if (!isset($input['rm']) || !isset($input['quantidade_creditos']) || !isset($input['tipo'])) {
        throw new Exception('RM, quantidade de créditos e tipo são obrigatórios');
    }

    $rm = (int)$input['rm'];
    $quantidade_creditos = (int)$input['quantidade_creditos'];
    $tipo = trim($input['tipo']);

    if ($rm <= 0) {
        throw new Exception('RM deve ser um número válido');
    }

    if ($quantidade_creditos <= 0) {
        throw new Exception('Quantidade de créditos deve ser maior que zero');
    }

    if (!in_array($tipo, ['Doação', 'Participação em Atividades'])) {
        throw new Exception('Tipo de crédito inválido');
    }

    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE rm = :rm LIMIT 1");
    $stmt->bindParam(':rm', $rm, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception('Usuário não encontrado com este RM');
    }

    $user_id = $user['id'];
    $username = $user['username'];

    $detalhes = '';
    if ($tipo === 'Doação') {
        if (!isset($input['o_que_foi_doado']) || !isset($input['quantidade_doacao'])) {
            throw new Exception('Para doação, é necessário informar o que foi doado e a quantidade');
        }
        $o_que_foi_doado = trim($input['o_que_foi_doado']);
        $quantidade_doacao = trim($input['quantidade_doacao']);
        
        if (empty($o_que_foi_doado) || empty($quantidade_doacao)) {
            throw new Exception('Todos os campos de doação devem ser preenchidos');
        }
        
        $detalhes = "Doação: {$o_que_foi_doado} (Quantidade: {$quantidade_doacao})";
        
    } elseif ($tipo === 'Participação em Atividades') {
        if (!isset($input['atividade_realizada'])) {
            throw new Exception('Para participação em atividades, é necessário informar a atividade realizada');
        }
        $atividade_realizada = trim($input['atividade_realizada']);
        
        if (empty($atividade_realizada)) {
            throw new Exception('A descrição da atividade deve ser preenchida');
        }
        
        $detalhes = "Atividade: {$atividade_realizada}";
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS historico_creditos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(32) NOT NULL,
                quantidade INT NOT NULL,
                categoria ENUM('Doacao', 'Atividade') NOT NULL,
                detalhes TEXT,
                data_adicao DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_data_adicao (data_adicao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        ");
    } catch (Exception $e) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS historico_creditos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(32) NOT NULL,
                quantidade INT NOT NULL,
                categoria VARCHAR(20) NOT NULL,
                detalhes TEXT,
                data_adicao DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
        ");
    }

    $stmt = $pdo->prepare("SELECT quantidade FROM creditos WHERE username = :user_id LIMIT 1");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();

    $creditos_atuais = $stmt->fetch(PDO::FETCH_ASSOC);
    $creditos_totals = 0;

    if ($creditos_atuais) {
        $nova_quantidade = $creditos_atuais['quantidade'] + $quantidade_creditos;
        $stmt = $pdo->prepare("UPDATE creditos SET quantidade = :quantidade WHERE username = :user_id");
        $stmt->bindParam(':quantidade', $nova_quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar créditos existentes');
        }
        
        $creditos_totals = $nova_quantidade;
    } else {
        $stmt = $pdo->prepare("INSERT INTO creditos (quantidade, username) VALUES (:quantidade, :user_id)");
        $stmt->bindParam(':quantidade', $quantidade_creditos, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao inserir novos créditos');
        }
        
        $creditos_totals = $quantidade_creditos;
    }

    $categoria = ($tipo === 'Doação') ? 'Doacao' : 'Atividade';
    
    $stmt = $pdo->prepare("
        INSERT INTO historico_creditos (user_id, quantidade, categoria, detalhes) 
        VALUES (:user_id, :quantidade, :categoria, :detalhes)
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindParam(':quantidade', $quantidade_creditos, PDO::PARAM_INT);
    $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
    $stmt->bindParam(':detalhes', $detalhes, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        error_log("Erro ao inserir histórico: " . print_r($stmt->errorInfo(), true));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Créditos adicionados com sucesso',
        'data' => [
            'usuario' => $username,
            'rm' => $rm,
            'creditos_adicionados' => $quantidade_creditos,
            'creditos_totais' => $creditos_totals,
            'tipo' => $tipo,
            'detalhes' => $detalhes
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>