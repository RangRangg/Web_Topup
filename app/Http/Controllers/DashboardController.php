<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display customer dashboard overview or joki workstation.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isJoki()) {
            return redirect()->route('joki.dashboard');
        }

        // Customer dashboard metrics
        $totalOrdersCount = Order::where('user_id', $user->id)->count();

        $activeOrdersCount = Order::where('user_id', $user->id)
            ->whereIn('order_status', ['pending', 'paid', 'waiting_assignment', 'processing'])
            ->count();

        $completedOrdersCount = Order::where('user_id', $user->id)
            ->where('order_status', 'completed')
            ->count();

        $totalSpent = (float) Order::where('user_id', $user->id)
            ->whereHas('payment', fn ($q) => $q->where('status', 'paid'))
            ->sum('total_amount');

        // Recent 5 orders
        $recentOrders = Order::with([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'payment',
            'jokiProfile',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $unreadNotificationsCount = $user->notifications()->where('is_read', false)->count();

        return view('dashboard.index', compact(
            'user',
            'totalOrdersCount',
            'activeOrdersCount',
            'completedOrdersCount',
            'totalSpent',
            'recentOrders',
            'unreadNotificationsCount'
        ));
    }

    /**
     * Display paginated customer orders with search and status filtering.
     */
    public function orders(Request $request): View
    {
        $user = Auth::user();

        $query = Order::with([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'payment',
            'jokiProfile.user',
        ])
            ->where('user_id', $user->id);

        // Filter by order status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('order_status', $request->status);
        }

        // Search by order code, account ID, or item name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('account_id', 'like', "%{$search}%")
                    ->orWhere('account_nickname', 'like', "%{$search}%")
                    ->orWhereHas('orderItems', function ($itemQ) use ($search) {
                        $itemQ->where('item_name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        $totalOrdersCount = Order::where('user_id', $user->id)->count();
        $unreadNotificationsCount = $user->notifications()->where('is_read', false)->count();

        return view('dashboard.orders', compact('user', 'orders', 'totalOrdersCount', 'unreadNotificationsCount'));
    }

    /**
     * Display dedicated customer order detail with policy check.
     */
    public function showOrder(Order $order): View
    {
        // Enforce Laravel Policy authorization: Customer can only view their own order
        $this->authorize('view', $order);

        $order->load([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'jokiProfile.user',
            'payment',
            'reviews',
            'user',
        ]);

        $firstItem = $order->orderItems->first();
        $orderType = $order->isJoki() ? 'Jasa Joki' : 'Top Up Game';
        $gameName = $firstItem?->jokiService?->game?->name
            ?? $firstItem?->gameProduct?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';

        return view('dashboard.order-detail', compact('order', 'orderType', 'gameName'));
    }

    /**
     * Display customer profile & security settings page.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $unreadNotificationsCount = $user->notifications()->where('is_read', false)->count();

        return view('dashboard.profile', compact('user', 'unreadNotificationsCount'));
    }

    /**
     * Update customer profile info.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    /**
     * Update customer password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui!');
    }

    /**
     * Display customer notifications list.
     */
    public function notifications(): View
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(15);
        $unreadNotificationsCount = $user->notifications()->where('is_read', false)->count();

        return view('dashboard.notifications', compact('user', 'notifications', 'unreadNotificationsCount'));
    }

    /**
     * Mark single notification as read.
     */
    public function markNotificationRead(Notification $notification): RedirectResponse
    {
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Mark all customer notifications as read.
     */
    public function markAllNotificationsRead(): RedirectResponse
    {
        $user = Auth::user();

        $user->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Semua notifikasi berhasil ditandai telah dibaca.');
    }
}
