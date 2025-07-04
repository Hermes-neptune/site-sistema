<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

try {
    $endpoint_id = $_ENV['DB_ENDPOINT_ID'];
    $host        = $_ENV['DB_HOST'] ;
    $port        = $_ENV['DB_PORT'] ;
    $dbname      = $_ENV['DB_NAME'] ;
    $user        = $_ENV['DB_USER'] ;
    $real_pass   = $_ENV['DB_PASSWORD'] ;

    $pass = "endpoint=$endpoint_id;$real_pass";

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

       // Configurações comentadas do MySQL mantidas para referência
    
//     $host = $_ENV['MYSQL_HOST'];
//     $db = $_ENV['MYSQL_DB'] ;
//     $user = $_ENV['MYSQL_USER'];
//     $pass = $_ENV['MYSQL_PASSWORD'];
//     $charset = $_ENV['MYSQL_CHARSET'];

//     $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    
//     $options = [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//         PDO::ATTR_EMULATE_PREPARES => false,
//     ];


    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>
