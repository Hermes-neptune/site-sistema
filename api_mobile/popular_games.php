<?php
    require 'config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método não permitido');
    }
    
    $stmt = $pdo->prepare("
        SELECT id, nome, imagem_url, categoria, rating
        FROM jogos_populares 
        WHERE ativo = 1 
        ORDER BY popularidade DESC 
        LIMIT 6
    ");
    $stmt->execute();
    
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $jogos_formatados = [];
    foreach ($jogos as $jogo) {
        $jogos_formatados[] = [
            'id' => $jogo['id'],
            'nome' => $jogo['nome'],
            'imagem_url' => $jogo['imagem_url'],
            'categoria' => $jogo['categoria'] ?? 'Ação',
            'rating' => $jogo['rating'] ?? '4.5'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'jogos' => $jogos_formatados,
        'count' => count($jogos_formatados)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>