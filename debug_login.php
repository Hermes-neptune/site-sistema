<?php
// debug_login.php
// Use este script para debugar problemas de login
header('Content-Type: application/json');
require 'process/db_connect.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "\n🔧 Variáveis de ambiente carregadas com sucesso.\n";
}

$encrypted_key = $_ENV['ENCRYPTION_KEY'];
echo "\n🔑 Chave de criptografia: $encrypted_key\n\n";


// Configurações do banco de dados
$host = 'localhost';
$dbname = 'tcc';
$username = 'root';
$password = '45163789';
echo "🔧 Iniciando debug de login...\n\n";
try {
    // Conectar ao MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexão com banco OK\n\n";
    
    // Testar se tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'users' existe\n\n";
    } else {
        echo "❌ Tabela 'users' não existe\n\n";
        exit;
    }
    
    // Listar usuários
    $stmt = $pdo->query("SELECT id, username, email, rm FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Usuários cadastrados:\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Nome: {$user['username']}, Email: {$user['email']}, RM: {$user['rm']}\n";
    }
    echo "\n";
    
    // Testar login com usuário específico
    $testRM = '23575';
    $testPassword = '45163789';
    
    echo "🔍 Testando login com:\n";
    echo "RM: $testRM\n";
    echo "Senha: $testPassword\n\n";
    
    // Buscar usuário
    $stmt = $pdo->prepare("SELECT * FROM users WHERE rm = :rm");
    $stmt->bindParam(':rm', $testRM);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT quantidade FROM creditos WHERE username = :id");
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();

    $creditos = $stmt->fetch(PDO::FETCH_ASSOC);
    $user['creditos'] = $creditos['quantidade'];


    if (!$user) {
        echo "❌ Usuário não encontrado\n";
        exit;
    }
    
    echo "✅ Usuário encontrado\n";
    echo $user ? "💰 Créditos: {$user['creditos']}\n" : "💰 Créditos: 0\n";
    echo "Hash armazenado: " . substr($user['password'], 0, 50) . "...\n";
    
    // Testar senha
    if (hash('sha256', $user['id']  . $encrypted_key . hash('sha256', $testRM . $testPassword)) === $user['password']) {
        echo "✅ Senha correta!\n";
        echo "🎉 Login funcionaria normalmente\n";
    } else {
        echo "❌ Senha incorreta!\n";
        echo "🔧 Gerando novo hash...\n";

        $newHash = hash('sha256', $user['id']  . $encrypted_key . hash('sha256', $testRM . $testPassword));
        echo "Novo hash: $newHash\n";
        echo "SQL para corrigir: UPDATE users SET password = '$newHash' WHERE rm = '$testRM';\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>