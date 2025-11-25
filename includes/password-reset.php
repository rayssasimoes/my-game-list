<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

// Retornar JSON
header('Content-Type: application/json');

try {
    $db = getDB();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
    exit;
}

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validar o email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => true, 'message' => 'Se o email estiver cadastrado, você receberá um link para redefinir sua senha.']);
        exit;
    }

    // Verificar se o email existe no banco de dados
    $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        try {
            // Deletar tokens antigos deste usuário
            $stmt = $db->prepare("DELETE FROM password_resets WHERE fk_user_id = ?");
            $stmt->execute([$user['id']]);

            // Gerar token único
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Inserir o token na tabela
            $stmt = $db->prepare("INSERT INTO password_resets (fk_user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);

            // Criar link de redefinição
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $baseUrl = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF'], 2);
            $resetLink = $baseUrl . "/index.php?page=reset-password&token=" . $token;

            // Configurar PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($_ENV['SMTP_USER'], 'MyGameList');
            $mail->addAddress($email, $user['name']);

            // Email HTML bonito
            $mail->isHTML(true);
            $mail->Subject = '🔐 Redefinição de Senha - MyGameList';
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f0f0f; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 40px auto; background: #1a1a1c; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.5); }
                    .header { background: linear-gradient(135deg, #E93D82 0%, #d62a6e 100%); padding: 40px 30px; text-align: center; }
                    .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 700; }
                    .header p { color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 15px; }
                    .content { padding: 40px 30px; color: #e0e0e0; }
                    .content p { line-height: 1.6; margin-bottom: 20px; font-size: 15px; }
                    .button { display: inline-block; background: #E93D82; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 8px; font-weight: 600; font-size: 16px; margin: 20px 0; transition: background 0.3s; }
                    .button:hover { background: #d62a6e; }
                    .link-box { background: #252527; border-radius: 8px; padding: 15px; margin: 20px 0; word-break: break-all; font-size: 13px; color: #a0a0a0; border-left: 3px solid #E93D82; }
                    .footer { padding: 30px; text-align: center; color: #707070; font-size: 13px; border-top: 1px solid #2a2a2c; }
                    .footer p { margin: 5px 0; }
                    .warning { background: #2a2220; border-left: 3px solid #ff6b6b; padding: 15px; border-radius: 6px; margin: 20px 0; color: #ffb8b8; font-size: 14px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎮 MyGameList</h1>
                        <p>Redefinição de Senha</p>
                    </div>
                    <div class='content'>
                        <p>Olá, <strong>" . htmlspecialchars($user['name']) . "</strong>!</p>
                        <p>Recebemos uma solicitação para redefinir a senha da sua conta. Clique no botão abaixo para criar uma nova senha:</p>
                        <center>
                            <a href='" . htmlspecialchars($resetLink) . "' class='button'>Redefinir Minha Senha</a>
                        </center>
                        <p>Ou copie e cole este link no seu navegador:</p>
                        <div class='link-box'>" . htmlspecialchars($resetLink) . "</div>
                        <div class='warning'>
                            ⚠️ <strong>Importante:</strong> Este link expira em 1 hora e só pode ser usado uma vez.
                        </div>
                        <p>Se você não solicitou a redefinição de senha, ignore este email. Sua conta permanecerá segura.</p>
                    </div>
                    <div class='footer'>
                        <p><strong>MyGameList</strong></p>
                        <p>Organize, descubra e jogue!</p>
                        <p style='margin-top: 15px; font-size: 12px;'>Este é um email automático, por favor não responda.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->AltBody = "Olá, " . $user['name'] . "!\n\nRecebemos uma solicitação para redefinir sua senha.\n\nClique no link abaixo para redefinir:\n" . $resetLink . "\n\nEste link expira em 1 hora.\n\nSe você não solicitou isso, ignore este email.\n\nMyGameList";

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Email enviado com sucesso! Verifique sua caixa de entrada.']);
        } catch (Exception $e) {
            error_log("Erro ao enviar email de reset: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar email. Tente novamente mais tarde.']);
        }
    } else {
        // Mensagem genérica por segurança
        echo json_encode(['success' => true, 'message' => 'Se o email estiver cadastrado, você receberá um link para redefinir sua senha.']);
    }
    exit;
}