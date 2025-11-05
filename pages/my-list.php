<?php
requireLogin();

$pageTitle = 'Minha Lista - MyGameList';
$user = getUser();

// Buscar jogos da lista do usuário
$db = getDB();
$stmt = $db->prepare("
    SELECT g.*, gu.status, gu.rating, gu.added_at 
    FROM games g
    INNER JOIN game_user gu ON g.id = gu.game_id
    WHERE gu.user_id = ?
    ORDER BY gu.added_at DESC
");
$stmt->execute([$user['id']]);
$myGames = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Header da página -->
    <div class="welcome-section">
        <div class="welcome-heading">
            <span class="fw-bold">Minha Lista de Jogos</span>
            <br>
            <?php echo count($myGames); ?> jogo<?php echo count($myGames) != 1 ? 's' : ''; ?> na sua lista
        </div>
    </div>

    <!-- Lista de Jogos -->
    <section class="my-games-section mb-5">
        <?php if (empty($myGames)): ?>
            <div class="text-center py-5">
                <p class="text-white-50 mb-4">Você ainda não adicionou nenhum jogo à sua lista.</p>
                <a href="index.php" class="btn btn-primary">Explorar Jogos</a>
            </div>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($myGames as $game): ?>
                    <div class="game-card">
                        <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                             alt="<?php echo htmlspecialchars($game['name']); ?>" 
                             class="game-card-image">
                        <div class="game-card-overlay">
                            <h3 class="game-card-title"><?php echo htmlspecialchars($game['name']); ?></h3>
                            <?php if ($game['status']): ?>
                                <p class="game-card-status">
                                    Status: <?php echo htmlspecialchars($game['status']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($game['rating']): ?>
                                <p class="game-card-rating">
                                    ⭐ <?php echo htmlspecialchars($game['rating']); ?>/10
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
