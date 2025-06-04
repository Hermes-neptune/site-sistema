<?php
    session_start();

    if (isset($_SESSION['id'])) {
        header('Location: protected.php');
        exit();
    }

    if (isset($_GET['error'])) {
        echo "<p style='color: red;'>Login inválido!</p>";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
</head>
<body>
<div class="form-container">
    <h2 class="title">Login</h2>
    <form action="authenticate.php" method="POST">
    <div class="form-group">
        <label for="username">RM:</label>
        <input type="text" name="login" required>
    </div>
    
    <div class="form-group">
        <label for="password">Senha:</label>
        <input type="password" name="password" required>
    </div>
    
    <div class="form-group">
        <button type="submit">Login</button>
    </div>
    
    <div class="link">
        <a href="register.php" >Criar conta</a>
    </div>
</form>
</div>
</body>
</html>
