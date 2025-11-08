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
    ORDER BY gu.rating DESC, gu.updated_at DESC
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
    ORDER BY gu.rating DESC, gu.updated_at DESC
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
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="profile-header-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($firstName); ?></h1>
                    <button class="btn-edit-profile">
                        <i class="bi bi-pencil"></i> Editar Perfil
                    </button>
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
            <button class="profile-tab" data-tab="activity">Atividade</button>
        </nav>

        <!-- Conteúdo das Abas -->
        
        <!-- Aba: Perfil -->
        <div class="tab-content active" data-tab-content="overview">
            <!-- Seção: 5 Jogos Favoritos -->
            <section class="favorite-games-section">
                <h2 class="section-title">JOGOS FAVORITOS</h2>
                <div class="favorite-games-grid">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <?php if (isset($favoriteGames[$i])): ?>
                            <?php $game = $favoriteGames[$i]; ?>
                            <div class="favorite-game-card">
                                <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>"
                                     class="favorite-game-cover">
                            </div>
                        <?php else: ?>
                            <div class="favorite-game-card empty">
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
                <h2 class="section-title">Jogos</h2>
                
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
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Favorito -->
                    <div class="filter-content" data-filter-content="favorites">
                        <?php if (empty($allFavorites)): ?>
                            <p class="empty-message">Nenhum jogo favorito ainda. Avalie jogos com 9+ estrelas para adicioná-los aqui!</p>
                        <?php else: ?>
                            <div class="games-list-grid">
                                <?php foreach ($allFavorites as $game): ?>
                                    <div class="game-list-card">
                                        <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                                             alt="<?php echo htmlspecialchars($game['name']); ?>"
                                             class="game-list-cover">
                                        <?php if ($game['rating']): ?>
                                            <div class="game-rating-badge">
                                                <i class="bi bi-star-fill"></i> <?php echo $game['rating']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </section>
        </div>

        <!-- Aba: Atividade -->
        <div class="tab-content" data-tab-content="activity">
            <p class="empty-message">Em breve: Linha do tempo de atividades</p>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
