<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
</head>
<body>
    <div class="register-container">
        <?php
        if (isset($_GET['error'])) {
            echo "<div class='error'>Erro ao cadastrar: " . htmlspecialchars($_GET['error']) . "</div>";
        }
        ?>
        
        <div class="logo">
            <img src="img/logo-black.png" alt="logo da empresa" class="logo-img"/>
            <p>Crie sua conta</p>
        </div>
        
        <form action="process/register_process.php" method="POST">
            <div class="form-group">
                <label for="rm">RM:</label>
                <input type="text" name="rm" id="rm" required>
            </div>

            <div class="form-group">
                <label for="rm">Username:</label>
                <input type="text" name="username" id="username" >
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-register">Cadastrar</button>
            </div>
        </form>
        
        <div class="login-link">
            <a href="login.php">Já tem uma conta? Faça login</a>
        </div>
    </div>
</body>
</html>