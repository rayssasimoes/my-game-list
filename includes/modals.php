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
                <form method="POST" action="index.php" id="loginForm">
                    <input type="hidden" name="action" value="login">
                    
                    <!-- Email ou Username com ícone -->
                    <div class="mb-3">
                        <label for="login_identifier" class="form-label">Email ou Nome de Usuário</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-person-circle"></i>
                            </span>
                            <input id="login_identifier" class="form-control" type="text" name="identifier" placeholder="email@exemplo.com ou @username" required autocomplete="username">
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
                                <input id="login_password" class="form-control form-control-with-icon" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                                <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="login_password"></i>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100">Entrar</button>
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
                            <input id="register_username" class="form-control" type="text" name="username" placeholder="username (único)" required autocomplete="username" pattern="[a-zA-Z0-9_]{3,20}">
                        </div>
                        <div class="form-text">3-20 caracteres, apenas letras, números e underscore</div>
                    </div>
                    
                    <!-- Email com ícone -->
                    <div class="mb-3">
                        <label for="register_email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input id="register_email" class="form-control" type="email" name="email" placeholder="seu@email.com" required autocomplete="email">
                        </div>
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
