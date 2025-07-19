<?php
    require 'config_api.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método não permitido');
    }
    
    $stmt = $pdo->prepare("
        SELECT id, assunto, detalhes, data 
        FROM noticias_mobile  
        ORDER BY data DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $avisos_formatados = [];
    foreach ($avisos as $aviso) {
        $avisos_formatados[] = [
            'id' => $aviso['id'],
            'titulo' => $aviso['assunto'],
            'descricao' => $aviso['detalhes'],
            'data' => $aviso['data'],
            'icone' => 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/icons/notification.png', 
            'imagem_fundo' => 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/backgrounds/notice_bg.jpg' 
        ];
    }
    
    echo json_encode([
        'success' => true,
        'avisos' => $avisos_formatados,
        'count' => count($avisos_formatados)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>