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
    <!-- Hero Section de Boas-vindas -->
    <?php if (isLoggedIn()): ?>
        <?php 
            $user = getUser(); 
            // Pegar apenas o primeiro nome
            $firstName = explode(' ', $user['name'])[0];
        ?>
        <div class="hero-section">
            <h1 class="hero-title">
                Bem-vindo (a) de volta, <span class="hero-name"><?php echo htmlspecialchars($firstName); ?></span>. Organize, descubra e jogue!
            </h1>
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

    <!-- Seção: Três Colunas -->
    <section class="discovery-section">
        <div class="discovery-grid">
            <!-- Coluna 1: Em Breve -->
            <div class="discovery-column">
                <div class="discovery-header">
                    <h3 class="discovery-title">Em Breve</h3>
                    <a href="#" class="discovery-see-more">›</a>
                </div>
                <div class="discovery-list">
                    <?php 
                    $upcomingGames = getUpcomingGames(5);
                    if (empty($upcomingGames)): ?>
                        <p class="text-white-50 small">Nenhum jogo encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingGames as $game): ?>
                            <div class="discovery-item">
                                <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                     class="discovery-cover">
                                <div class="discovery-info">
                                    <div class="discovery-game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                                    <div class="discovery-meta"><?php echo htmlspecialchars($game['release_date']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna 2: Recentemente Antecipados -->
            <div class="discovery-column">
                <div class="discovery-header">
                    <h3 class="discovery-title">Recentemente Antecipados</h3>
                    <a href="#" class="discovery-see-more">›</a>
                </div>
                <div class="discovery-list">
                    <?php 
                    $hypedGames = getHypedGames(5);
                    if (empty($hypedGames)): ?>
                        <p class="text-white-50 small">Nenhum jogo encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($hypedGames as $game): ?>
                            <div class="discovery-item">
                                <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                     class="discovery-cover">
                                <div class="discovery-info">
                                    <div class="discovery-game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                                    <div class="discovery-meta"><?php echo htmlspecialchars($game['release_date']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna 3: Sucessos Inesperados -->
            <div class="discovery-column">
                <div class="discovery-header">
                    <h3 class="discovery-title">Sucessos Inesperados</h3>
                    <a href="#" class="discovery-see-more">›</a>
                </div>
                <div class="discovery-list">
                    <?php 
                    $hiddenGems = getHiddenGems(5);
                    if (empty($hiddenGems)): ?>
                        <p class="text-white-50 small">Nenhum jogo encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($hiddenGems as $game): ?>
                            <div class="discovery-item">
                                <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                     class="discovery-cover">
                                <div class="discovery-info">
                                    <div class="discovery-game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                                    <div class="discovery-meta">Méd <?php echo $game['rating']; ?>★</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>