<?php
    require 'config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método não permitido');
    }
    
    $stmt = $pdo->prepare("
        SELECT id, titulo, descricao, imagem_url, data_criacao 
        FROM jogo_destaque 
        WHERE ativo = 1 
        ORDER BY data_atualizacao DESC 
        LIMIT 1
    ");
    
    $stmt->execute();
    $jogo_destaque = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($jogo_destaque) {
        echo json_encode([
            'success' => true,
            'game' => $jogo_destaque
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'game' => [
                'id' => null,
                'titulo' => 'Street Fighter',
                'descricao' => 'Jogo mais jogado do mês',
                'imagem_url' => null,
                'data_criacao' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>