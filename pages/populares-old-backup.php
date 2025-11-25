<?php
$pageTitle = 'Jogos Populares - MyGameList';

// Função helper para construir URLs de paginação
function buildPaginationUrl($page, $genre, $platform) {
    $url = 'index.php?page=populares&pageNum=' . $page;
    if ($genre) $url .= '&genre=' . urlencode($genre);
    if ($platform) $url .= '&platform=' . urlencode($platform);
    return $url;
}

// Parâmetros de paginação
$gamesPerPage = 48; // 8 linhas x 6 colunas
$currentPage = isset($_GET['pageNum']) && is_numeric($_GET['pageNum']) && $_GET['pageNum'] > 0 
    ? (int)$_GET['pageNum'] 
    : 1;

// Filtros
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';
$selectedPlatform = isset($_GET['platform']) ? $_GET['platform'] : '';

// Debug: Log dos filtros recebidos
error_log("Filtros recebidos - Gênero: '{$selectedGenre}', Plataforma: '{$selectedPlatform}'");

// Limpar cache se solicitado
if (isset($_GET['clearcache'])) {
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'popular_games') === 0) {
            unset($_SESSION[$key]);
        }
    }
    header('Location: index.php?page=populares');
    exit;
}

// Buscar jogos populares
$games = getPopularGamesFiltered($gamesPerPage, $currentPage, $selectedGenre, $selectedPlatform);

// Log simples
error_log("[Populares] Jogos: " . count($games) . " | Página: {$currentPage}/{$totalPages}");

// Se logado, buscar jogos que já estão na lista do usuário
$userGames = [];
if (isLoggedIn()) {
    $user = getUser();
    $db = getDB();
    $stmt = $db->prepare("
        SELECT g.igdb_id, gu.status 
        FROM game_user gu 
        INNER JOIN games g ON gu.game_id = g.id 
        WHERE gu.user_id = ? AND g.igdb_id IS NOT NULL
    ");
    $stmt->execute([$user['id']]);
    $userGamesList = $stmt->fetchAll();
    
    foreach ($userGamesList as $userGame) {
        $userGames[$userGame['igdb_id']] = $userGame['status'];
    }
}

// Total de páginas (limitando a 50 páginas para performance)
$totalPages = 50;

include 'includes/header.php';
?>

<link rel="stylesheet" href="public/css/pages/populares.css">

<div class="populares-page">
    <!-- Hero Section -->
    <div class="populares-hero">
        <div class="container">
            <h1 class="populares-title">Jogos Populares</h1>
            <p class="populares-subtitle">Descubra os jogos mais bem avaliados e populares do momento</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
        <div class="container">
            <div class="filters-wrapper">
                <div class="filter-group">
                    <label for="genreFilter" class="filter-label">
                        <i class="bi bi-funnel"></i> Gênero
                    </label>
                    <select id="genreFilter" class="filter-select">
                        <option value="">Todos os Gêneros</option>
                        <option value="4">Fighting</option>
                        <option value="5">Shooter</option>
                        <option value="7">Music</option>
                        <option value="8">Platform</option>
                        <option value="9">Puzzle</option>
                        <option value="10">Racing</option>
                        <option value="11">Real Time Strategy (RTS)</option>
                        <option value="12">Role-playing (RPG)</option>
                        <option value="13">Simulator</option>
                        <option value="14">Sport</option>
                        <option value="15">Strategy</option>
                        <option value="16">Turn-based strategy (TBS)</option>
                        <option value="24">Tactical</option>
                        <option value="25">Hack and slash/Beat 'em up</option>
                        <option value="26">Quiz/Trivia</option>
                        <option value="30">Pinball</option>
                        <option value="31">Adventure</option>
                        <option value="32">Indie</option>
                        <option value="33">Arcade</option>
                        <option value="34">Visual Novel</option>
                        <option value="35">Card & Board Game</option>
                        <option value="36">MOBA</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="platformFilter" class="filter-label">
                        <i class="bi bi-controller"></i> Plataforma
                    </label>
                    <select id="platformFilter" class="filter-select">
                        <option value="">Todas as Plataformas</option>
                        <option value="6">PC (Microsoft Windows)</option>
                        <option value="48">PlayStation 4</option>
                        <option value="49">Xbox One</option>
                        <option value="130">Nintendo Switch</option>
                        <option value="167">PlayStation 5</option>
                        <option value="169">Xbox Series X|S</option>
                        <option value="34">Android</option>
                        <option value="39">iOS</option>
                        <option value="14">Mac</option>
                        <option value="3">Linux</option>
                    </select>
                </div>

                <button id="applyFiltersBtn" class="btn-apply-filters">
                    <i class="bi bi-check-circle"></i> Aplicar Filtros
                </button>

                <button id="clearFiltersBtn" class="btn-clear-filters">
                    <i class="bi bi-x-circle"></i> Limpar
                </button>
            </div>
        </div>
    </div>

    <!-- Grid de Jogos -->
    <div class="container">
        <!-- Debug Info (remover depois) -->
        <?php if (isset($_GET['debug'])): ?>
            <div style="background: #333; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; color: #fff; font-family: monospace;">
                <strong style="font-size: 1.2rem;">🔍 Debug Info:</strong><br><br>
                
                <strong>Credenciais:</strong><br>
                IGDB_CLIENT_ID: <?php echo IGDB_CLIENT_ID ? '✅ Configurado (' . strlen(IGDB_CLIENT_ID) . ' chars)' : '❌ NÃO CONFIGURADO'; ?><br>
                IGDB_CLIENT_SECRET: <?php echo IGDB_CLIENT_SECRET ? '✅ Configurado (' . strlen(IGDB_CLIENT_SECRET) . ' chars)' : '❌ NÃO CONFIGURADO'; ?><br><br>
                
                <strong>Token:</strong><br>
                <?php 
                $testToken = getIGDBToken();
                echo $testToken ? '✅ Token obtido: ' . substr($testToken, 0, 20) . '...' : '❌ Erro ao obter token';
                ?><br><br>
                
                <strong>Página:</strong><br>
                Jogos retornados: <?php echo count($games); ?><br>
                Página atual: <?php echo $currentPage; ?><br>
                Total de páginas: <?php echo $totalPages; ?><br>
                Gênero: <?php echo $selectedGenre ?: 'Nenhum'; ?><br>
                Plataforma: <?php echo $selectedPlatform ?: 'Nenhuma'; ?><br><br>
                
                <strong>Cache:</strong><br>
                <?php 
                $cacheCount = 0;
                foreach ($_SESSION as $key => $value) {
                    if (strpos($key, 'popular_games') === 0 && !strpos($key, '_time')) {
                        $cacheCount++;
                    }
                }
                echo "Entradas em cache: {$cacheCount}<br>";
                ?>
                
                <br>
                <a href="index.php?page=populares&clearcache=1" style="color: #667eea; text-decoration: underline;">🗑️ Limpar Cache e Recarregar</a> |
                <a href="test-igdb.php" target="_blank" style="color: #667eea; text-decoration: underline;">🧪 Teste Completo da API</a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($games)): ?>
            <div class="no-games-message">
                <i class="bi bi-search"></i>
                <h3>Nenhum jogo encontrado</h3>
                <p>Tente ajustar os filtros ou voltar para a primeira página</p>
                <?php if ($currentPage > 1 || $selectedGenre || $selectedPlatform): ?>
                    <a href="index.php?page=populares" class="btn-back-home">Voltar para o início</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="populares-games-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="game-card-link">
                            <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                 alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                 class="game-card-image">
                        </a>
                        
                        <!-- Overlay de hover com quick actions -->
                        <div class="game-card-hover-content">
                            <!-- Título do jogo -->
                            <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="game-card-hover-title-link">
                                <h3 class="game-card-hover-title"><?php echo htmlspecialchars($game['name']); ?></h3>
                            </a>
                            
                            <!-- Quick Actions (apenas para usuários logados) -->
                            <?php if (isLoggedIn()): ?>
                                <?php 
                                    $gameStatus = isset($userGames[$game['id']]) ? $userGames[$game['id']] : null;
                                ?>
                                <div class="quick-actions">
                                    <button class="quick-action-btn <?php echo $gameStatus === 'completed' ? 'active' : ''; ?>" 
                                            data-action="completed" 
                                            data-game-id="<?php echo $game['id']; ?>"
                                            data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                            data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>"
                                            title="Marcar como Jogado">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button class="quick-action-btn <?php echo $gameStatus === 'playing' ? 'active' : ''; ?>" 
                                            data-action="playing" 
                                            data-game-id="<?php echo $game['id']; ?>"
                                            data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                            data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>"
                                            title="Jogando Agora">
                                        <i class="bi bi-controller"></i>
                                    </button>
                                    <button class="quick-action-btn <?php echo $gameStatus === 'want_to_play' ? 'active' : ''; ?>" 
                                            data-action="want_to_play" 
                                            data-game-id="<?php echo $game['id']; ?>"
                                            data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                            data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>"
                                            title="Lista de Desejos">
                                        <i class="bi bi-bookmark-heart"></i>
                                    </button>
                                    <button class="quick-action-btn <?php echo $gameStatus === 'dropped' ? 'active' : ''; ?>" 
                                            data-action="dropped" 
                                            data-game-id="<?php echo $game['id']; ?>"
                                            data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                            data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>"
                                            title="Backlog">
                                        <i class="bi bi-hourglass-split"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Debug: Status da paginação -->
        <?php if (isset($_GET['debug'])): ?>
            <div style="background: #444; color: #fff; padding: 1rem; margin: 1rem 0; border-radius: 8px;">
                <strong>Paginação Debug:</strong><br>
                Jogos vazios? <?php echo empty($games) ? 'SIM' : 'NÃO'; ?><br>
                Total de jogos: <?php echo count($games); ?><br>
                Condição (!empty($games)): <?php echo !empty($games) ? 'TRUE (mostra paginação)' : 'FALSE (não mostra)'; ?>
            </div>
        <?php endif; ?>
        
        <!-- Sistema de Paginação -->
        <?php if (count($games) > 0): ?>
            <nav class="pagination-container" aria-label="Navegação de páginas">
                <ul class="pagination-list">
                    <!-- Botão Anterior -->
                    <?php if ($currentPage > 1): ?>
                        <li class="pagination-item">
                            <a href="<?php echo buildPaginationUrl($currentPage - 1, $selectedGenre, $selectedPlatform); ?>" 
                               class="pagination-link pagination-prev">
                                <span aria-hidden="true">&lt;</span> Prev
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-prev disabled">
                                <span aria-hidden="true">&lt;</span> Prev
                            </span>
                        </li>
                    <?php endif; ?>

                    <!-- Números das páginas -->
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    // Mostrar primeira página
                    if ($startPage > 1): ?>
                        <li class="pagination-item">
                            <a href="<?php echo buildPaginationUrl(1, $selectedGenre, $selectedPlatform); ?>" 
                               class="pagination-link">1</a>
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
                                <a href="<?php echo buildPaginationUrl($i, $selectedGenre, $selectedPlatform); ?>" 
                                   class="pagination-link"><?php echo $i; ?></a>
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
                            <a href="<?php echo buildPaginationUrl($totalPages, $selectedGenre, $selectedPlatform); ?>" 
                               class="pagination-link"><?php echo $totalPages; ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Botão Próximo -->
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="pagination-item">
                            <a href="<?php echo buildPaginationUrl($currentPage + 1, $selectedGenre, $selectedPlatform); ?>" 
                               class="pagination-link pagination-next">
                                Next <span aria-hidden="true">&gt;</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="pagination-item">
                            <span class="pagination-link pagination-next disabled">
                                Next <span aria-hidden="true">&gt;</span>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php elseif ($currentPage > 1 || $selectedGenre || $selectedPlatform): ?>
            <!-- Mostrar paginação básica se não houver jogos mas há filtros/páginas -->
            <div style="text-align: center; padding: 2rem; color: #999;">
                <p>⚠️ Nenhum resultado encontrado com os filtros atuais</p>
                <a href="index.php?page=populares" class="btn-back-home">Voltar para o início</a>
            </div>
        <?php endif; ?>
        
        <!-- Mensagem quando API não retorna jogos -->
        <?php if (empty($games) && $currentPage == 1 && !$selectedGenre && !$selectedPlatform): ?>
            <div style="text-align: center; padding: 2rem; color: #999;">
                <p>⚠️ Nenhum jogo foi carregado da API IGDB</p>
                <p style="font-size: 0.9rem;">Verifique suas credenciais da API no arquivo .env</p>
                <p style="font-size: 0.85rem; margin-top: 1rem;">
                    <a href="index.php?page=populares&debug=1" style="color: #667eea;">Clique aqui para ver informações de debug</a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Filtros com JavaScript
console.log('🔧 Iniciando script de filtros...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM carregado');
    
    const genreFilter = document.getElementById('genreFilter');
    const platformFilter = document.getElementById('platformFilter');
    const applyBtn = document.getElementById('applyFiltersBtn');
    const clearBtn = document.getElementById('clearFiltersBtn');
    
    // Debug detalhado
    console.log('Elementos encontrados:', {
        genreFilter: !!genreFilter,
        platformFilter: !!platformFilter,
        applyBtn: !!applyBtn,
        clearBtn: !!clearBtn
    });
    
    // Verificar se os elementos existem
    if (!genreFilter || !platformFilter || !applyBtn || !clearBtn) {
        console.error('❌ Elementos de filtro não encontrados!');
        alert('ERRO: Elementos de filtro não encontrados. Abra o console (F12) para mais detalhes.');
        return;
    }
    
    console.log('✅ Todos os elementos encontrados');
    
    // Restaurar filtros da URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('genre')) {
        genreFilter.value = urlParams.get('genre');
    }
    if (urlParams.has('platform')) {
        platformFilter.value = urlParams.get('platform');
    }
    
    // Mostrar/ocultar botão limpar baseado em filtros ativos
    function updateClearButtonVisibility() {
        if (genreFilter.value || platformFilter.value) {
            clearBtn.style.display = 'flex';
        } else {
            clearBtn.style.display = 'none';
        }
    }
    updateClearButtonVisibility();
    
    // Aplicar filtros
    console.log('📌 Adicionando listener ao botão aplicar');
    applyBtn.addEventListener('click', function(e) {
        console.log('🔘 Botão APLICAR clicado!');
        e.preventDefault();
        const genre = genreFilter.value;
        const platform = platformFilter.value;
        
        console.log('Valores dos filtros:', { genre, platform });
        
        let url = 'index.php?page=populares';
        if (genre) url += '&genre=' + encodeURIComponent(genre);
        if (platform) url += '&platform=' + encodeURIComponent(platform);
        
        console.log('🔗 URL construída:', url);
        console.log('🚀 Navegando...');
        window.location.href = url;
    });
    
    // Limpar filtros
    console.log('📌 Adicionando listener ao botão limpar');
    clearBtn.addEventListener('click', function(e) {
        console.log('🧹 Botão LIMPAR clicado!');
        e.preventDefault();
        console.log('Limpando filtros e redirecionando...');
        window.location.href = 'index.php?page=populares';
    });
    
    // Atualizar visibilidade do botão limpar ao mudar filtros
    genreFilter.addEventListener('change', updateClearButtonVisibility);
    platformFilter.addEventListener('change', updateClearButtonVisibility);
    
    // Permitir aplicar filtros com Enter
    [genreFilter, platformFilter].forEach(select => {
        select.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyBtn.click();
            }
        });
    });
    
    console.log("✅ Script de filtros carregado completamente!");
    
    // ===== TESTE DE DIAGNÓSTICO FINAL =====
    setTimeout(() => {
        const diagnosticReport = {
            scriptLoaded: true,
            applyButton: applyBtn ? 'ENCONTRADO ✅' : 'NÃO ENCONTRADO ❌',
            clearButton: clearBtn ? 'ENCONTRADO ✅' : 'NÃO ENCONTRADO ❌',
            genreSelect: genreFilter ? 'ENCONTRADO ✅' : 'NÃO ENCONTRADO ❌',
            platformSelect: platformFilter ? 'ENCONTRADO ✅' : 'NÃO ENCONTRADO ❌',
            eventListenersActive: 'Verificar clicando nos botões'
        };
        
        console.log("🔍 ==================== DIAGNÓSTICO FINAL ====================");
        console.table(diagnosticReport);
        console.log("💡 INSTRUÇÕES:");
        console.log("1. Se todos elementos foram ENCONTRADOS ✅ mas os botões não funcionam:");
        console.log("   → Problema está na lógica do evento ou navegação");
        console.log("2. Se algum elemento não foi encontrado ❌:");
        console.log("   → Verificar HTML - IDs podem estar errados");
        console.log("3. Clique no botão 'Aplicar Filtros' e veja se aparece o log '🔘 Botão APLICAR clicado!'");
        console.log("=============================================================");
    }, 500);
});
</script>

<?php include 'includes/footer.php'; ?>
