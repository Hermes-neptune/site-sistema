<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$user_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'clear_all_data':
            clearAllUserData($pdo, $user_id);
            break;
        
        case 'delete_account':
            deleteUserAccount($pdo, $user_id);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            exit();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

/**
 * Limpa todos os dados do usuário mantendo a conta ativa
 */
function clearAllUserData($pdo, $user_id) {
    try {
        $pdo->beginTransaction();

        // Limpar mensagens privadas (enviadas e recebidas)
        $stmt = $pdo->prepare("DELETE FROM mensagens_privadas WHERE remetente_id = ? OR destinatario_id = ?");
        $stmt->execute([$user_id, $user_id]);

        // Limpar amizades
        $stmt = $pdo->prepare("DELETE FROM amizades WHERE user_id = ? OR friend_id = ?");
        $stmt->execute([$user_id, $user_id]);

        // Limpar histórico de créditos
        $stmt = $pdo->prepare("DELETE FROM historico_creditos WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Resetar créditos para zero
        $stmt = $pdo->prepare("UPDATE creditos SET quantidade = 0 WHERE username = ?");
        $stmt->execute([$user_id]);

        // Limpar presenças
        $stmt = $pdo->prepare("DELETE FROM presencas WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Limpar pendências
        $stmt = $pdo->prepare("DELETE FROM pendencias WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Resetar foto para padrão
        $default_photo = 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/fotosuser/user.png';
        $stmt = $pdo->prepare("UPDATE users SET photo = ?, nome_completo = username, telefone = NULL WHERE id = ?");
        $stmt->execute([$default_photo, $user_id]);

        // Resetar preferências para padrão
        $stmt = $pdo->prepare("UPDATE user_preferences SET 
            email_notifications = true, 
            push_notifications = true, 
            credit_alerts = true, 
            message_notifications = true, 
            mobile_notif = true,
            updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Resetar privacidade para padrão
        $stmt = $pdo->prepare("UPDATE user_privacy SET 
            public_profile = true, 
            show_online_status = false, 
            allow_direct_messages = true, 
            share_activity = false,
            updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Todos os dados foram limpos com sucesso. Sua conta permanece ativa.',
            'redirect' => false
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao limpar dados: ' . $e->getMessage()]);
    }
}

/**
 * Exclui completamente a conta do usuário
 */
function deleteUserAccount($pdo, $user_id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Usuário não encontrado");
        }

        // As foreign keys com ON DELETE CASCADE cuidarão da exclusão automática:
        // - mensagens_privadas
        // - amizades  
        // - historico_creditos
        // - presencas
        // - pendencias
        // - user_preferences
        // - user_privacy
        // - creditos

        // Excluir tokens de reset de senha (se existirem)
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = (SELECT email FROM users WHERE id = ?)");
        $stmt->execute([$user_id]);

        // Excluir o usuário (isso acionará CASCADE nas outras tabelas)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        $pdo->commit();
        
        session_destroy();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Conta excluída com sucesso. Você será redirecionado.',
            'redirect' => true,
            'redirect_url' => '../index.php'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir conta: ' . $e->getMessage()]);
    }
}

function verifyUserConfirmation($user_id, $confirmation_token) {
    return isset($_SESSION["confirm_action_$user_id"]) && 
    $_SESSION["confirm_action_$user_id"] === $confirmation_token;
}

function generateConfirmationToken($user_id) {
    $token = bin2hex(random_bytes(16));
    $_SESSION["confirm_action_$user_id"] = $token;
    $_SESSION["confirm_action_time_$user_id"] = time();
    return $token;
}
?>