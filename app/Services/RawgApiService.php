<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RawgApiService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rawg.key');
        $this->baseUrl = 'https://api.rawg.io/api';
    }

    /**
     * Fetch a list of games.
     *
     * @param array $options
     * @return array|null
     */
    public function getGames(array $options = [])
    {
        $defaultOptions = [
            'key' => $this->apiKey,
            'page' => 1,
            'page_size' => 20,
        ];

        $response = Http::get("{$this->baseUrl}/games", array_merge($defaultOptions, $options));

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Fetch popular games from RAWG API.
     * Returns games that are trending now, ordered by most added (popularity).
     *
     * @param int $limit
     * @return array
     */
    public function getPopularGames(int $limit = 6)
    {
        return Cache::remember("popular_games_{$limit}", now()->addHours(6), function () use ($limit) {
            // Busca jogos lançados nos últimos 12 meses, ordenados por mais populares (mais adicionados)
            $oneYearAgo = now()->subYear()->format('Y-m-d');
            $today = now()->format('Y-m-d');
            
            $response = Http::get("{$this->baseUrl}/games", [
                'key' => $this->apiKey,
                'dates' => "{$oneYearAgo},{$today}",
                'ordering' => '-added',
                'page_size' => $limit,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['results'] ?? [];
            }

            return [];
        });
    }

    /**
     * Fetch popular games with pagination and filters.
     *
     * @param int $page
     * @param int $pageSize
     * @param string $ordering
     * @param array $platforms
     * @return array
     */
    public function getPopularGamesPaginated(int $page = 1, int $pageSize = 40, string $ordering = '-rating', array $platforms = [])
    {
        $cacheKey = "popular_games_page_{$page}_{$pageSize}_{$ordering}_" . implode('_', $platforms);
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($page, $pageSize, $ordering, $platforms) {
            $oneYearAgo = now()->subYear()->format('Y-m-d');
            $today = now()->format('Y-m-d');
            
            $params = [
                'key' => $this->apiKey,
                'dates' => "{$oneYearAgo},{$today}",
                'ordering' => $ordering,
                'page' => $page,
                'page_size' => $pageSize,
            ];

            // Adiciona filtro de plataformas se fornecido
            if (!empty($platforms)) {
                $params['platforms'] = implode(',', $platforms);
            }

            $response = Http::get("{$this->baseUrl}/games", $params);

            if ($response->successful()) {
                return $response->json();
            }

            return ['results' => [], 'count' => 0, 'next' => null, 'previous' => null];
        });
    }
}
