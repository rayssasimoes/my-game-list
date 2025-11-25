<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifique se o token não está vazio
    if (empty($token)) {
        echo "Token inválido.";
        exit;
    }

    // Verifique se o token é válido e não expirou
    require 'includes/db.php';
    $stmt = $db->prepare("SELECT fk_user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset) {
        // Exiba o formulário para redefinir a senha
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Redefinir Senha</title>
        </head>
        <body>
            <h1>Redefinir Senha</h1>
            <form method="POST" action="index.php?page=reset-password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <label for="password">Nova Senha:</label>
                <input type="password" name="password" id="password" required>
                <label for="confirm_password">Confirme a Nova Senha:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="submit">Redefinir Senha</button>
            </form>
        </body>
        </html>
        <?php
    } else {
        echo "Token inválido ou expirado.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Verifique se as senhas coincidem
    if ($newPassword !== $confirmPassword) {
        echo "As senhas não coincidem.";
        exit;
    }

    // Verifique se o token é válido
    require 'includes/db.php';
    $stmt = $db->prepare("SELECT fk_user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset) {
        // Atualize a senha do usuário
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['fk_user_id']]);

        // Remova o token usado
        $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        echo "Senha redefinida com sucesso!";
    } else {
        echo "Token inválido ou expirado.";
    }
}
?>