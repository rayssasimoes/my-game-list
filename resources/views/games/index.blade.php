<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search Games') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('games.index') }}">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search for a game..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>

                    <!-- Search Results -->
                    <div class="mt-4">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            @forelse ($games as $game)
                                <div class="col">
                                    <div class="card h-100">
                                        <img src="{{ $game['background_image'] ?? 'https://via.placeholder.com/400x300' }}" class="card-img-top" alt="{{ $game['name'] }}" style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $game['name'] }}</h5>
                                            <p class="card-text"><small class="text-muted">Rating: {{ $game['rating'] }} / 5</small></p>
                                            <p class="card-text"><small class="text-muted">Metacritic: {{ $game['metacritic'] ?? 'N/A' }}</small></p>
                                            <p class="card-text"><small class="text-muted">Released: {{ $game['released'] }}</small></p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-center text-muted">No games found. Try another search!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
