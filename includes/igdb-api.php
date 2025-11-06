<?php

// Helper simples para pegar variáveis de ambiente (.env) quando necessário
function env($key, $default = null) {
    // 1) getenv
    $val = getenv($key);
    if ($val !== false) return $val;

    // 2) $_ENV
    if (isset($_ENV[$key])) return $_ENV[$key];

    // 3) Ler .env local (apenas fallback para desenvolvimento)
    $envFile = __DIR__ . '/../.env';
    if (is_file($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);
            if ($k === $key) return $v;
        }
    }

    return $default;
}

define('IGDB_CLIENT_ID', env('IGDB_CLIENT_ID', ''));
define('IGDB_CLIENT_SECRET', env('IGDB_CLIENT_SECRET', ''));

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

// Buscar jogos "Em Breve" (lançamentos próximos - próximos 30 dias)
function getUpcomingGames($limit = 8) {
    $cacheKey = "upcoming_games_{$limit}";
    
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && 
        (time() - $_SESSION[$cacheKey . '_time']) < 21600) {
        return $_SESSION[$cacheKey];
    }
    
    $today = time();
    $thirtyDaysFromNow = $today + (30 * 24 * 60 * 60);
    
    $query = "
        fields name, cover.url, first_release_date;
        where first_release_date > {$today} & first_release_date < {$thirtyDaysFromNow} & cover != null;
        sort first_release_date asc;
        limit {$limit};
    ";
    
    $games = igdbRequest('games', $query);
    
    $mesesPtBr = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
    ];
    
    $formatted = [];
    foreach ($games as $game) {
        $coverUrl = isset($game['cover']['url']) 
            ? str_replace('t_thumb', 't_cover_small', 'https:' . $game['cover']['url'])
            : 'https://via.placeholder.com/90x120?text=No+Image';
            
        if (isset($game['first_release_date'])) {
            $mes = (int)date('n', $game['first_release_date']);
            $dia = date('j', $game['first_release_date']);
            $releaseDate = $mesesPtBr[$mes] . ' ' . $dia;
        } else {
            $releaseDate = 'TBA';
        }
            
        $formatted[] = [
            'id' => $game['id'],
            'name' => $game['name'],
            'cover' => $coverUrl,
            'release_date' => $releaseDate
        ];
    }
    
    $_SESSION[$cacheKey] = $formatted;
    $_SESSION[$cacheKey . '_time'] = time();
    
    return $formatted;
}

// Buscar jogos "Recentemente Antecipados" (alto hype, lançamento distante)
function getHypedGames($limit = 8) {
    $cacheKey = "hyped_games_{$limit}";
    
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && 
        (time() - $_SESSION[$cacheKey . '_time']) < 21600) {
        return $_SESSION[$cacheKey];
    }
    
    $sixMonthsFromNow = time() + (180 * 24 * 60 * 60);
    
    $query = "
        fields name, cover.url, first_release_date, hypes;
        where first_release_date > {$sixMonthsFromNow} & hypes > 50 & cover != null;
        sort hypes desc;
        limit {$limit};
    ";
    
    $games = igdbRequest('games', $query);
    
    $mesesPtBr = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
    ];
    
    $formatted = [];
    foreach ($games as $game) {
        $coverUrl = isset($game['cover']['url']) 
            ? str_replace('t_thumb', 't_cover_small', 'https:' . $game['cover']['url'])
            : 'https://via.placeholder.com/90x120?text=No+Image';
            
        if (isset($game['first_release_date'])) {
            $mes = (int)date('n', $game['first_release_date']);
            $dia = date('j', $game['first_release_date']);
            $ano = date('Y', $game['first_release_date']);
            $releaseDate = $mesesPtBr[$mes] . ' ' . $dia . ', ' . $ano;
        } else {
            $releaseDate = 'TBA';
        }
            
        $formatted[] = [
            'id' => $game['id'],
            'name' => $game['name'],
            'cover' => $coverUrl,
            'release_date' => $releaseDate,
            'hypes' => $game['hypes'] ?? 0
        ];
    }
    
    $_SESSION[$cacheKey] = $formatted;
    $_SESSION[$cacheKey . '_time'] = time();
    
    return $formatted;
}

// Buscar "Sucessos Inesperados" (hidden gems - alta avaliação, poucos reviews)
function getHiddenGems($limit = 8) {
    $cacheKey = "hidden_gems_{$limit}";
    
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && 
        (time() - $_SESSION[$cacheKey . '_time']) < 21600) {
        return $_SESSION[$cacheKey];
    }
    
    $query = "
        fields name, cover.url, total_rating, total_rating_count;
        where total_rating > 80 & total_rating_count > 50 & total_rating_count < 1000 & cover != null;
        sort total_rating desc;
        limit {$limit};
    ";
    
    $games = igdbRequest('games', $query);
    
    $formatted = [];
    foreach ($games as $game) {
        $coverUrl = isset($game['cover']['url']) 
            ? str_replace('t_thumb', 't_cover_small', 'https:' . $game['cover']['url'])
            : 'https://via.placeholder.com/90x120?text=No+Image';
            
        $rating = isset($game['total_rating']) 
            ? number_format($game['total_rating'] / 20, 1) // Converter de 0-100 para 0-5
            : 0;
            
        $formatted[] = [
            'id' => $game['id'],
            'name' => $game['name'],
            'cover' => $coverUrl,
            'rating' => $rating
        ];
    }
    
    $_SESSION[$cacheKey] = $formatted;
    $_SESSION[$cacheKey . '_time'] = time();
    
    return $formatted;
}
