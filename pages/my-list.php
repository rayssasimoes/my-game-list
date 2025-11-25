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

foreach ($myGames as $game) {
    if (isset($stats[$game['status']])) {
        $stats[$game['status']]++;
    }
}

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

<!-- Modal: Editar Status do Jogo -->
<div id="editGameModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2 class="modal-title">Editar Jogo</h2>
            <button class="modal-close" aria-label="Fechar" onclick="closeEditGameModal()">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editGameForm">
                <input type="hidden" id="editGameId" name="game_id">
                
                <div class="form-group">
                    <label class="form-label">Nome do Jogo</label>
                    <p id="editGameName" class="game-name-display"></p>
                </div>
                
                <div class="form-group">
                    <label for="editGameStatus" class="form-label">Status</label>
                    <select id="editGameStatus" name="status" class="form-input" required>
                        <option value="playing">🎮 Jogando</option>
                        <option value="completed">✅ Jogado</option>
                        <option value="want_to_play">⭐ Lista de Desejos</option>
                        <option value="dropped">❌ Abandonado</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditGameModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEditGameId = null;
let currentEditGameName = null;

function editGame(gameId) {
    currentEditGameId = gameId;
    
    // Buscar informações do jogo
    fetch(`includes/get-game-info.php?game_id=${gameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentEditGameName = data.game.name;
                document.getElementById('editGameId').value = gameId;
                document.getElementById('editGameName').textContent = data.game.name;
                document.getElementById('editGameStatus').value = data.game.status;
                
                // Abrir modal
                document.getElementById('editGameModal').classList.add('show');
                document.body.classList.add('modal-open');
            } else {
                alert('Erro ao carregar informações do jogo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar informações do jogo');
        });
}

function closeEditGameModal() {
    document.getElementById('editGameModal').classList.remove('show');
    document.body.classList.remove('modal-open');
}

// Submeter formulário de edição
document.getElementById('editGameForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('includes/update-game-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditGameModal();
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao atualizar jogo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar jogo');
    });
});

function removeGame(gameId) {
    if (!confirm('Tem certeza que deseja remover este jogo da sua lista?')) {
        return;
    }
    
    fetch('includes/remove-game.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `game_id=${gameId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao remover jogo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao remover jogo');
    });
}

// Fechar modal ao clicar fora
document.addEventListener('click', (e) => {
    if (e.target.id === 'editGameModal') {
        closeEditGameModal();
    }
});
</script>

<style>
.game-name-display {
    font-weight: 600;
    color: white;
    font-size: 1.1rem;
    margin: 0;
}

.modal-small {
    max-width: 500px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 500;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: white;
    font-size: 1rem;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    background: rgba(255, 255, 255, 0.08);
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-primary, .btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}
</style>

<?php include 'includes/footer.php'; ?>
