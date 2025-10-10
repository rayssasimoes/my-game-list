<?php

namespace App\Http\Controllers;

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
    public function index(Request $request)
    {
        $options = [];
        if ($request->has('search')) {
            $options['search'] = $request->input('search');
        }

        $games = $this->rawgApiService->getGames($options);

        return view('games.index', [
            'games' => $games['results'] ?? [],
        ]);
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
