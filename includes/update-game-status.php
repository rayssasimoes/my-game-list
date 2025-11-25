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
if (!isset($_POST['game_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$gameId = intval($_POST['game_id']);
$status = $_POST['status'];
$user = getUser();

// Validar status
$validStatuses = ['playing', 'completed', 'want_to_play', 'dropped'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit;
}

try {
    $db = getDB();
    
    // Verificar se o jogo pertence ao usuário
    $stmt = $db->prepare("SELECT id FROM game_user WHERE game_id = ? AND user_id = ?");
    $stmt->execute([$gameId, $user['id']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Jogo não encontrado na sua lista']);
        exit;
    }
    
    // Atualizar status
    $stmt = $db->prepare("
        UPDATE game_user 
        SET status = ?, updated_at = NOW() 
        WHERE game_id = ? AND user_id = ?
    ");
    $stmt->execute([$status, $gameId, $user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Jogo atualizado com sucesso!'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao atualizar jogo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar jogo: ' . $e->getMessage()
    ]);
}
