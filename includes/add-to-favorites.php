<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Verificar se está logado
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
    exit;
}

// Validar dados
if (!isset($_POST['game_id']) || !isset($_POST['game_name']) || !isset($_POST['game_cover'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$gameId = $_POST['game_id'];
$gameName = $_POST['game_name'];
$gameCover = $_POST['game_cover'];
$rating = 10; // Rating fixo 10 para favoritos (não visível ao usuário)
$user = getUser();

try {
    $db = getDB();
    
    // Verificar se o jogo já existe na tabela games
    $stmt = $db->prepare("SELECT id FROM games WHERE igdb_id = ?");
    $stmt->execute([$gameId]);
    $existingGame = $stmt->fetch();
    
    if ($existingGame) {
        $internalGameId = $existingGame['id'];
    } else {
        // Inserir jogo na tabela games
        $stmt = $db->prepare("
            INSERT INTO games (igdb_id, name, cover_url, slug) 
            VALUES (?, ?, ?, ?)
        ");
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $gameName));
        $stmt->execute([$gameId, $gameName, $gameCover, $slug]);
        $internalGameId = $db->lastInsertId();
    }
    
    // Verificar se o usuário já tem este jogo
    $stmt = $db->prepare("SELECT id, rating FROM game_user WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$user['id'], $internalGameId]);
    $existingEntry = $stmt->fetch();
    
    if ($existingEntry) {
        // Atualizar rating para 10 (favorito)
        $stmt = $db->prepare("
            UPDATE game_user 
            SET rating = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$rating, $existingEntry['id']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Jogo adicionado aos favoritos com sucesso!',
            'updated' => true
        ]);
    } else {
        // Adicionar jogo com rating 10 e status 'completed' por padrão
        $stmt = $db->prepare("
            INSERT INTO game_user (user_id, game_id, status, rating) 
            VALUES (?, ?, 'completed', ?)
        ");
        $stmt->execute([$user['id'], $internalGameId, $rating]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Jogo adicionado aos favoritos com sucesso!',
            'created' => true
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro ao adicionar favorito: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao adicionar jogo aos favoritos: ' . $e->getMessage()
    ]);
}
