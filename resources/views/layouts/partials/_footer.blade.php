<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container text-center text-md-start">
        <div class="row text-center text-md-start">

            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold text-warning">MyGameList</h5>
                <p>
                    Sua plataforma para organizar e descobrir novos jogos. Explore nossa coleção e monte sua lista pessoal.
                </p>
            </div>

            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Conteúdo</h5>
                <p><a href="{{ url('/') }}" class="text-white" style="text-decoration: none;">Início</a></p>
                <p><a href="#" class="text-white" style="text-decoration: none;">Explorar Jogos</a></p>
            </div>

            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Legal</h5>
                <p><a href="#" class="text-white" style="text-decoration: none;">Termos & Condições</a></p>
                <p><a href="#" class="text-white" style="text-decoration: none;">Política de Privacidade</a></p>
            </div>

            <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold">Contato</h5>
                <p><i class="fas fa-home me-3"></i> Santarém, PA, Brasil</p>
                <p><i class="fas fa-envelope me-3"></i> contato@mygamelist.com</p>
            </div>
        </div>

        <hr class="mb-4">

        <div class="row align-items-center">
            <div class="col-md-7 col-lg-8">
                <p>© {{ date('Y') }} Copyright:
                    <a href="{{ url('/') }}" class="text-white" style="text-decoration: none;">
                        <strong>MyGameList</strong>
                    </a>
                </p>
            </div>
            <div class="col-md-5 col-lg-4">
                <div class="text-center text-md-end">
                    </div>
            </div>
        </div>
    </div>
</footer>