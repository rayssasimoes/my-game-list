<?php
/**
 * Endpoint AJAX para autocomplete de busca
 * Retorna jogos que correspondem ao termo de busca
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/igdb-api.php';

header('Content-Type: application/json');

// Verifica se é uma requisição AJAX
if (!isset($_GET['query']) || empty(trim($_GET['query']))) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['query']);

// Busca apenas se tiver pelo menos 2 caracteres
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Limita a 8 resultados para o autocomplete
// Limites por tipo (para não poluir o dropdown)
$maxGames = 3;
$maxUsers = 3;

$games = searchGames($query, $maxGames);

// Busca local por usuários (máx $maxUsers)
$db = getDB();
$like = '%' . $query . '%';
$stmt = $db->prepare("SELECT id, username, name, avatar_path FROM users WHERE username LIKE :q1 OR name LIKE :q2 LIMIT :limit");
// Alguns drivers não aceitam bind direto de LIMIT via nomeado sem cast para int,
// então usamos bindValue com PDO::PARAM_INT para :limit
$stmt->bindValue(':q1', $like, PDO::PARAM_STR);
$stmt->bindValue(':q2', $like, PDO::PARAM_STR);
$stmt->bindValue(':limit', (int)$maxUsers, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Unificar resultados com campo 'type' para o frontend saber como renderizar
$results = [];

// Adicionar jogos
foreach ($games as $game) {
    $year = null;
    if (isset($game['first_release_date'])) {
        $year = date('Y', $game['first_release_date']);
    }

    $results[] = [
        'type' => 'game',
        'id' => $game['id'] ?? null,
        'name' => $game['name'],
        'cover' => $game['cover'],
        'year' => $year
    ];
}

// Adicionar usuários
foreach ($users as $u) {
    $results[] = [
        'type' => 'user',
        'id' => $u['id'],
        'username' => $u['username'],
        'name' => $u['name'],
        'avatar' => $u['avatar_path']
    ];
}

echo json_encode($results);
