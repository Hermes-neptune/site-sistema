<?php
session_start();
require '../vendor/autoload.php';
require 'db_connect.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['id'];

if (!isset($_FILES['profilePhoto']) || $_FILES['profilePhoto']['error'] !== UPLOAD_ERR_OK) {
    $error_message = "Erro no upload do arquivo: ";
    switch ($_FILES['profilePhoto']['error']) {
        case UPLOAD_ERR_INI_SIZE:
            $error_message .= "O arquivo excede o tamanho máximo permitido pelo servidor.";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $error_message .= "O arquivo excede o tamanho máximo permitido pelo formulário.";
            break;
        case UPLOAD_ERR_PARTIAL:
            $error_message .= "O upload foi interrompido.";
            break;
        case UPLOAD_ERR_NO_FILE:
            $error_message .= "Nenhum arquivo foi enviado.";
            break;
        default:
            $error_message .= "Erro desconhecido.";
    }
    header("Location: ../upload_foto.php?status=error&msg=" . urlencode($error_message));
    exit();
}

$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
$file_type = $_FILES['profilePhoto']['type'];
if (!in_array($file_type, $allowed_types)) {
    header("Location: ../upload_foto.php?status=error&msg=" . urlencode("Tipo de arquivo não permitido. Use apenas JPG, JPEG ou PNG."));
    exit();
}

$max_size = 5 * 1024 * 1024; 
if ($_FILES['profilePhoto']['size'] > $max_size) {
    header("Location: ../upload_foto.php?status=error&msg=" . urlencode("O arquivo é muito grande. O tamanho máximo permitido é 5MB."));
    exit();
}

$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: ../upload_foto.php?status=error&msg=" . urlencode("Usuário não encontrado."));
    exit();
}

$file_extension = pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION);
$file_name = $usuario['username'] . '_' . $user_id . '_' . time() . '.' . $file_extension;

$supabase_url = $_ENV['SUPABASE_URL'] ;
$supabase_key = $_ENV['SUPABASE_KEY'];
$bucket_name = $_ENV['SUPABASE_BUCKET'];
$file_path = $file_name;

$temp_file = $_FILES['profilePhoto']['tmp_name'];
$file_content = file_get_contents($temp_file);

$ch = curl_init();
$url = $supabase_url . "/storage/v1/object/" . $bucket_name . "/" . $file_path;

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $file_content);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $supabase_key,
    "Content-Type: " . $file_type,
    "x-upsert: true" 
]);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

$debug_info = date('Y-m-d H:i:s') . " - Tentativa de upload para " . $url . "\n";
$debug_info .= "Chave usada: " . substr($supabase_key, 0, 10) . "...\n";
if ($curl_error) {
    $debug_info .= "Erro cURL: " . $curl_error . "\n";
}

rewind($verbose);
$verboseLog = stream_get_contents($verbose);
$debug_info .= "Log detalhado: " . $verboseLog . "\n";
$debug_info .= "Resposta: " . $response . "\n";
$debug_info .= "Código HTTP: " . $http_code . "\n";
$debug_info .= "-------------------------------------------\n";

file_put_contents('upload_debug.log', $debug_info, FILE_APPEND);

curl_close($ch);

if ($http_code >= 200 && $http_code < 300) {
    $public_url = $supabase_url . "/storage/v1/object/public/" . $bucket_name . "/" . $file_path;
    
    $sql_update = "UPDATE users SET photo = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    
    if ($stmt_update->execute([$public_url, $user_id])) {
        header("Location: ../upload_foto.php?status=success");
        exit();
    } else {
        header("Location: ../upload_foto.php?status=error&msg=" . urlencode("Erro ao atualizar o banco de dados."));
        exit();
    }
} else {
    $error_data = json_decode($response, true);
    $error_message = isset($error_data['error']) ? $error_data['error'] : "Erro no upload (HTTP " . $http_code . ").";
    $error_message .= " Verifique se o bucket '" . $bucket_name . "' existe e tem as permissões corretas.";
    
    if ($curl_error) {
        $error_message .= " Erro cURL: " . $curl_error;
    }
    
    header("Location: ../upload_foto.php?status=error&msg=" . urlencode("Erro no upload para o Supabase: " . $error_message));
    exit();
}
?>
