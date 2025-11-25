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
if (!isset($_GET['game_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do jogo não fornecido']);
    exit;
}

$gameId = intval($_GET['game_id']);
$user = getUser();

try {
    $db = getDB();
    
    // Buscar informações do jogo
    $stmt = $db->prepare("
        SELECT g.id, g.name, g.cover_url, gu.status, gu.notes
        FROM games g
        INNER JOIN game_user gu ON g.id = gu.game_id
        WHERE g.id = ? AND gu.user_id = ?
    ");
    $stmt->execute([$gameId, $user['id']]);
    $game = $stmt->fetch();
    
    if (!$game) {
        echo json_encode(['success' => false, 'message' => 'Jogo não encontrado']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'game' => $game
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar informações do jogo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar informações do jogo'
    ]);
}
