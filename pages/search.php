<?php
$pageTitle = 'Buscar Jogos - MyGameList';

// Pegar o termo de busca
$searchTerm = $_GET['q'] ?? '';
$searchTerm = trim($searchTerm);

// Se não houver termo de busca, redirecionar para home
if (empty($searchTerm)) {
    header('Location: index.php');
    exit;
}

// Buscar jogos na API
$games = searchGames($searchTerm, 20);
$totalResults = count($games);

include 'includes/header.php';
?>

<!-- Página de Resultados de Busca -->
<div class="container py-5">
    <!-- Hero Section - Título da busca -->
    <div class="search-hero">
        <h1 class="search-hero-title">Resultados para: <span class="search-term"><?php echo htmlspecialchars($searchTerm); ?></span></h1>
        <p class="search-results-count"><?php echo $totalResults; ?> <?php echo $totalResults === 1 ? 'jogo encontrado' : 'jogos encontrados'; ?></p>
    </div>

    <!-- Abas de navegação -->
    <div class="search-tabs">
        <button class="search-tab active" data-tab="games">
            <i class="bi bi-controller"></i> Jogos
        </button>
        <button class="search-tab" data-tab="users" disabled>
            <i class="bi bi-people"></i> Usuários
        </button>
        <button class="search-tab" data-tab="lists" disabled>
            <i class="bi bi-list-task"></i> Listas
        </button>
    </div>

    <!-- Conteúdo da aba Jogos -->
    <div class="search-content" id="games-tab">
        <?php if (empty($games)): ?>
            <!-- Mensagem quando não há resultados -->
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h3>Nenhum jogo encontrado</h3>
                <p>Tente usar palavras-chave diferentes ou verificar a ortografia.</p>
            </div>
        <?php else: ?>
            <!-- Lista de resultados -->
            <div class="search-results-list">
                <?php foreach ($games as $game): ?>
                    <div class="search-result-card">
                        <!-- Coluna Esquerda: Informações do jogo -->
                        <a href="index.php?page=game&id=<?php echo $game['id']; ?>" class="search-card-info">
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
                        <?php if (isLoggedIn()): ?>
                            <div class="search-card-actions">
                                <div class="search-actions-row">
                                    <button class="search-action-btn" data-action="played" data-game-id="<?php echo $game['id']; ?>">
                                        <i class="bi bi-check-circle"></i>
                                        <span>Jogado</span>
                                    </button>
                                    <button class="search-action-btn" data-action="playing" data-game-id="<?php echo $game['id']; ?>">
                                        <i class="bi bi-play-circle"></i>
                                        <span>Jogando</span>
                                    </button>
                                    <button class="search-action-btn" data-action="backlog" data-game-id="<?php echo $game['id']; ?>">
                                        <i class="bi bi-list-ul"></i>
                                        <span>Backlog</span>
                                    </button>
                                    <button class="search-action-btn" data-action="wishlist" data-game-id="<?php echo $game['id']; ?>">
                                        <i class="bi bi-heart"></i>
                                        <span>Wishlist</span>
                                    </button>
                                </div>
                                <button class="search-action-btn search-action-primary" data-action="log" data-game-id="<?php echo $game['id']; ?>">
                                    <i class="bi bi-journal-plus"></i>
                                    <span>Registro</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="search-card-actions">
                                <div class="search-login-prompt">
                                    <p class="login-prompt-main">
                                        <button class="btn-register-small" onclick="openModal('registerModal')">Criar Conta</button>
                                        para adicionar jogos à sua lista
                                    </p>
                                    <p class="login-prompt-text">
                                        ou <button class="btn-login-link" onclick="openModal('loginModal')">iniciar sessão</button> se já tem uma conta.
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
