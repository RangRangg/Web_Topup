<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiProfile;
use App\Models\JokiService;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\User;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Admin Dashboard Overview
     * Displays 7 core statistics, recent orders, and recent payments.
     */
    public function index()
    {
        $stats = [
            'total_revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'completed_orders' => Order::where('order_status', 'completed')->count(),
            'customers_count' => User::where('role', 'customer')->count(),
            'active_jokis_count' => User::where('role', 'joki')->where('is_active', true)->count(),
            'pending_payments_count' => Payment::where('status', 'pending')->count(),
        ];

        $recentOrders = Order::with(['orderItems', 'payment', 'user', 'jokiProfile'])
            ->latest()
            ->limit(8)
            ->get();

        $recentPayments = Payment::with('order')
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.index', compact('stats', 'recentOrders', 'recentPayments'));
    }

    /**
     * Users Management
     * View, search, filter, toggle active/inactive, and update role.
     */
    public function users(Request $request)
    {
        $query = User::withCount('orders')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->paginate(15)->withQueryString();

        $roleCounts = [
            'total' => User::count(),
            'customer' => User::where('role', 'customer')->count(),
            'joki' => User::where('role', 'joki')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users', compact('users', 'roleCounts'));
    }

    /**
     * Update User (Name, Role, and Active Status)
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:customer,joki,admin',
            'is_active' => 'required|in:0,1,true,false',
        ]);

        $newActiveStatus = $request->boolean('is_active');

        // Prevent admin from demoting or deactivating their own account
        if ($user->id === auth()->id()) {
            if ($validated['role'] !== 'admin') {
                return back()->with('error', 'Anda tidak dapat menurunkan role akun admin Anda sendiri.');
            }

            if (! $newActiveStatus) {
                return back()->with('error', 'Anda tidak dapat menonaktifkan akun admin Anda sendiri.');
            }
        }

        $oldValues = [
            'name' => $user->name,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];

        $user->name = trim($validated['name']);
        $user->role = $validated['role'];
        $user->is_active = $newActiveStatus;
        $user->save();

        // If newly designated as joki, ensure booster profile exists
        if ($user->role === 'joki' && ! $user->jokiProfile) {
            JokiProfile::create([
                'user_id' => $user->id,
                'nickname' => $user->name,
                'phone' => $user->phone,
                'bio' => 'Booster profesional terverifikasi oleh Admin Aethereal Joki.',
                'specialized_games' => ['Mobile Legends', 'Valorant'],
                'highest_rank' => 'Mythical Glory / Radiant',
                'rating' => 5.00,
                'status' => 'available',
                'is_verified' => true,
            ]);
        }

        $statusChanged = $oldValues['is_active'] !== $user->is_active;
        $roleChanged = $oldValues['role'] !== $user->role;
        $nameChanged = $oldValues['name'] !== $user->name;

        if ($nameChanged || $roleChanged || $statusChanged) {
            $changeDetails = [];
            if ($nameChanged) {
                $changeDetails[] = "nama diubah dari '{$oldValues['name']}' menjadi '{$user->name}'";
            }
            if ($roleChanged) {
                $changeDetails[] = "role diubah dari '{$oldValues['role']}' menjadi '{$user->role}'";
            }
            if ($statusChanged) {
                $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
                $changeDetails[] = "status akun {$statusText}";
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_updated_user',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'details' => "Admin memperbarui data pengguna {$user->name}: ".implode(', ', $changeDetails).'.',
            ]);
        }

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Toggle User Active Status
     */
    public function updateUserStatus(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun admin Anda sendiri.');
        }

        $oldStatus = $user->is_active;
        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_user_status',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['is_active' => $oldStatus],
            'new_values' => ['is_active' => $user->is_active],
            'details' => "Admin mengubah status akun {$user->name} menjadi ".($user->is_active ? 'Aktif' : 'Nonaktif').'.',
        ]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    /**
     * Update User Role
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:customer,joki,admin',
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak dapat menurunkan role akun admin Anda sendiri.');
        }

        $oldRole = $user->role;
        $user->role = $validated['role'];
        $user->save();

        // If newly designated as joki, ensure booster profile exists
        if ($user->role === 'joki' && ! $user->jokiProfile) {
            JokiProfile::create([
                'user_id' => $user->id,
                'nickname' => $user->name,
                'phone' => $user->phone,
                'bio' => 'Booster profesional terverifikasi oleh Admin Aethereal Joki.',
                'specialized_games' => ['Mobile Legends', 'Valorant'],
                'highest_rank' => 'Mythical Glory / Radiant',
                'rating' => 5.00,
                'status' => 'available',
                'is_verified' => true,
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_user_role',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['role' => $oldRole],
            'new_values' => ['role' => $user->role],
            'details' => "Admin mengubah role {$user->name} dari {$oldRole} menjadi {$user->role}.",
        ]);

        return back()->with('success', "Role {$user->name} berhasil diubah menjadi {$user->role}.");
    }

    /**
     * Games Management
     */
    public function games(Request $request)
    {
        $query = Game::withCount(['gameProducts', 'jokiServices'])->orderBy('order_priority')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('publisher', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $games = $query->paginate(12)->withQueryString();
        $categories = Game::select('category')->distinct()->pluck('category');

        return view('admin.games', compact('games', 'categories'));
    }

    /**
     * Store New Game
     */
    public function storeGame(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:games,slug',
            'category' => 'required|string|max:50',
            'publisher' => 'nullable|string|max:100',
            'icon_url' => 'nullable|url|max:255',
            'banner_url' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'badge' => 'nullable|string|max:20',
            'order_priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            if (Game::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] .= '-'.Str::lower(Str::random(4));
            }
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order_priority'] = $validated['order_priority'] ?? 0;

        $game = Game::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_created_game',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $game->toArray(),
            'details' => "Admin menambahkan game baru {$game->name}.",
        ]);

        return back()->with('success', "Game {$game->name} berhasil ditambahkan!");
    }

    /**
     * Update Game
     */
    public function updateGame(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('games', 'slug')->ignore($game->id)],
            'category' => 'required|string|max:50',
            'publisher' => 'nullable|string|max:100',
            'icon_url' => 'nullable|url|max:255',
            'banner_url' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'badge' => 'nullable|string|max:20',
            'order_priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order_priority'] = $validated['order_priority'] ?? $game->order_priority;

        $old = $game->toArray();
        $game->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_game',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $old,
            'new_values' => $game->toArray(),
            'details' => "Admin memperbarui game {$game->name}.",
        ]);

        return back()->with('success', "Game {$game->name} berhasil diperbarui!");
    }

    /**
     * Destroy Game
     */
    public function destroyGame(Request $request, Game $game)
    {
        $name = $game->name;
        $gameData = $game->toArray();
        $game->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_deleted_game',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $gameData,
            'details' => "Admin menghapus game {$name}.",
        ]);

        return back()->with('success', "Game {$name} berhasil dihapus!");
    }

    /**
     * Toggle Game Status
     */
    public function toggleGameStatus(Request $request, Game $game)
    {
        $game->update(['is_active' => ! $game->is_active]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_toggled_game_status',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['is_active' => $game->is_active],
            'details' => "Admin mengubah status aktif game {$game->name} menjadi ".($game->is_active ? 'Aktif' : 'Nonaktif').'.',
        ]);

        return back()->with('success', "Status game {$game->name} berhasil diubah.");
    }

    /**
     * Game Products Management
     */
    public function products(Request $request)
    {
        $query = GameProduct::with('game')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('game_id')) {
            $query->where('game_id', $request->game_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->paginate(15)->withQueryString();
        $games = Game::orderBy('name')->get();

        return view('admin.products', compact('products', 'games'));
    }

    /**
     * Store Game Product
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:game_products,slug',
            'code' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name'].'-'.Str::random(5));
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['stock'] = $validated['stock'] ?? 999;

        $product = GameProduct::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_created_product',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $product->toArray(),
            'details' => "Admin menambahkan produk {$product->name}.",
        ]);

        return back()->with('success', "Produk {$product->name} berhasil ditambahkan!");
    }

    /**
     * Update Game Product
     */
    public function updateProduct(Request $request, GameProduct $product)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:100',
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('game_products', 'slug')->ignore($product->id)],
            'code' => 'nullable|string|max:50',
            'category' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $old = $product->toArray();
        $product->update($validated);

        if ((float) $old['price'] !== (float) $product->price) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'price_changed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => ['price' => $old['price']],
                'new_values' => ['price' => $product->price],
                'details' => "Harga produk {$product->name} diubah dari Rp ".number_format($old['price'], 0, ',', '.').' menjadi Rp '.number_format($product->price, 0, ',', '.').'.',
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_product',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $old,
            'new_values' => $product->toArray(),
            'details' => "Admin memperbarui produk {$product->name}.",
        ]);

        return back()->with('success', "Produk {$product->name} berhasil diperbarui!");
    }

    /**
     * Destroy Game Product
     */
    public function destroyProduct(Request $request, GameProduct $product)
    {
        $name = $product->name;
        $productData = $product->toArray();
        $product->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_deleted_product',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $productData,
            'details' => "Admin menghapus produk {$name}.",
        ]);

        return back()->with('success', "Produk {$name} berhasil dihapus!");
    }

    /**
     * Toggle Product Status
     */
    public function toggleProductStatus(Request $request, GameProduct $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_toggled_product_status',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['is_active' => $product->is_active],
            'details' => "Admin mengubah status produk {$product->name} menjadi ".($product->is_active ? 'Aktif' : 'Nonaktif').'.',
        ]);

        return back()->with('success', "Status produk {$product->name} berhasil diubah.");
    }

    /**
     * Joki Services Management
     */
    public function jokiServices(Request $request)
    {
        $query = JokiService::with('game')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('service_type', 'like', "%{$s}%")
                    ->orWhere('rank_from', 'like', "%{$s}%")
                    ->orWhere('rank_to', 'like', "%{$s}%");
            });
        }

        if ($request->filled('game_id')) {
            $query->where('game_id', $request->game_id);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $services = $query->paginate(15)->withQueryString();
        $games = Game::orderBy('name')->get();

        return view('admin.joki-services', compact('services', 'games'));
    }

    /**
     * Store Joki Service
     */
    public function storeJokiService(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:joki_services,slug',
            'service_type' => 'required|string|max:50',
            'rank_from' => 'nullable|string|max:50',
            'rank_to' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'estimated_duration' => 'nullable|string|max:50',
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
            'features' => 'nullable|string',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name'].'-'.Str::random(5));
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_popular'] = $request->boolean('is_popular', false);
        $validated['unit'] = $validated['unit'] ?? 'Paket';
        $validated['estimated_duration'] = $validated['estimated_duration'] ?? '2-4 Jam';

        if (! empty($validated['features'])) {
            $validated['features'] = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['features']))));
        } else {
            $validated['features'] = null;
        }

        $service = JokiService::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_created_joki_service',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $service->toArray(),
            'details' => "Admin menambahkan layanan joki {$service->name}.",
        ]);

        return back()->with('success', "Layanan joki {$service->name} berhasil ditambahkan!");
    }

    /**
     * Update Joki Service
     */
    public function updateJokiService(Request $request, JokiService $service)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:100',
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('joki_services', 'slug')->ignore($service->id)],
            'service_type' => 'required|string|max:50',
            'rank_from' => 'nullable|string|max:50',
            'rank_to' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'estimated_duration' => 'nullable|string|max:50',
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
            'features' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_popular'] = $request->boolean('is_popular', false);

        if (isset($validated['features'])) {
            $validated['features'] = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['features']))));
        }

        $old = $service->toArray();
        $service->update($validated);

        if ((float) $old['price'] !== (float) $service->price) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'price_changed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => ['price' => $old['price']],
                'new_values' => ['price' => $service->price],
                'details' => "Harga layanan joki {$service->name} diubah dari Rp ".number_format($old['price'], 0, ',', '.').' menjadi Rp '.number_format($service->price, 0, ',', '.').'.',
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_joki_service',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $old,
            'new_values' => $service->toArray(),
            'details' => "Admin memperbarui layanan joki {$service->name}.",
        ]);

        return back()->with('success', "Layanan joki {$service->name} berhasil diperbarui!");
    }

    /**
     * Destroy Joki Service
     */
    public function destroyJokiService(Request $request, JokiService $service)
    {
        $name = $service->name;
        $serviceData = $service->toArray();
        $service->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_deleted_joki_service',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $serviceData,
            'details' => "Admin menghapus layanan joki {$name}.",
        ]);

        return back()->with('success', "Layanan joki {$name} berhasil dihapus!");
    }

    /**
     * Toggle Joki Service Status
     */
    public function toggleJokiServiceStatus(Request $request, JokiService $service)
    {
        $service->update(['is_active' => ! $service->is_active]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_toggled_joki_service_status',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['is_active' => $service->is_active],
            'details' => "Admin mengubah status layanan joki {$service->name} menjadi ".($service->is_active ? 'Aktif' : 'Nonaktif').'.',
        ]);

        return back()->with('success', "Status layanan joki {$service->name} berhasil diubah.");
    }

    /**
     * Orders Management
     */
    public function orders(Request $request)
    {
        $query = Order::with([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'payment',
            'jokiProfile.user',
            'user',
        ])->latest();

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_code', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_email', 'like', "%{$s}%")
                    ->orWhere('customer_whatsapp', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'joki') {
                $query->whereHas('orderItems', fn ($q) => $q->whereNotNull('joki_service_id'));
            } elseif ($request->type === 'topup') {
                $query->whereHas('orderItems', fn ($q) => $q->whereNotNull('game_product_id'));
            }
        }

        $orders = $query->paginate(15)->withQueryString();
        $boosters = JokiProfile::with('user')->get();

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'processing_orders' => Order::where('order_status', 'processing')->count(),
            'completed_orders' => Order::where('order_status', 'completed')->count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.orders', compact('orders', 'stats', 'boosters'));
    }

    /**
     * Show Order Detail
     */
    public function showOrder(Order $order)
    {
        $order->load([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'payment',
            'jokiProfile.user',
            'user',
            'promotion',
            'reviews',
        ]);

        $boosters = JokiProfile::with('user')->get();
        $waService = app(WhatsAppService::class);
        $waUrls = [
            'admin' => $waService->getAdminRedirectUrl($order),
            'booster' => $waService->isJokiOrder($order) ? $waService->getBoosterRedirectUrl($order) : null,
            'customer_pending' => $waService->getCustomerPendingRedirectUrl($order),
            'customer_success' => $waService->getCustomerSuccessRedirectUrl($order),
            'admin_phone' => $waService->getAdminPhone(),
            'booster_phone' => $waService->getBoosterPhone(),
            'customer_phone' => $waService->getCustomerPhone($order),
            'is_joki' => $waService->isJokiOrder($order),
        ];

        return view('admin.orders-detail', compact('order', 'boosters', 'waUrls'));
    }

    /**
     * Update Order Status & Payment Status
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'order_status' => 'required|in:pending,paid,waiting_assignment,processing,in_review,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,expired,failed,refunded',
            'progress_note' => 'nullable|string|max:500',
            'joki_profile_id' => 'nullable|exists:joki_profiles,id',
        ]);

        $oldStatus = $order->order_status;

        if ($validated['order_status'] === 'completed' && ! $order->completed_at) {
            $order->completed_at = now();
            if ($order->jokiProfile) {
                $order->jokiProfile->increment('completed_orders_count');
            }
        }

        $order->order_status = $validated['order_status'];
        if (isset($validated['progress_note'])) {
            $order->progress_note = $validated['progress_note'];
        }
        if (! empty($validated['joki_profile_id'])) {
            $order->joki_profile_id = $validated['joki_profile_id'];
        }
        $order->save();

        if ($order->payment) {
            $order->payment->update([
                'status' => $validated['payment_status'],
                'paid_at' => $validated['payment_status'] === 'paid' && ! $order->payment->paid_at ? now() : $order->payment->paid_at,
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_order',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['order_status' => $oldStatus],
            'new_values' => [
                'order_status' => $validated['order_status'],
                'payment_status' => $validated['payment_status'],
                'progress_note' => $validated['progress_note'] ?? null,
                'joki_profile_id' => $validated['joki_profile_id'] ?? null,
            ],
            'details' => "Admin memperbarui status order #{$order->order_code}.",
        ]);

        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'title' => "Update Status Order #{$order->order_code}",
                'message' => "Status pesanan Anda telah diperbarui menjadi {$validated['order_status']}.",
                'type' => 'order',
                'link' => route('orders.show', $order->order_code),
            ]);
        }

        return back()->with('success', "Status order #{$order->order_code} berhasil diperbarui!");
    }

    /**
     * Assign Booster to Order
     */
    public function assignBooster(Request $request, Order $order)
    {
        $this->authorize('assign', $order);

        $validated = $request->validate([
            'joki_profile_id' => 'required|exists:joki_profiles,id',
        ]);

        $booster = JokiProfile::with('user')->findOrFail($validated['joki_profile_id']);
        $oldStatus = $order->order_status;

        $newStatus = in_array($oldStatus, ['waiting_assignment', 'paid', 'pending'])
            ? OrderStatus::PROCESSING->value
            : $oldStatus;

        $order->update([
            'joki_profile_id' => $booster->id,
            'order_status' => $newStatus,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_assigned_booster',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'order_code' => $order->order_code,
                'joki_profile_id' => $booster->id,
                'order_status' => $newStatus,
            ],
            'details' => "Admin menugaskan booster {$booster->nickname} untuk order #{$order->order_code}.",
        ]);

        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'title' => 'Booster Telah Ditugaskan!',
                'message' => "Pesanan Anda kini sedang dikerjakan oleh Booster {$booster->nickname}.",
                'type' => 'order',
                'link' => route('orders.show', $order->order_code),
            ]);
        }

        if ($booster->user_id) {
            Notification::create([
                'user_id' => $booster->user_id,
                'title' => 'Tugas Baru Masuk!',
                'message' => "Anda telah ditugaskan untuk order #{$order->order_code}.",
                'type' => 'order',
                'link' => route('orders.show', $order->order_code),
            ]);
        }

        return back()->with('success', "Booster {$booster->nickname} berhasil ditugaskan untuk order #{$order->order_code}!");
    }

    /**
     * Payments Oversight
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['order.user'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('payment_code', 'like', "%{$s}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_code', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->paginate(15)->withQueryString();

        $paymentStats = [
            'total_paid' => (float) Payment::where('status', 'paid')->sum('amount'),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'paid_count' => Payment::where('status', 'paid')->count(),
            'failed_count' => Payment::whereIn('status', ['failed', 'expired'])->count(),
        ];

        return view('admin.payments', compact('payments', 'paymentStats'));
    }

    /**
     * Update Payment Status (Verify payment proof / approve)
     */
    public function updatePaymentStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,failed,expired,refunded',
        ]);

        $oldStatus = $payment->status;
        $payment->status = $validated['status'];

        if ($validated['status'] === 'paid' && ! $payment->paid_at) {
            $payment->paid_at = now();
            // Automatically advance order status if order was pending
            if ($payment->order && in_array($payment->order->order_status, ['pending', OrderStatus::PENDING->value])) {
                $hasJoki = $payment->order->isJoki();
                $payment->order->update([
                    'order_status' => $hasJoki ? OrderStatus::WAITING_ASSIGNMENT->value : OrderStatus::COMPLETED->value,
                    'completed_at' => $hasJoki ? null : now(),
                ]);
            }
        }

        $payment->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_payment',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $payment->status],
            'details' => "Admin memperbarui status pembayaran {$payment->payment_code} menjadi {$payment->status}.",
        ]);

        if ($payment->order && $payment->order->user_id) {
            Notification::create([
                'user_id' => $payment->order->user_id,
                'title' => "Status Pembayaran #{$payment->payment_code}",
                'message' => "Status pembayaran pesanan #{$payment->order->order_code} kini: {$payment->status}.",
                'type' => 'payment',
                'link' => route('orders.show', $payment->order->order_code),
            ]);
        }

        return back()->with('success', "Status pembayaran {$payment->payment_code} berhasil diperbarui menjadi {$payment->status}!");
    }

    /**
     * Promotions CRUD
     */
    public function promotions(Request $request)
    {
        $query = Promotion::withCount('orders')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        $promotions = $query->paginate(12)->withQueryString();

        return view('admin.promotions', compact('promotions'));
    }

    /**
     * Store Promotion
     */
    public function storePromotion(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code',
            'name' => 'required|string|max:100',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['min_spend'] = $validated['min_spend'] ?? 0;

        $promotion = Promotion::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_created_promotion',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $promotion->toArray(),
            'details' => "Admin membuat kode promo baru {$promotion->code}.",
        ]);

        return back()->with('success', "Kode promo {$promotion->code} berhasil dibuat!");
    }

    /**
     * Update Promotion
     */
    public function updatePromotion(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promotions', 'code')->ignore($promotion->id)],
            'name' => 'required|string|max:100',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $old = $promotion->toArray();
        $promotion->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_updated_promotion',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $old,
            'new_values' => $promotion->toArray(),
            'details' => "Admin memperbarui promo {$promotion->code}.",
        ]);

        return back()->with('success', "Promo {$promotion->code} berhasil diperbarui!");
    }

    /**
     * Destroy Promotion
     */
    public function destroyPromotion(Request $request, Promotion $promotion)
    {
        $code = $promotion->code;
        $data = $promotion->toArray();
        $promotion->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_deleted_promotion',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $data,
            'details' => "Admin menghapus promo {$code}.",
        ]);

        return back()->with('success', "Promo {$code} berhasil dihapus!");
    }

    /**
     * Toggle Promotion Status
     */
    public function togglePromotionStatus(Request $request, Promotion $promotion)
    {
        $promotion->update(['is_active' => ! $promotion->is_active]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_toggled_promotion_status',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['is_active' => $promotion->is_active],
            'details' => "Admin mengubah status promo {$promotion->code} menjadi ".($promotion->is_active ? 'Aktif' : 'Nonaktif').'.',
        ]);

        return back()->with('success', "Status promo {$promotion->code} berhasil diubah.");
    }

    /**
     * Reports & Analytics
     * Daily revenue, monthly revenue, order statistics, and popular games.
     */
    public function reports()
    {
        // 1. Daily Revenue (Last 14 days)
        $dailyDates = [];
        $dailyRevenue = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyDates[$date] = now()->subDays($i)->format('d M');
            $dailyRevenue[$date] = 0.0;
        }

        $paidPayments14 = Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(14)->startOfDay())
            ->get();

        foreach ($paidPayments14 as $pay) {
            $d = $pay->created_at->format('Y-m-d');
            if (isset($dailyRevenue[$d])) {
                $dailyRevenue[$d] += (float) $pay->amount;
            }
        }

        // 2. Monthly Revenue (Current Year)
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = now()->format('Y').'-'.str_pad($m, 2, '0', STR_PAD_LEFT);
            $monthLabel = date('M', mktime(0, 0, 0, $m, 1));
            $monthlyRevenue[$monthLabel] = 0.0;
        }

        $paidPaymentsYear = Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->startOfYear())
            ->get();

        foreach ($paidPaymentsYear as $pay) {
            $mLabel = $pay->created_at->format('M');
            if (isset($monthlyRevenue[$mLabel])) {
                $monthlyRevenue[$mLabel] += (float) $pay->amount;
            }
        }

        // 3. Order Statistics
        $ordersByStatus = [
            'pending' => Order::where('order_status', 'pending')->count(),
            'paid' => Order::where('order_status', 'paid')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'completed' => Order::where('order_status', 'completed')->count(),
            'cancelled' => Order::where('order_status', 'cancelled')->count(),
        ];

        $jokiOrdersCount = Order::whereHas('orderItems', fn ($q) => $q->whereNotNull('joki_service_id'))->count();
        $topupOrdersCount = Order::whereHas('orderItems', fn ($q) => $q->whereNotNull('game_product_id'))->count();

        // 4. Game Statistics
        $topGames = Game::withCount(['gameProducts', 'jokiServices'])
            ->get()
            ->map(function ($game) {
                $productOrderCount = Order::whereHas('orderItems.gameProduct', fn ($q) => $q->where('game_id', $game->id))->count();
                $jokiOrderCount = Order::whereHas('orderItems.jokiService', fn ($q) => $q->where('game_id', $game->id))->count();
                $game->total_orders_count = $productOrderCount + $jokiOrderCount;

                return $game;
            })
            ->sortByDesc('total_orders_count')
            ->take(5);

        $summary = [
            'total_sales' => (float) Payment::where('status', 'paid')->sum('amount'),
            'total_orders' => Order::count(),
            'avg_order_value' => Order::count() > 0 ? (float) (Payment::where('status', 'paid')->sum('amount') / max(1, Order::count())) : 0.0,
            'completion_rate' => Order::count() > 0 ? round((Order::where('order_status', 'completed')->count() / Order::count()) * 100, 1) : 0,
        ];

        return view('admin.reports', compact(
            'dailyDates',
            'dailyRevenue',
            'monthlyRevenue',
            'ordersByStatus',
            'jokiOrdersCount',
            'topupOrdersCount',
            'topGames',
            'summary'
        ));
    }

    /**
     * Audit Logs Viewer
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('action', 'like', "%{$s}%")
                    ->orWhere('details', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(20)->withQueryString();
        $distinctActions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit-logs', compact('logs', 'distinctActions'));
    }

    /**
     * Display Telegram API Integration configuration page.
     */
    public function telegram(TelegramService $telegramService)
    {
        $settings = $telegramService->getSettings();
        $connectionTest = ! empty($settings['bot_token'])
            ? $telegramService->testConnection($settings['bot_token'])
            : ['success' => false, 'message' => 'Token belum dikonfigurasi.', 'bot' => null];

        $recentChats = ! empty($settings['bot_token'])
            ? $telegramService->detectRecentChats($settings['bot_token'])
            : [];

        return view('admin.telegram', compact('settings', 'connectionTest', 'recentChats'));
    }

    /**
     * Update Telegram Bot API Token & notification settings.
     */
    public function updateTelegram(Request $request, TelegramService $telegramService)
    {
        $validated = $request->validate([
            'bot_token' => 'required|string|max:255',
            'chat_id' => 'required|string|max:100',
            'purchase_chat_id' => 'nullable|string|max:100',
            'joki_chat_id' => 'nullable|string|max:100',
            'enabled' => 'nullable|boolean',
        ]);

        $validated['enabled'] = $request->boolean('enabled');

        $oldSettings = $telegramService->getSettings();
        $updatedSettings = $telegramService->updateSettings($validated);

        // Record in audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_telegram_api',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => [
                'bot_token' => $oldSettings['masked_token'],
                'chat_id' => $oldSettings['chat_id'],
                'purchase_chat_id' => $oldSettings['purchase_chat_id'],
                'joki_chat_id' => $oldSettings['joki_chat_id'],
                'enabled' => $oldSettings['enabled'],
            ],
            'new_values' => [
                'bot_token' => $telegramService->maskToken($validated['bot_token']),
                'chat_id' => $validated['chat_id'],
                'purchase_chat_id' => $validated['purchase_chat_id'] ?? null,
                'joki_chat_id' => $validated['joki_chat_id'] ?? null,
                'enabled' => $validated['enabled'],
            ],
            'details' => 'Memperbarui konfigurasi API Telegram Bot & Chat ID notifikasi.',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Telegram API berhasil diperbarui!',
                'settings' => $updatedSettings,
            ]);
        }

        return redirect()->route('admin.telegram')->with('success', 'Konfigurasi Telegram API berhasil disimpan!');
    }

    /**
     * Test connection to Telegram Bot or send a test alert.
     */
    public function testTelegram(Request $request, TelegramService $telegramService)
    {
        $validated = $request->validate([
            'bot_token' => 'nullable|string|max:255',
            'chat_id' => 'nullable|string|max:100',
            'send_message' => 'nullable|boolean',
        ]);

        $token = ! empty($validated['bot_token']) ? $validated['bot_token'] : null;
        $chatId = ! empty($validated['chat_id']) ? $validated['chat_id'] : null;
        $sendMessage = $request->boolean('send_message');

        if ($sendMessage) {
            if (empty($chatId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target Chat ID harus diisi untuk mengirim pesan uji coba.',
                ], 422);
            }

            $result = $telegramService->sendTestNotification($chatId, $token);

            return response()->json($result);
        }

        $result = $telegramService->testConnection($token);

        return response()->json($result);
    }

    /**
     * Detect recent chats for the given bot token.
     */
    public function detectTelegramChats(Request $request, TelegramService $telegramService)
    {
        $token = $request->input('bot_token');
        $chats = $telegramService->detectRecentChats($token ?: null);

        return response()->json([
            'success' => true,
            'chats' => $chats,
            'count' => count($chats),
        ]);
    }
}
