@extends('layouts.app')

@section('content')
<div class="container popular-games-page">
    <!-- Filtros e Ordenação -->
    <div class="filters-section mb-4">
        <form method="GET" action="{{ route('games.popular') }}" class="row g-3">
            <!-- Ordenação -->
            <div class="col-md-4">
                <label for="ordering" class="form-label text-white-50">Ordenar por</label>
                <select name="ordering" id="ordering" class="form-select form-select-dark" onchange="this.form.submit()">
                    <option value="-rating" {{ $ordering === '-rating' ? 'selected' : '' }}>Mais bem avaliados</option>
                    <option value="-released" {{ $ordering === '-released' ? 'selected' : '' }}>Mais recentes</option>
                    <option value="-added" {{ $ordering === '-added' ? 'selected' : '' }}>Mais populares</option>
                    <option value="-metacritic" {{ $ordering === '-metacritic' ? 'selected' : '' }}>Metacritic</option>
                </select>
            </div>

            <!-- Filtro de Plataformas -->
            <div class="col-md-8">
                <label class="form-label text-white-50">Plataformas</label>
                <div class="platform-filters">
                    @php
                        $platformOptions = [
                            '4' => 'PC',
                            '187' => 'PlayStation 5',
                            '18' => 'PlayStation 4',
                            '1' => 'Xbox One',
                            '186' => 'Xbox Series X',
                            '7' => 'Nintendo Switch',
                        ];
                    @endphp
                    @foreach($platformOptions as $id => $name)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="platforms[]" 
                                   id="platform_{{ $id }}" value="{{ $id }}"
                                   {{ in_array($id, $selectedPlatforms) ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label text-white-50" for="platform_{{ $id }}">
                                {{ $name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    <!-- Contador de Resultados -->
    <div class="results-info mb-4">
        <p class="text-white-50">
            <i class="bi bi-joystick me-2"></i>
            Exibindo {{ count($games) }} de {{ number_format($totalGames, 0, ',', '.') }} jogos
        </p>
    </div>

    <!-- Grid de Jogos -->
    <div class="games-grid-large mb-5">
        @forelse($games as $game)
            @php
                $cardImage = $game['background_image'] ?? '';
            @endphp
            <div class="game-card">
                <img src="{{ $cardImage }}" alt="{{ $game['name'] }}" class="game-card-image">
                <div class="game-card-overlay">
                    <h3 class="game-card-title">{{ $game['name'] }}</h3>
                    <div class="game-card-meta">
                        @if(isset($game['released']))
                            <span class="game-release-date">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ \Carbon\Carbon::parse($game['released'])->format('Y') }}
                            </span>
                        @endif
                        @if(isset($game['metacritic']) && $game['metacritic'])
                            <span class="metacritic-score">{{ $game['metacritic'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-emoji-frown fs-1 text-white-50 mb-3"></i>
                <p class="text-white-50">Nenhum jogo encontrado com os filtros selecionados.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($hasNextPage || $hasPreviousPage)
        <nav aria-label="Navegação de páginas">
            <ul class="pagination justify-content-center">
                @if($hasPreviousPage)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('games.popular', ['page' => $currentPage - 1, 'ordering' => $ordering, 'platforms' => $selectedPlatforms]) }}">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </a>
                    </li>
                @endif
                
                <li class="page-item active">
                    <span class="page-link">Página {{ $currentPage }}</span>
                </li>
                
                @if($hasNextPage)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('games.popular', ['page' => $currentPage + 1, 'ordering' => $ordering, 'platforms' => $selectedPlatforms]) }}">
                            Próxima <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>
@endsection
