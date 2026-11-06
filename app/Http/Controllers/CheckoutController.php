<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutRequest;
use App\Models\AuditLog;
use App\Models\GameProduct;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentServiceInterface $paymentService
    ) {}

    /**
     * Display checkout summary review page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $productId = $request->query('product_id');

        if (! $productId) {
            return redirect()->route('games')
                ->with('info', 'Silakan pilih game dan nominal produk terlebih dahulu.');
        }

        $product = GameProduct::with('game')
            ->where('is_active', true)
            ->findOrFail($productId);

        $quantity = max(1, (int) $request->query('quantity', 1));
        $paymentMethod = $request->query('payment_method', 'qris');

        // Always compute price strictly from database
        $unitPrice = (float) $product->price;
        $subtotal = $unitPrice * $quantity;
        $paymentFee = $this->paymentService->calculateFee($paymentMethod, $subtotal);
        $total = $subtotal + $paymentFee;

        $paymentMethods = $this->paymentService->getAvailableMethods();

        return view('checkout.index', [
            'product' => $product,
            'quantity' => $quantity,
            'paymentMethod' => $paymentMethod,
            'paymentMethods' => $paymentMethods,
            'unitPrice' => $unitPrice,
            'subtotal' => $subtotal,
            'paymentFee' => $paymentFee,
            'total' => $total,
            'accountId' => $request->query('account_id'),
            'accountServer' => $request->query('account_server'),
            'accountNickname' => $request->query('account_nickname'),
        ]);
    }

    /**
     * Process checkout submission and create order + payment inside a database transaction.
     */
    public function store(CheckoutRequest $request): RedirectResponse
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
                $q->where('game_product_id', $validated['product_id'])
                    ->where('quantity', $validated['quantity']);
            })
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('orders.show', $recentDuplicate->order_code)
                ->with('info', "Pesanan duplikat terdeteksi. Mengarahkan ke pesanan aktif #{$recentDuplicate->order_code}.");
        }

        try {
            $order = DB::transaction(function () use ($validated, $request, $idempotencyKey) {
                // 1. Strictly fetch product from database with row lock and ensure active
                $product = GameProduct::with('game')
                    ->where('id', $validated['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 2. Compute amounts securely on server
                $quantity = (int) $validated['quantity'];
                $unitPrice = (float) $product->price;
                $subtotal = $unitPrice * $quantity;

                // 3. Calculate payment fee
                $paymentFee = $this->paymentService->calculateFee($validated['payment_method'], $subtotal);

                // 4. Validate and apply promo code if present
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
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'order_status' => OrderStatus::PENDING->value,
                ]);

                // 7. Create Order Item record
                OrderItem::create([
                    'order_id' => $order->id,
                    'game_product_id' => $product->id,
                    'joki_service_id' => null,
                    'item_type' => 'product',
                    'item_name' => $product->name.' ('.$product->game->name.')',
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'meta' => [
                        'game_name' => $product->game->name,
                        'product_code' => $product->code,
                        'category' => $product->category,
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

                // 9. Record Audit Log for security tracking
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_order_created',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'order_code' => $order->order_code,
                        'product_id' => $product->id,
                        'subtotal' => $subtotal,
                        'fee' => $paymentFee,
                        'total' => $totalAmount,
                        'payment_status' => $payment->status,
                    ],
                    'details' => "Pesanan top up #{$order->order_code} untuk {$product->name} berhasil dibuat. Total: Rp ".number_format($totalAmount, 0, ',', '.').'.',
                ]);

                // 10. Record Notification if user is logged in
                if (auth()->check()) {
                    Notification::create([
                        'user_id' => auth()->id(),
                        'title' => "Pesanan #{$order->order_code} Dibuat",
                        'message' => 'Silakan selesaikan pembayaran Rp '.number_format($totalAmount, 0, ',', '.')." untuk memproses {$product->name}.",
                        'type' => 'order',
                        'link' => route('orders.show', $order->order_code),
                    ]);
                }

                return $order;
            });

            // Dispatch Telegram notification for newly created purchase order
            app(TelegramService::class)->notifyOrderCreated($order);

            return redirect()->route('orders.show', $order->order_code)
                ->with('success', "Pesanan #{$order->order_code} berhasil dibuat! Silakan selesaikan pembayaran sesuai instruksi.")
                ->with('whatsapp_redirect_url', route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'admin']));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Terjadi kendala saat memproses pesanan: '.$e->getMessage());
        }
    }

    /**
     * Simulate payment action for testing & verification.
     */
    public function simulatePay(Order $order): RedirectResponse
    {
        if (! $order->payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $this->paymentService->simulatePaymentSuccess($order->payment);
        $order->refresh();

        $message = $order->isTopUp()
            ? "Pembayaran berhasil diverifikasi secara otomatis! Pesanan top up #{$order->order_code} telah selesai dan diamond berhasil dikirim ke akun Anda."
            : "Pembayaran berhasil diverifikasi secara otomatis! Status order #{$order->order_code} kini aktif dan masuk antrean booster.";

        return redirect()->route('orders.show', $order->order_code)
            ->with('success', $message);
    }
}
