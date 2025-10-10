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
}
