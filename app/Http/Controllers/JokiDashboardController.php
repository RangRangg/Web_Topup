<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\JokiProgressRequest;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JokiDashboardController extends Controller
{
    /**
     * Get or create the booster's JokiProfile defensively.
     */
    protected function getBoosterProfile()
    {
        $user = Auth::user();

        return $user->jokiProfile ?? $user->jokiProfile()->create([
            'nickname' => $user->name,
            'phone' => $user->phone,
            'status' => 'active',
            'rating' => 5.0,
            'completed_orders_count' => 0,
            'is_verified' => true,
        ]);
    }

    /**
     * Display booster workstation overview dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        // 1. Available Orders in pool (Unassigned & Paid/Waiting Assignment)
        $availableOrdersQuery = Order::with(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'payment', 'user'])
            ->whereNull('joki_profile_id')
            ->whereIn('order_status', [
                OrderStatus::WAITING_ASSIGNMENT->value,
                OrderStatus::PAID->value,
                'waiting_assignment',
                'paid',
            ]);

        $availableOrdersCount = $availableOrdersQuery->count();
        $recentAvailableOrders = (clone $availableOrdersQuery)->latest()->take(5)->get();

        // 2. Active Orders assigned to this booster
        $activeOrdersQuery = Order::with(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'payment', 'user'])
            ->where('joki_profile_id', $jokiProfile->id)
            ->whereIn('order_status', [
                OrderStatus::PROCESSING->value,
                OrderStatus::WAITING_ASSIGNMENT->value,
                'processing',
                'waiting_assignment',
            ]);

        $activeOrdersCount = $activeOrdersQuery->count();
        $recentAssignedOrders = (clone $activeOrdersQuery)->latest()->take(5)->get();

        // 3. Completed Orders by this booster
        $completedOrders = Order::with(['orderItems.jokiService.game', 'payment'])
            ->where('joki_profile_id', $jokiProfile->id)
            ->where('order_status', OrderStatus::COMPLETED->value)
            ->get();

        $completedOrdersCount = $completedOrders->count();

        // 4. Total Net Earnings (80% net split for pro booster)
        $grossRevenue = (float) $completedOrders->sum('total_amount');
        $totalEarnings = $grossRevenue * 0.80;

        // Potential active earnings
        $potentialEarnings = (float) $activeOrdersQuery->sum('total_amount') * 0.80;

        return view('joki.dashboard.index', compact(
            'user',
            'jokiProfile',
            'availableOrdersCount',
            'activeOrdersCount',
            'completedOrdersCount',
            'totalEarnings',
            'grossRevenue',
            'potentialEarnings',
            'recentAssignedOrders',
            'recentAvailableOrders'
        ));
    }

    /**
     * Display booster orders management (Assigned & Pool).
     */
    public function orders(Request $request): View
    {
        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        $tab = $request->query('tab', 'assigned'); // 'assigned' or 'available'
        $search = trim($request->query('search', ''));
        $status = $request->query('status', 'all');

        // Available Pool Query
        $availableQuery = Order::with(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'payment', 'user'])
            ->whereNull('joki_profile_id')
            ->whereIn('order_status', [
                OrderStatus::WAITING_ASSIGNMENT->value,
                OrderStatus::PAID->value,
                'waiting_assignment',
                'paid',
            ]);

        // Assigned Orders Query
        $assignedQuery = Order::with(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'payment', 'user'])
            ->where('joki_profile_id', $jokiProfile->id);

        if ($status !== 'all') {
            $assignedQuery->where('order_status', $status);
        }

        // Search filters
        if (! empty($search)) {
            $searchClosure = function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('account_id', 'like', "%{$search}%")
                    ->orWhere('account_nickname', 'like', "%{$search}%")
                    ->orWhereHas('orderItems', function ($itemQ) use ($search) {
                        $itemQ->where('item_name', 'like', "%{$search}%");
                    });
            };

            $assignedQuery->where($searchClosure);
            $availableQuery->where($searchClosure);
        }

        $assignedOrders = $assignedQuery->latest()->paginate(10, ['*'], 'assigned_page')->withQueryString();
        $availableOrders = $availableQuery->latest()->paginate(10, ['*'], 'available_page')->withQueryString();

        $assignedCount = Order::where('joki_profile_id', $jokiProfile->id)->count();
        $availableCount = Order::whereNull('joki_profile_id')
            ->whereIn('order_status', [
                OrderStatus::WAITING_ASSIGNMENT->value,
                OrderStatus::PAID->value,
                'waiting_assignment',
                'paid',
            ])->count();

        return view('joki.dashboard.orders', compact(
            'user',
            'jokiProfile',
            'assignedOrders',
            'availableOrders',
            'tab',
            'assignedCount',
            'availableCount'
        ));
    }

    /**
     * Display order detail with booster controls and policy authorization.
     */
    public function showOrder(Order $order): View
    {
        // Enforce Laravel Policy authorization:
        // Booster can view if assigned to them, or if available in pool.
        // Booster CANNOT view orders assigned to another booster (403 Forbidden).
        $this->authorize('view', $order);

        $jokiProfile = $this->getBoosterProfile();

        $order->load([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'payment',
            'user',
            'reviews',
            'jokiProfile',
        ]);

        $isAssignedToMe = $order->joki_profile_id === $jokiProfile->id;
        $isAvailableInPool = is_null($order->joki_profile_id) && in_array($order->order_status, [
            OrderStatus::WAITING_ASSIGNMENT->value,
            OrderStatus::PAID->value,
            'waiting_assignment',
            'paid',
        ]);

        $firstItem = $order->orderItems->first();
        $gameName = $firstItem?->jokiService?->game?->name
            ?? $firstItem?->gameProduct?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';
        $waService = app(WhatsAppService::class);
        $customerPhone = $waService->getCustomerPhone($order);
        $clientHelpMessage = "Halo {$order->customer_name}, saya booster Aethereal Joki untuk order #{$order->order_code}. Mohon konfirmasi kode verifikasi/keamanan untuk login akun {$gameName}. Terima kasih.";
        $adminReportMessage = "Halo Admin Aethereal Joki, saya booster {$jokiProfile->nickname} ingin melaporkan progres order #{$order->order_code} ({$gameName}).";

        $waUrls = [
            'client' => $waService->generateUrl($customerPhone, $clientHelpMessage),
            'admin' => $waService->generateUrl($waService->getAdminPhone(), $adminReportMessage),
            'client_phone' => $customerPhone,
            'admin_phone' => $waService->getAdminPhone(),
        ];

        return view('joki.dashboard.order-detail', compact(
            'order',
            'jokiProfile',
            'isAssignedToMe',
            'isAvailableInPool',
            'gameName',
            'waUrls'
        ));
    }

    /**
     * Booster accepts / claims an available order from pool.
     */
    public function claimOrder(Request $request, Order $order): RedirectResponse
    {
        // Enforce Laravel Policy authorization: Order must be unassigned and in waiting_assignment/paid
        $this->authorize('assign', $order);

        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        $oldStatus = $order->order_status;

        $order->update([
            'joki_profile_id' => $jokiProfile->id,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        // Audit Log for status change
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'booster_claimed_order',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => [
                'order_status' => $oldStatus,
                'joki_profile_id' => null,
            ],
            'new_values' => [
                'order_status' => OrderStatus::PROCESSING->value,
                'joki_profile_id' => $jokiProfile->id,
            ],
            'details' => "Booster {$jokiProfile->nickname} (#{$jokiProfile->id}) mengambil order #{$order->order_code} dari antrean pool.",
        ]);

        // Customer notification
        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'title' => 'Pro Booster Telah Ditugaskan!',
                'message' => "Order #{$order->order_code} kini sedang diproses oleh Pro Booster {$jokiProfile->nickname}.",
                'type' => 'order',
                'link' => route('dashboard.orders.show', $order->order_code),
            ]);
        }

        return redirect()->route('joki.dashboard.orders.show', $order->order_code)
            ->with('success', "Order #{$order->order_code} berhasil Anda ambil! Segera mulai pengerjaan target rank.");
    }

    /**
     * Booster updates progress status (PROCESSING / COMPLETED) and progress note.
     */
    public function updateProgress(JokiProgressRequest $request, Order $order): RedirectResponse
    {
        // Enforce Laravel Policy authorization: Must be assigned to this booster
        $this->authorize('update', $order);

        $validated = $request->validated();

        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        $oldStatus = $order->order_status;
        $oldNote = $order->progress_note;
        $newStatus = $validated['order_status'];
        $newNote = $validated['progress_note'] ?? $order->progress_note;

        $updateData = [
            'order_status' => $newStatus,
            'progress_note' => $newNote,
        ];

        if ($newStatus === 'completed' && ! $order->completed_at) {
            $updateData['completed_at'] = now();
            $jokiProfile->increment('completed_orders_count');
        }

        $order->update($updateData);

        // Audit Log for status update
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'booster_order_status_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => [
                'order_status' => $oldStatus,
                'progress_note' => $oldNote,
            ],
            'new_values' => [
                'order_status' => $newStatus,
                'progress_note' => $newNote,
            ],
            'details' => "Booster {$jokiProfile->nickname} memperbarui pesanan #{$order->order_code} menjadi '{$newStatus}' dengan progress note.",
        ]);

        // Customer notification
        if ($order->user_id) {
            $statusLabel = $newStatus === 'completed'
                ? 'Telah Selesai! Target pengerjaan joki telah tercapai.'
                : 'Sedang Berlangsung. Pro booster sedang aktif bermain.';

            Notification::create([
                'user_id' => $order->user_id,
                'title' => "Update Pengerjaan Order #{$order->order_code}",
                'message' => "Status: {$statusLabel}".($newNote ? " — Catatan: \"{$newNote}\"" : ''),
                'type' => 'order',
                'link' => route('dashboard.orders.show', $order->order_code),
            ]);
        }

        return back()->with('success', "Progres order #{$order->order_code} berhasil diperbarui menjadi {$newStatus}!");
    }

    /**
     * Display booster profile settings.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        return view('joki.dashboard.profile', compact('user', 'jokiProfile'));
    }

    /**
     * Update booster profile settings.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        $validated = $request->validate([
            'nickname' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,resting,busy'],
            'highest_rank' => ['nullable', 'string', 'max:100'],
            'specialized_games' => ['nullable', 'array'],
        ], [
            'nickname.required' => 'Nickname booster wajib diisi.',
            'status.required' => 'Status keaktifan wajib dipilih.',
            'status.in' => 'Status keaktifan tidak valid.',
        ]);

        $jokiProfile->update([
            'nickname' => $validated['nickname'],
            'phone' => $validated['phone'],
            'bio' => $validated['bio'],
            'status' => $validated['status'],
            'highest_rank' => $validated['highest_rank'],
            'specialized_games' => $validated['specialized_games'] ?? [],
        ]);

        if (! empty($validated['phone'])) {
            $user->update(['phone' => $validated['phone']]);
        }

        return back()->with('success', 'Profil pro booster berhasil diperbarui!');
    }

    /**
     * Display booster financial earnings & history page.
     */
    public function earnings(): View
    {
        $user = Auth::user();
        $jokiProfile = $this->getBoosterProfile();

        // Completed orders
        $completedOrders = Order::with(['orderItems.jokiService.game', 'payment', 'user'])
            ->where('joki_profile_id', $jokiProfile->id)
            ->where('order_status', OrderStatus::COMPLETED->value)
            ->latest()
            ->paginate(15);

        // All-time completed orders calculation
        $allCompleted = Order::where('joki_profile_id', $jokiProfile->id)
            ->where('order_status', OrderStatus::COMPLETED->value)
            ->get();

        $grossRevenue = (float) $allCompleted->sum('total_amount');
        $totalEarnings = $grossRevenue * 0.80; // 80% booster cut

        // Active pending earnings
        $activeOrders = Order::where('joki_profile_id', $jokiProfile->id)
            ->whereIn('order_status', [OrderStatus::PROCESSING->value, 'processing'])
            ->get();

        $pendingPotentialEarnings = (float) $activeOrders->sum('total_amount') * 0.80;

        return view('joki.dashboard.earnings', compact(
            'user',
            'jokiProfile',
            'completedOrders',
            'grossRevenue',
            'totalEarnings',
            'pendingPotentialEarnings'
        ));
    }
}
