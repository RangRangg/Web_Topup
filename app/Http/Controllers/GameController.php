<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    /**
     * Display a listing of games with search, category filters, and popular games.
     */
    public function index(Request $request): View
    {
        $popularGames = Game::where('is_active', true)
            ->whereNotNull('badge')
            ->orderBy('order_priority')
            ->take(4)
            ->get();

        $query = Game::withCount([
            'jokiServices' => fn ($q) => $q->where('is_active', true),
            'gameProducts' => fn ($q) => $q->where('is_active', true),
        ])->where('is_active', true);

        if ($request->filled('type')) {
            $query->where('category', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $games = $query->orderBy('order_priority')->get();

        return view('games.index', compact('games', 'popularGames'));
    }

    /**
     * Display detailed information for a specific game and its top-up products.
     */
    public function show(Game $game): View
    {
        $game->load([
            'gameProducts' => fn ($q) => $q->where('is_active', true)->orderBy('price'),
            'jokiServices' => fn ($q) => $q->where('is_active', true)->orderBy('price'),
        ]);

        $relatedGames = Game::where('is_active', true)
            ->where('id', '!=', $game->id)
            ->where('category', $game->category)
            ->take(3)
            ->get();

        if ($relatedGames->isEmpty()) {
            $relatedGames = Game::where('is_active', true)
                ->where('id', '!=', $game->id)
                ->take(3)
                ->get();
        }

        return view('games.show', compact('game', 'relatedGames'));
    }
}
