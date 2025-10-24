<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index']);

Route::get('/dashboard', [GameController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rota para adicionar um jogo à lista do usuário
    Route::post('games/add', [GameController::class, 'add'])->name('games.add');

    Route::get('/minha-lista', [GameController::class, 'myList'])->name('games.my-list');
});

Route::resource('games', GameController::class);

require __DIR__.'/auth.php';
