<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db_connect.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401 );
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
    exit;
}

$current_user_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';

try {
    if ($action === 'enviar_solicitacao' && isset($_POST['friend_id'])) {
        $friend_id = $_POST['friend_id'];

        if ($current_user_id == $friend_id) {
            throw new Exception('Você não pode adicionar a si mesmo.');
        }

        $stmt = $pdo->prepare("SELECT id FROM amizades WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
        $stmt->execute([$current_user_id, $friend_id, $friend_id, $current_user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Já existe uma solicitação de amizade pendente ou vocês já são amigos.');
        }

        $stmt = $pdo->prepare("INSERT INTO amizades (user_id, friend_id) VALUES (?, ?)");
        $stmt->execute([$current_user_id, $friend_id]);
        echo json_encode(['status' => 'success', 'message' => 'Solicitação de amizade enviada.']);

    } elseif ($action === 'aceitar_solicitacao' && isset($_POST['request_id'])) {
        $request_id = $_POST['request_id'];
        $stmt = $pdo->prepare("UPDATE amizades SET status = 'aceito', data_aceite = NOW() WHERE id = ? AND friend_id = ?");
        $stmt->execute([$request_id, $current_user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Amizade aceita.']);

    } elseif ($action === 'rejeitar_solicitacao' && isset($_POST['request_id'])) {
        $request_id = $_POST['request_id'];
        $stmt = $pdo->prepare("DELETE FROM amizades WHERE id = ? AND friend_id = ?");
        $stmt->execute([$request_id, $current_user_id]);
        echo json_encode(['status' => 'success', 'message' => 'Solicitação rejeitada.']);

    } else {
        http_response_code(400 );
        echo json_encode(['status' => 'error', 'message' => 'Ação inválida.']);
    }
} catch (Exception $e) {
    http_response_code(500 );
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
