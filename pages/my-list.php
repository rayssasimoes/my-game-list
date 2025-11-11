<?php
requireLogin();

$pageTitle = 'Minha Lista - MyGameList';
$user = getUser();

// Pegar o status da URL (se fornecido)
$activeTab = $_GET['tab'] ?? 'completed';

// Validar tab
$validTabs = ['completed', 'playing', 'dropped', 'want_to_play'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'completed';
}

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

// Mapeamento de títulos das tabs
$tabTitles = [
    'completed' => 'Jogado',
    'playing' => 'Jogando',
    'dropped' => 'Abandonado',
    'want_to_play' => 'Lista de Desejos'
];

include 'includes/header.php';
?>

<div class="mylist-container">
    <!-- Hero Section -->
    <div class="mylist-hero">
        <h1 class="mylist-title">Minha Lista</h1>
        <p class="mylist-subtitle"><?php echo $stats['total']; ?> jogos na coleção</p>
    </div>

    <?php if (empty($myGames)): ?>
        <!-- Estado vazio -->
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h3>Sua lista está vazia</h3>
            <p>Comece adicionando seus jogos favoritos!</p>
            <a href="index.php" class="btn-browse">
                <i class="bi bi-search"></i> Explorar Jogos
            </a>
        </div>
    <?php else: ?>
        <!-- Tabs de Navegação -->
        <div class="mylist-tabs">
            <button class="mylist-tab <?php echo $activeTab === 'completed' ? 'active' : ''; ?>" data-tab="completed">
                <i class="bi bi-check-circle"></i>
                <span>Jogado</span>
                <span class="tab-count"><?php echo $stats['completed']; ?></span>
            </button>
            <button class="mylist-tab <?php echo $activeTab === 'playing' ? 'active' : ''; ?>" data-tab="playing">
                <i class="bi bi-controller"></i>
                <span>Jogando</span>
                <span class="tab-count"><?php echo $stats['playing']; ?></span>
            </button>
            <button class="mylist-tab <?php echo $activeTab === 'dropped' ? 'active' : ''; ?>" data-tab="dropped">
                <i class="bi bi-x-circle"></i>
                <span>Abandonado</span>
                <span class="tab-count"><?php echo $stats['dropped']; ?></span>
            </button>
            <button class="mylist-tab <?php echo $activeTab === 'want_to_play' ? 'active' : ''; ?>" data-tab="want_to_play">
                <i class="bi bi-bookmark-heart"></i>
                <span>Lista de Desejos</span>
                <span class="tab-count"><?php echo $stats['want_to_play']; ?></span>
            </button>
        </div>

        <!-- Conteúdo das Tabs -->
        <?php foreach (['completed', 'playing', 'dropped', 'want_to_play'] as $status): ?>
            <div class="mylist-tab-content <?php echo $activeTab === $status ? 'active' : ''; ?>" data-tab="<?php echo $status; ?>">
                <?php if (empty($gamesByStatus[$status])): ?>
                    <div class="empty-tab-state">
                        <i class="bi bi-inbox"></i>
                        <p>Nenhum jogo em "<?php echo $tabTitles[$status]; ?>"</p>
                    </div>
                <?php else: ?>
                    <div class="mylist-games-grid">
                        <?php foreach ($gamesByStatus[$status] as $game): ?>
                            <?php include 'includes/game-card-mylist.php'; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginação (placeholder) -->
                    <div class="mylist-pagination">
                        <a href="#" class="pagination-link">‹ Prev</a>
                        <span class="pagination-current">1</span>
                        <a href="#" class="pagination-link">Next ›</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
