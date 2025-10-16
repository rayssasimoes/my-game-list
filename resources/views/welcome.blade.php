@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Hero Section --}}
    <div class="p-5 mb-4 bg-white rounded-3 shadow-sm">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Bem-vindo ao MyGameList</h1>
            <p class="col-md-8 fs-4">O seu espaço para organizar e descobrir novos jogos. Explore nossa coleção pública e comece a montar a sua lista pessoal.</p>
        </div>
    </div>

    {{-- Games Section --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        @foreach ($games as $game)
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="{{ $game->background_image }}" class="card-img-top" alt="{{ $game->name }}" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate" title="{{ $game->name }}">{{ $game->name }}</h5>
                        
                        {{-- Formulário para adicionar o jogo à lista do usuário --}}
                        @auth
                            <form action="{{ route('games.add') }}" method="POST" class="mt-auto">
                                @csrf
                                <input type="hidden" name="game_id" value="{{ $game->id }}">
                                <button type="submit" class="btn btn-primary w-100">Adicionar à Lista</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection