<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Não logado");
}

$userId = $_SESSION['user_id'];
$db = getDB();

echo "<h1>Teste de Datas</h1>";
echo "<p>User ID da sessão: " . $userId . "</p>";

// Teste 1: SELECT *
echo "<h2>Teste 1: SELECT *</h2>";
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user1 = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($user1);
echo "</pre>";

// Teste 2: SELECT específico
echo "<h2>Teste 2: SELECT específico</h2>";
$stmt = $db->prepare("SELECT id, username, created_at, updated_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user2 = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($user2);
echo "</pre>";

// Teste 3: SELECT com DATE_FORMAT
echo "<h2>Teste 3: SELECT com DATE_FORMAT</h2>";
$stmt = $db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at, DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as updated_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user3 = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($user3);
echo "</pre>";

// Teste 4: Query direta
echo "<h2>Teste 4: Query direta (sem prepared statement)</h2>";
$result = $db->query("SELECT id, username, created_at, updated_at FROM users WHERE id = " . intval($userId));
$user4 = $result->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($user4);
echo "</pre>";
?>
