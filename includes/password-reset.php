<?php
/**
 * Password Reset - Solicitação de Recuperação de Senha
 * 
 * Este arquivo processa a solicitação de redefinição de senha:
 * 1. Valida o email do usuário
 * 2. Gera um token seguro
 * 3. Salva o token no banco de dados
 * 4. Envia o email de recuperação com PHPMailer
 * 
 * @author Sistema de Autenticação
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define o header como JSON
header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verifica se a ação é de redefinição de senha
$action = $_POST['action'] ?? '';
if ($action !== 'request_password_reset') {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit;
}

// Validação do email
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

try {
    $pdo = getDB();
    
    // Verifica se o email existe no banco de dados
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Por segurança, sempre retorna sucesso mesmo se o email não existir
    // Isso evita que atacantes descubram emails válidos
    if (!$user) {
        // Log para debug (remover em produção)
        error_log("Tentativa de recuperação para email não cadastrado: $email");
        
        echo json_encode([
            'success' => true,
            'message' => 'Se o email estiver cadastrado, você receberá um link de recuperação em instantes.'
        ]);
        exit;
    }
    
    // Gera um token seguro de 64 caracteres
    $token = bin2hex(random_bytes(32));
    
    // Define a expiração do token (1 hora)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Remove tokens antigos deste usuário (limpeza)
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE fk_user_id = ?");
    $stmt->execute([$user['id']]);
    
    // Insere o novo token no banco
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (fk_user_id, token, expires_at) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user['id'], $token, $expiresAt]);
    
    // ==== CONFIGURAÇÃO DO PHPMAILER ====
    $mail = new PHPMailer(true);
    
    try {
        // Configurações do servidor SMTP (via .env)
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? 'SEU_EMAIL@gmail.com';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? 'SUA_SENHA_APP';
        
        // Determina o tipo de encriptação
        $encryption = strtoupper($_ENV['SMTP_ENCRYPTION'] ?? 'TLS');
        $mail->SMTPSecure = $encryption === 'SSL' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;
        $mail->CharSet    = 'UTF-8';
        
        // Configurações opcionais para debug (remover em produção)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        // $mail->Debugoutput = 'error_log';
        
        // Remetente
        $fromName = $_ENV['SMTP_FROM_NAME'] ?? 'My Game List';
        $mail->setFrom($_ENV['SMTP_USERNAME'] ?? 'SEU_EMAIL@gmail.com', $fromName);
        
        // Destinatário
        $mail->addAddress($user['email'], $user['name']);
        
        // Reply-to (opcional)
        $mail->addReplyTo('noreply@mygamelist.com', 'No Reply');
        
        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Redefinição de Senha - My Game List';
        
        // Link de redefinição (usa APP_URL do .env)
        // Link de redefinição (Definido manualmente para o domínio de produção)
        $appUrl = 'http://mygamelist.infinityfreeapp.com';
        $resetLink = $appUrl . "/pages/redefinir.php?token=" . $token;
        
        // Corpo do email em HTML
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .button:hover { background: #5568d3; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎮 My Game List</h1>
                    <p>Redefinição de Senha</p>
                </div>
                <div class='content'>
                    <p>Olá, <strong>{$user['name']}</strong>!</p>
                    
                    <p>Recebemos uma solicitação para redefinir a senha da sua conta.</p>
                    
                    <p>Clique no botão abaixo para criar uma nova senha:</p>
                    
                    <center>
                        <a href='{$resetLink}' class='button'>Redefinir Minha Senha</a>
                    </center>
                    
                    <p>Ou copie e cole este link no seu navegador:</p>
                    <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>
                        <code>{$resetLink}</code>
                    </p>
                    
                    <div class='warning'>
                        <strong>⚠️ Importante:</strong>
                        <ul>
                            <li>Este link expira em <strong>1 hora</strong></li>
                            <li>Se você não solicitou esta redefinição, ignore este email</li>
                            <li>Nunca compartilhe este link com ninguém</li>
                        </ul>
                    </div>
                </div>
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " My Game List. Todos os direitos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Corpo alternativo (texto puro) para clientes que não suportam HTML
        $mail->AltBody = "
        Olá, {$user['name']}!
        
        Recebemos uma solicitação para redefinir a senha da sua conta.
        
        Acesse o link abaixo para criar uma nova senha:
        {$resetLink}
        
        IMPORTANTE:
        - Este link expira em 1 hora
        - Se você não solicitou esta redefinição, ignore este email
        - Nunca compartilhe este link com ninguém
        
        ---
        My Game List - Sistema de Recuperação de Senha
        ";
        
        // Envia o email
        $mail->send();
        
        // Log de sucesso
        error_log("Email de recuperação enviado para: $email");
        
        echo json_encode([
            'success' => true,
            'message' => 'Email enviado com sucesso! Verifique sua caixa de entrada e spam.'
        ]);
        
    } catch (Exception $e) {
        // Log do erro
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao enviar o email. Tente novamente mais tarde.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro no password-reset.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação. Tente novamente.'
    ]);
}
