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
    
    <style>
        /* Reset Password Page Styles */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header .icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .reset-header h1 {
            color: #1e293b;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .reset-header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert i {
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-group-addon {
            position: absolute;
            left: 15px;
            color: #94a3b8;
            font-size: 1.2rem;
            z-index: 1;
            pointer-events: none;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #1e293b;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control.password-invalid {
            border-color: #ef4444;
        }
        
        .form-control.password-valid {
            border-color: #22c55e;
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .form-text.invalid {
            color: #ef4444;
        }
        
        .form-text.valid {
            color: #22c55e;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            z-index: 2;
        }
        
        .password-toggle-icon:hover {
            color: #667eea;
        }
        
        .password-toggle-icon.password-icon-inactive {
            opacity: 0.5;
            pointer-events: none;
        }
        
        .password-toggle-icon.password-icon-active {
            opacity: 1;
            pointer-events: auto;
        }
        
        .btn {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary-custom:active {
            transform: translateY(0);
        }
        
        .btn-secondary-custom {
            background: #f1f5f9;
            color: #475569;
            margin-top: 10px;
        }
        
        .btn-secondary-custom:hover {
            background: #e2e8f0;
        }
        
        .success-message {
            text-align: center;
            padding: 20px;
        }
        
        .success-message .icon {
            font-size: 5rem;
            color: #22c55e;
            margin-bottom: 20px;
        }
        
        .success-message h2 {
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .success-message p {
            color: #64748b;
            margin-bottom: 25px;
        }
        
        @media (max-width: 600px) {
            .reset-container {
                padding: 30px 20px;
            }
            
            .reset-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
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
            
            <p style="text-align: center; color: #64748b; margin-bottom: 20px;">
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
    
    <script>
        // ==== PASSWORD TOGGLE ====
        document.querySelectorAll('.password-toggle-icon').forEach(icon => {
            const targetId = icon.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);

            if (!passwordInput) return;

            // Ativa/desativa ícone conforme input
            passwordInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    icon.classList.remove('password-icon-inactive');
                    icon.classList.add('password-icon-active');
                } else {
                    icon.classList.remove('password-icon-active');
                    icon.classList.add('password-icon-inactive');
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                    passwordInput.type = 'password';
                }
            });

            // Alterna visibilidade
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!this.classList.contains('password-icon-active')) return;

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                }
            });
        });

        // ==== PASSWORD VALIDATION ====
        const newPasswordInput = document.getElementById('new_password');
        const newPasswordHint = document.getElementById('new_password_hint');
        
        if (newPasswordInput) {
            let validationTimeout;

            newPasswordInput.addEventListener('input', function() {
                const value = this.value;
                
                clearTimeout(validationTimeout);

                if (value.length === 0) {
                    this.classList.remove('password-invalid');
                    if (newPasswordHint) newPasswordHint.classList.remove('invalid');
                    return;
                }

                validationTimeout = setTimeout(() => {
                    if (value.length < 6) {
                        this.classList.add('password-invalid');
                        if (newPasswordHint) newPasswordHint.classList.add('invalid');
                    } else {
                        this.classList.remove('password-invalid');
                        this.classList.add('password-valid');
                        if (newPasswordHint) newPasswordHint.classList.remove('invalid');
                    }
                }, 400);
            });
        }

        // ==== PASSWORD MATCH VALIDATION ====
        const confirmPasswordInput = document.querySelector('.password-match-validation');
        if (confirmPasswordInput) {
            const matchTarget = document.getElementById(confirmPasswordInput.dataset.matchTarget);
            const matchHint = document.getElementById('confirm_password_hint');
            
            function validatePasswordMatch() {
                if (confirmPasswordInput.value.length > 0) {
                    if (confirmPasswordInput.value !== matchTarget.value) {
                        confirmPasswordInput.classList.add('password-invalid');
                        confirmPasswordInput.classList.remove('password-valid');
                        if (matchHint) {
                            matchHint.style.display = 'block';
                            matchHint.classList.add('invalid');
                        }
                        return false;
                    } else {
                        confirmPasswordInput.classList.remove('password-invalid');
                        confirmPasswordInput.classList.add('password-valid');
                        if (matchHint) {
                            matchHint.style.display = 'none';
                        }
                        return true;
                    }
                }
                return true;
            }
            
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
            matchTarget.addEventListener('input', validatePasswordMatch);
            
            // Validação no submit
            document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
                if (!validatePasswordMatch()) {
                    e.preventDefault();
                    alert('As senhas não coincidem. Por favor, verifique.');
                }
            });
        }
    </script>
</body>
</html>
