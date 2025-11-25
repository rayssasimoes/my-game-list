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
if (!isset($_POST['game_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do jogo não fornecido']);
    exit;
}

$gameId = intval($_POST['game_id']);
$user = getUser();

try {
    $db = getDB();
    
    // Remover o jogo da lista do usuário (isso remove dos favoritos)
    $stmt = $db->prepare("DELETE FROM game_user WHERE user_id = ? AND game_id = ?");
    $stmt->execute([$user['id'], $gameId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Jogo removido dos favoritos com sucesso!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Jogo não encontrado na sua lista'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro ao remover favorito: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao remover jogo dos favoritos: ' . $e->getMessage()
    ]);
}
