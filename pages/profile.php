<?php
requireLogin();

$pageTitle = 'Perfil - MyGameList';
$user = getUser();

// Pegar primeiro nome
$firstName = explode(' ', $user['name'])[0];

// Buscar estatísticas do usuário
$db = getDB();

// Total de jogos completados
$stmt = $db->prepare("SELECT COUNT(*) as total FROM game_user WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$user['id']]);
$completedTotal = $stmt->fetch()['total'];

// Jogos completados este ano
$stmt = $db->prepare("
    SELECT COUNT(*) as total 
    FROM game_user 
    WHERE user_id = ? 
    AND status = 'completed' 
    AND YEAR(updated_at) = YEAR(CURRENT_DATE)
");
$stmt->execute([$user['id']]);
$completedThisYear = $stmt->fetch()['total'];

// Buscar jogos favoritos (máximo 5 para a seção de destaque)
$stmt = $db->prepare("
    SELECT g.*, gu.status, gu.rating 
    FROM games g
    INNER JOIN game_user gu ON g.id = gu.game_id
    WHERE gu.user_id = ? AND gu.rating >= 9
    ORDER BY gu.updated_at DESC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$favoriteGames = $stmt->fetchAll();

// Buscar todos os jogos favoritos para a lista completa
$stmt = $db->prepare("
    SELECT g.*, gu.status, gu.rating 
    FROM games g
    INNER JOIN game_user gu ON g.id = gu.game_id
    WHERE gu.user_id = ? AND gu.rating >= 9
    ORDER BY gu.updated_at DESC
");
$stmt->execute([$user['id']]);
$allFavorites = $stmt->fetchAll();

// Buscar jogos por status
$gamesByStatus = [
    'playing' => [],
    'completed' => [],
    'dropped' => []
];

foreach (['playing', 'completed', 'dropped'] as $status) {
    $stmt = $db->prepare("
        SELECT g.*, gu.status, gu.rating 
        FROM games g
        INNER JOIN game_user gu ON g.id = gu.game_id
        WHERE gu.user_id = ? AND gu.status = ?
        ORDER BY gu.updated_at DESC
    ");
    $stmt->execute([$user['id'], $status]);
    $gamesByStatus[$status] = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="profile-page">
    <div class="profile-container">
        
        <!-- Cabeçalho do Perfil -->
        <div class="profile-header">
            <div class="profile-header-left">
                <div class="profile-avatar">
                    <?php if (!empty($user['avatar_path'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar de <?php echo htmlspecialchars($firstName); ?>">
                    <?php else: ?>
                        <svg class="default-avatar-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="profile-header-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($firstName); ?></h1>
                    <a href="index.php?page=edit-profile" class="btn-edit-profile">
                        <i class="bi bi-pencil"></i> Editar Perfil
                    </a>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $completedTotal; ?></span>
                    <span class="stat-label">JOGOS CONCLUÍDOS</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $completedThisYear; ?></span>
                    <span class="stat-label">CONCLUÍDOS ESTE ANO</span>
                </div>
            </div>
        </div>

        <!-- Bio (se existir) -->
        <?php if (!empty($user['bio'])): ?>
            <div class="profile-bio">
                <p class="bio-text"><?php echo htmlspecialchars($user['bio']); ?></p>
            </div>
        <?php endif; ?>

        <!-- Navegação de Abas -->
        <nav class="profile-tabs">
            <button class="profile-tab active" data-tab="overview">Perfil</button>
            <button class="profile-tab" data-tab="games">Jogos</button>
        </nav>

        <!-- Conteúdo das Abas -->
        
        <!-- Aba: Perfil -->
        <div class="tab-content active" data-tab-content="overview">
            <!-- Seção: 5 Jogos Favoritos -->
            <section class="favorite-games-section">
                <div class="section-header-with-action">
                    <h2 class="section-title">JOGOS FAVORITOS</h2>
                    <button class="btn-add-favorite-search" onclick="openFavoriteSearchModal()">
                        <i class="bi bi-search"></i> Adicionar Favorito
                    </button>
                </div>
                <div class="favorite-games-grid">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <?php if (isset($favoriteGames[$i])): ?>
                            <?php $game = $favoriteGames[$i]; ?>
                            <div class="favorite-game-card">
                                <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>"
                                     class="favorite-game-cover">
                                <div class="favorite-game-overlay">
                                    <button class="btn-remove-favorite" onclick="removeFavorite(<?php echo $game['id']; ?>, '<?php echo htmlspecialchars($game['name']); ?>')" title="Remover dos favoritos">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="favorite-game-card empty" onclick="openFavoriteSearchModal()">
                                <button class="btn-add-favorite">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </section>
        </div>

        <!-- Aba: Jogos -->
        <div class="tab-content" data-tab-content="games">
            <!-- Seção: Lista de Jogos -->
            <section class="games-list-section">
                <!-- Sub-navegação de filtros -->
                <nav class="games-filter-tabs">
                    <button class="filter-tab active" data-filter="playing">Jogando</button>
                    <button class="filter-tab" data-filter="completed">Jogado</button>
                    <button class="filter-tab" data-filter="dropped">Abandonado</button>
                    <button class="filter-tab" data-filter="favorites">Favorito</button>
                </nav>

                <!-- Conteúdo dos filtros -->
                <div class="games-filter-content">
                    
                    <!-- Tab: Jogando -->
                    <div class="filter-content active" data-filter-content="playing">
                        <?php if (empty($gamesByStatus['playing'])): ?>
                            <p class="empty-message">Nenhum jogo nesta categoria</p>
                        <?php else: ?>
                            <div class="games-list-grid">
                                <?php foreach ($gamesByStatus['playing'] as $game): ?>
                                            <div class="game-list-card">
                                                <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                                     alt="<?php echo htmlspecialchars($game['name']); ?>"
                                                     class="game-list-cover">
                                                <div class="game-card-overlay">
                                                    <button class="game-action-btn" onclick="editGame(<?php echo $game['id']; ?>)" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="game-action-btn btn-danger" onclick="removeGame(<?php echo $game['id']; ?>)" title="Remover">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Jogado -->
                    <div class="filter-content" data-filter-content="completed">
                        <?php if (empty($gamesByStatus['completed'])): ?>
                            <p class="empty-message">Nenhum jogo nesta categoria</p>
                        <?php else: ?>
                            <div class="games-list-grid">
                                <?php foreach ($gamesByStatus['completed'] as $game): ?>
                                    <div class="game-list-card">
                                        <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                             alt="<?php echo htmlspecialchars($game['name']); ?>"
                                             class="game-list-cover">
                                        <div class="game-card-overlay">
                                            <button class="game-action-btn" onclick="editGame(<?php echo $game['id']; ?>)" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="game-action-btn btn-danger" onclick="removeGame(<?php echo $game['id']; ?>)" title="Remover">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Abandonado -->
                    <div class="filter-content" data-filter-content="dropped">
                        <?php if (empty($gamesByStatus['dropped'])): ?>
                            <p class="empty-message">Nenhum jogo nesta categoria</p>
                        <?php else: ?>
                            <div class="games-list-grid">
                                <?php foreach ($gamesByStatus['dropped'] as $game): ?>
                                    <div class="game-list-card">
                                        <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                             alt="<?php echo htmlspecialchars($game['name']); ?>"
                                             class="game-list-cover">
                                        <div class="game-card-overlay">
                                            <button class="game-action-btn" onclick="editGame(<?php echo $game['id']; ?>)" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="game-action-btn btn-danger" onclick="removeGame(<?php echo $game['id']; ?>)" title="Remover">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Favorito -->
                    <div class="filter-content" data-filter-content="favorites">
                        <?php if (empty($allFavorites)): ?>
                            <p class="empty-message">Nenhum jogo favorito ainda. Adicione jogos clicando no botão 'Adicionar Favorito'!</p>
                        <?php else: ?>
                            <div class="games-list-grid">
                                <?php foreach ($allFavorites as $game): ?>
                                    <div class="game-list-card">
                                        <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                             alt="<?php echo htmlspecialchars($game['name']); ?>"
                                             class="game-list-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </section>
        </div>

    </div>
</div>

<!-- Modal: Adicionar Jogo Favorito -->
<div id="favoriteSearchModal" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content modal-medium">
        <div class="modal-header">
            <h2 class="modal-title">Adicionar Jogo Favorito</h2>
            <button class="modal-close" aria-label="Fechar" onclick="closeFavoriteSearchModal()">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="favorite-search-container">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" 
                           id="favoriteSearchInput" 
                           class="favorite-search-input" 
                           placeholder="Pesquisar jogos..." 
                           autocomplete="off">
                </div>
                <div id="favoriteSearchResults" class="favorite-search-results"></div>
            </div>
        </div>
    </div>
</div>

<script>
function openFavoriteSearchModal() {
    const modal = document.getElementById('favoriteSearchModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');
    setTimeout(() => {
        document.getElementById('favoriteSearchInput').focus();
    }, 100);
}

function closeFavoriteSearchModal() {
    const modal = document.getElementById('favoriteSearchModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    document.getElementById('favoriteSearchInput').value = '';
    document.getElementById('favoriteSearchResults').innerHTML = '';
}

// Aguardar DOM carregar
document.addEventListener('DOMContentLoaded', function() {
    // Fechar modal ao clicar fora
    document.addEventListener('click', (e) => {
        if (e.target.id === 'favoriteSearchModal') {
            closeFavoriteSearchModal();
        }
    });

    // Pesquisa de jogos com debounce
    let searchTimeout;
    const searchInput = document.getElementById('favoriteSearchInput');
    const searchResults = document.getElementById('favoriteSearchResults');

    if (!searchInput || !searchResults) {
        console.error('[FAVORITOS] Elementos de pesquisa não encontrados');
        return;
    }

    console.log('[FAVORITOS] Sistema de pesquisa inicializado');

    searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        searchResults.innerHTML = '';
        return;
    }
    
    searchResults.innerHTML = '<div class="search-loading">Buscando...</div>';
    
    searchTimeout = setTimeout(() => {
        fetch(`includes/search-autocomplete.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(games => {
                if (games.length === 0) {
                    searchResults.innerHTML = '<div class="search-no-results">Nenhum jogo encontrado</div>';
                    return;
                }
                
                let html = '<div class="favorite-search-grid">';
                games.forEach(game => {
                    html += `
                        <div class="favorite-search-item" onclick="addGameToFavorites(${game.id}, '${game.name.replace(/'/g, "\\'")}'  , '${game.cover}')">
                            <img src="${game.cover}" alt="${game.name}" class="favorite-search-cover">
                            <div class="favorite-search-info">
                                <div class="favorite-search-name">${game.name}</div>
                                <div class="favorite-search-year">${game.year || 'Ano desconhecido'}</div>
                            </div>
                            <button class="btn-add-to-favorites">
                                <i class="bi bi-star"></i> Adicionar
                            </button>
                        </div>
                    `;
                });
                html += '</div>';
                searchResults.innerHTML = html;
            })
            .catch(error => {
                console.error('[FAVORITOS] Erro na busca:', error);
                searchResults.innerHTML = '<div class="search-error">Erro ao buscar jogos</div>';
            });
    }, 300);
    });
});

function addGameToFavorites(gameId, gameName, gameCover) {
    console.log('[FAVORITOS] Adicionando jogo:', gameId, gameName);
    fetch('includes/add-to-favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `game_id=${gameId}&game_name=${encodeURIComponent(gameName)}&game_cover=${encodeURIComponent(gameCover)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeFavoriteSearchModal();
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao adicionar jogo aos favoritos');
        }
    })
    .catch(error => {
        alert('Erro ao adicionar jogo aos favoritos');
    });
}

function removeFavorite(gameId, gameName) {
    if (!confirm(`Remover "${gameName}" dos favoritos?`)) {
        return;
    }
    
    console.log('[FAVORITOS] Removendo jogo:', gameId);
    
    fetch('includes/remove-from-favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `game_id=${gameId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao remover jogo dos favoritos');
        }
    })
    .catch(error => {
        console.error('[FAVORITOS] Erro ao remover:', error);
        alert('Erro ao remover jogo dos favoritos');
    });
}
</script>

<style>
.section-header-with-action {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.btn-add-favorite-search {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-favorite-search:hover {
    background: #5568d3;
    transform: translateY(-1px);
}

.favorite-search-container {
    width: 100%;
}

.search-input-wrapper {
    position: relative;
    margin-bottom: 1.5rem;
}

.search-input-wrapper .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.5);
    font-size: 1.1rem;
}

.favorite-search-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: white;
    font-size: 1rem;
}

.favorite-search-input:focus {
    outline: none;
    border-color: #667eea;
    background: rgba(255, 255, 255, 0.08);
}

.favorite-search-results {
    max-height: 60vh;
    overflow-y: auto;
}

.favorite-search-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.favorite-search-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.favorite-search-item:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.2);
}

.favorite-search-cover {
    width: 50px;
    height: 67px;
    object-fit: cover;
    border-radius: 4px;
}

.favorite-search-info {
    flex: 1;
}

.favorite-search-name {
    font-weight: 600;
    color: white;
    margin-bottom: 0.25rem;
}

.favorite-search-year {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.6);
}

.btn-add-to-favorites {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.btn-add-to-favorites:hover {
    background: #5568d3;
}

.search-loading, .search-no-results, .search-error {
    text-align: center;
    padding: 2rem;
    color: rgba(255, 255, 255, 0.6);
}

.favorite-game-card.empty {
    cursor: pointer;
}

.favorite-game-card.empty:hover {
    background: rgba(255, 255, 255, 0.1);
}

.favorite-game-card {
    position: relative;
}

.favorite-game-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.favorite-game-card:hover .favorite-game-overlay {
    opacity: 1;
}

.btn-remove-favorite {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ef4444;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1.2rem;
}

.btn-remove-favorite:hover {
    background: #dc2626;
    transform: scale(1.1);
}
</style>

<?php include 'includes/footer.php'; ?>

<!-- Modal: Editar Status do Jogo (perfil) -->
<div id="editGameModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2 class="modal-title">Editar Jogo</h2>
            <button class="modal-close" aria-label="Fechar" onclick="closeEditGameModal()">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editGameForm">
                <input type="hidden" id="editGameId" name="game_id">
                
                <div class="form-group">
                    <label class="form-label">Nome do Jogo</label>
                    <p id="editGameName" class="game-name-display"></p>
                </div>
                
                <div class="form-group">
                    <label for="editGameStatus" class="form-label">Status</label>
                    <select id="editGameStatus" name="status" class="form-input" required>
                        <option value="playing">🎮 Jogando</option>
                        <option value="completed">✅ Jogado</option>
                        <option value="want_to_play">⭐ Lista de Desejos</option>
                        <option value="dropped">❌ Abandonado</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditGameModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEditGameId = null;

function editGame(gameId) {
    currentEditGameId = gameId;
    fetch(`includes/get-game-info.php?game_id=${gameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editGameId').value = gameId;
                document.getElementById('editGameName').textContent = data.game.name;
                document.getElementById('editGameStatus').value = data.game.status;
                document.getElementById('editGameModal').classList.add('show');
                document.body.classList.add('modal-open');
            } else {
                alert('Erro ao carregar informações do jogo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar informações do jogo');
        });
}

function closeEditGameModal() {
    document.getElementById('editGameModal').classList.remove('show');
    document.body.classList.remove('modal-open');
}

document.getElementById('editGameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('includes/update-game-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditGameModal();
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao atualizar jogo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar jogo');
    });
});

function removeGame(gameId) {
    if (!confirm('Tem certeza que deseja remover este jogo da sua lista?')) {
        return;
    }
    fetch('includes/remove-game.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `game_id=${gameId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao remover jogo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao remover jogo');
    });
}

// Fechar modal ao clicar fora
document.addEventListener('click', (e) => {
    if (e.target.id === 'editGameModal') {
        closeEditGameModal();
    }
});
</script>
<script>
document.addEventListener('click', (e) => {
    if (e.target.id === 'editGameModal') {
        closeEditGameModal();
    }
});
</script>
