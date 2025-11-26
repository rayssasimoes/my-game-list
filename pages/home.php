<?php
$pageTitle = 'MyGameList';

// Buscar jogos populares
$games = getPopularGames(20);
$sectionTitle = "Populares agora";

// Se logado, buscar jogos que já estão na lista do usuário
$userGames = [];
if (isLoggedIn()) {
    $user = getUser();
    $db = getDB();
    // Buscar jogos com JOIN para pegar o igdb_id
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
            
            // Definir saudação baseada nos pronomes
            $greeting = 'Boas-vindas'; // Padrão
            if (isset($user['pronouns'])) {
                if ($user['pronouns'] === 'female') {
                    $greeting = 'Bem-vinda';
                } elseif ($user['pronouns'] === 'male') {
                    $greeting = 'Bem-vindo';
                } elseif ($user['pronouns'] === 'neutral') {
                    $greeting = 'Boas-vindas';
                }
            }
        ?>
        <div class="hero-section">
            <h1 class="hero-title">
                <?php echo $greeting; ?> de volta, <span class="hero-name"><?php echo htmlspecialchars($firstName); ?></span>. Organize, descubra e jogue!
            </h1>
        </div>
    <?php else: ?>
        <div class="welcome-message-compact">
            <h1 class="welcome-title">Boas-vindas ao <span class="welcome-name-app">MyGameList</span>! Descubra, organize e compartilhe sua coleção de jogos favoritos</h1>
        </div>
    <?php endif; ?>

    <!-- Seção: Populares no Momento -->
    <section class="popular-games-section mb-5">
        <a href="index.php?page=populares" class="section-header-link">
            <div class="section-header-with-arrow">
                <h2 class="section-title"><?php echo $sectionTitle; ?></h2>
                <span class="scroll-indicator">›</span>
            </div>
        </a>
        
        <?php if (empty($games)): ?>
            <p class="text-white-50">Nenhum jogo encontrado.</p>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="game-card-link">
                            <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                 alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                 class="game-card-image">
                        </a>
                        
                        <!-- Overlay de hover (ações rápidas + nome) -->
                        <div class="game-card-hover-content">
                            <!-- Título do jogo -->
                            <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="game-card-hover-title-link">
                                <h3 class="game-card-hover-title"><?php echo htmlspecialchars($game['name']); ?></h3>
                            </a>
                            
                            <!-- Ações rápidas embaixo (apenas para logados) -->
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
                                    <!-- botão 'mais' removido conforme solicitado -->
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
                <a href="index.php?page=populares&tipo=em_breve" class="section-header-link">
                    <div class="section-header-with-arrow">
                        <h3 class="discovery-title">Em Breve</h3>
                        <span class="scroll-indicator">›</span>
                    </div>
                </a>
                <div class="discovery-list">
                    <?php 
                    $upcomingGames = getUpcomingGames(5);
                    if (empty($upcomingGames)): ?>
                        <p class="text-white-50 small">Nenhum jogo encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($upcomingGames as $game): ?>
                            <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="discovery-item">
                                <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                     class="discovery-cover">
                                <div class="discovery-info">
                                    <div class="discovery-game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                                    <div class="discovery-meta"><?php echo htmlspecialchars($game['release_date']); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna 2: Recentemente Antecipados -->
            <div class="discovery-column">
                <a href="index.php?page=populares&tipo=hyped" class="section-header-link">
                    <div class="section-header-with-arrow">
                        <h3 class="discovery-title">Recentemente Antecipados</h3>
                        <span class="scroll-indicator">›</span>
                    </div>
                </a>
                <div class="discovery-list">
                    <?php 
                    $hypedGames = getHypedGames(5);
                    if (empty($hypedGames)): ?>
                        <p class="text-white-50 small">Nenhum jogo encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($hypedGames as $game): ?>
                            <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="discovery-item">
                                <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                     class="discovery-cover">
                                <div class="discovery-info">
                                    <div class="discovery-game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                                    <div class="discovery-meta"><?php echo htmlspecialchars($game['release_date']); ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna 3: Sucessos Inesperados -->
            <div class="discovery-column">
                <a href="index.php?page=populares&tipo=hidden_gems" class="section-header-link">
                    <div class="section-header-with-arrow">
                        <h3 class="discovery-title">Sucessos Inesperados</h3>
                        <span class="scroll-indicator">›</span>
                    </div>
                </a>
                <div class="discovery-list">
                    <?php 
                    $hiddenGems = getHiddenGems(5);
                    if (empty($hiddenGems)): ?>
                        <p class="text-white-50 small">Nenhum jogo encontrado.</p>
                    <?php else: ?>
                        <?php foreach ($hiddenGems as $game): ?>
                            <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="discovery-item">
                                <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($game['name']); ?>" 
                                     class="discovery-cover">
                                <div class="discovery-info">
                                    <div class="discovery-game-name"><?php echo htmlspecialchars($game['name']); ?></div>
                                    <div class="discovery-meta">Méd <?php echo $game['rating']; ?>★</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>