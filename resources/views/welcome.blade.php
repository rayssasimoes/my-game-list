@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Seção: Populares no Momento -->
    <section class="popular-games-section popular-games-container mb-5">
        <div class="section-header mb-4 d-flex justify-content-between align-items-center">
            <h2 class="section-title text-white">Populares agora</h2>
            <a href="{{ route('games.popular') }}" class="view-more-link">Ver mais</a>
        </div>
        
        <div class="games-grid">
            @foreach($popularGames as $game)
                @php
                    $cardImage = $game['background_image'] ?? '';
                @endphp
                <div class="game-card">
                    <img src="{{ $cardImage }}" alt="{{ $game['name'] }}" class="game-card-image">
                    <div class="game-card-overlay">
                        <h3 class="game-card-title">{{ $game['name'] }}</h3>
                        @if(isset($game['metacritic']) && $game['metacritic'])
                            <div class="game-card-rating">
                                <span class="metacritic-score">{{ $game['metacritic'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection