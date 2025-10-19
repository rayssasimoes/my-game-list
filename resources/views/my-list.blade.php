@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Minha Lista de Jogos</h1>
    </div>

    @if ($games->isEmpty())
        <div class="alert alert-info">
            Você ainda não adicionou nenhum jogo à sua lista. <a href="{{ url('/') }}">Explore os jogos</a> e comece a sua coleção!
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            @foreach ($games as $game)
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $game->background_image }}" class="card-img-top" alt="{{ $game->name }}" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate" title="{{ $game->name }}">{{ $game->name }}</h5>
                            {{-- Aqui podemos adicionar status do jogo (e.g., jogando, zerado) no futuro --}}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
