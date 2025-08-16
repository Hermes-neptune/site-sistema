<?php
session_start();
require '../../process/db_connect.php';

if (file_exists(__DIR__ . '../../../../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../');
    $dotenv->load();
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['user_id'])) {
        throw new Exception('ID do usuário é obrigatório');
    }
    
    $user_id = $input['user_id'];
    
    
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.name,
            m.descricao,
            m.url_img,
            u.username
        FROM medalhas m
        INNER JOIN users u ON m.user_id = u.id
        WHERE m.user_id = ?
        ORDER BY m.id DESC
    ");
    
    $stmt->execute([$user_id]);
    $medalhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formatted_medalhas = [];
    foreach ($medalhas as $medalha) {
        $formatted_medalhas[] = [
            'id' => (int)$medalha['id'],
            'name' => $medalha['name'],
            'descricao' => $medalha['descricao'],
            'url_img' => $medalha['url_img'] ?: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/medal_default.png',
            'username' => $medalha['username']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'medalhas' => $formatted_medalhas,
        'total_medalhas' => count($formatted_medalhas)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar medalhas: ' . $e->getMessage(),
        'medalhas' => [],
        'total_medalhas' => 0
    ]);
}
?>