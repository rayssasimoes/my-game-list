<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Carrega o autoload do Composer

// Carregar as variáveis do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    // Obter as credenciais do banco de dados do .env
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    // Criar a conexão com o banco de dados
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Configurar o modo de erro do PDO para exceções
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exibir mensagem de erro caso a conexão falhe
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Verificar se a requisição é POST e se a ação é "forgot-password"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'forgot-password') {
    $email = $_POST['email'] ?? '';

    // Valide o email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Se o email estiver cadastrado, você receberá um link para redefinir sua senha.";
        exit;
    }

    // Verifique se o email existe no banco de dados
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        try {
            // Gere um token único
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Insira o token na tabela de resets
            $stmt = $db->prepare("INSERT INTO password_resets (fk_user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);

            // Envie o email com o link de redefinição
            $resetLink = $_ENV['APP_URL'] . "/index.php?page=reset-password&token=$token";

            $mail = new PHPMailer(true);

            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            // Remetente e destinatário
            $mail->setFrom($_ENV['SMTP_USER'], 'My Game List');
            $mail->addAddress($email);

            // Conteúdo do email
            $mail->isHTML(true);
            $mail->Subject = 'Redefinição de Senha';
            $mail->Body = "Clique no link abaixo para redefinir sua senha:<br><br>
                           <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            echo "Se o email estiver cadastrado, você receberá um link para redefinir sua senha.";
        } catch (Exception $e) {
            echo "Erro ao processar a solicitação. Tente novamente mais tarde.";
        }
    } else {
        // Mensagem genérica para segurança
        echo "Se o email estiver cadastrado, você receberá um link para redefinir sua senha.";
    }
}