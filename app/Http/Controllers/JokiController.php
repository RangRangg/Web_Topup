<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use App\Enums\OrderStatus;
use App\Http\Requests\JokiCheckoutRequest;
use App\Models\AuditLog;
use App\Models\Game;
use App\Models\JokiService;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\Review;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class JokiController extends Controller
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * Display listing of joki services with game, category, and search filters.
     */
    public function index(Request $request): View
    {
        $games = Game::where('is_active', true)->orderBy('order_priority')->get();

        $categories = [
            (object) ['slug' => 'rank', 'name' => 'Push Rank'],
            (object) ['slug' => 'mabar', 'name' => 'Joki Gendong / Mabar'],
            (object) ['slug' => 'winrate', 'name' => 'Farming Winrate'],
            (object) ['slug' => 'placement', 'name' => 'Placement Match'],
            (object) ['slug' => 'quest', 'name' => 'Quest & Event Clear'],
        ];

        $query = JokiService::with('game')
            ->where('is_active', true);

        if ($request->filled('game')) {
            $query->whereHas('game', function ($q) use ($request) {
                $q->where('slug', $request->game);
            });
        }

        if ($request->filled('category')) {
            $query->where('service_type', $request->category);
        } elseif ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('rank_from', 'like', "%{$s}%")
                    ->orWhere('rank_to', 'like', "%{$s}%");
            });
        }

        $packages = $query->paginate(9)->withQueryString();

        return view('joki.index', compact('packages', 'games', 'categories'));
    }

    /**
     * Display detailed joki service page with target, duration, price, and reviews.
     */
    public function show(JokiService $service): View
    {
        $service->load('game');

        $relatedPackages = JokiService::where('game_id', $service->game_id)
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->take(3)
            ->get();

        $reviews = Review::where('service_name', $service->name)
            ->orWhere(function ($q) use ($service) {
                if ($service->game) {
                    $q->where('game_name', $service->game->name);
                }
            })
            ->latest()
            ->take(6)
            ->get();

        $package = $service; // For backwards-compatible views

        return view('joki.show', compact('service', 'package', 'relatedPackages', 'reviews'));
    }

    /**
     * Display dedicated joki checkout form with live order calculations.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $serviceSlugOrId = $request->query('service') ?? $request->query('service_id') ?? $request->query('package');

        $service = null;
        if ($serviceSlugOrId) {
            $service = JokiService::with('game')
                ->where('is_active', true)
                ->where(function ($q) use ($serviceSlugOrId) {
                    $q->where('slug', $serviceSlugOrId)
                        ->orWhere('id', $serviceSlugOrId);
                })
                ->first();
        }

        if (! $service) {
            $service = JokiService::with('game')
                ->where('is_active', true)
                ->first();
        }

        if (! $service) {
            return redirect()->route('joki')
                ->with('error', 'Layanan joki belum tersedia.');
        }

        $allServices = JokiService::with('game')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $paymentMethods = $this->paymentService->getAvailableMethods();
        $promotions = Promotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->take(3)
            ->get();

        $package = $service;

        return view('joki.checkout', compact('service', 'package', 'allServices', 'paymentMethods', 'promotions'));
    }

    /**
     * Process joki checkout submission inside a secure database transaction.
     */
    public function processCheckout(JokiCheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $idempotencyKey = $validated['idempotency_key'] ?? null;

        // 1. Idempotency Check: if idempotency key provided and order exists, return it
        if ($idempotencyKey) {
            $existingOrder = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder) {
                return redirect()->route('orders.show', $existingOrder->order_code)
                    ->with('info', "Pesanan #{$existingOrder->order_code} telah diproses sebelumnya (Idempotent).");
            }
        }

        // 2. Duplicate order protection: detect rapid identical submission (within 15 seconds)
        $recentDuplicate = Order::where('customer_whatsapp', $validated['customer_whatsapp'])
            ->where('account_id', $validated['account_id'])
            ->where('order_status', OrderStatus::PENDING->value)
            ->where('created_at', '>=', now()->subSeconds(15))
            ->whereHas('orderItems', function ($q) use ($validated) {
                $q->where('joki_service_id', $validated['service_id']);
            })
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('orders.show', $recentDuplicate->order_code)
                ->with('info', "Pesanan duplikat terdeteksi. Mengarahkan ke pesanan aktif #{$recentDuplicate->order_code}.");
        }

        try {
            $order = DB::transaction(function () use ($validated, $request, $idempotencyKey) {
                // 1. Fetch joki service from database with row lock
                $service = JokiService::with('game')
                    ->where('id', $validated['service_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Compute prices strictly on server
                $unitPrice = (float) $service->price;
                $quantity = 1;
                $subtotal = $unitPrice * $quantity;

                // 3. Compute payment method administrative fee
                $paymentFee = $this->paymentService->calculateFee($validated['payment_method'], $subtotal);

                // 4. Validate and apply promo code if provided
                $discountAmount = 0.0;
                $promotionId = null;

                if (! empty($validated['promo_code'])) {
                    $promo = Promotion::where('code', strtoupper(trim($validated['promo_code'])))
                        ->where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                        })
                        ->first();

                    if ($promo && $subtotal >= (float) $promo->min_spend) {
                        $promotionId = $promo->id;

                        if ($promo->discount_type === 'percentage') {
                            $discountAmount = ($subtotal * (float) $promo->discount_value) / 100;
                            if ($promo->max_discount && $discountAmount > (float) $promo->max_discount) {
                                $discountAmount = (float) $promo->max_discount;
                            }
                        } else {
                            $discountAmount = min((float) $promo->discount_value, $subtotal);
                        }

                        $promo->increment('used_count');
                    }
                }

                $totalAmount = max(0, $subtotal + $paymentFee - $discountAmount);

                // 5. Generate unique Order Code
                do {
                    $orderCode = 'AJ-'.strtoupper(Str::random(6));
                } while (Order::where('order_code', $orderCode)->exists());

                // 6. Create Order record with PENDING status
                $order = Order::create([
                    'order_code' => $orderCode,
                    'idempotency_key' => $idempotencyKey ?? (string) Str::uuid(),
                    'user_id' => auth()->id(),
                    'joki_profile_id' => null,
                    'promotion_id' => $promotionId,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_whatsapp' => $validated['customer_whatsapp'],
                    'account_id' => $validated['account_id'],
                    'account_server' => $validated['account_server'] ?? null,
                    'account_nickname' => $validated['account_nickname'] ?? null,
                    'login_via' => $validated['login_via'],
                    'account_notes' => $validated['account_notes'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'order_status' => OrderStatus::PENDING->value,
                ]);

                // 7. Create OrderItem record for joki service
                OrderItem::create([
                    'order_id' => $order->id,
                    'game_product_id' => null,
                    'joki_service_id' => $service->id,
                    'item_type' => 'joki_service',
                    'item_name' => $service->name.' ('.($service->game?->name ?? 'Joki').')',
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'meta' => [
                        'game_name' => $service->game?->name,
                        'service_type' => $service->service_type,
                        'target' => $service->target,
                        'estimated_duration' => $service->estimated_duration,
                        'payment_fee' => $paymentFee,
                    ],
                ]);

                // 8. Create Payment record using PaymentServiceInterface
                $payment = $this->paymentService->createPayment(
                    $order,
                    $validated['payment_method'],
                    [
                        'payment_fee' => $paymentFee,
                        'user_ip' => $request->ip(),
                    ]
                );

                // 9. Record Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'joki_order_created',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'order_code' => $order->order_code,
                        'service_id' => $service->id,
                        'subtotal' => $subtotal,
                        'fee' => $paymentFee,
                        'total' => $totalAmount,
                        'payment_status' => $payment->status,
                    ],
                    'details' => "Order joki #{$order->order_code} untuk paket {$service->name} berhasil dibuat. Total: Rp ".number_format($totalAmount, 0, ',', '.').'.',
                ]);

                // 10. Record Notification if user is authenticated
                if (auth()->check()) {
                    Notification::create([
                        'user_id' => auth()->id(),
                        'title' => "Pesanan Joki #{$order->order_code} Dibuat",
                        'message' => 'Silakan selesaikan pembayaran Rp '.number_format($totalAmount, 0, ',', '.')." untuk pesanan {$service->name}.",
                        'type' => 'order',
                        'link' => route('orders.show', $order->order_code),
                    ]);
                }

                return $order;
            });

            // Dispatch Telegram notification for newly created joki order
            app(TelegramService::class)->notifyOrderCreated($order);

            return redirect()->route('orders.show', $order->order_code)
                ->with('success', "Pesanan joki #{$order->order_code} berhasil dibuat! Silakan selesaikan pembayaran untuk alokasi booster.")
                ->with('whatsapp_redirect_url', route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'admin']));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kendala saat memproses pesanan joki: '.$e->getMessage());
        }
    }
}
