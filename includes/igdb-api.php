<?php

define('IGDB_CLIENT_ID', '8moen985l6yy84pd61d7d4net3k26g');
define('IGDB_CLIENT_SECRET', 'bwwru0snjnk13e5ko1aoyi2clbucu3');

// Função para pegar token de acesso do IGDB
function getIGDBToken() {
    // Verificar se já tem token válido em sessão
    if (isset($_SESSION['igdb_token']) && $_SESSION['igdb_token_expires'] > time()) {
        return $_SESSION['igdb_token'];
    }
    
    // Requisitar novo token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://id.twitch.tv/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => IGDB_CLIENT_ID,
        'client_secret' => IGDB_CLIENT_SECRET,
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        $_SESSION['igdb_token'] = $data['access_token'];
        $_SESSION['igdb_token_expires'] = time() + $data['expires_in'];
        return $data['access_token'];
    }
    
    return null;
}

// Função para fazer requisição na API do IGDB
function igdbRequest($endpoint, $query) {
    $token = getIGDBToken();
    
    if (!$token) {
        return [];
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.igdb.com/v4/{$endpoint}");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Client-ID: ' . IGDB_CLIENT_ID,
        'Authorization: Bearer ' . $token,
        'Content-Type: text/plain'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return [];
}

// Buscar jogos populares
function getPopularGames($limit = 12) {
    // Cache simples em sessão por 6 horas
    $cacheKey = "popular_games_{$limit}";
    
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && 
        (time() - $_SESSION[$cacheKey . '_time']) < 21600) {
        return $_SESSION[$cacheKey];
    }
    
    $query = "
        fields name, cover.url, rating, first_release_date;
        where rating != null & cover != null & first_release_date != null;
        sort rating desc;
        limit {$limit};
    ";
    
    $games = igdbRequest('games', $query);
    
    // Formatar dados
    $formatted = [];
    foreach ($games as $game) {
        $coverUrl = isset($game['cover']['url']) 
            ? str_replace('t_thumb', 't_cover_big', 'https:' . $game['cover']['url'])
            : 'https://via.placeholder.com/264x352?text=No+Image';
            
        $formatted[] = [
            'id' => $game['id'],
            'name' => $game['name'],
            'cover' => $coverUrl,
            'rating' => $game['rating'] ?? 0
        ];
    }
    
    // Salvar em cache
    $_SESSION[$cacheKey] = $formatted;
    $_SESSION[$cacheKey . '_time'] = time();
    
    return $formatted;
}

// Buscar jogos por termo de busca
function searchGames($searchTerm, $limit = 20) {
    $query = "
        fields name, cover.url, rating, first_release_date;
        search \"{$searchTerm}\";
        where cover != null;
        limit {$limit};
    ";
    
    $games = igdbRequest('games', $query);
    
    // Formatar dados
    $formatted = [];
    foreach ($games as $game) {
        $coverUrl = isset($game['cover']['url']) 
            ? str_replace('t_thumb', 't_cover_big', 'https:' . $game['cover']['url'])
            : 'https://via.placeholder.com/264x352?text=No+Image';
            
        $formatted[] = [
            'id' => $game['id'],
            'name' => $game['name'],
            'cover' => $coverUrl,
            'rating' => $game['rating'] ?? 0
        ];
    }
    
    return $formatted;
}

// Buscar detalhes de um jogo
function getGameDetails($gameId) {
    $query = "
        fields name, cover.url, summary, rating, genres.name, platforms.name, 
               release_dates.date, screenshots.url;
        where id = {$gameId};
    ";
    
    $games = igdbRequest('games', $query);
    
    if (empty($games)) {
        return null;
    }
    
    $game = $games[0];
    $coverUrl = isset($game['cover']['url']) 
        ? str_replace('t_thumb', 't_cover_big', 'https:' . $game['cover']['url'])
        : 'https://via.placeholder.com/264x352?text=No+Image';
    
    return [
        'id' => $game['id'],
        'name' => $game['name'],
        'cover' => $coverUrl,
        'summary' => $game['summary'] ?? 'Sem descrição disponível',
        'rating' => $game['rating'] ?? 0,
        'genres' => array_column($game['genres'] ?? [], 'name'),
        'platforms' => array_column($game['platforms'] ?? [], 'name'),
        'screenshots' => array_map(function($s) {
            return 'https:' . str_replace('t_thumb', 't_screenshot_big', $s['url']);
        }, $game['screenshots'] ?? [])
    ];
}
