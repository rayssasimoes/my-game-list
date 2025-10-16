<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\RawgApiService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $rawgApiService;

    public function __construct(RawgApiService $rawgApiService)
    {
        $this->rawgApiService = $rawgApiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $games = Game::latest()->take(20)->get();
        return view('welcome', compact('games'));
    }

    /**
     * Display a listing of the games on the dashboard.
     */
    public function dashboard()
    {
        $games = Game::latest()->take(20)->get();
        return view('dashboard', compact('games'));
    }

    /**
     * Attach a game to the authenticated user's list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
        ]);

        $user = auth()->user();
        $gameId = $request->input('game_id');

        // Evita duplicatas
        if ($user->games()->where('game_id', $gameId)->exists()) {
            return back()->with('info', 'Este jogo já está na sua lista.');
        }

        $user->games()->attach($gameId, ['list_status' => 'desejo']);

        return back()->with('success', 'Jogo adicionado à sua lista!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}