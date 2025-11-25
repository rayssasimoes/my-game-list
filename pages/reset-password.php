<?php
// Verificar se há token na URL
$token = $_GET['token'] ?? '';
$pageTitle = 'Redefinir Senha - MyGameList';

// Variável para controlar o estado da página
$tokenValid = false;
$errorMessage = '';
$successMessage = '';

// Se for POST, processar a redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'Todos os campos são obrigatórios.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'As senhas não coincidem.';
    } elseif (strlen($newPassword) < 6) {
        $errorMessage = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // Verificar token e atualizar senha
        $db = getDB();
        $stmt = $db->prepare("SELECT fk_user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset) {
            // Atualizar senha
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $reset['fk_user_id']]);
            
            // Remover token
            $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $successMessage = 'Senha redefinida com sucesso! Você será redirecionado para o login em 3 segundos...';
        } else {
            $errorMessage = 'Link inválido ou expirado.';
        }
    }
} elseif (!empty($token)) {
    // Verificar se o token é válido (GET request)
    $db = getDB();
    $stmt = $db->prepare("SELECT fk_user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reset) {
        $tokenValid = true;
    } else {
        $errorMessage = 'Link inválido ou expirado.';
    }
} else {
    $errorMessage = 'Token não fornecido.';
}

include 'includes/header.php';
?>

<style>
.reset-password-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.reset-password-container {
    max-width: 480px;
    width: 100%;
    background: #1a1a1c;
    border-radius: 16px;
    padding: 3rem 2.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.reset-password-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.reset-icon {
    width: 72px;
    height: 72px;
    background: linear-gradient(135deg, #E93D82 0%, #d62a6e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
}

.reset-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
}

.reset-subtitle {
    font-size: 0.938rem;
    color: #a0a0a0;
    line-height: 1.5;
}

.reset-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group-reset {
    position: relative;
}

.form-group-reset label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #e0e0e0;
    margin-bottom: 0.5rem;
}

.password-input-wrapper {
    position: relative;
}

.form-control-reset {
    width: 100%;
    padding: 0.875rem 3rem 0.875rem 1rem;
    background: #252527;
    border: 2px solid #3a3a3c;
    border-radius: 8px;
    color: #ffffff;
    font-size: 0.938rem;
    transition: all 0.3s;
}

.form-control-reset:focus {
    outline: none;
    border-color: #E93D82;
    background: #2a2a2c;
}

.form-control-reset.is-valid {
    border-color: #22c55e;
}

.form-control-reset.is-invalid {
    border-color: #ef4444;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #a0a0a0;
    font-size: 1.125rem;
    cursor: pointer;
    padding: 0;
    transition: color 0.2s;
}

.toggle-password:hover {
    color: #ffffff;
}

.password-strength {
    margin-top: 0.5rem;
    font-size: 0.813rem;
}

.password-strength.weak { color: #ef4444; }
.password-strength.medium { color: #f59e0b; }
.password-strength.strong { color: #22c55e; }

.form-hint {
    margin-top: 0.4rem;
    font-size: 0.813rem;
    color: #a0a0a0;
}

.form-hint.error {
    color: #ef4444;
}

.btn-reset-password {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #E93D82 0%, #d62a6e 100%);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 0.5rem;
}

.btn-reset-password:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(233, 61, 130, 0.4);
}

.btn-reset-password:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.reset-message {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.938rem;
}

.reset-message.success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}

.reset-message.error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.reset-message i {
    font-size: 1.25rem;
}

.back-to-login {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #2a2a2c;
}

.back-to-login a {
    color: #E93D82;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.2s;
}

.back-to-login a:hover {
    color: #d62a6e;
}

@media (max-width: 576px) {
    .reset-password-container {
        padding: 2rem 1.5rem;
    }
    
    .reset-title {
        font-size: 1.5rem;
    }
    
    .reset-icon {
        width: 64px;
        height: 64px;
        font-size: 1.75rem;
    }
}
</style>

<div class="reset-password-page">
    <div class="reset-password-container">
        <div class="reset-password-header">
            <div class="reset-icon">
                <i class="bi bi-key"></i>
            </div>
            <h1 class="reset-title">Redefinir Senha</h1>
            <p class="reset-subtitle">Crie uma nova senha forte para sua conta</p>
        </div>

        <?php if ($successMessage): ?>
            <div class="reset-message success">
                <i class="bi bi-check-circle-fill"></i>
                <span><?php echo htmlspecialchars($successMessage); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 3000);
            </script>
        <?php elseif ($errorMessage): ?>
            <div class="reset-message error">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?php echo htmlspecialchars($errorMessage); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($tokenValid && !$successMessage): ?>
            <form method="POST" action="" class="reset-form" id="resetPasswordForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group-reset">
                    <label for="password">Nova Senha</label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               class="form-control-reset" 
                               id="password" 
                               name="password" 
                               placeholder="Mínimo 6 caracteres" 
                               required 
                               minlength="6">
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group-reset">
                    <label for="confirm_password">Confirmar Senha</label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               class="form-control-reset" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Digite a senha novamente" 
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-hint" id="confirmHint"></div>
                </div>

                <button type="submit" class="btn-reset-password" id="submitBtn">
                    Redefinir Senha
                </button>
            </form>
        <?php endif; ?>

        <div class="back-to-login">
            <a href="index.php">
                <i class="bi bi-arrow-left"></i>
                Voltar para o início
            </a>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Password strength indicator
document.getElementById('password')?.addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    if (password.length === 0) {
        strengthDiv.textContent = '';
        this.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    if (strength <= 2) {
        strengthDiv.textContent = 'Força: Fraca';
        strengthDiv.className = 'password-strength weak';
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
    } else if (strength <= 3) {
        strengthDiv.textContent = 'Força: Média';
        strengthDiv.className = 'password-strength medium';
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    } else {
        strengthDiv.textContent = 'Força: Forte';
        strengthDiv.className = 'password-strength strong';
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
});

// Confirm password validation
document.getElementById('confirm_password')?.addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const hint = document.getElementById('confirmHint');
    
    if (confirm.length === 0) {
        hint.textContent = '';
        this.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    if (password === confirm) {
        hint.textContent = 'As senhas coincidem';
        hint.className = 'form-hint';
        hint.style.color = '#22c55e';
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    } else {
        hint.textContent = 'As senhas não coincidem';
        hint.className = 'form-hint error';
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
    }
});

// Form submission
document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('As senhas não coincidem!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('A senha deve ter pelo menos 6 caracteres!');
        return false;
    }
    
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').textContent = 'Redefinindo...';
});
</script>

<?php include 'includes/footer.php'; ?>
