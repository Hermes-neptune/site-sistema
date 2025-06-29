<?php
    use chillerlan\QRCode\{QRCode, QROptions};
    require 'vendor/autoload.php';

    session_start();

    if (!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    require 'process/db_connect.php'; 

    $sql = "SELECT username, email, codigo_unico, photo FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);
    $users = $stmt->fetch();

    if (!$users) {
        die('Erro: Usuário não encontrado.');
    }

    $user_photo_url = !empty($users['photo']) ? htmlspecialchars($users['photo']) : 'img/user.png';

    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_L,
        'imageBase64' => true,
        'scale' => 128, 
    ]);

    $qrcode = (new QRCode($options))->render($users['codigo_unico']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/thema.css">
    <link rel="stylesheet" href="css/cracha.css" />
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
    <title>Cracha - <?php echo htmlspecialchars($users['username']); ?></title>
</head>
<body>
    <div class="container">
        <div class="card-content">
            <div class="card-fita">
                <img src="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/hermesssistem//faixa.svg" alt=""> 
            </div>

            <header class="card-header">
                <span>Cracha</span>

                <div class="card-credential">
                    <span>Codigo: <?php echo htmlspecialchars($users['codigo_unico']); ?></span>
                </div>
            </header>

            <div class="card-data">
                <div class="card-image">
                    <div class="card-mask">
                        <img src="<?php echo $user_photo_url; ?>" alt="Foto do perfil" class="card-img" />
                    </div>
                </div>

                <h2 class="card-name"><?php echo htmlspecialchars($users['username']); ?></h2>
                <h3 class="card-profession">Aluno</h3>

                <div class="card-qrcode">
                    <img id="qrcode" src="<?php echo $qrcode; ?>" alt="QR Code do RM">
                </div>

                <a href="protected.php" class="card-button">
                    <img src="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/hermesssistem//see.svg" alt="Ver histórico">
                    <span>Ver histórico</span>
                </a>
            </div>
        </div>
    </div>
    <script src="js/thema.js"></script>
</body>
</html>
