<?php
    session_start();
    require 'vendor/autoload.php';
    require 'processos/db_connect.php';

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['id'];
    
    $sql = "SELECT username, email, photo FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch();

    $user_photo_url = !empty($usuario['photo']) ? htmlspecialchars($usuario['photo']) : 'img/user.png';

    $status_message = '';
    $status_class = '';

    if (isset($_GET['status'])) {
        if ($_GET['status'] === 'success') {
            $status_message = 'Foto de perfil atualizada com sucesso!';
            $status_class = 'success';
        } elseif ($_GET['status'] === 'error') {
            $status_message = 'Erro ao atualizar a foto de perfil. ' . (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '');
            $status_class = 'error';
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Foto de Perfil</title>
    <link rel="stylesheet" href="css/thema.css">
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
    <link rel="stylesheet" href="css/upload_foto.css">
</head>
<body>
    <div class="upload-container">
        <h2>Upload de Foto de Perfil</h2>
        
        <?php if (!empty($status_message)): ?>
            <div class="status-message <?php echo $status_class; ?>">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="preview-container">
            <img src="<?php echo $user_photo_url; ?>" alt="Foto de perfil atual" class="photo-preview" id="photoPreview">
            <p>Foto de perfil atual</p>
        </div>
        
        <form action="processar_upload_foto.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profilePhoto">Selecione uma nova foto:</label>
                <input type="file" name="profilePhoto" id="profilePhoto" class="form-control" accept="image/*" required>
                <small>Formatos aceitos: JPG, JPEG, PNG. Tamanho máximo: 5MB.</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Atualizar Foto de Perfil</button>
            </div>
        </form>
        
        <a href="protected.php" class="back-link">Voltar para a página principal</a>
    </div>
    
    <script src="js/thema.js"></script>
    <script>
        document.getElementById('profilePhoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('photoPreview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
