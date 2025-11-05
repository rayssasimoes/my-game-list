<?php
$pageTitle = 'MyGameList - Lista de Jogos';

// Buscar jogos populares ou resultados de busca
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $games = searchGames($searchTerm);
    $sectionTitle = "Resultados para: " . htmlspecialchars($searchTerm);
} else {
    $games = getPopularGames(12);
    $sectionTitle = "Populares agora";
}

include 'includes/header.php';
?>

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
        <div class="welcome-message mb-5 text-center">
            <h1 class="display-5 fw-bold text-white mb-3">Bem-vindo(a) ao MyGameList!</h1>
            <p class="lead text-white-50">Descubra, organize e compartilhe sua coleção de jogos favoritos</p>
        </div>
    <?php endif; ?>

    <!-- Seção: Populares no Momento -->
    <section class="popular-games-section mb-5">
        <div class="section-header">
            <h2 class="section-title"><?php echo $sectionTitle; ?></h2>
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
                        <div class="game-card-overlay">
                            <h3 class="game-card-title"><?php echo htmlspecialchars($game['name']); ?></h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
