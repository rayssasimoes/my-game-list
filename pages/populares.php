<?php
$pageTitle = 'Jogos Populares - MyGameList';

// Parâmetros de paginação
// Usar 54 itens por página (divisível por 6 para manter o grid sem buracos)
$gamesPerPage = 54;
// Capturar número da página atual:
// - Priorizar $_GET['page'] quando for numérico (compatibilidade com requisições que enviem apenas o número)
// - Fallback para $_GET['pageNum'] (implementação existente)
// - Padrão: 1
$currentPageNum = 1;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPageNum = max(1, (int)$_GET['page']);
} elseif (isset($_GET['pageNum']) && is_numeric($_GET['pageNum'])) {
    $currentPageNum = max(1, (int)$_GET['pageNum']);
}
// Garantir tipo inteiro durante todo o uso
$currentPageNum = (int) $currentPageNum;
// (debug removido)

// Estimativa de páginas (teto) — será ajustada dinamicamente quando soubermos que não há próxima página
$totalPages = 50; // Limitado para performance

// Filtros
$selectedGenre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$selectedPlatform = isset($_GET['platform']) ? trim($_GET['platform']) : '';

// Limpar cache
if (isset($_GET['clearcache'])) {
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'popular_games') === 0) unset($_SESSION[$key]);
    }
    header('Location: index.php?page=populares');
    exit;
}

// Buscar jogos: solicitar exatamente $gamesPerPage itens (limit = 54 conforme solicitado)
$games = getPopularGamesFiltered($gamesPerPage, $currentPageNum, $selectedGenre, $selectedPlatform);

// Verificar existência provável de próxima página: se retornou exatamente $gamesPerPage, pode haver próxima.
// Obs: para detectar com certeza é preciso solicitar +1 item ao IGDB (método alternativo),
// mas aqui obedecemos ao requisito de enviar limit=54.
$hasNextPage = count($games) === $gamesPerPage;
error_log("[Populares] Jogos retornados (exibindo): " . count($games) . ", hasNext (provável): " . ($hasNextPage ? '1' : '0'));

// Jogos do usuário
$userGames = [];
if (isLoggedIn()) {
    $user = getUser();
    $db = getDB();
    $stmt = $db->prepare("SELECT g.igdb_id, gu.status FROM game_user gu 
                          INNER JOIN games g ON gu.game_id = g.id 
                          WHERE gu.user_id = ? AND g.igdb_id IS NOT NULL");
    $stmt->execute([$user['id']]);
    foreach ($stmt->fetchAll() as $row) {
        $userGames[$row['igdb_id']] = $row['status'];
    }
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="public/css/pages/populares.css">

<div class="populares-page">
    <div class="populares-hero">
        <div class="container">
            <h1 class="populares-title">Jogos Populares</h1>
            <p class="populares-subtitle">Descubra os jogos mais bem avaliados e populares do momento</p>
        </div>
    </div>

    <div class="filters-section">
        <div class="container">
            <div class="filters-wrapper">
                <div class="filter-group">
                    <label for="genreFilter" class="filter-label"><i class="bi bi-funnel"></i> Gênero</label>
                    <select id="genreFilter" class="filter-select">
                        <option value="">Todos os Gêneros</option>
                        <option value="4" <?= $selectedGenre == '4' ? 'selected' : '' ?>>Fighting</option>
                        <option value="5" <?= $selectedGenre == '5' ? 'selected' : '' ?>>Shooter</option>
                        <option value="12" <?= $selectedGenre == '12' ? 'selected' : '' ?>>RPG</option>
                        <option value="31" <?= $selectedGenre == '31' ? 'selected' : '' ?>>Adventure</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="platformFilter" class="filter-label"><i class="bi bi-joystick"></i> Plataforma</label>
                    <select id="platformFilter" class="filter-select">
                        <option value="">Todas as Plataformas</option>
                        <option value="6" <?= $selectedPlatform == '6' ? 'selected' : '' ?>>PC</option>
                        <option value="48" <?= $selectedPlatform == '48' ? 'selected' : '' ?>>PlayStation 4</option>
                        <option value="49" <?= $selectedPlatform == '49' ? 'selected' : '' ?>>Xbox One</option>
                        <option value="130" <?= $selectedPlatform == '130' ? 'selected' : '' ?>>Nintendo Switch</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button id="applyFiltersBtn" class="btn-apply-filters">
                        <i class="bi bi-check-circle"></i> Aplicar Filtros
                    </button>
                    <button id="clearFiltersBtn" class="btn-clear-filters" style="display: <?= $selectedGenre || $selectedPlatform ? 'inline-flex' : 'none' ?>;">
                        <i class="bi bi-x-circle"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id="lista-jogos">
        <?php if (empty($games)): ?>
            <div class="no-games-message">
                <i class="bi bi-search"></i>
                <h3>Nenhum jogo encontrado</h3>
                <p>Tente ajustar os filtros ou voltar para a primeira página</p>
                <a href="index.php?page=populares" class="btn-back-home">Voltar para o início</a>
            </div>
        <?php else: ?>
            <div class="populares-games-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <a href="index.php?page=game&id=<?= $game['id'] ?>" class="game-card-link">
                            <img src="<?= htmlspecialchars($game['cover']) ?>" 
                                 alt="<?= htmlspecialchars($game['name']) ?>" 
                                 class="game-card-image">
                        </a>
                        
                        <div class="game-card-hover-content">
                            <a href="index.php?page=game&id=<?= $game['id'] ?>" class="game-card-hover-title-link">
                                <h3 class="game-card-hover-title"><?= htmlspecialchars($game['name']) ?></h3>
                            </a>
                            
                            <?php if (isLoggedIn()): 
                                $gameStatus = $userGames[$game['id']] ?? null; ?>
                                <div class="quick-actions">
                                    <button class="quick-action-btn <?= $gameStatus === 'completed' ? 'active' : '' ?>" 
                                            data-action="completed" 
                                            data-game-id="<?= $game['id'] ?>"
                                            data-game-name="<?= htmlspecialchars($game['name']) ?>"
                                            data-game-cover="<?= htmlspecialchars($game['cover']) ?>"
                                            title="Jogado">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="quick-action-btn <?= $gameStatus === 'playing' ? 'active' : '' ?>" 
                                            data-action="playing" 
                                            data-game-id="<?= $game['id'] ?>"
                                            data-game-name="<?= htmlspecialchars($game['name']) ?>"
                                            data-game-cover="<?= htmlspecialchars($game['cover']) ?>"
                                            title="Jogando">
                                        <i class="bi bi-controller"></i>
                                    </button>
                                    <button class="quick-action-btn <?= $gameStatus === 'want_to_play' ? 'active' : '' ?>" 
                                            data-action="want_to_play" 
                                            data-game-id="<?= $game['id'] ?>"
                                            data-game-name="<?= htmlspecialchars($game['name']) ?>"
                                            data-game-cover="<?= htmlspecialchars($game['cover']) ?>"
                                            title="Lista de Desejos">
                                        <i class="bi bi-bookmark"></i>
                                    </button>
                                    <button class="quick-action-btn <?= $gameStatus === 'dropped' ? 'active' : '' ?>" 
                                            data-action="dropped" 
                                            data-game-id="<?= $game['id'] ?>"
                                            data-game-name="<?= htmlspecialchars($game['name']) ?>"
                                            data-game-cover="<?= htmlspecialchars($game['cover']) ?>"
                                            title="Abandonado">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PAGINAÇÃO -->
            <nav class="pagination-container">
                <ul class="pagination-list">
                    <?php
                    // Preparar parâmetros base (preservar filtros)
                    $baseParams = [];
                    if ($selectedGenre) $baseParams['genre'] = $selectedGenre;
                    if ($selectedPlatform) $baseParams['platform'] = $selectedPlatform;

                    $currentPageInt = max(1, intval($currentPageNum));

                    // Obter total de resultados do IGDB para calcular páginas corretamente
                    $totalCount = 0;
                    try {
                        $totalCount = intval(getPopularGamesCount($selectedGenre, $selectedPlatform));
                    } catch (Exception $e) {
                        error_log('Erro ao obter contagem de jogos: ' . $e->getMessage());
                        $totalCount = 0;
                    }

                    if ($totalCount > 0) {
                        $totalPagesInt = max(1, intval(ceil($totalCount / $gamesPerPage)));
                    } else {
                        // Fallback: se não conseguimos obter a contagem, estimar com heurística
                        $totalPagesInt = intval($totalPages);
                        if (isset($hasNextPage) && !$hasNextPage) {
                            $totalPagesInt = max($currentPageInt, 1);
                        }
                    }

                    // Janela de páginas
                    $window = 5;
                    $half = floor($window / 2);
                    $startPage = max(1, $currentPageInt - $half);
                    $endPage = min($totalPagesInt, $startPage + $window - 1);
                    $startPage = max(1, $endPage - $window + 1);

                    // Helper para construir URL com parâmetros (closure local para evitar redeclaração)
                    $buildPageUrl = function($pageNum, $baseParams) {
                        $params = array_merge(['page' => 'populares', 'pageNum' => $pageNum], $baseParams);
                        $url = 'index.php?' . http_build_query($params);
                        // Adicionar âncora para rolar até a lista de jogos após carregamento
                        return $url . '#lista-jogos';
                    };
                    ?>

                    <!-- Botão Anterior -->
                    <?php if ((int)$currentPageInt > 1): ?>
                        <li class="pagination-item">
                            <a href="<?= htmlspecialchars($buildPageUrl($currentPageInt - 1, $baseParams)) ?>" class="pagination-link pagination-prev">&lt;</a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-prev disabled">&lt;</span>
                        </li>
                    <?php endif; ?>

                    <!-- Primeira página e reticências antes da janela -->
                    <?php if ($startPage > 1): ?>
                        <li class="pagination-item"><a href="<?= htmlspecialchars($buildPageUrl(1, $baseParams)) ?>" class="pagination-link">1</a></li>
                        <?php if ($startPage > 2): ?>
                            <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Páginas da janela -->
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="pagination-item">
                            <?php if ((int)$currentPageInt === (int)$i): ?>
                                <span class="pagination-link pagination-current active" aria-current="page"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($buildPageUrl($i, $baseParams)) ?>" class="pagination-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <!-- Reticências e última página -->
                    <?php if ($endPage < $totalPagesInt): ?>
                        <?php if ($endPage < $totalPagesInt - 1): ?>
                            <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                        <?php endif; ?>
                        <li class="pagination-item"><a href="<?= htmlspecialchars($buildPageUrl($totalPagesInt, $baseParams)) ?>" class="pagination-link"><?php echo $totalPagesInt; ?></a></li>
                    <?php endif; ?>

                    <!-- Botão Próximo -->
                    <?php if ($currentPageInt < $totalPagesInt): ?>
                        <li class="pagination-item">
                            <a href="<?= htmlspecialchars($buildPageUrl($currentPageInt + 1, $baseParams)) ?>" class="pagination-link pagination-next">&gt;</a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-next disabled">&gt;</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    'use strict';
    console.log('[FILTROS] Iniciando...');
    
    function init() {
        const genreFilter = document.getElementById('genreFilter');
        const platformFilter = document.getElementById('platformFilter');
        const applyBtn = document.getElementById('applyFiltersBtn');
        const clearBtn = document.getElementById('clearFiltersBtn');
        
        if (!genreFilter || !platformFilter || !applyBtn || !clearBtn) {
            console.error('[FILTROS] Elementos não encontrados!');
            return;
        }
        
        console.log('[FILTROS] Elementos OK!');
        
        // Aplicar
        applyBtn.onclick = function() {
            console.log('[FILTROS] Aplicando...');
            let url = 'index.php?page=populares';
            if (genreFilter.value) url += '&genre=' + genreFilter.value;
            if (platformFilter.value) url += '&platform=' + platformFilter.value;
            window.location.href = url;
        };
        
        // Limpar
        clearBtn.onclick = function() {
            console.log('[FILTROS] Limpando...');
            window.location.href = 'index.php?page=populares';
        };
        
        // Toggle botão limpar
        function toggleClear() {
            clearBtn.style.display = (genreFilter.value || platformFilter.value) ? 'inline-flex' : 'none';
        }
        genreFilter.onchange = toggleClear;
        platformFilter.onchange = toggleClear;
        
        console.log('[FILTROS] Pronto!');
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

<?php include 'includes/footer.php'; ?>
