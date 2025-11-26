<?php
$pageTitle = 'Buscar - MyGameList';

// Pegar o termo de busca
$searchTerm = $_GET['q'] ?? '';
$searchTerm = trim($searchTerm);

// Se não houver termo de busca, redirecionar para home
if (empty($searchTerm)) {
    header('Location: index.php');
    exit;
}

// Always perform both searches: IGDB games and local users
 $db = getDB();

 // Resultado genérico
 $games = [];
 $users = [];
 $userGames = [];

 // Buscar jogos na API (IGDB)
 $games = searchGames($searchTerm, 20);

 // Busca local por usuários (username ou name)
 $q = '%' . $searchTerm . '%';
 // Usar placeholders nomeados distintos para evitar problemas com drivers PDO que não
 // aceitam o mesmo nome repetido. Passamos ambos os parâmetros na ordem correta.
 $stmt = $db->prepare("SELECT id, username, name, avatar_path FROM users WHERE username LIKE :q1 OR name LIKE :q2 LIMIT 50");
 $stmt->execute([':q1' => $q, ':q2' => $q]);
 $users = $stmt->fetchAll();

 // Mapear jogos do usuário logado para exibir estados na lista de resultados
 if (isLoggedIn()) {
     $user = getUser();
     $stmt = $db->prepare("SELECT g.igdb_id, gu.status FROM game_user gu 
                           INNER JOIN games g ON gu.game_id = g.id 
                           WHERE gu.user_id = ? AND g.igdb_id IS NOT NULL");
     $stmt->execute([$user['id']]);
     foreach ($stmt->fetchAll() as $row) {
         $userGames[$row['igdb_id']] = $row['status'];
     }
 }

 $gamesCount = count($games);
 $usersCount = count($users);
 $totalResults = $gamesCount + $usersCount;

include 'includes/header.php';
?>

<!-- Página de Resultados de Busca -->
<div class="container py-5">
    <!-- Hero Section - Título da busca (Busca Universal) -->
    <div class="search-hero">
        <h1 class="search-hero-title">Resultados para: <span class="search-term"><?php echo htmlspecialchars($searchTerm); ?></span></h1>
        <p class="search-results-count"><?php echo $totalResults; ?> resultado<?php echo $totalResults === 1 ? '' : 's'; ?> • <?php echo $gamesCount; ?> jogo<?php echo $gamesCount === 1 ? '' : 's'; ?> • <?php echo $usersCount; ?> usuário<?php echo $usersCount === 1 ? '' : 's'; ?></p>

        <!-- Tabs: Jogos / Comunidade -->
        <div class="search-tabs" role="tablist" aria-label="Resultados">
            <button id="tab-btn-games" class="tab-btn <?php echo ($gamesCount > 0) ? 'active' : (($gamesCount === 0 && $usersCount > 0) ? '' : 'active'); ?>" data-target="tab-games" role="tab" aria-selected="<?php echo $gamesCount > 0 ? 'true' : 'false'; ?>">Jogos (<?php echo $gamesCount; ?>)</button>
            <button id="tab-btn-users" class="tab-btn <?php echo ($gamesCount === 0 && $usersCount > 0) ? 'active' : ''; ?>" data-target="tab-users" role="tab" aria-selected="<?php echo ($gamesCount === 0 && $usersCount > 0) ? 'true' : 'false'; ?>">Comunidade (<?php echo $usersCount; ?>)</button>
        </div>
    </div>



    <!-- Conteúdo dos resultados (Busca Universal) -->
    <div class="search-content">
        <?php if ($totalResults === 0): ?>
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h3>Nenhum resultado encontrado</h3>
                <p>Tente usar palavras-chave diferentes ou verificar a ortografia.</p>
            </div>
        <?php else: ?>
            <!-- Seção Jogos -->
            <section id="tab-games" class="search-section games-section" aria-hidden="false">
                <h2>Jogos</h2>
                <?php if ($gamesCount === 0): ?>
                    <div class="section-empty muted">Nenhum jogo encontrado</div>
                <?php else: ?>
                    <div class="search-results-list">
                        <?php foreach ($games as $game): ?>
                            <div class="search-result-card">
                                <a href="index.php?page=game&id=<?php echo (isset($game['igdb_id']) && $game['igdb_id']) ? $game['igdb_id'] : $game['id']; ?>" class="search-card-info">
                                    <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                         alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                         class="search-card-cover">
                                    <div class="search-card-details">
                                        <h3 class="search-card-title">
                                            <?php echo htmlspecialchars($game['name']); ?>
                                            <?php if ($game['year']): ?>
                                                <span class="search-card-year">(<?php echo $game['year']; ?>)</span>
                                            <?php endif; ?>
                                        </h3>
                                        <div class="search-card-platforms">
                                            <i class="bi bi-controller"></i>
                                            <span>
                                                <?php 
                                                if (!empty($game['platforms'])) {
                                                    echo htmlspecialchars(implode(', ', array_slice($game['platforms'], 0, 3)));
                                                    if (count($game['platforms']) > 3) {
                                                        echo ' +' . (count($game['platforms']) - 3);
                                                    }
                                                } else {
                                                    echo 'Plataforma não especificada';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Coluna Direita: Botões de ação -->
                                <?php if (isLoggedIn()): 
                                    $gameStatus = $userGames[$game['id']] ?? null; ?>
                                    <div class="search-card-actions">
                                        <div class="search-actions-row">
                                            <button class="search-action-btn <?= $gameStatus === 'completed' ? 'active' : '' ?>" 
                                                    data-action="completed" 
                                                    data-game-id="<?php echo $game['id']; ?>"
                                                    data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                                    data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                                                <i class="bi bi-check-circle"></i>
                                                <span>Jogado</span>
                                            </button>
                                            <button class="search-action-btn <?= $gameStatus === 'playing' ? 'active' : '' ?>" 
                                                    data-action="playing" 
                                                    data-game-id="<?php echo $game['id']; ?>"
                                                    data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                                    data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                                                <i class="bi bi-controller"></i>
                                                <span>Jogando</span>
                                            </button>
                                            <button class="search-action-btn <?= $gameStatus === 'dropped' ? 'active' : '' ?>" 
                                                    data-action="dropped" 
                                                    data-game-id="<?php echo $game['id']; ?>"
                                                    data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                                    data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                                                <i class="bi bi-x-circle"></i>
                                                <span>Abandonado</span>
                                            </button>
                                            <button class="search-action-btn <?= $gameStatus === 'want_to_play' ? 'active' : '' ?>" 
                                                    data-action="want_to_play" 
                                                    data-game-id="<?php echo $game['id']; ?>"
                                                    data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                                    data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                                                <i class="bi bi-bookmark-heart"></i>
                                                <span>Lista de Desejos</span>
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="search-card-actions">
                                        <div class="search-login-prompt">
                                            <p class="login-prompt-main">
                                                <button class="btn-register-small" onclick="openModal('registerModal')">Criar Conta</button>
                                                para adicionar jogos à sua lista
                                            </p>
                                            <p class="login-prompt-text">
                                                ou <button class="btn-login-link" onclick="openModal('authModal','login')">iniciar sessão</button> se já tem uma conta.
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Seção Comunidade -->
            <?php if ($usersCount > 0): ?>
            <section id="tab-users" class="search-section users-section" aria-hidden="true">
                <h2>Comunidade</h2>
                <div class="user-results-list">
                    <?php foreach ($users as $result_member): ?>
                        <a href="index.php?page=profile&id=<?php echo $result_member['id']; ?>" class="user-result-card">
                            <div class="search-result-avatar">
                                <?php // Usar avatar do usuário do resultado quando disponível; caso contrário usar imagem padrão estática ?>
                                <img src="<?php echo htmlspecialchars(!empty($result_member['avatar_path']) ? $result_member['avatar_path'] : 'assets/images/default_avatar.png'); ?>" alt="<?php echo htmlspecialchars($result_member['username']); ?>">
                            </div>
                            <div class="user-details">
                                <div class="user-username">@<?php echo htmlspecialchars($result_member['username']); ?></div>
                                <div class="user-name"><?php echo htmlspecialchars($result_member['name']); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionButtons = document.querySelectorAll('.search-action-btn');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const gameName = this.dataset.gameName;
            const gameCover = this.dataset.gameCover;
            const status = this.dataset.action;
            
            // Verificar se o botão já está ativo (remover do jogo da lista)
            const isActive = this.classList.contains('active');
            
            // Desabilitar botão temporariamente
            const btnElement = this;
            btnElement.disabled = true;
            btnElement.style.opacity = '0.6';
            
            if (isActive) {
                // Remover jogo da lista
                fetch('includes/remove-from-list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `game_id=${gameId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remover classe active
                        btnElement.classList.remove('active');
                        
                        // Feedback visual temporário
                        const originalHTML = btnElement.innerHTML;
                        btnElement.innerHTML = '<i class="bi bi-check"></i> <span>Removido!</span>';
                        
                        setTimeout(() => {
                            btnElement.innerHTML = originalHTML;
                            btnElement.style.opacity = '1';
                            btnElement.disabled = false;
                        }, 1500);
                        
                        showSearchNotification('Jogo removido da lista!', 'success');
                    } else {
                        btnElement.style.opacity = '1';
                        btnElement.disabled = false;
                        showSearchNotification(data.message || 'Erro ao remover jogo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btnElement.style.opacity = '1';
                    btnElement.disabled = false;
                    showSearchNotification('Erro ao remover jogo', 'error');
                });
            } else {
                // Adicionar jogo à lista
                fetch('includes/add-to-list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `game_id=${gameId}&status=${status}&game_name=${encodeURIComponent(gameName)}&game_cover=${encodeURIComponent(gameCover)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remover classe active de todos os botões do mesmo card
                        const card = btnElement.closest('.search-card-actions');
                        const allBtns = card.querySelectorAll('.search-action-btn');
                        allBtns.forEach(btn => btn.classList.remove('active'));
                        
                        // Marcar apenas o botão clicado como ativo
                        btnElement.classList.add('active');
                        
                        // Feedback visual temporário
                        const originalHTML = btnElement.innerHTML;
                        btnElement.innerHTML = '<i class="bi bi-check"></i> <span>Adicionado!</span>';
                        
                        setTimeout(() => {
                            btnElement.innerHTML = originalHTML;
                            btnElement.style.opacity = '1';
                            btnElement.disabled = false;
                        }, 1500);
                        
                        // Mostrar notificação de sucesso
                        const messages = {
                            'completed': 'Marcado como Jogado!',
                            'playing': 'Adicionado em Jogando!',
                            'want_to_play': 'Adicionado à Lista de Desejos!',
                            'dropped': 'Marcado como Abandonado!'
                        };
                        showSearchNotification(messages[status] || 'Jogo adicionado à sua lista!', 'success');
                    } else {
                        // Erro ao adicionar
                        btnElement.style.opacity = '1';
                        btnElement.disabled = false;
                        showSearchNotification(data.message || 'Erro ao adicionar jogo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btnElement.style.opacity = '1';
                    btnElement.disabled = false;
                    showSearchNotification('Erro ao adicionar jogo', 'error');
                });
            }
        });
    });
    
    // Função para mostrar notificação
    function showSearchNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `search-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${type === 'success' ? '#22c55e' : '#ef4444'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            font-weight: 600;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Adicionar animações CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .search-action-btn.active {
            background: rgba(102, 126, 234, 0.2);
            border-color: #667eea;
            color: #667eea;
        }
    `;
    document.head.appendChild(style);
});
</script>

<script>
// Tabs switcher for search results
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.search-tabs .tab-btn');
    const tabGames = document.getElementById('tab-games');
    const tabUsers = document.getElementById('tab-users');

    function activateTab(targetId) {
        tabButtons.forEach(btn => {
            const t = btn.getAttribute('data-target');
            if (t === targetId) {
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            }
        });

        if (tabGames) tabGames.style.display = (targetId === 'tab-games') ? '' : 'none';
        if (tabUsers) tabUsers.style.display = (targetId === 'tab-users') ? '' : 'none';
    }

    // Initial state: if there are games, show games; else show users (if any)
    const defaultTab = (<?php echo ($gamesCount > 0) ? "'tab-games'" : (($usersCount > 0) ? "'tab-users'" : "'tab-games'") ; ?>);
    activateTab(defaultTab.replace(/'/g, ''));

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-target');
            activateTab(target);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>

