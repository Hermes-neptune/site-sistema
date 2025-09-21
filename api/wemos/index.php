<?php
require_once 'config.php';

function errorResponse($code, $message) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

function successResponse($data) {
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/site-sistema-git/site-sistema/api/wemos/index.php', '', $path);
$segments = explode('/', trim($path, '/'));

switch($method) {
    case 'GET':
        if ($segments[0] === 'aluno' && isset($segments[1])) {
            getAluno($pdo, $segments[1]);
        } else {
            errorResponse(404, 'Endpoint não encontrado');
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($segments[0] === 'debitar') {
            debitarCredito($pdo, $input);
        } else {
            errorResponse(404, 'Endpoint não encontrado');
        }
        break;
        
    default:
        errorResponse(405, 'Método não permitido');
}

/**
 * Busca informações do aluno pelo RM
 */
function getAluno($pdo, $rm) {
    try {
        if (!is_numeric($rm)) {
            errorResponse(400, 'RM deve ser numérico');
        }
        
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.nome_completo, u.rm, u.photo, u.tipo,
                   COALESCE(c.quantidade, 0) as creditos
            FROM users u
            LEFT JOIN creditos c ON u.id = c.username
            WHERE u.rm = :rm
        ");
        
        $stmt->bindParam(':rm', $rm, PDO::PARAM_INT);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            errorResponse(404, 'Usuário não encontrado');
        }
        
        $response = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome_completo'] ?: $usuario['username'],
            'username' => $usuario['username'],
            'rm' => (int)$usuario['rm'],
            'creditos' => (int)$usuario['creditos'],
            'tipo' => $usuario['tipo'],
            'photo' => $usuario['photo']
        ];
        
        successResponse($response);
        
    } catch(PDOException $e) {
        error_log("Erro ao buscar aluno: " . $e->getMessage());
        errorResponse(500, 'Erro interno do servidor');
    }
}

/**
 * Debita 1 crédito do usuário
 */
function debitarCredito($pdo, $input) {
    try {
        if (!isset($input['rm']) || !is_numeric($input['rm'])) {
            errorResponse(400, 'RM obrigatório e deve ser numérico');
        }
        
        $rm = $input['rm'];
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.nome_completo, u.rm,
                   COALESCE(c.quantidade, 0) as creditos
            FROM users u
            LEFT JOIN creditos c ON u.id = c.username
            WHERE u.rm = :rm
        ");
        
        $stmt->bindParam(':rm', $rm, PDO::PARAM_INT);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            $pdo->rollBack();
            errorResponse(404, 'Usuário não encontrado');
        }
        
        $creditosAtuais = (int)$usuario['creditos'];
        
        if ($creditosAtuais <= 0) {
            $pdo->rollBack();
            errorResponse(400, 'Créditos insuficientes');
        }
        
        $novoSaldo = $creditosAtuais - 1;
        $userId = $usuario['id'];
        
        $stmt = $pdo->prepare("SELECT id_cred FROM creditos WHERE username = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE creditos 
                SET quantidade = :novo_saldo 
                WHERE username = :user_id
            ");
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO creditos (quantidade, username) 
                VALUES (:novo_saldo, :user_id)
            ");
        }
        
        $stmt->bindParam(':novo_saldo', $novoSaldo, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $stmt = $pdo->prepare("
            INSERT INTO historico_creditos (user_id, quantidade, categoria, detalhes, data_adicao)
            VALUES (:user_id, :quantidade, 'Atividade', :detalhes, NOW())
        ");
        
        $quantidade = -1; 
        $detalhes = 'Débito via sistema Wemos - Consumo no refeitório';
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':detalhes', $detalhes);
        $stmt->execute();
        
        $pdo->commit();
        
        $response = [
            'usuario' => [
                'id' => $userId,
                'nome' => $usuario['nome_completo'] ?: $usuario['username'],
                'rm' => (int)$usuario['rm']
            ],
            'creditos_anterior' => $creditosAtuais,
            'creditos_atual' => $novoSaldo,
            'debito_realizado' => 1,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        successResponse($response);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Erro ao debitar crédito: " . $e->getMessage());
        errorResponse(500, 'Erro interno do servidor');
    }
}

/**
 * Endpoint adicional para consultar histórico (GET /historico/{rm})
 */
function getHistorico($pdo, $rm, $limit = 10) {
    try {
        if (!is_numeric($rm)) {
            errorResponse(400, 'RM deve ser numérico');
        }
        
        $stmt = $pdo->prepare("
            SELECT hc.quantidade, hc.categoria, hc.detalhes, hc.data_adicao,
                   u.nome_completo, u.username, u.rm
            FROM historico_creditos hc
            INNER JOIN users u ON hc.user_id = u.id
            WHERE u.rm = :rm
            ORDER BY hc.data_adicao DESC
            LIMIT :limit
        ");
        
        $stmt->bindParam(':rm', $rm, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$historico) {
            errorResponse(404, 'Nenhum histórico encontrado');
        }
        
        successResponse([
            'usuario' => [
                'nome' => $historico[0]['nome_completo'] ?: $historico[0]['username'],
                'rm' => (int)$historico[0]['rm']
            ],
            'historico' => array_map(function($item) {
                return [
                    'quantidade' => (int)$item['quantidade'],
                    'categoria' => $item['categoria'],
                    'detalhes' => $item['detalhes'],
                    'data' => $item['data_adicao']
                ];
            }, $historico)
        ]);
        
    } catch(PDOException $e) {
        error_log("Erro ao buscar histórico: " . $e->getMessage());
        errorResponse(500, 'Erro interno do servidor');
    }
}

/**
 * Endpoint para verificar status da API (GET /status)
 */
function getStatus($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total_usuarios FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        successResponse([
            'status' => 'online',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => 'connected',
            'total_usuarios' => (int)$result['total_usuarios']
        ]);
        
    } catch(PDOException $e) {
        errorResponse(500, 'Erro de conexão com banco de dados');
    }
}

if ($method === 'GET') {
    if ($segments[0] === 'status') {
        getStatus($pdo);
    } elseif ($segments[0] === 'historico' && isset($segments[1])) {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        getHistorico($pdo, $segments[1], $limit);
    }
}

?>