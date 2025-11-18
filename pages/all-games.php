<?php
$pageTitle = 'Navegação de Jogos - MyGameList';

// Definir o número de jogos por página e calcular o offset
$gamesPerPage = 50;
$currentPage = isset($_GET['pageNum']) && is_numeric($_GET['pageNum']) && $_GET['pageNum'] > 0 
    ? (int)$_GET['pageNum'] 
    : 1;
$offset = ($currentPage - 1) * $gamesPerPage;

// Buscar jogos populares da API IGDB
$games = getPopularGames($gamesPerPage, $offset);

// Total estimado de jogos na API IGDB
$totalGamesCount = 377915; // Este número pode ser atualizado periodicamente

// Definir o número total de páginas (limitando a 10 páginas por performance)
$totalPages = 10;

include 'includes/header.php';
?>

<!-- Barra de Sub-Cabeçalho -->
<div class="browse-subheader">
    <div class="container-fluid">
        <div class="subheader-content">
            <!-- Lado Esquerdo: Total de Jogos -->
            <div class="subheader-left">
                <h1 class="total-games-count"><?php echo number_format($totalGamesCount, 0, ',', '.'); ?> Jogos</h1>
            </div>
            
            <!-- Lado Direito: Controlos de Filtragem -->
            <div class="subheader-right">
                <button class="btn-apply-filters">
                    <i class="bi bi-funnel"></i>
                    Aplicar Filtros
                </button>
                
                <div class="sort-dropdown">
                    <button class="btn-sort-by" id="sortDropdownBtn">
                        <span>Ordenar por: <strong>Popularidade</strong></span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="sort-dropdown-menu" id="sortDropdownMenu">
                        <a href="#" class="sort-option active" data-sort="popularity">Popularidade</a>
                        <a href="#" class="sort-option" data-sort="title">Título do Jogo</a>
                        <a href="#" class="sort-option" data-sort="release-date">Data de Lançamento</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conteúdo Principal -->
<div class="browse-content">
    <div class="container-fluid">
        <?php if (empty($games)): ?>
            <div class="no-games-message">
                <p>Nenhum jogo encontrado nesta página.</p>
                <?php if ($currentPage > 1): ?>
                    <a href="index.php?page=all-games" class="btn-back-to-first">Voltar para a primeira página</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Grid de Jogos Compactos (apenas covers) -->
            <div class="games-browse-grid">
                <?php foreach ($games as $game): ?>
                    <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="game-cover-item">
                        <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                             alt="<?php echo htmlspecialchars($game['name']); ?>"
                             title="<?php echo htmlspecialchars($game['name']); ?>">
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Paginação -->
            <nav class="pagination-container" aria-label="Navegação de páginas">
                <ul class="pagination-list">
                    <!-- Botão Anterior -->
                    <?php if ($currentPage > 1): ?>
                        <li class="pagination-item">
                            <a href="index.php?page=all-games&pageNum=<?php echo $currentPage - 1; ?>" 
                               class="pagination-link pagination-prev">
                                <span aria-hidden="true">&lt;</span> Anterior
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-prev disabled">
                                <span aria-hidden="true">&lt;</span> Anterior
                            </span>
                        </li>
                    <?php endif; ?>

                    <!-- Números das páginas -->
                    <?php
                    // Lógica para mostrar números de página com reticências
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    // Mostrar primeira página
                    if ($startPage > 1): ?>
                        <li class="pagination-item">
                            <a href="index.php?page=all-games&pageNum=1" class="pagination-link">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="pagination-item">
                                <span class="pagination-ellipsis">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Páginas do meio -->
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="pagination-item">
                            <?php if ($i === $currentPage): ?>
                                <span class="pagination-link pagination-current" aria-current="page"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="index.php?page=all-games&pageNum=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Mostrar última página -->
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="pagination-item">
                                <span class="pagination-ellipsis">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="pagination-item">
                            <a href="index.php?page=all-games&pageNum=<?php echo $totalPages; ?>" class="pagination-link"><?php echo $totalPages; ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Botão Próximo -->
                    <?php if ($currentPage < $totalPages && count($games) === $gamesPerPage): ?>
                        <li class="pagination-item">
                            <a href="index.php?page=all-games&pageNum=<?php echo $currentPage + 1; ?>" 
                               class="pagination-link pagination-next">
                                Próx. <span aria-hidden="true">&gt;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-next disabled">
                                Próx. <span aria-hidden="true">&gt;</span>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
// Dropdown de ordenação
document.addEventListener('DOMContentLoaded', function() {
    const sortBtn = document.getElementById('sortDropdownBtn');
    const sortMenu = document.getElementById('sortDropdownMenu');
    const sortOptions = document.querySelectorAll('.sort-option');
    
    if (sortBtn && sortMenu) {
        sortBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sortMenu.classList.toggle('show');
        });
        
        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function() {
            sortMenu.classList.remove('show');
        });
        
        // Prevenir fechamento ao clicar dentro do menu
        sortMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Atualizar seleção
        sortOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover classe active de todas as opções
                sortOptions.forEach(opt => opt.classList.remove('active'));
                
                // Adicionar classe active à opção clicada
                this.classList.add('active');
                
                // Atualizar texto do botão
                const sortText = this.textContent;
                sortBtn.querySelector('strong').textContent = sortText;
                
                // Fechar dropdown
                sortMenu.classList.remove('show');
                
                // Aqui você pode adicionar lógica para reordenar os jogos
                // Por exemplo, fazer uma requisição AJAX ou recarregar a página com parâmetros
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
