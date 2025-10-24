@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Hero Section -->
    <section class="hero-simple mb-5">
        <h1 class="hero-title">Bem-vindo ao MyGameList</h1>
        <p class="hero-subtitle">Organize, descubra e compartilhe sua paixão por jogos</p>
    </section>

    <!-- Seção: Populares no Momento -->
    <section class="popular-games-section mb-5">
        <h2 class="section-title text-white mb-4">Populares no Momento</h2>
        
        <div class="games-grid">
            @foreach($popularGames as $game)
                <div class="game-card">
                    <div class="game-card-image" style="background-image: url('{{ $game['background_image'] ?? '' }}');">
                        <div class="game-card-overlay">
                            <h3 class="game-card-title">{{ $game['name'] }}</h3>
                            @if(isset($game['metacritic']) && $game['metacritic'])
                                <div class="game-card-rating">
                                    <span class="metacritic-score">{{ $game['metacritic'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection