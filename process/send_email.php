<?php 
use PHPMailer\PHPMailer\PHPMailer;

if (file_exists(__DIR__ . '/../../../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../');
    $dotenv->load();
}

function getEmailTemplate($nomeDestinatario, $mensagem, $assunto, $detalhesAdicionais = []) {
    $defaults = [
        'empresa' => 'Hermes',
        'remetente' => 'Equipe Hermes',
        'cargo' => 'Atendimento',
        'email_contato' => $_ENV['MAIL_USERNAME'] ?? 'contato@hermes.com',
        'telefone' => '(11) 98433-3615',
        'website' => 'https://9fde943a7e20.ngrok-free.app/site-sistema-git/site-sistema/',
        'tipo_mensagem' => 'info', 
        'botao_acao' => null,
        'link_acao' => '#',
        'informacoes_extras' => []
    ];
    
    $dados = array_merge($defaults, $detalhesAdicionais);
    
    $classeBox = 'highlight-box';
    $icone = '📋';
    
    switch($dados['tipo_mensagem']) {
        case 'success':
            $classeBox = 'success-box';
            $icone = '✅';
            break;
        case 'warning':
            $classeBox = 'warning-box';
            $icone = '⚠️';
            break;
        case 'error':
            $classeBox = 'error-box';
            $icone = '❌';
            break;
    }
    
    return "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$assunto</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #314ccc;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .email-container {
                background: white;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 600px;
            }
            
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #e1e5e9;
                padding-bottom: 20px;
            }
            
            .header h1 {
                color: #314ccc;
                font-size: 2.5rem;
                margin-bottom: 10px;
            }
            
            .header p {
                color: #666;
                font-size: 1.1rem;
            }
            
            .content-section {
                margin-bottom: 25px;
            }
            
            .content-section h2 {
                color: #333;
                font-size: 1.5rem;
                margin-bottom: 15px;
                border-left: 4px solid #314ccc;
                padding-left: 15px;
            }
            
            .content-section p {
                color: #555;
                line-height: 1.6;
                margin-bottom: 15px;
                font-size: 16px;
            }
            
            .highlight-box {
                background: #e3f2fd;
                color: #1565c0;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #1565c0;
            }
            
            .success-box {
                background: #efe;
                color: #363;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #363;
            }
            
            .warning-box {
                background: #fff3cd;
                color: #856404;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #ffc107;
            }
            
            .error-box {
                background: #fee;
                color: #c33;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #c33;
            }
            
            .btn-primary {
                display: inline-block;
                padding: 14px 30px;
                background: #314ccc;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                transition: transform 0.2s;
                margin: 10px 5px;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                background: #2a42b8;
            }
            
            .button-group {
                text-align: center;
                margin: 30px 0;
            }
            
            .footer {
                border-top: 2px solid #e1e5e9;
                padding-top: 20px;
                margin-top: 30px;
                text-align: center;
            }
            
            .footer p {
                color: #666;
                font-size: 14px;
                margin-bottom: 10px;
            }
            
            .footer a {
                color: #314ccc;
                text-decoration: none;
            }
            
            .footer a:hover {
                text-decoration: underline;
            }
            
            .contact-info {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            
            .contact-info h3 {
                color: #333;
                margin-bottom: 10px;
            }
            
            .contact-info p {
                color: #555;
                margin: 5px 0;
            }
            
            ul {
                margin: 15px 0 15px 20px;
                color: #555;
            }
            
            li {
                margin: 8px 0;
                line-height: 1.4;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>📧 {$dados['empresa']}</h1>
                <p>$assunto</p>
            </div>

            <div class='content-section'>
                <h2>Olá $nomeDestinatario!</h2>
                <p>Esperamos que esta mensagem encontre você bem. Estamos entrando em contato com as seguintes informações:</p>
            </div>

            <div class='$classeBox'>
                <strong>$icone Mensagem:</strong> $mensagem
            </div>
            
            " . (!empty($dados['informacoes_extras']) ? "
            <div class='content-section'>
                <h2>Informações Adicionais</h2>
                <ul>
                    " . implode('', array_map(function($info) { return "<li>$info</li>"; }, $dados['informacoes_extras'])) . "
                </ul>
            </div>
            " : "") . "
            
            " . ($dados['botao_acao'] ? "
            <div class='button-group'>
                <a href='{$dados['link_acao']}' class='btn-primary'>{$dados['botao_acao']}</a>
            </div>
            " : "") . "

            <div class='contact-info'>
                <h3>📞 Informações de Contato</h3>
                <p><strong>Email:</strong> {$dados['email_contato']}</p>
                <p><strong>Telefone:</strong> {$dados['telefone']}</p>
                <p><strong>Website:</strong> {$dados['website']}</p>
            </div>

            <div class='footer'>
                <p>Atenciosamente,<br><strong>{$dados['remetente']}</strong><br>{$dados['cargo']} | {$dados['empresa']}</p>
                <p style='font-size: 12px; color: #999; margin-top: 15px;'>
                    Este email foi enviado automaticamente pelo sistema {$dados['empresa']}. 
                    Por favor, não responda diretamente a este email.
                </p>
            </div>
        </div>
    </body>
    </html>";
}

function sendEmail($destinatario, $mensagem, $assunto, $nomeDestinatario, $detalhesAdicionais = []) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'];
        
        $mail->CharSet = 'UTF-8';
        
        $empresa = $detalhesAdicionais['empresa'] ?? 'Hermes';
        $mail->setFrom($_ENV['MAIL_USERNAME'], $empresa);
        $mail->addAddress($destinatario, $nomeDestinatario);
        
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = getEmailTemplate($nomeDestinatario, $mensagem, $assunto, $detalhesAdicionais);
        
        $mail->AltBody = strip_tags("
        Olá $nomeDestinatario!
        
        Assunto: $assunto
        
        Mensagem: $mensagem
        
        Atenciosamente,
        Equipe " . ($detalhesAdicionais['empresa'] ?? 'Hermes')
        );
        
        $mail->send();
        return [
            'sucesso' => true,
            'mensagem' => 'Email enviado com sucesso!'
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => "Erro ao enviar email: {$mail->ErrorInfo}"
        ];
    }
}
?>