<?php
// Verificar se o ID do jogo foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$gameId = (int)$_GET['id'];

// Buscar detalhes do jogo
$game = getGameDetails($gameId);

if (!$game) {
    header('Location: index.php');
    exit;
}

$pageTitle = $game['name'] . ' - MyGameList';

// Buscar estatísticas de catalogação do banco de dados
$stats = ['plays' => 0, 'playing' => 0, 'backlogs' => 0, 'wishlists' => 0];
try {
    $db = getDB();
    $statsQuery = $db->prepare("
        SELECT 
            SUM(CASE WHEN gu.status = 'completed' THEN 1 ELSE 0 END) as plays,
            SUM(CASE WHEN gu.status = 'playing' THEN 1 ELSE 0 END) as playing,
            SUM(CASE WHEN gu.status = 'dropped' THEN 1 ELSE 0 END) as backlogs,
            SUM(CASE WHEN gu.status = 'want_to_play' THEN 1 ELSE 0 END) as wishlists
        FROM game_user gu
        INNER JOIN games g ON gu.game_id = g.id
        WHERE g.igdb_id = ?
    ");
    $statsQuery->execute([$gameId]);
    $fetchedStats = $statsQuery->fetch();
    if ($fetchedStats) {
        $stats = $fetchedStats;
    }
} catch (Exception $e) {
    // Se houver erro de conexão, continua com valores padrão
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Verificar se o usuário está logado e se o jogo está na sua lista
$userGameStatus = null;
if (isLoggedIn()) {
    try {
        $user = getUser();
        $db = getDB();
        $statusQuery = $db->prepare("
            SELECT gu.status 
            FROM game_user gu
            INNER JOIN games g ON gu.game_id = g.id
            WHERE gu.user_id = ? AND g.igdb_id = ?
        ");
        $statusQuery->execute([$user['id'], $gameId]);
        $userGame = $statusQuery->fetch();
        if ($userGame) {
            $userGameStatus = $userGame['status'];
        }
    } catch (Exception $e) {
        // Se houver erro de conexão, continua sem status
        error_log("Erro ao buscar status do usuário: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<div class="game-details-page">
    <div class="container-fluid">
        <div class="game-details-layout">
            <!-- Coluna Esquerda: Capa e Ações -->
            <aside class="game-sidebar">
                <div class="game-cover-wrapper">
                    <img src="<?php echo htmlspecialchars($game['cover']); ?>" 
                         alt="<?php echo htmlspecialchars($game['name']); ?>" 
                         class="game-cover-large">
                </div>

                <?php if (isLoggedIn()): ?>
                    <!-- Botões de Status (Usuário Logado) -->
                    <div class="game-status-buttons">
                        <button class="status-btn <?php echo $userGameStatus === 'completed' ? 'active' : ''; ?>" 
                                data-status="completed"
                                data-game-id="<?php echo $game['id']; ?>"
                                data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                            <i class="bi bi-check-circle"></i>
                            <span>Jogado</span>
                        </button>
                        <button class="status-btn <?php echo $userGameStatus === 'playing' ? 'active' : ''; ?>" 
                                data-status="playing"
                                data-game-id="<?php echo $game['id']; ?>"
                                data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                            <i class="bi bi-controller"></i>
                            <span>Jogando</span>
                        </button>
                        <button class="status-btn <?php echo $userGameStatus === 'dropped' ? 'active' : ''; ?>" 
                                data-status="dropped"
                                data-game-id="<?php echo $game['id']; ?>"
                                data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                            <i class="bi bi-x-circle"></i>
                            <span>Abandonado</span>
                        </button>
                        <button class="status-btn <?php echo $userGameStatus === 'want_to_play' ? 'active' : ''; ?>" 
                                data-status="want_to_play"
                                data-game-id="<?php echo $game['id']; ?>"
                                data-game-name="<?php echo htmlspecialchars($game['name']); ?>"
                                data-game-cover="<?php echo htmlspecialchars($game['cover']); ?>">
                            <i class="bi bi-bookmark-heart"></i>
                            <span>Lista de Desejos</span>
                        </button>
                    </div>
                <?php else: ?>
                    <!-- CTA para Visitantes (Não Logado) -->
                    <div class="game-cta-box">
                        <div class="cta-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h3 class="cta-title">Rastreie seu Progresso</h3>
                        <p class="cta-description">Crie uma conta ou inicie sessão para adicionar este jogo à sua Lista e registar o seu progresso.</p>
                        <div class="cta-buttons">
                            <button class="btn-cta-primary" onclick="openModal('registerModal')">Criar Conta</button>
                            <button class="btn-cta-secondary" onclick="openModal('loginModal')">Iniciar Sessão</button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <!-- Estatísticas de Catalogação (Apenas para Logados) -->
                    <div class="game-stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['plays'] ?? 0); ?></div>
                            <div class="stat-label">Jogadas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['playing'] ?? 0); ?></div>
                            <div class="stat-label">A Jogar</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['backlogs'] ?? 0); ?></div>
                            <div class="stat-label">Abandonados</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats['wishlists'] ?? 0); ?></div>
                            <div class="stat-label">Listas de Desejos</div>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>

            <!-- Coluna Direita: Informações do Jogo -->
            <main class="game-main-content">
                <div class="game-header">
                    <h1 class="game-title"><?php echo htmlspecialchars($game['name']); ?></h1>
                </div>

                <!-- Informações Básicas -->
                <div class="game-info-section">
                    <div class="info-row">
                        <?php if ($game['release_date']): ?>
                            <div class="info-item">
                                <span class="info-label">Data de Lançamento:</span>
                                <span class="info-value"><?php echo htmlspecialchars($game['release_date']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($game['genres'])): ?>
                            <div class="info-item">
                                <span class="info-label">Géneros:</span>
                                <span class="info-value"><?php echo htmlspecialchars(implode(', ', $game['genres'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($game['platforms'])): ?>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Plataformas:</span>
                                <span class="info-value"><?php echo htmlspecialchars(implode(', ', array_slice($game['platforms'], 0, 5))); ?><?php echo count($game['platforms']) > 5 ? '...' : ''; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($game['developers'])): ?>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Desenvolvedores:</span>
                                <span class="info-value"><?php echo htmlspecialchars(implode(', ', $game['developers'])); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($game['publishers'])): ?>
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Editoras:</span>
                                <span class="info-value"><?php echo htmlspecialchars(implode(', ', $game['publishers'])); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sinopse -->
                <div class="game-summary-section">
                    <h2 class="section-title">Sobre o Jogo</h2>
                    <p class="game-summary"><?php echo nl2br(htmlspecialchars($game['summary'])); ?></p>
                </div>

                <!-- Screenshots -->
                <?php if (!empty($game['screenshots'])): ?>
                    <div class="game-screenshots-section">
                        <h2 class="section-title">Capturas de Ecrã</h2>
                        <div class="screenshots-grid">
                            <?php foreach (array_slice($game['screenshots'], 0, 6) as $screenshot): ?>
                                <div class="screenshot-item">
                                    <img src="<?php echo htmlspecialchars($screenshot); ?>" 
                                         alt="Screenshot" 
                                         loading="lazy">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<script>
// Handler para os botões de status
document.addEventListener('DOMContentLoaded', function() {
    const statusButtons = document.querySelectorAll('.status-btn');
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const gameName = this.dataset.gameName;
            const gameCover = this.dataset.gameCover;
            const status = this.dataset.status;
            
            // Desabilitar botão temporariamente
            const btnElement = this;
            btnElement.disabled = true;
            btnElement.style.opacity = '0.6';
            
            // Fazer requisição AJAX para adicionar o jogo
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
                    // Remover classe active de todos os botões
                    statusButtons.forEach(btn => btn.classList.remove('active'));
                    
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
                    showGameNotification(messages[status] || 'Jogo adicionado à sua lista!', 'success');
                } else {
                    // Erro ao adicionar
                    btnElement.style.opacity = '1';
                    btnElement.disabled = false;
                    showGameNotification(data.message || 'Erro ao adicionar jogo', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                btnElement.style.opacity = '1';
                btnElement.disabled = false;
                showGameNotification('Erro ao adicionar jogo', 'error');
            });
        });
    });
    
    // Função para mostrar notificação
    function showGameNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `game-notification ${type}`;
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
    `;
    document.head.appendChild(style);
});
</script>

<?php include 'includes/footer.php'; ?>
