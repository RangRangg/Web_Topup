<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use App\Models\Game;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopUpController extends Controller
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * Display top-up hub or redirect to game top-up page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->filled('game')) {
            $game = Game::where('slug', $request->game)->where('is_active', true)->first();
            if ($game) {
                return redirect()->route('topup.game', $game->slug);
            }
        }

        $games = Game::where('is_active', true)
            ->whereHas('gameProducts', fn ($q) => $q->where('is_active', true))
            ->withCount(['gameProducts' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('order_priority')
            ->get();

        $selectedGame = $games->first();

        if ($selectedGame && ! $request->has('browse')) {
            return redirect()->route('topup.game', $selectedGame->slug);
        }

        return view('topup.index', compact('games', 'selectedGame'));
    }

    /**
     * Display dedicated Top Up form for a specific game.
     */
    public function game(Game $game): View
    {
        $products = $game->gameProducts()
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $allGames = Game::where('is_active', true)
            ->whereHas('gameProducts', fn ($q) => $q->where('is_active', true))
            ->orderBy('order_priority')
            ->get();

        $paymentMethods = $this->paymentService->getAvailableMethods();

        $promotions = Promotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->take(3)
            ->get();

        return view('topup.game', compact('game', 'products', 'allGames', 'paymentMethods', 'promotions'));
    }
}
