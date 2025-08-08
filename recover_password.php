<?php
session_start();
require 'process/db_connect.php';
require 'process/send_email.php';

if (isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Por favor, digite seu email.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Por favor, digite um email válido.';
        $messageType = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Salvar token no banco
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expiry]);
            
            sendEmail($email, $url.'/reset_password.php?token='.$token, 'Recuperação de Senha', 'user');
            
            $message = 'Instruções enviadas para seu email.';
            $messageType = 'success';
        } else {
            // Por segurança, não revele se o email existe ou não
            $message = 'Se o email estiver cadastrado, você receberá as instruções.';
            $messageType = 'success';
        }
        
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="shortcut icon" type="image/x-icon" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa/Neptune.png">
    <link rel="stylesheet" href="css/recover_password.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/logo-black.png" alt="logo da empresa" class="logo-img"/>
            <p>Recuperar senha</p>
        </div>

        <?php if ($message): ?>
            <div class="<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>ℹ️ Informação:</strong> Digite seu email cadastrado para receber as instruções de recuperação de senha.
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Digite seu email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                Enviar Instruções
            </button>
        </form>

        <div class="back-link">
            <a href="login.php">← Voltar para o login</a>
        </div>
        
        <div class="register-link">
            <p>Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.querySelector('.btn-login');
            const email = document.querySelector('#email').value;
            
            if (email) {
                btn.innerHTML = 'Enviando...';
                btn.disabled = true;
                btn.style.opacity = '0.7';
            }
        });
    </script>
</body>
</html>