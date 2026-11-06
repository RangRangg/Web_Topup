<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UploadProofRequest;
use App\Models\AuditLog;
use App\Models\JokiService;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display checkout page (kept for backwards compatibility, redirects to /joki/checkout).
     */
    public function checkout(JokiService $package): RedirectResponse
    {
        return redirect()->route('joki.checkout', ['service' => $package->slug]);
    }

    /**
     * Store order (legacy endpoint for universal store).
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('package_id')) {
            return redirect()->route('joki.checkout', ['service' => $request->package_id]);
        }

        return redirect()->route('checkout');
    }

    /**
     * Track order by code.
     */
    public function track(Request $request): View
    {
        $orderCode = $request->input('code');
        $order = null;

        if ($orderCode) {
            $order = Order::with(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'jokiProfile', 'payment'])
                ->where('order_code', strtoupper(trim($orderCode)))
                ->first();
        }

        return view('orders.track', compact('order', 'orderCode'));
    }

    /**
     * Display order details with server-side authorization check.
     */
    public function show(string $code, WhatsAppService $waService): View|RedirectResponse
    {
        $order = Order::with([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'jokiProfile.user',
            'payment',
            'user',
            'reviews.user',
        ])
            ->where('order_code', strtoupper(trim($code)))
            ->firstOrFail();

        // Enforce server-side authorization policy
        if (auth()->check()) {
            $this->authorize('view', $order);
        } else {
            // If order belongs to an authenticated user, require login to view
            if ($order->user_id !== null) {
                return redirect()->route('login')
                    ->with('info', 'Silakan login terlebih dahulu untuk mengakses detail pesanan ini.');
            }
        }

        $waUrls = [
            'admin' => $waService->getAdminRedirectUrl($order),
            'booster' => $waService->isJokiOrder($order) ? $waService->getBoosterRedirectUrl($order) : null,
            'customer_pending' => $waService->getCustomerPendingRedirectUrl($order),
            'customer_success' => $waService->getCustomerSuccessRedirectUrl($order),
            'customer_auto' => $waService->getCustomerAutoRedirectUrl($order),
            'admin_phone' => $waService->getAdminPhone(),
            'booster_phone' => $waService->getBoosterPhone(),
            'customer_phone' => $waService->getCustomerPhone($order),
            'is_joki' => $waService->isJokiOrder($order),
        ];

        return view('orders.show', compact('order', 'waUrls'));
    }

    /**
     * Redirect directly to WhatsApp with tailored prefilled message.
     */
    public function waRedirect(string $code, string $target, WhatsAppService $waService): RedirectResponse
    {
        $order = Order::with([
            'orderItems.jokiService.game',
            'orderItems.gameProduct.game',
            'payment',
        ])
            ->where('order_code', strtoupper(trim($code)))
            ->firstOrFail();

        $redirectUrl = match ($target) {
            'admin' => $waService->getAdminRedirectUrl($order),
            'booster' => $waService->getBoosterRedirectUrl($order),
            'customer-pending' => $waService->getCustomerPendingRedirectUrl($order),
            'customer-success' => $waService->getCustomerSuccessRedirectUrl($order),
            default => $waService->getCustomerAutoRedirectUrl($order),
        };

        return redirect()->away($redirectUrl);
    }

    /**
     * Submit review for a completed order.
     */
    public function storeReview(StoreReviewRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('review', $order);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $order, $request) {
            $firstItem = $order->orderItems->first();
            $gameName = $firstItem?->jokiService?->game?->name ?? $firstItem?->meta['game_name'] ?? 'Game';
            $serviceName = $firstItem?->item_name ?? 'Layanan Joki';

            $review = Review::create([
                'user_id' => auth()->id(),
                'order_id' => $order->id,
                'joki_profile_id' => $order->joki_profile_id,
                'customer_name' => auth()->user()?->name ?? $order->customer_name,
                'game_name' => $gameName,
                'service_name' => $serviceName,
                'rating' => (int) $validated['rating'],
                'comment' => $validated['comment'],
                'is_verified' => true,
            ]);

            // Recalculate booster rating if assigned
            if ($order->joki_profile_id && $order->jokiProfile) {
                $newRating = Review::where('joki_profile_id', $order->joki_profile_id)->avg('rating');
                if ($newRating) {
                    $order->jokiProfile->update([
                        'rating' => round($newRating, 2),
                    ]);
                }
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'review_submitted',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'order_code' => $order->order_code,
                    'rating' => $validated['rating'],
                    'review_id' => $review->id,
                ],
                'details' => "Review bintang {$validated['rating']} diberikan untuk order #{$order->order_code}.",
            ]);
        });

        return back()->with('success', 'Terima kasih! Ulasan bintang Anda berhasil disimpan.');
    }

    /**
     * Joki booster self-claims an available unassigned order.
     */
    public function claim(Order $order): RedirectResponse
    {
        $this->authorize('assign', $order);

        $user = auth()->user();
        $jokiProfile = $user->jokiProfile;

        if (! $jokiProfile) {
            return back()->with('error', 'Profil booster Anda belum terdaftar.');
        }

        $order->update([
            'joki_profile_id' => $jokiProfile->id,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'booster_claimed_order',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'new_values' => [
                'order_code' => $order->order_code,
                'joki_profile_id' => $jokiProfile->id,
                'order_status' => OrderStatus::PROCESSING->value,
            ],
            'details' => "Booster {$jokiProfile->nickname} mengambil pengerjaan order #{$order->order_code}.",
        ]);

        if ($order->user_id) {
            Notification::create([
                'user_id' => $order->user_id,
                'title' => 'Booster Telah Ditugaskan!',
                'message' => "Order #{$order->order_code} kini sedang dikerjakan oleh Booster {$jokiProfile->nickname}.",
                'type' => 'order',
                'link' => route('orders.show', $order->order_code),
            ]);
        }

        return back()->with('success', "Pesanan #{$order->order_code} berhasil Anda ambil! Silakan mulai proses pengerjaan.");
    }

    /**
     * Assigned booster (or admin) updates order progress (e.g. mark as completed).
     */
    public function updateProgress(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', 'in:processing,completed'],
        ]);

        $newStatus = $validated['status'];

        if ($newStatus === 'completed') {
            $order->update([
                'order_status' => OrderStatus::COMPLETED->value,
                'completed_at' => now(),
            ]);

            if ($order->jokiProfile) {
                $order->jokiProfile->increment('completed_orders_count');
            }
        } else {
            $order->update([
                'order_status' => $newStatus,
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'order_progress_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'order_code' => $order->order_code,
                'order_status' => $newStatus,
            ],
            'details' => "Status order #{$order->order_code} diperbarui menjadi {$newStatus}.",
        ]);

        if ($order->user_id) {
            $statusLabel = $newStatus === 'completed' ? 'Telah Selesai! Silakan cek akun Anda.' : 'Sedang Dikerjakan.';
            Notification::create([
                'user_id' => $order->user_id,
                'title' => "Update Order #{$order->order_code}",
                'message' => "Status pesanan joki Anda: {$statusLabel}",
                'type' => 'order',
                'link' => route('orders.show', $order->order_code),
            ]);
        }

        return back()->with('success', "Status pengerjaan order #{$order->order_code} berhasil diperbarui menjadi {$newStatus}.");
    }

    /**
     * Upload proof of transfer (for manual payment verification).
     */
    public function uploadProof(UploadProofRequest $request, string $code): RedirectResponse
    {
        $order = Order::with('payment')->where('order_code', strtoupper(trim($code)))->firstOrFail();

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('proofs', 'public');

            $isTopUp = $order->isTopUp();
            $newStatus = $isTopUp
                ? OrderStatus::COMPLETED->value
                : ($order->joki_profile_id ? OrderStatus::PROCESSING->value : OrderStatus::WAITING_ASSIGNMENT->value);

            $order->order_status = $newStatus;
            if ($isTopUp) {
                $order->completed_at = now();
            }
            $order->save();

            if ($order->payment) {
                $order->payment->update([
                    'status' => 'paid',
                    'payment_proof' => $path,
                    'paid_at' => now(),
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'payment_proof_uploaded',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => ['payment_status' => 'paid', 'order_status' => $newStatus],
                'details' => "Bukti pembayaran diunggah untuk order #{$order->order_code}.".($isTopUp ? ' Pesanan top up otomatis selesai dikirim.' : ''),
            ]);

            $successMsg = $isTopUp
                ? 'Bukti transfer berhasil diunggah! Pembayaran lunas dan diamond otomatis sukses terkirim ke akun Anda.'
                : 'Bukti transfer berhasil diunggah! Pesanan Anda kini menunggu alokasi booster.';

            return back()->with('success', $successMsg);
        }

        return back()->with('error', 'Gagal mengunggah file bukti pembayaran.');
    }
}
