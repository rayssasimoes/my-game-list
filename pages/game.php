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

// Incluir função de tradução
require_once __DIR__ . '/../includes/translate.php';

// Traduzir o summary se estiver em inglês
if (!empty($game['summary'])) {
    $game['summary'] = translateText($game['summary'], 'pt-BR', 'en');
}

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
                    <?php
                        $cover1 = $game['cover'];
                        // Tentar derivar uma versão maior (t_original) para telas 2x
                        $cover2 = preg_replace('/t_[^\\/]+/', 't_original', $cover1);
                    ?>
                    <img src="<?php echo htmlspecialchars($cover1); ?>" 
                         srcset="<?php echo htmlspecialchars($cover1); ?> 1x, <?php echo htmlspecialchars($cover2); ?> 2x"
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
                                <span class="info-label">Gêneros:</span>
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

                <!-- Trailer (se houver) - iframe nativo do YouTube -->
                <?php if (!empty($game['videos']) && is_array($game['videos'])):
                    $firstVideo = $game['videos'][0];
                    $ytId = $firstVideo['id'];
                ?>
                    <div class="game-trailer">
                        <h2 class="section-title">Trailer</h2>
                        <div class="video-embed" style="max-width:900px; margin:0.5rem auto 0;">
                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($ytId); ?>?rel=0&modestbranding=1" title="Trailer - <?php echo htmlspecialchars($game['name']); ?>" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sinopse -->
                <div class="game-summary-section">
                    <h2 class="section-title">Sobre o Jogo</h2>
                    <p class="game-summary"><?php echo nl2br(htmlspecialchars($game['summary'])); ?></p>
                </div>

                <!-- Screenshots -->
                <?php if (!empty($game['screenshots'])): ?>
                    <div class="game-screenshots-section">
                        <h2 class="section-title">Capturas de Tela</h2>
                        <div class="screenshots-grid">
                            <?php foreach (array_slice($game['screenshots'], 0, 6) as $index => $screenshot): ?>
                                <div class="screenshot-item" onclick="openScreenshotModal(<?php echo $index; ?>)">
                                    <img src="<?php echo htmlspecialchars($screenshot); ?>" 
                                         alt="Captura de tela <?php echo $index + 1; ?>" 
                                         loading="lazy">
                                    <div class="screenshot-overlay">
                                        <i class="bi bi-zoom-in"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<!-- Modal de Screenshot -->
<div id="screenshotModal" class="screenshot-modal">
    <div class="screenshot-modal-content">
        <button class="screenshot-modal-close" onclick="closeScreenshotModal()">
            <i class="bi bi-x-lg"></i>
        </button>
        <button class="screenshot-nav-btn screenshot-prev" onclick="navigateScreenshot(-1)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button class="screenshot-nav-btn screenshot-next" onclick="navigateScreenshot(1)">
            <i class="bi bi-chevron-right"></i>
        </button>
        <img id="screenshotModalImg" src="" alt="Screenshot expandida">
        <div class="screenshot-modal-footer">
            <button class="screenshot-download-btn" onclick="downloadScreenshot()">
                <i class="bi bi-download"></i> Baixar Imagem
            </button>
            <span class="screenshot-counter"><span id="currentScreenshot">1</span> / <span id="totalScreenshots">1</span></span>
        </div>
    </div>
</div>

<script>
// Screenshots
const screenshots = <?php echo json_encode(!empty($game['screenshots']) ? array_slice($game['screenshots'], 0, 6) : []); ?>;
let currentScreenshotIndex = 0;

function openScreenshotModal(index) {
    currentScreenshotIndex = index;
    updateScreenshotModal();
    document.getElementById('screenshotModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeScreenshotModal() {
    document.getElementById('screenshotModal').classList.remove('active');
    document.body.style.overflow = '';
}

function navigateScreenshot(direction) {
    currentScreenshotIndex += direction;
    if (currentScreenshotIndex < 0) currentScreenshotIndex = screenshots.length - 1;
    if (currentScreenshotIndex >= screenshots.length) currentScreenshotIndex = 0;
    updateScreenshotModal();
}

function updateScreenshotModal() {
    document.getElementById('screenshotModalImg').src = screenshots[currentScreenshotIndex];
    document.getElementById('currentScreenshot').textContent = currentScreenshotIndex + 1;
    document.getElementById('totalScreenshots').textContent = screenshots.length;
}

function downloadScreenshot() {
    const imageUrl = screenshots[currentScreenshotIndex];
    const fileName = `screenshot-${currentScreenshotIndex + 1}.jpg`;
    
    // Fazer fetch da imagem para converter em blob
    fetch(imageUrl)
        .then(response => response.blob())
        .then(blob => {
            // Criar URL do blob
            const blobUrl = window.URL.createObjectURL(blob);
            
            // Criar link de download
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Limpar URL do blob
            window.URL.revokeObjectURL(blobUrl);
        })
        .catch(error => {
            console.error('Erro ao baixar imagem:', error);
            // Fallback: abrir em nova aba
            window.open(imageUrl, '_blank');
        });
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeScreenshotModal();
    if (e.key === 'ArrowLeft') navigateScreenshot(-1);
    if (e.key === 'ArrowRight') navigateScreenshot(1);
});

// Handler para os botões de status
document.addEventListener('DOMContentLoaded', function() {
    const statusButtons = document.querySelectorAll('.status-btn');
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const gameName = this.dataset.gameName;
            const gameCover = this.dataset.gameCover;
            const status = this.dataset.status;
            
            // Verificar se o botão já está ativo (remover do jogo da lista)
            const isActive = this.classList.contains('active');
            
            // Desabilitar botão temporariamente
            const btnElement = this;
            btnElement.disabled = true;
            btnElement.style.opacity = '0.6';
            
            if (isActive) {
                // Remover jogo da lista
                fetch('includes/remove-from-list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `game_id=${gameId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remover classe active
                        btnElement.classList.remove('active');
                        
                        // Feedback visual temporário
                        const originalHTML = btnElement.innerHTML;
                        btnElement.innerHTML = '<i class="bi bi-check"></i> <span>Removido!</span>';
                        
                        setTimeout(() => {
                            btnElement.innerHTML = originalHTML;
                            btnElement.style.opacity = '1';
                            btnElement.disabled = false;
                        }, 1500);
                        
                        showGameNotification('Jogo removido da lista!', 'success');
                    } else {
                        btnElement.style.opacity = '1';
                        btnElement.disabled = false;
                        showGameNotification(data.message || 'Erro ao remover jogo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    btnElement.style.opacity = '1';
                    btnElement.disabled = false;
                    showGameNotification('Erro ao remover jogo', 'error');
                });
            } else {
                // Adicionar jogo à lista
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
            }
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
