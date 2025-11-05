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
$games = searchGames($query, 8);

// Formata os resultados para o frontend
$results = [];
foreach ($games as $game) {
    // Formata o ano de lançamento
    $year = null;
    if (isset($game['first_release_date'])) {
        $year = date('Y', $game['first_release_date']);
    }

    $results[] = [
        'id' => $game['id'] ?? null,
        'name' => $game['name'],
        'cover' => $game['cover'],
        'year' => $year
    ];
}

echo json_encode($results);
