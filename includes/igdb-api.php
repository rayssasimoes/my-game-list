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
            // Remover aspas simples ou duplas do valor
            $v = trim($v, '"\'');
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
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("IGDB Token Error (cURL): " . $error);
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        $_SESSION['igdb_token'] = $data['access_token'];
        $_SESSION['igdb_token_expires'] = time() + $data['expires_in'];
        error_log("IGDB Token obtido com sucesso");
        return $data['access_token'];
    }
    
    error_log("IGDB Token Error: " . print_r($data, true));
    return null;
}

// Incluir tradução (se disponível)
require_once __DIR__ . '/translate.php';

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
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("IGDB API Error (cURL): " . $error);
        return [];
    }
    
    if ($httpCode === 200) {
        $decoded = json_decode($response, true);
        error_log("IGDB API Success - Endpoint: {$endpoint} - Jogos retornados: " . count($decoded));
        return $decoded;
    }
    
    error_log("IGDB API Error - HTTP {$httpCode} - Response: " . substr($response, 0, 200));
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

// Buscar jogos populares com filtros (para página populares.php)
function getPopularGamesFiltered($limit = 48, $page = 1, $genreId = '', $platformId = '') {
    // Validar credenciais
    if (empty(IGDB_CLIENT_ID) || empty(IGDB_CLIENT_SECRET)) {
        error_log("ERRO: Credenciais IGDB não configuradas!");
        return [];
    }
    
    // Calcular offset
    $offset = ($page - 1) * $limit;
    
    // Cache baseado nos parâmetros
    $cacheKey = "popular_games_filtered_{$limit}_{$page}_{$genreId}_{$platformId}";
    
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time']) && 
        (time() - $_SESSION[$cacheKey . '_time']) < 1800) { // Cache de 30 minutos
        error_log("Retornando jogos do cache: {$cacheKey}");
        return $_SESSION[$cacheKey];
    }
    
    error_log("Buscando jogos da API - Página: {$page}, Limite: {$limit}, Gênero: {$genreId}, Plataforma: {$platformId}");
    
    // Construir query com filtros - tornar a filtragem server-side
    // Usar total_rating ao invés de rating para ter mais resultados
    $whereConditions = ['total_rating != null', 'cover != null'];

    // Suporte a múltiplos ids (ex: "12,34") - garantir sanitização
    if (!empty($genreId)) {
        $genreIds = array_filter(array_map('intval', explode(',', $genreId)));
        if (!empty($genreIds)) {
            $whereConditions[] = 'genres = (' . implode(',', $genreIds) . ')';
        }
    }

    if (!empty($platformId)) {
        $platformIds = array_filter(array_map('intval', explode(',', $platformId)));
        if (!empty($platformIds)) {
            $whereConditions[] = 'platforms = (' . implode(',', $platformIds) . ')';
        }
    }

    $whereClause = implode(' & ', $whereConditions);
    
    $query = "
        fields name, cover.url, total_rating, first_release_date;
        where {$whereClause};
        sort total_rating desc;
        limit {$limit};
        offset {$offset};
    ";
    
    error_log("Query IGDB: " . $query);
    $games = igdbRequest('games', $query);
    error_log("Jogos retornados pela API: " . count($games));
    
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
            'rating' => $game['total_rating'] ?? $game['rating'] ?? 0
        ];
    }
    
    // Salvar em cache
    $_SESSION[$cacheKey] = $formatted;
    $_SESSION[$cacheKey . '_time'] = time();
    
    return $formatted;
}

// Retorna o total de jogos que casam com os filtros (usado para paginação)
function getPopularGamesCount($genreId = '', $platformId = '') {
    // Validar credenciais
    if (empty(IGDB_CLIENT_ID) || empty(IGDB_CLIENT_SECRET)) {
        error_log("ERRO: Credenciais IGDB não configuradas!");
        return 0;
    }

    // Construir where igual ao usado em getPopularGamesFiltered
    $whereConditions = ['total_rating != null', 'cover != null'];

    if (!empty($genreId)) {
        $genreIds = array_filter(array_map('intval', explode(',', $genreId)));
        if (!empty($genreIds)) {
            $whereConditions[] = 'genres = (' . implode(',', $genreIds) . ')';
        }
    }

    if (!empty($platformId)) {
        $platformIds = array_filter(array_map('intval', explode(',', $platformId)));
        if (!empty($platformIds)) {
            $whereConditions[] = 'platforms = (' . implode(',', $platformIds) . ')';
        }
    }

    $whereClause = implode(' & ', $whereConditions);

    // Usar 'count' no corpo da query para obter somente o número total
    $query = "
        where {$whereClause};
        count;
    ";

    error_log("IGDB Count Query: " . $query);
    $res = igdbRequest('games', $query);

    // A resposta do IGDB para 'count' costuma ser um número simples ou um array com o número
    if (is_int($res)) return $res;
    if (is_array($res) && count($res) > 0) {
        // Pode vir como [123] ou ['count' => 123]
        $first = reset($res);
        if (is_int($first)) return $first;
        if (is_array($first) && isset($first['count'])) return (int)$first['count'];
    }

    return 0;
}

// Buscar jogos por termo de busca
function searchGames($searchTerm, $limit = 20) {
    $query = "
        fields name, cover.url, first_release_date, platforms.name;
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
        
        // Extrair ano da data de lançamento
        $year = isset($game['first_release_date']) 
            ? date('Y', $game['first_release_date'])
            : null;
        
        // Extrair nomes das plataformas
        $platforms = [];
        if (isset($game['platforms']) && is_array($game['platforms'])) {
            foreach ($game['platforms'] as $platform) {
                if (isset($platform['name'])) {
                    $platforms[] = $platform['name'];
                }
            }
        }
            
        $formatted[] = [
            'id' => $game['id'],
            'name' => $game['name'],
            'cover' => $coverUrl,
            'year' => $year,
            'platforms' => $platforms
        ];
    }
    
    return $formatted;
}

// Buscar detalhes de um jogo (foco em catalogação, sem métricas comunitárias)
function getGameDetails($gameId) {
    $query = "
        fields name, cover.url, summary, genres.name, platforms.name, 
               release_dates.date, release_dates.human, 
               involved_companies.company.name, involved_companies.developer,
               involved_companies.publisher, screenshots.url, hypes;
        where id = {$gameId};
    ";
    
    $games = igdbRequest('games', $query);
    
    if (empty($games)) {
        return null;
    }
    
    $game = $games[0];
    $coverUrl = isset($game['cover']['url']) 
        ? str_replace('t_thumb', 't_1080p', 'https:' . $game['cover']['url'])
        : 'https://via.placeholder.com/264x352?text=No+Image';
    
    // Processar data de lançamento
    $releaseDate = null;
    if (isset($game['release_dates']) && !empty($game['release_dates'])) {
        $firstRelease = $game['release_dates'][0];
        if (isset($firstRelease['human'])) {
            $releaseDate = $firstRelease['human'];
        } elseif (isset($firstRelease['date'])) {
            $releaseDate = date('d M Y', $firstRelease['date']);
        }
    }
    
    // Processar empresas envolvidas
    $developers = [];
    $publishers = [];
    if (isset($game['involved_companies'])) {
        foreach ($game['involved_companies'] as $company) {
            if (isset($company['company']['name'])) {
                if (isset($company['developer']) && $company['developer']) {
                    $developers[] = $company['company']['name'];
                }
                if (isset($company['publisher']) && $company['publisher']) {
                    $publishers[] = $company['company']['name'];
                }
            }
        }
    }
    
    // Traduzir summary para pt-BR quando possível
    $summaryText = $game['summary'] ?? 'Sem descrição disponível';
    if (!empty($summaryText) && function_exists('translateText')) {
        $translated = translateText($summaryText, 'pt-BR', 'en');
        // Caso a tradução retorne uma mensagem de erro, manter o original
        if (!preg_match('/QUERY\s+LENGTH\s+LIMIT|MAX\s+ALLOWED\s+QUERY|LENGTH\s+LIMIT\s+EXCEEDED/i', $translated)) {
            $summaryText = $translated;
        }
    }

    return [
        'id' => $game['id'],
        'name' => $game['name'],
        'cover' => $coverUrl,
        'summary' => $summaryText,
        'genres' => array_column($game['genres'] ?? [], 'name'),
        'platforms' => array_column($game['platforms'] ?? [], 'name'),
        'release_date' => $releaseDate,
        'developers' => $developers,
        'publishers' => $publishers,
        'hypes' => $game['hypes'] ?? 0,
        'screenshots' => array_map(function($s) {
            return 'https:' . str_replace('t_thumb', 't_1080p', $s['url']);
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
