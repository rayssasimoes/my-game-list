<?php

/**
 * Página de Redefinição de Senha
 * 
 * Esta página permite que o usuário redefina sua senha usando o token
 * recebido por email.
 * 
 * Fluxo:
 * 1. Valida o token recebido via GET
 * 2. Verifica se o token não expirou
 * 3. Permite definir nova senha
 * 4. Atualiza o hash da senha no banco de dados
 * 
 * @author Sistema de Autenticação
 * @version 1.0
 */

session_start();
require_once __DIR__ . '/../config/database.php';

// ==== INICIALIZAÇÃO ====
$token = $_GET['token'] ?? '';
$error = null;
$success = null;
$tokenValid = false;
$userId = null;
$userName = '';

// ==== VALIDAÇÃO DO TOKEN ====
if (empty($token)) {
    $error = 'Token inválido ou ausente.';
} else {
    try {
        $pdo = getDB();
        
        // Busca o token no banco e verifica se não expirou
        $stmt = $pdo->prepare("
            SELECT pr.fk_user_id, pr.expires_at, u.name, u.email
            FROM password_resets pr
            INNER JOIN users u ON pr.fk_user_id = u.id
            WHERE pr.token = ? AND pr.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $resetData = $stmt->fetch();
        
        if ($resetData) {
            $tokenValid = true;
            $userId = $resetData['fk_user_id'];
            $userName = $resetData['name'];
        } else {
            // Verifica se o token existe mas expirou
            $stmt = $pdo->prepare("SELECT expires_at FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            $expiredToken = $stmt->fetch();
            
            if ($expiredToken) {
                $error = 'Este link de recuperação expirou. Solicite um novo link.';
            } else {
                $error = 'Token inválido ou já utilizado.';
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao validar token: " . $e->getMessage());
        $error = 'Erro ao processar solicitação. Tente novamente.';
    }
}

// ==== PROCESSAMENTO DO FORMULÁRIO ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validações
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'As senhas não coincidem.';
    } else {
        try {
            $pdo = getDB();
            
            // Hash da nova senha
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Atualiza a senha do usuário
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            // Remove o token usado (segurança)
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            // Log de sucesso
            error_log("Senha redefinida com sucesso para usuário ID: $userId");
            
            $success = true;
            $tokenValid = false; // Previne reenvio do formulário
            
        } catch (Exception $e) {
            error_log("Erro ao redefinir senha: " . $e->getMessage());
            $error = 'Erro ao atualizar senha. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - My Game List</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS Global -->
    <link rel="stylesheet" href="../public/css/global.css">
    <!-- CSS de componentes -->
    <link rel="stylesheet" href="../public/css/components/buttons.css">
    <link rel="stylesheet" href="../public/css/components/forms.css">
    
    <!-- CSS da página de redefinição (carregado por último para sobrescrever regras gerais) -->
    <link rel="stylesheet" href="../public/css/pages/redefinir.css">
</head>
<body class="reset-password-page">
    <div class="reset-container">
        <?php if ($success): ?>
            <!-- Sucesso -->
            <div class="success-message">
                <div class="icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h2>Senha Redefinida!</h2>
                <p>Sua senha foi alterada com sucesso. Agora você já pode fazer login com sua nova senha.</p>
                <a href="../index.php" class="btn btn-primary-custom">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Fazer Login
                </a>
            </div>
        <?php elseif (!$tokenValid): ?>
            <!-- Token inválido ou expirado -->
            <div class="reset-header">
                <div class="icon">
                    <i class="bi bi-exclamation-triangle-fill" style="color: #ef4444;"></i>
                </div>
                <h1>Link Inválido</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-x-circle-fill"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <p class="reset-info-text">
                Solicite um novo link de recuperação de senha.
            </p>
            
            <a href="../index.php" class="btn btn-secondary-custom">
                <i class="bi bi-arrow-left"></i>
                Voltar para o Login
            </a>
        <?php else: ?>
            <!-- Formulário de redefinição -->
            <div class="reset-header">
                <div class="icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1>Criar Nova Senha</h1>
                <p>Olá, <?php echo htmlspecialchars($userName); ?>! Defina sua nova senha abaixo.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="resetPasswordForm">
                <!-- Nova Senha -->
                <div class="form-group">
                    <label for="new_password" class="form-label">Nova Senha</label>
                    <div class="password-container">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input 
                                id="new_password" 
                                class="form-control password-validation" 
                                type="password" 
                                name="new_password" 
                                placeholder="Mínimo 6 caracteres" 
                                required 
                                minlength="6"
                                autocomplete="new-password"
                            >
                            <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="new_password"></i>
                        </div>
                    </div>
                    <small class="form-text" id="new_password_hint">A senha deve ter no mínimo 6 caracteres</small>
                </div>
                
                <!-- Confirmar Senha -->
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmar Senha</label>
                    <div class="password-container">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input 
                                id="confirm_password" 
                                class="form-control password-match-validation" 
                                type="password" 
                                name="confirm_password" 
                                placeholder="Digite a senha novamente" 
                                required 
                                minlength="6"
                                autocomplete="new-password"
                                data-match-target="new_password"
                            >
                            <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="confirm_password"></i>
                        </div>
                    </div>
                    <small class="form-text" id="confirm_password_hint" style="display: none; color: #ef4444;">
                        As senhas não coincidem
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-circle"></i>
                    Redefinir Senha
                </button>
                
                <a href="../index.php" class="btn btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Script da página -->
    <script src="../public/js/redefinir.js"></script>
</body>
</html>
