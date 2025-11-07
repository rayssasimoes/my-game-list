<?php
requireLogin();

$pageTitle = 'Minha Lista - MyGameList';
$user = getUser();

// Buscar jogos da lista do usuário
$db = getDB();
$stmt = $db->prepare("
    SELECT g.*, gu.status, gu.rating, gu.notes, gu.added_at, gu.updated_at
    FROM games g
    INNER JOIN game_user gu ON g.id = gu.game_id
    WHERE gu.user_id = ?
    ORDER BY gu.updated_at DESC
");
$stmt->execute([$user['id']]);
$myGames = $stmt->fetchAll();

// Calcular estatísticas
$stats = [
    'total' => count($myGames),
    'playing' => 0,
    'completed' => 0,
    'want_to_play' => 0,
    'dropped' => 0
];

$totalRating = 0;
$ratedGames = 0;

foreach ($myGames as $game) {
    if (isset($stats[$game['status']])) {
        $stats[$game['status']]++;
    }
    if ($game['rating']) {
        $totalRating += $game['rating'];
        $ratedGames++;
    }
}

$averageRating = $ratedGames > 0 ? round($totalRating / $ratedGames, 1) : 0;

// Organizar jogos por status
$gamesByStatus = [
    'playing' => [],
    'completed' => [],
    'want_to_play' => [],
    'dropped' => []
];

foreach ($myGames as $game) {
    $gamesByStatus[$game['status']][] = $game;
}

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">
            Minha Lista de <span class="hero-name">Jogos</span>
        </h1>
        <p class="total-games-text"><?php echo $stats['total']; ?> jogos na coleção</p>
    </div>

    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-completed">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['completed']; ?></div>
                <div class="stat-label">Completados</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-playing">
                <i class="bi bi-controller"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['playing']; ?></div>
                <div class="stat-label">Jogando</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['dropped']; ?></div>
                <div class="stat-label">Abandonado</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon-wishlist">
                <i class="bi bi-bookmark-heart"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['want_to_play']; ?></div>
                <div class="stat-label">Lista de Desejos</div>
            </div>
        </div>
    </div>

    <?php if (empty($myGames)): ?>
        <!-- Estado vazio -->
        <div class="empty-state">
            <i class="bi bi-inbox empty-state-icon"></i>
            <h3 class="empty-state-title">Sua lista está vazia</h3>
            <p class="empty-state-text">Comece adicionando seus jogos favoritos para organizar sua coleção!</p>
            <a href="index.php" class="btn-primary-custom">
                <i class="bi bi-search"></i> Explorar Jogos
            </a>
        </div>
    <?php else: ?>
        <!-- Barra de Filtros -->
        <div class="filter-bar">
            <button class="filter-link" id="filterToggle">
                Filtrar <i class="bi bi-chevron-down"></i>
            </button>
        </div>

        <!-- Tabs de Status -->
        <div class="list-tabs" id="filterTabs">
            <button class="list-tab active" data-status="all">
                <i class="bi bi-grid-3x3-gap"></i>
                Todos <span class="tab-count"><?php echo $stats['total']; ?></span>
            </button>
            <button class="list-tab" data-status="playing">
                <i class="bi bi-controller"></i>
                Jogando <span class="tab-count"><?php echo $stats['playing']; ?></span>
            </button>
            <button class="list-tab" data-status="completed">
                <i class="bi bi-check-circle"></i>
                Completados <span class="tab-count"><?php echo $stats['completed']; ?></span>
            </button>
            <button class="list-tab" data-status="want_to_play">
                <i class="bi bi-bookmark"></i>
                Quero Jogar <span class="tab-count"><?php echo $stats['want_to_play']; ?></span>
            </button>
            <button class="list-tab" data-status="dropped">
                <i class="bi bi-x-circle"></i>
                Abandonados <span class="tab-count"><?php echo $stats['dropped']; ?></span>
            </button>
        </div>

        <!-- Conteúdo das Tabs -->
        <div class="tab-content active" data-status="all">
            <div class="games-grid-mylist">
                <?php foreach ($myGames as $game): ?>
                    <?php include 'includes/game-card-mylist.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php foreach (['playing', 'completed', 'want_to_play', 'dropped'] as $status): ?>
            <div class="tab-content" data-status="<?php echo $status; ?>">
                <?php if (empty($gamesByStatus[$status])): ?>
                    <div class="empty-tab-state">
                        <i class="bi bi-inbox"></i>
                        <p>Nenhum jogo nesta categoria</p>
                    </div>
                <?php else: ?>
                    <div class="games-grid-mylist">
                        <?php foreach ($gamesByStatus[$status] as $game): ?>
                            <?php include 'includes/game-card-mylist.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
