<div class="mylist-game-card" data-game-id="<?php echo $game['id']; ?>">
    <div class="mylist-card-cover">
        <a href="index.php?page=game&id=<?php echo (isset($game['igdb_id']) && $game['igdb_id']) ? $game['igdb_id'] : $game['id']; ?>" class="mylist-cover-link">
            <img src="<?php echo htmlspecialchars($game['cover_url'] ?? 'https://via.placeholder.com/264x352?text=No+Image'); ?>" 
                 alt="<?php echo htmlspecialchars($game['name']); ?>">
        </a>
        
        <!-- Badge de Status -->
        <div class="status-badge status-badge-<?php echo $game['status']; ?>">
            <?php
            $statusLabels = [
                'playing' => 'Jogando',
                'completed' => 'Completado',
                'want_to_play' => 'Quero Jogar',
                'dropped' => 'Abandonado'
            ];
            echo $statusLabels[$game['status']] ?? $game['status'];
            ?>
        </div>
    </div>
    
    <div class="mylist-card-info">
        <h3 class="mylist-card-title"><?php echo htmlspecialchars($game['name']); ?></h3>
        
        <!-- Ações rápidas -->
        <div class="mylist-card-actions">
            <button class="mylist-action-btn" onclick="editGame(<?php echo $game['id']; ?>)" title="Editar">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="mylist-action-btn" onclick="removeGame(<?php echo $game['id']; ?>)" title="Remover">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</div>
