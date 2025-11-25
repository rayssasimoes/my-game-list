<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Verificar se está logado
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Você precisa estar logado'
    ]);
    exit;
}

// Verificar se os dados foram enviados
if (!isset($_POST['game_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do jogo não fornecido'
    ]);
    exit;
}

$gameId = (int) $_POST['game_id'];
$userId = getUser()['id'];

try {
    $db = getDB();
    
    // Buscar o ID interno do jogo na tabela games
    $stmt = $db->prepare("SELECT id FROM games WHERE igdb_id = ?");
    $stmt->execute([$gameId]);
    $game = $stmt->fetch();
    
    if (!$game) {
        echo json_encode([
            'success' => false,
            'message' => 'Jogo não encontrado'
        ]);
        exit;
    }
    
    $internalGameId = $game['id'];
    
    // Remover o jogo da lista do usuário
    $stmt = $db->prepare("DELETE FROM game_user WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$userId, $internalGameId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Jogo removido da lista'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Jogo não estava na sua lista'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao remover jogo da lista: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar solicitação'
    ]);
}
