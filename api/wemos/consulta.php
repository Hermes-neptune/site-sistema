<?php
require '../config_api.php';

function jsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([
        'sucesso' => false,
        'erro' => 'Método não permitido. Use GET.'
    ], 405);
}

if (!isset($_GET['rm']) || empty($_GET['rm'])) {
    jsonResponse([
        'sucesso' => false,
        'erro' => 'Parâmetro RM é obrigatório.'
    ], 400);
}

$rm = trim($_GET['rm']);

if (!preg_match('/^\d{5}$/', $rm)) {
    jsonResponse([
        'sucesso' => false,
        'erro' => 'RM deve conter exatamente 5 dígitos numéricos.'
    ], 400);
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.rm, 
            COALESCE(u.nome_completo, u.username) as nome,
            COALESCE(c.quantidade, 0) as creditos
        FROM users u
        LEFT JOIN creditos c ON u.id = c.username
        WHERE u.rm = ?
    ");
    $stmt->execute([$rm]);
    $aluno = $stmt->fetch();
    
    if ($aluno) {
        jsonResponse([
            'sucesso' => true,
            'nome' => $aluno['nome'],
            'creditos' => (int)$aluno['creditos'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        jsonResponse([
            'sucesso' => false,
            'erro' => 'Aluno não encontrado com o RM informado.',
            'rm' => $rm
        ], 404);
    }
    
} catch (PDOException $e) {
    error_log("Erro na consulta: " . $e->getMessage());
    jsonResponse([
        'sucesso' => false,
        'erro' => 'Erro interno do servidor. Tente novamente mais tarde.'
    ], 500);
}
?>