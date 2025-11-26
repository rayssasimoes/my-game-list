<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Verificar se está logado
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Você precisa estar logado para adicionar jogos'
    ]);
    exit;
}

// Verificar se os dados foram enviados
if (!isset($_POST['game_id']) || !isset($_POST['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
    exit;
}

$gameId = (int) $_POST['game_id'];
$status = $_POST['status'];
$gameName = $_POST['game_name'] ?? null;
$gameCover = $_POST['game_cover'] ?? null;
$userId = getUser()['id'];


// Validar status
$validStatuses = ['playing', 'completed', 'want_to_play', 'dropped'];
if (!in_array($status, $validStatuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Status inválido'
    ]);
    exit;
}

try {
    $db = getDB();
    
    // Verificar se o jogo existe na tabela games (buscar por igdb_id)
    $stmt = $db->prepare("SELECT id, name FROM games WHERE igdb_id = ?");
    $stmt->execute([$gameId]);
    $gameExists = $stmt->fetch();
    
    // Se o jogo não existir, inserir na tabela games
    if (!$gameExists) {
        // Se não temos o nome do jogo, não podemos inserir
        if (!$gameName) {
            echo json_encode([
                'success' => false,
                'message' => 'Dados do jogo incompletos'
            ]);
            exit;
        }
        
        // Criar slug simples a partir do nome
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $gameName)));

        // Normalizar cover_url para uma versão maior quando possível
        if ($gameCover) {
            // Trocar tokens de tamanho (ex: t_thumb, t_cover_small) por t_720p para melhor qualidade
            $gameCover = preg_replace('/t_[^\\/]+/', 't_720p', $gameCover);
        }

        // Inserir jogo com igdb_id
        $stmt = $db->prepare("
            INSERT INTO games (igdb_id, name, slug, cover_url, created_at, updated_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$gameId, $gameName, $slug, $gameCover]);
        
        // Pegar o ID auto-incremento do jogo recém inserido
        $internalGameId = $db->lastInsertId();
    } else {
        // Usar o ID interno do banco
        $internalGameId = $gameExists['id'];
    }
    
    // Verificar se o jogo já está na lista do usuário (usar ID interno do banco)
    $stmt = $db->prepare("SELECT id, status FROM game_user WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$userId, $internalGameId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Atualizar o status
        $stmt = $db->prepare("
            UPDATE game_user 
            SET status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ? AND game_id = ?
        ");
        $stmt->execute([$status, $userId, $internalGameId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'action' => 'updated'
        ]);
    } else {
        // Inserir novo registro
        $stmt = $db->prepare("
            INSERT INTO game_user (user_id, game_id, status, added_at, updated_at) 
            VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$userId, $internalGameId, $status]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Jogo adicionado com sucesso',
            'action' => 'added'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao adicionar jogo à lista: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação'
    ]);
}
