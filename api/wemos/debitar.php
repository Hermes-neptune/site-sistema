<?php
require '../config_api.php';

function jsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'sucesso' => false,
        'erro' => 'Método não permitido. Use POST.'
    ], 405);
}

if (!isset($_POST['rm']) || empty($_POST['rm'])) {
    jsonResponse([
        'sucesso' => false,
        'erro' => 'Parâmetro RM é obrigatório.'
    ], 400);
}

$rm = trim($_POST['rm']);

if (!preg_match('/^\d{5}$/', $rm)) {
    jsonResponse([
        'sucesso' => false,
        'erro' => 'RM deve conter exatamente 5 dígitos numéricos.'
    ], 400);
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("SELECT id, rm, COALESCE(nome_completo, username) as nome FROM users WHERE rm = ?");
    $stmt->execute([$rm]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $pdo->rollback();
        jsonResponse([
            'sucesso' => false,
            'erro' => 'Aluno não encontrado com o RM informado.',
            'rm' => $rm
        ], 404);
    }
    
    $userId = $usuario['id'];
    
    $stmt = $pdo->prepare("SELECT quantidade FROM creditos WHERE username = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $credito = $stmt->fetch();
    
    $creditosAtuais = $credito ? (int)$credito['quantidade'] : 0;
    
    if ($creditosAtuais <= 0) {
        $pdo->rollback();
        jsonResponse([
            'sucesso' => false,
            'erro' => 'Aluno não possui créditos suficientes.',
            'rm' => str_pad($usuario['rm'], 5, '0', STR_PAD_LEFT),
            'nome' => $usuario['nome'],
            'creditos' => $creditosAtuais
        ], 400);
    }
    
    $novoSaldo = $creditosAtuais - 1;
    
    if ($credito) {
        $stmt = $pdo->prepare("UPDATE creditos SET quantidade = ? WHERE username = ?");
        $stmt->execute([$novoSaldo, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO creditos (username, quantidade) VALUES (?, ?)");
        $stmt->execute([$userId, $novoSaldo]);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO historico_creditos (user_id, quantidade, categoria, detalhes) 
        VALUES (?, ?, 'Atividade', ?)
    ");
    $detalhes = "Débito de crédito via sistema Wemos D1 - RM: " . $rm;
    $stmt->execute([$userId, -1, $detalhes]);
    
    $pdo->commit();
    
    jsonResponse([
        'sucesso' => true,
        'rm' => str_pad($usuario['rm'], 5, '0', STR_PAD_LEFT),
        'nome' => $usuario['nome'],
        'creditos_anteriores' => $creditosAtuais,
        'creditos' => $novoSaldo,
        'valor_debitado' => 1,
        'timestamp' => date('Y-m-d H:i:s'),
        'mensagem' => 'Crédito debitado com sucesso!'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollback();
    error_log("Erro no débito: " . $e->getMessage());
    jsonResponse([
        'sucesso' => false,
        'erro' => 'Erro interno do servidor. Tente novamente mais tarde.'
    ], 500);
}
?>