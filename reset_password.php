<?php
session_start();
require 'process/db_connect.php';

if (isset($_SESSION['id'])) {
    header('Location: protected.php');
    exit();
}

if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }


$message = '';
$messageType = '';
$token = $_GET['token'] ?? '';
$tokenValid = false;

if (!empty($token)) {    
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expiry > NOW()");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() > 0) {
        $tokenValid = true;
        $email = $stmt->fetch()['email'];
    } else {
        $message = 'Token inválido ou expirado.';
        $messageType = 'error';
    }
    } else {
    $message = 'Token não fornecido.';
    $messageType = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($password)) {
        $message = 'Por favor, digite uma senha.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'A senha deve ter pelo menos 6 caracteres.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'As senhas não coincidem.';
        $messageType = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $email = $stmt->fetch()['email'];
        
        $sql = "SELECT id, password,rm FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Atualizar senha
        $new_hash = hash('sha256', $user['id'] .$_ENV['ENCRYPTION_KEY'] . hash('sha256', $user['rm'] . $password));
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_hash, $email]);

        // Remover token usado
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        
        $message = 'Senha redefinida com sucesso! Você pode fazer login agora.';
        $messageType = 'success';
        
        echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 3000);</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="shortcut icon" type="image/x-icon" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa/Neptune.png">
    <link rel="stylesheet" href="css/reset_password.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/logo-black.png" alt="logo da empresa" class="logo-img"/>
            <p>Redefinir sua senha</p>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($tokenValid && $messageType !== 'success'): ?>
            <div class="password-requirements">
                <strong>⚠️ Requisitos da senha:</strong>
                <ul>
                    <li>Mínimo de 6 caracteres</li>
                    <li>Recomendado: letras, números e símbolos</li>
                    <li>Evite senhas muito simples</li>
                </ul>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">Nova senha:</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Digite sua nova senha"
                        required
                        minlength="6"
                    >
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar senha:</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirme sua nova senha"
                        required
                        minlength="6"
                    >
                    <div class="password-match" id="passwordMatch"></div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    Redefinir Senha
                </button>
            </form>
        <?php elseif (!$tokenValid): ?>
            <div class="info-box">
                <strong>ℹ️ Token inválido:</strong> O link de recuperação pode ter expirado ou ser inválido. Solicite um novo link de recuperação.
            </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="login.php">← Voltar para o login</a>
        </div>
        
        <div class="register-link">
            <p>Lembrou da senha? <a href="login.php">Faça login</a></p>
        </div>
    </div>

    <script src="js/reset_password.js"></script>
</body>
</html>