@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="container">
                        <div class="row">
                            @if(isset($games) && !$games->isEmpty())
                                @foreach ($games as $game)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <img src="{{ $game->background_image }}" class="card-img-top" alt="{{ $game->name }}" style="height: 200px; object-fit: cover;">
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title">{{ $game->name }}</h5>
                                                
                                                <div class="mt-auto">
                                                    {{-- O botão de adicionar só aparece se o jogo ainda não estiver na lista do usuário --}}
                                                    @if(Auth::user() && !Auth::user()->games->contains($game->id))
                                                        <form action="{{ route('games.add') }}" method="POST" class="mt-2">
                                                            @csrf
                                                            <input type="hidden" name="game_id" value="{{ $game->id }}">
                                                            <button type="submit" class="btn btn-primary w-100">Adicionar à Minha Lista</button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="btn btn-success w-100" disabled>Já está na sua lista</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col">
                                    <p>Nenhum jogo encontrado no banco de dados.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
