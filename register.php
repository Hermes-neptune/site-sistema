<?php
    session_start();

    if (isset($_GET['error'])) {
        echo "<p style='color: red;'>Erro ao cadastrar: " . htmlspecialchars($_GET['error']) . "</p>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
</head>
<body>
<div class="form-container">
    <h2 class="title">Cadastro de Usuário</h2>
    <form action="process/register_process.php" method="POST">
    <div class="form-group">
        <label for="rm">RM:</label>
        <input type="text" name="rm" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Senha:</label>
        <input type="password" name="password" required>
    </div>

    <div class="form-group">
        <button type="submit">Cadastrar</button>
    </div>
    
    <div class="link">
        <a href="login.php">Iniciar sessão</a>
    </div>

    </form>
    </div>
</body>
</html>
