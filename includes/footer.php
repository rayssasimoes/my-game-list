    </main>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Entrar</h5>
                    <button type="button" class="btn-close" onclick="closeModal('loginModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="mb-3">
                            <label for="login_email" class="form-label">Email</label>
                            <input id="login_email" class="form-control" type="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Senha</label>
                            <input id="login_password" class="form-control" type="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Criar Conta</h5>
                    <button type="button" class="btn-close" onclick="closeModal('registerModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="mb-3">
                            <label for="register_name" class="form-label">Nome</label>
                            <input id="register_name" class="form-control" type="text" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="register_email" class="form-label">Email</label>
                            <input id="register_email" class="form-control" type="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="register_password" class="form-label">Senha</label>
                            <input id="register_password" class="form-control" type="password" name="password" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Criar Conta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> <a href="/" class="footer-brand">MyGameList</a>. Todos os direitos reservados.
                </div>
            </div>
        </div>
    </footer>

    <script src="public/js/app.js"></script>
</body>
</html>
