<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiService;
use App\Models\Promotion;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class HomeController extends Controller
{
    /**
     * Render the main landing page for Aethereal Joki.
     */
    public function index(Request $request): View
    {
        try {
            $popularGames = Game::where('is_active', true)
                ->withCount(['gameProducts', 'jokiServices'])
                ->orderBy('order_priority', 'asc')
                ->get();

            $topUpProducts = GameProduct::with('game')
                ->where('is_active', true)
                ->orderBy('price', 'asc')
                ->take(8)
                ->get();

            $jokiServices = JokiService::with('game')
                ->where('is_active', true)
                ->orderByDesc('is_popular')
                ->take(6)
                ->get();

            $promotions = Promotion::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', now());
                })
                ->orderByDesc('discount_value')
                ->take(3)
                ->get();

            $reviews = Review::with(['user', 'order'])
                ->where('is_verified', true)
                ->latest()
                ->take(6)
                ->get();

            $allPackages = JokiService::with('game')
                ->where('is_active', true)
                ->get();
        } catch (Throwable $e) {
            report($e);
            $popularGames = collect();
            $topUpProducts = collect();
            $jokiServices = collect();
            $promotions = collect();
            $reviews = collect();
            $allPackages = collect();
        }

        $stats = [
            'completed_orders' => 15420,
            'active_boosters' => 48,
            'satisfaction_rate' => 99.8,
            'avg_speed' => '2-4 Jam',
        ];

        $recentActivities = [
            ['user' => 'Aditya S.', 'game' => 'Mobile Legends', 'action' => 'Mythic Honor ke Glory', 'time' => '5 menit lalu', 'status' => 'Selesai'],
            ['user' => 'Fauzan R.', 'game' => 'Valorant', 'action' => 'Silver ke Gold', 'time' => '12 menit lalu', 'status' => 'Diproses'],
            ['user' => 'Rian H.', 'game' => 'Free Fire', 'action' => 'Top Up 720 Diamonds', 'time' => '18 menit lalu', 'status' => 'Selesai'],
            ['user' => 'Kevin S.', 'game' => 'Genshin Impact', 'action' => 'Blessing of Welkin Moon', 'time' => '25 menit lalu', 'status' => 'Selesai'],
            ['user' => 'Dimas W.', 'game' => 'Genshin Impact', 'action' => 'Abyss 36 Star Full Clear', 'time' => '40 menit lalu', 'status' => 'Selesai'],
        ];

        return view('home', compact(
            'popularGames',
            'topUpProducts',
            'jokiServices',
            'promotions',
            'reviews',
            'allPackages',
            'stats',
            'recentActivities'
        ));
    }

    /**
     * Generate dynamic XML sitemap for SEO.
     */
    public function sitemap(): Response
    {
        $games = Game::where('is_active', true)->select('slug', 'updated_at')->get();
        $jokiServices = JokiService::where('is_active', true)->select('slug', 'updated_at')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $staticRoutes = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('games'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('topup'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('joki'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('ai-assistant'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('tracking'), 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => route('login'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('register'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        foreach ($staticRoutes as $item) {
            $xml .= '<url>';
            $xml .= '<loc>'.htmlspecialchars($item['loc']).'</loc>';
            $xml .= '<lastmod>'.now()->toDateString().'</lastmod>';
            $xml .= '<changefreq>'.$item['changefreq'].'</changefreq>';
            $xml .= '<priority>'.$item['priority'].'</priority>';
            $xml .= '</url>';
        }

        foreach ($games as $game) {
            // Game Details
            $xml .= '<url>';
            $xml .= '<loc>'.htmlspecialchars(route('games.show', $game->slug)).'</loc>';
            $xml .= '<lastmod>'.($game->updated_at ? $game->updated_at->toDateString() : now()->toDateString()).'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';

            // Top Up Game
            $xml .= '<url>';
            $xml .= '<loc>'.htmlspecialchars(route('topup.game', $game->slug)).'</loc>';
            $xml .= '<lastmod>'.($game->updated_at ? $game->updated_at->toDateString() : now()->toDateString()).'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        foreach ($jokiServices as $service) {
            $xml .= '<url>';
            $xml .= '<loc>'.htmlspecialchars(route('joki.show', $service->slug)).'</loc>';
            $xml .= '<lastmod>'.($service->updated_at ? $service->updated_at->toDateString() : now()->toDateString()).'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
