<footer class="footer-modern">
    <div class="footer-container">
        <div class="footer-content">
            <!-- Copyright -->
            <span class="footer-copyright">
                © {{ date('Y') }} <a href="{{ url('/') }}" class="footer-brand">MyGameList</a>. Todos os direitos reservados.
            </span>
            
            <!-- Links -->
            <ul class="footer-links">
                <li>
                    <a href="{{ url('/') }}">Início</a>
                </li>
                <li>
                    <a href="{{ route('games.popular') }}">Explorar Jogos</a>
                </li>
                <li>
                    <a href="#">Sobre</a>
                </li>
                <li>
                    <a href="#">Política de Privacidade</a>
                </li>
                <li>
                    <a href="#">Contato</a>
                </li>
            </ul>
        </div>
    </div>
</footer>
