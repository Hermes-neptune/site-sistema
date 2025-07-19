<?php 
session_start(); 
require 'process/db_connect.php';

if (isset($_SESSION['id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'protected.php';
    
    $allowed_redirects = ['protected.php', 'config.php'];
    if (!in_array($redirect, $allowed_redirects)) {
        $redirect = 'protected.php';
    }
    
    header('Location: ' . $redirect);
    exit();
}
$redirect_param = isset($_GET['redirect']) ? $_GET['redirect'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa/Neptune.png">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/logo-black.png" alt="logo da empresa" class="logo-img"/>
            <p>Faça login em sua conta</p>
        </div>
        
        <form action="process/authenticate.php" method="POST">
            <?php if($redirect_param): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_param); ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">RM:</label>
                <input type="text" name="login">
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="forgot-password">
                <a href="recover_password.php">Esqueceu sua senha?</a>
            </div>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        
        <div class="register-link">
            <p>Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
        </div>
    </div>
</body>
</html>