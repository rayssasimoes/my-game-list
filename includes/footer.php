    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-inner">
            <div class="footer-top">
                <a href="/" class="footer-brand-wrap" aria-label="MyGameList">
                    <span class="footer-logo">MyGameList</span>
                </a>

                <ul class="footer-links" role="list">
                    <li><a href="/about.php">Sobre</a></li>
                    <li><a href="/privacy.php">Política de Privacidade</a></li>
                    <li><a href="/contact.php">Contato</a></li>
                </ul>
            </div>

            <hr class="footer-divider" />

            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> <a href="/" class="footer-brand">MyGameList</a>. Todos os direitos reservados.</span>
            </div>
        </div>
    </footer>

    <!-- Incluir modais de autenticação -->
    <?php include 'includes/modals.php'; ?>

    <script src="public/js/app.js"></script>
</body>
</html>
