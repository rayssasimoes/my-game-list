<?php
/**
 * Endpoint AJAX para verificar disponibilidade de username e email
 * Retorna: { "available": true/false }
 */

// Carrega as configurações de banco
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Verifica se é uma requisição válida
if (!isset($_GET['type']) || !isset($_GET['value'])) {
    echo json_encode(['available' => true]);
    exit;
}

$type = $_GET['type']; // 'username' ou 'email'
$value = trim($_GET['value']);

// Validação básica
if (empty($value) || !in_array($type, ['username', 'email'])) {
    echo json_encode(['available' => true]);
    exit;
}

try {
    // Pega conexão do banco
    $db = getDB();
    
    // Verifica se já existe no banco
    $stmt = $db->prepare("SELECT id FROM users WHERE {$type} = ?");
    $stmt->execute([$value]);
    $exists = $stmt->fetch();
    
    echo json_encode(['available' => !$exists]);
} catch (Exception $e) {
    // Em caso de erro, não bloqueia o cadastro
    error_log("Erro check-availability: " . $e->getMessage());
    echo json_encode(['available' => true]);
}
