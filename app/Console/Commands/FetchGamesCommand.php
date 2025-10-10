<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\RawgApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchGamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches games from the RAWG API and stores them in the database.';

    /**
     * Execute the console command.
     */
    public function handle(RawgApiService $rawgApiService)
    {
        $this->info('Fetching games from RAWG API...');

        $games = $rawgApiService->getGames();

        if (empty($games) || !isset($games['results']) || empty($games['results'])) {
            $this->error('No games found or API request failed.');
            return;
        }

        $gamesList = $games['results'];
        $progressBar = $this->output->createProgressBar(count($gamesList));
        $progressBar->start();

        foreach ($gamesList as $gameData) {
            Game::updateOrCreate(
                ['rawg_id' => $gameData['id']],
                [
                    'name' => $gameData['name'],
                    'slug' => $gameData['slug'],
                    'released' => $gameData['released'],
                    'background_image' => $gameData['background_image'],
                    'rating' => $gameData['rating'],
                    'metacritic' => $gameData['metacritic'],
                ]
            );
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info('\nGames fetched and stored successfully!');
    }
}
