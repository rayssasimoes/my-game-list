<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
     * Returns games that are trending now, ordered by release date and rating.
     *
     * @param int $limit
     * @return array
     */
    public function getPopularGames(int $limit = 6)
    {
        // Busca jogos lançados nos últimos 12 meses, ordenados por rating
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = now()->format('Y-m-d');
        
        $response = Http::get("{$this->baseUrl}/games", [
            'key' => $this->apiKey,
            'dates' => "{$oneYearAgo},{$today}",
            'ordering' => '-rating',
            'page_size' => $limit,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['results'] ?? [];
        }

        return [];
    }
}
