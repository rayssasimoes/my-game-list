<?php
/**
 * Endpoint AJAX para autocomplete de busca
 * Retorna jogos que correspondem ao termo de busca
 */

// Iniciar sessão para cache do token IGDB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Log para debug
error_log("Search autocomplete - Query: {$query}");

$games = searchGames($query, $maxGames);

// Log resultado
error_log("Search autocomplete - Jogos encontrados: " . count($games));

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
    // Enviar apenas o primeiro nome para manter o dropdown compacto
    $fullName = trim($u['name'] ?? '');
    if ($fullName === '') {
        $shortName = '';
    } else {
        // dividir por espaços em branco e pegar o primeiro pedaço
        $parts = preg_split('/\s+/', $fullName);
        $shortName = $parts[0] ?? $fullName;
    }

    $results[] = [
        'type' => 'user',
        'id' => $u['id'],
        'username' => $u['username'],
        'name' => $shortName,
        'avatar' => $u['avatar_path']
    ];
}

echo json_encode($results);
