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