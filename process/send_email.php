<?php
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

function sendEmail($destinatario, $mensagem, $assunto, $nomeDestinatario) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAIL_PORT'];
        
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom($_ENV['MAIL_USERNAME'], 'Hermes');

        $mail->addAddress($destinatario, $nomeDestinatario);
        
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;
        
        $mail->send();
        return "Email enviado com sucesso!";
        
    } catch (Exception $e) {
        return "Erro ao enviar email: {$mail->ErrorInfo}";
    }
}
?>