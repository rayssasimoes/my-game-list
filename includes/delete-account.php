<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Só aceitar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$user = getUser();
$userId = $user['id'];

try {
    $db = getDB();
    // Iniciar transação
    $db->beginTransaction();

    // Apagar usuário (constraints ON DELETE CASCADE irão limpar game_user, password_resets etc.)
    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);

    // Commit
    $db->commit();

    // Destruir sessão localmente
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();

    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log('Erro ao excluir conta: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno ao apagar conta']);
    exit;
}

?>
