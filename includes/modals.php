<!-- ============================================
     MODAIS DE AUTENTICAÇÃO
     ============================================ -->

<!-- Login Modal -->
<div class="modal" id="loginModal">
    <div class="modal-dialog">
        <div class="modal-content modal-dark-theme">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </h5>
                <button type="button" class="btn-close" onclick="closeModal('loginModal')">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Mensagem de erro -->
                <div id="login-error-message" class="alert alert-error" style="display: none;">
                    <i class="bi bi-exclamation-circle"></i> <span id="login-error-text"></span>
                </div>
                
                <form method="POST" action="index.php" id="loginForm">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="redirect" id="login_redirect" value="">
                    
                    <!-- Email ou Username com ícone -->
                    <div class="mb-3">
                        <label for="login_identifier" class="form-label">Email ou Nome de Usuário</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-person-circle"></i>
                            </span>
                            <input id="login_identifier" class="form-control" type="text" name="identifier" placeholder="Email / Username" required autocomplete="username">
                        </div>
                    </div>
                    
                    <!-- Senha com ícone e toggle -->
                    <div class="mb-3">
                        <label for="login_password" class="form-label">Senha</label>
                        <div class="password-wrapper">
                            <div class="input-group password-container">
                                <span class="input-group-addon">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="login_password" class="form-control form-control-with-icon login-password-field" type="password" name="password" placeholder="******" required autocomplete="current-password">
                                <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="login_password"></i>
                            </div>
                            <small class="form-text login-error-hint" style="display: none; color: #dc3545; margin-top: 0.35rem;">Senha incorreta</small>
                        </div>
                    </div>

                    <!-- (removed) Manter Conectado - simplified login modal -->
                    
                    <!-- Botões de ação -->
                    <div class="login-actions">
                        <button type="submit" class="btn btn-primary-custom-compact">Entrar</button>
                        <button type="button" class="btn btn-link-custom" onclick="showForgotPassword()">Esqueci a senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal" id="registerModal">
    <div class="modal-dialog">
        <div class="modal-content modal-dark-theme">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Criar Conta
                </h5>
                <button type="button" class="btn-close" onclick="closeModal('registerModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php" id="registerForm">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="redirect" id="register_redirect" value="">
                    
                    <!-- Nome com ícone -->
                    <div class="mb-3">
                        <label for="register_name" class="form-label">Nome Completo</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-person"></i>
                            </span>
                            <input id="register_name" class="form-control" type="text" name="name" placeholder="Seu nome completo" required autocomplete="name">
                        </div>
                    </div>

                    <!-- Username com ícone -->
                    <div class="mb-3">
                        <label for="register_username" class="form-label">Nome de Usuário</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-at"></i>
                            </span>
                            <input id="register_username" class="form-control availability-check" type="text" name="username" placeholder="username" required autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}" data-check-type="username">
                        </div>
                        <div class="form-text availability-hint" id="username-hint">3-20 caracteres, apenas letras, números e underscore</div>
                    </div>
                    
                    <!-- Email com ícone -->
                    <div class="mb-3">
                        <label for="register_email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input id="register_email" class="form-control availability-check" type="email" name="email" placeholder="seu@email.com" required autocomplete="email" data-check-type="email">
                        </div>
                        <div class="form-text availability-hint" id="email-hint" style="display: none;">Este email já está em uso</div>
                    </div>
                    
                    <!-- Senha com ícone e toggle -->
                    <div class="mb-3">
                        <label for="register_password" class="form-label">Senha</label>
                        <div class="password-wrapper">
                            <div class="input-group password-container">
                                <span class="input-group-addon">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="register_password" class="form-control form-control-with-icon password-validation" type="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password">
                                <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="register_password"></i>
                            </div>
                            <div class="form-text password-hint" id="password-hint">A senha deve ter no mínimo 6 caracteres</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100">Criar Conta</button>
                </form>
            </div>
        </div>
    </div>
</div>
 
<!-- Forgot Password Modal -->
<div class="modal" id="forgotPasswordModal">
    <div class="modal-dialog">
        <div class="modal-content modal-dark-theme">
            <div class="modal-header forgot-password-header">
                <!-- Botão voltar no canto superior esquerdo -->
                <button type="button" class="back-link-btn" onclick="backToLogin()">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
                
                <h5 class="modal-title">
                    <i class="bi bi-key"></i> <span id="forgot-password-title">Redefinir Senha</span>
                </h5>
                <button type="button" class="btn-close" onclick="closeModal('forgotPasswordModal')">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Mensagem de feedback -->
                <div id="forgot-password-message" class="alert" style="display: none;">
                    <i class="bi bi-info-circle"></i> <span id="forgot-password-text"></span>
                </div>
                
                <form id="forgotPasswordForm">
                    <input type="hidden" name="action" value="request_password_reset">
                    
                    <!-- Email com ícone -->
                    <div class="mb-3">
                        <label for="forgot_email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input id="forgot_email" class="form-control" type="email" name="email" placeholder="Digite seu email cadastrado" required autocomplete="email">
                        </div>
                        <small class="form-text" style="color: #94a3b8; margin-top: 0.5rem;">
                            Enviaremos um link de recuperação para este email
                        </small>
                    </div>
                    
                    <!-- Botão centralizado -->
                    <button type="submit" class="btn btn-primary-custom w-100" id="forgot-password-submit">
                        <i class="bi bi-send"></i> Enviar Link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>