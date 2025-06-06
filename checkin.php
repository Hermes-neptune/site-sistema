<?php
    session_start();
    require 'vendor/autoload.php';
    require 'processos/db_connect.php';

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['id'];
    $data_atual = date('Y-m-d');

    // Fetch user photo URL
    $sql_user = "SELECT photo FROM users WHERE id = ?";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$user_id]);
    $usuario = $stmt_user->fetch();

    // Define the user photo URL with fallback
    $user_photo_url = (!empty($usuario) && !empty($usuario['photo'])) ? htmlspecialchars($usuario['photo']) : 'img/user.png';

    // Check if presence is already registered for today
    $sql_check = "SELECT COUNT(*) FROM presencas WHERE user_id = ? AND data = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$user_id, $data_atual]);
    $presenca_existe = $stmt_check->fetchColumn();

    if (!$presenca_existe) {
        // Register presence
        $sql_insert = "INSERT INTO presencas (user_id, data) VALUES (?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$user_id, $data_atual]);
        $presenca_msg = "Presença registrada com sucesso!";
    } else {
        $presenca_msg = "Presença já registrada para hoje.";
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in</title>
    <link rel="stylesheet" href="css/thema.css">
    <link rel="stylesheet" href="css/check-in.css">
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
</head>

<body>
    <div class="container">
        <div class="card check-in">
            <div class="card-mask">
                <!-- Use the dynamic photo URL -->
                <img src="<?php echo $user_photo_url; ?>" alt="Foto do perfil" class="card-img">
            </div>
            <h3>Bem-vindo ao Check-in!</h3>
            <div class="card_text">
                <p><?php echo htmlspecialchars($presenca_msg); ?></p>
            </div>
        </div>
    </div>
    <script src="js/thema.js"></script>
</body>

</html>
