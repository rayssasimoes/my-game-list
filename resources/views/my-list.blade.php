@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-white mb-3">Minha Lista de Jogos</h2>
            <p class="text-white-50">Gerencie sua coleção pessoal de jogos.</p>
        </div>
    </div>

    @if ($games->isEmpty())
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            Você ainda não adicionou nenhum jogo à sua lista. <a href="{{ url('/') }}" class="alert-link">Explore os jogos</a> e comece a sua coleção!
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach ($games as $game)
                <div class="col">
                    <div class="card bg-dark text-white h-100 shadow">
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
