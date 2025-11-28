    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-inner">
            <div class="footer-top">
                <div class="footer-brand-wrap" aria-label="MyGameList">
                    <span class="footer-logo">MyGameList</span>
                </div>

                <ul class="footer-links" role="list">
                    <li><span class="footer-link-text">Sobre</span></li>
                    <li><span class="footer-link-text">Política de Privacidade</span></li>
                    <li><span class="footer-link-text">Contato</span></li>
                </ul>
            </div>

            <hr class="footer-divider" />

            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> <span class="footer-brand">MyGameList</span>. Todos os direitos reservados.</span>
            </div>
        </div>
    </footer>

    <!-- Incluir modais de autenticação -->
    <?php include 'includes/modals.php'; ?>

    <script src="public/js/app.js?v=<?php echo filemtime(__DIR__ . '/../public/js/app.js'); ?>"></script>
</body>
</html>
