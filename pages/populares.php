<?php
$pageTitle = 'Jogos Populares - MyGameList';

// Parâmetros de paginação
$gamesPerPage = 48;
$currentPage = (int)(isset($_GET['pageNum']) && is_numeric($_GET['pageNum']) ? max(1, (int)$_GET['pageNum']) : 1);
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

// Buscar jogos
$games = getPopularGamesFiltered($gamesPerPage, $currentPage, $selectedGenre, $selectedPlatform);
error_log("[Populares] Jogos retornados: " . count($games));

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

    <div class="container">
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
                    $currentPageInt = intval($currentPage);
                    $totalPagesInt = intval($totalPages);
                    $startPage = max(1, $currentPageInt - 2);
                    $endPage = min($totalPagesInt, $currentPageInt + 2);
                    ?>
                    
                    <!-- Botão Anterior -->
                    <?php if ($currentPageInt > 1): ?>
                        <li class="pagination-item">
                            <a href="index.php?page=populares&pageNum=<?php echo $currentPageInt - 1; ?><?php if($selectedGenre) echo '&genre='.urlencode($selectedGenre); ?><?php if($selectedPlatform) echo '&platform='.urlencode($selectedPlatform); ?>" class="pagination-link pagination-prev">&lt; Prev</a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-prev disabled">&lt; Prev</span>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Primeira página -->
                    <?php if ($startPage > 1): ?>
                        <li class="pagination-item">
                            <a href="index.php?page=populares&pageNum=1<?php if($selectedGenre) echo '&genre='.urlencode($selectedGenre); ?><?php if($selectedPlatform) echo '&platform='.urlencode($selectedPlatform); ?>" class="pagination-link">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Páginas do meio -->
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="pagination-item">
                            <?php if ($i == $currentPageInt): ?>
                                <span class="pagination-link pagination-current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="index.php?page=populares&pageNum=<?php echo $i; ?><?php if($selectedGenre) echo '&genre='.urlencode($selectedGenre); ?><?php if($selectedPlatform) echo '&platform='.urlencode($selectedPlatform); ?>" class="pagination-link"><?php echo $i; ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Última página -->
                    <?php if ($endPage < $totalPagesInt): ?>
                        <?php if ($endPage < $totalPagesInt - 1): ?>
                            <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                        <?php endif; ?>
                        <li class="pagination-item">
                            <a href="index.php?page=populares&pageNum=<?php echo $totalPagesInt; ?><?php if($selectedGenre) echo '&genre='.urlencode($selectedGenre); ?><?php if($selectedPlatform) echo '&platform='.urlencode($selectedPlatform); ?>" class="pagination-link"><?php echo $totalPagesInt; ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Botão Próximo -->
                    <?php if ($currentPageInt < $totalPagesInt): ?>
                        <li class="pagination-item">
                            <a href="index.php?page=populares&pageNum=<?php echo $currentPageInt + 1; ?><?php if($selectedGenre) echo '&genre='.urlencode($selectedGenre); ?><?php if($selectedPlatform) echo '&platform='.urlencode($selectedPlatform); ?>" class="pagination-link pagination-next">Next &gt;</a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-next disabled">Next &gt;</span>
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
