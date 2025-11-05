<?php
$pageTitle = 'MyGameList';

// Buscar jogos populares ou resultados de busca
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $games = searchGames($searchTerm);
    $sectionTitle = "Resultados para: " . htmlspecialchars($searchTerm);
} else {
    $games = getPopularGames(20);
    $sectionTitle = "Populares agora";
}

include 'includes/header.php';
?>

<!-- Conteúdo da página para usuários logados -->
<div class="container py-5">
    <!-- Frase de Boas-vindas -->
    <?php if (isLoggedIn()): ?>
        <?php $user = getUser(); ?>
        <div class="welcome-section">
            <div class="welcome-heading">
                <span class="fw-bold">Olá, <?php echo htmlspecialchars($user['name']); ?>!</span>
                <br>
                O que vamos jogar hoje?
            </div>
        </div>
    <?php else: ?>
        <div class="welcome-message-compact">
            <h1 class="welcome-title">Bem-vindo(a) ao MyGameList!</h1>
            <p class="welcome-subtitle">Descubra, organize e compartilhe sua coleção de jogos favoritos</p>
        </div>
    <?php endif; ?>

    <!-- Seção: Populares no Momento -->
    <section class="popular-games-section mb-5">
        <div class="section-header-with-arrow">
            <h2 class="section-title"><?php echo $sectionTitle; ?></h2>
            <span class="scroll-indicator">›</span>
        </div>
        
        <?php if (empty($games)): ?>
            <p class="text-white-50">Nenhum jogo encontrado.</p>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                             alt="<?php echo htmlspecialchars($game['name']); ?>" 
                             class="game-card-image">
                        
                        <!-- Overlay de hover (nome no centro + botões embaixo) -->
                        <div class="game-card-hover-content">
                            <h3 class="game-card-hover-title"><?php echo htmlspecialchars($game['name']); ?></h3>

                            <!-- Para usuários logados -->
                            <?php if (isLoggedIn()): ?>
                                <div class="game-card-actions">
                                    <button class="action-btn" title="Jogado">
                                        <span class="action-btn-icon">🎮</span>
                                    </button>
                                    <button class="action-btn" title="Backlog">
                                        <span class="action-btn-icon">📋</span>
                                    </button>
                                    <button class="action-btn" title="Mais opções">
                                        <span class="action-btn-icon">⋮</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
