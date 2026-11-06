<?php

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MockPaymentService implements PaymentServiceInterface
{
    /**
     * List of available payment methods with fees and metadata.
     */
    protected array $methods = [
        'qris' => [
            'name' => 'QRIS Instan (Semua E-Wallet & Mobile Banking)',
            'category' => 'qris',
            'fee' => 750,
            'fee_type' => 'flat',
            'badge' => 'REKOMENDASI 1-DETIK',
            'icon' => '⚡',
            'description' => 'Scan otomatis GoPay, OVO, Dana, ShopeePay, BCA, Livin Mandiri, BRImo',
        ],
        'bca' => [
            'name' => 'BCA Virtual Account',
            'category' => 'va',
            'fee' => 2500,
            'fee_type' => 'flat',
            'badge' => 'OTOMATIS 24 JAM',
            'icon' => '🏦',
            'description' => 'Konfirmasi instan via m-BCA, KlikBCA, atau ATM BCA',
        ],
        'mandiri' => [
            'name' => 'Mandiri Livin Virtual Account',
            'category' => 'va',
            'fee' => 2500,
            'fee_type' => 'flat',
            'badge' => 'OTOMATIS 24 JAM',
            'icon' => '🏦',
            'description' => 'Konfirmasi instan via aplikasi Livin by Mandiri & ATM Mandiri',
        ],
        'briva' => [
            'name' => 'BRI Virtual Account (BRIVA)',
            'category' => 'va',
            'fee' => 2500,
            'fee_type' => 'flat',
            'badge' => 'OTOMATIS 24 JAM',
            'icon' => '🏦',
            'description' => 'Konfirmasi instan via BRImo, Internet Banking, atau ATM BRI',
        ],
        'bni' => [
            'name' => 'BNI Virtual Account',
            'category' => 'va',
            'fee' => 2500,
            'fee_type' => 'flat',
            'badge' => 'OTOMATIS 24 JAM',
            'icon' => '🏦',
            'description' => 'Konfirmasi instan via BNI Mobile Banking & ATM BNI',
        ],
        'dana' => [
            'name' => 'DANA E-Wallet',
            'category' => 'ewallet',
            'fee' => 1000,
            'fee_type' => 'flat',
            'badge' => 'TERPOPULER',
            'icon' => '📱',
            'description' => 'Bayar mudah dan cepat langsung dari saldo akun DANA',
        ],
        'gopay' => [
            'name' => 'GoPay E-Wallet',
            'category' => 'ewallet',
            'fee' => 1000,
            'fee_type' => 'flat',
            'badge' => 'INSTAN',
            'icon' => '📱',
            'description' => 'Pembayaran terverifikasi kilat dengan aplikasi GoPay / Gojek',
        ],
        'ovo' => [
            'name' => 'OVO E-Wallet',
            'category' => 'ewallet',
            'fee' => 1000,
            'fee_type' => 'flat',
            'badge' => 'INSTAN',
            'icon' => '📱',
            'description' => 'Pembayaran instan langsung dari aplikasi dompet digital OVO',
        ],
        'shopeepay' => [
            'name' => 'ShopeePay',
            'category' => 'ewallet',
            'fee' => 1000,
            'fee_type' => 'flat',
            'badge' => 'INSTAN',
            'icon' => '📱',
            'description' => 'Bayar cepat dari aplikasi Shopee dengan saldo ShopeePay',
        ],
        'alfamart' => [
            'name' => 'Alfamart / Alfamidi',
            'category' => 'convenience_store',
            'fee' => 2500,
            'fee_type' => 'flat',
            'badge' => 'GERAI RITEL',
            'icon' => '🏪',
            'description' => 'Bayar tunai di kasir gerai Alfamart terdekat dengan kode bayar',
        ],
        'indomaret' => [
            'name' => 'Indomaret',
            'category' => 'convenience_store',
            'fee' => 2500,
            'fee_type' => 'flat',
            'badge' => 'GERAI RITEL',
            'icon' => '🏪',
            'description' => 'Bayar tunai di kasir gerai Indomaret terdekat dengan kode bayar',
        ],
    ];

    /**
     * Get list of all available payment methods.
     */
    public function getAvailableMethods(): array
    {
        return $this->methods;
    }

    /**
     * Calculate payment administrative fee.
     */
    public function calculateFee(string $paymentMethod, float $amount): float
    {
        if (! isset($this->methods[$paymentMethod])) {
            return 1000.0;
        }

        $method = $this->methods[$paymentMethod];

        if ($method['fee_type'] === 'flat') {
            return (float) $method['fee'];
        }

        if ($method['fee_type'] === 'percentage') {
            return round(($amount * $method['fee']) / 100, 2);
        }

        return 0.0;
    }

    /**
     * Create or initialize a payment record for an order.
     */
    public function createPayment(Order $order, string $paymentMethod, array $options = []): Payment
    {
        $methodKey = strtolower($paymentMethod);
        $methodInfo = $this->methods[$methodKey] ?? [
            'name' => strtoupper($paymentMethod),
            'category' => 'general',
            'fee' => 0,
        ];

        $paymentCode = 'PAY-'.strtoupper(Str::random(8));

        // Generate mock credentials based on channel
        $vaNumber = null;
        $qrString = null;

        if ($methodInfo['category'] === 'va') {
            $prefix = match ($methodKey) {
                'bca' => '88012',
                'mandiri' => '89012',
                'briva' => '12801',
                'bni' => '8277',
                default => '88012',
            };
            $vaNumber = $prefix.mt_rand(10000000, 99999999);
        } elseif ($methodInfo['category'] === 'convenience_store') {
            $prefix = ($methodKey === 'alfamart') ? 'ALFA' : 'INDO';
            $vaNumber = $prefix.mt_rand(10000000, 99999999);
        } elseif ($methodInfo['category'] === 'qris') {
            $qrString = '00020101021226500016ID.CO.AETHEREAL.WWW01189360000000000000000215'.$order->order_code.'5204581253033605802ID5914AETHEREAL JOKI6007JAKARTA6304'.strtoupper(Str::random(4));
        }

        $payload = [
            'method_name' => $methodInfo['name'],
            'category' => $methodInfo['category'] ?? 'other',
            'va_number' => $vaNumber,
            'qr_string' => $qrString,
            'expires_at' => now()->addHours(2)->toIso8601String(),
            'instructions' => $this->getInstructions($methodKey, $vaNumber),
            'meta' => $options,
        ];

        return Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_code' => $paymentCode,
                'payment_method' => $methodKey,
                'amount' => $order->total_amount,
                'status' => PaymentStatus::PENDING->value,
                'payload' => $payload,
            ]
        );
    }

    /**
     * Check payment status.
     */
    public function checkStatus(Payment $payment): array
    {
        return [
            'payment_code' => $payment->payment_code,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'method' => $payment->payment_method,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ];
    }

    /**
     * Generate HMAC-SHA256 signature for webhook payload.
     */
    public function generateWebhookSignature(string|array $payload): string
    {
        if (is_array($payload)) {
            unset($payload['signature']);
            ksort($payload);
            $raw = json_encode($payload);
        } else {
            $raw = $payload;
        }

        $secret = config('services.payment.webhook_secret', 'aethereal-secure-webhook-secret-key-2026');

        return hash_hmac('sha256', $raw, $secret);
    }

    /**
     * Verify authenticity of incoming webhook payload signature.
     */
    public function verifyWebhookSignature(string|array $payload, string $signature): bool
    {
        $expected = $this->generateWebhookSignature($payload);
        if (hash_equals($expected, $signature)) {
            return true;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (is_array($decoded)) {
                $expectedDecoded = $this->generateWebhookSignature($decoded);
                if (hash_equals($expectedDecoded, $signature)) {
                    return true;
                }
            }
        } elseif (is_array($payload)) {
            $raw = json_encode($payload);
            $expectedRaw = $this->generateWebhookSignature($raw);
            if (hash_equals($expectedRaw, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process incoming webhook notification idempotently and securely.
     */
    public function processWebhook(array $payload, ?string $rawPayload = null, ?string $signature = null): array
    {
        // 1. Signature Verification
        if ($signature !== null) {
            $dataToVerify = $rawPayload ?? $payload;
            if (! $this->verifyWebhookSignature($dataToVerify, $signature)) {
                return [
                    'success' => false,
                    'status_code' => 403,
                    'error' => 'Invalid webhook signature.',
                ];
            }
        }

        $orderCode = $payload['order_code'] ?? $payload['order_id'] ?? null;
        if ($orderCode && str_contains($orderCode, '-')) {
            $parts = explode('-', $orderCode);
            if (count($parts) >= 2 && $parts[0] === 'AJ') {
                $orderCode = $parts[0].'-'.$parts[1];
            }
        }
        $paymentCode = $payload['payment_code'] ?? null;
        $incomingAmount = isset($payload['amount']) ? (float) $payload['amount'] : (isset($payload['gross_amount']) ? (float) $payload['gross_amount'] : null);
        $transactionStatus = strtolower($payload['transaction_status'] ?? $payload['status'] ?? 'pending');
        $eventId = $payload['event_id'] ?? $payload['transaction_id'] ?? null;

        // 2. Locate Payment and Order
        $payment = null;
        if ($paymentCode) {
            $payment = Payment::with('order')->where('payment_code', $paymentCode)->first();
        } elseif ($orderCode) {
            $order = Order::with('payment')->where('order_code', strtoupper(trim($orderCode)))->first();
            $payment = $order?->payment;
        }

        if (! $payment || ! $payment->order) {
            return [
                'success' => false,
                'status_code' => 404,
                'error' => 'Payment or associated order not found.',
            ];
        }

        $order = $payment->order;

        // 3. Prevent Nominal Manipulation (Server-side validation)
        if ($incomingAmount !== null && abs((float) $payment->amount - $incomingAmount) > 0.01) {
            AuditLog::create([
                'user_id' => $order->user_id,
                'action' => 'webhook_tamper_attempt',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'PaymentWebhook',
                'details' => "Peringatan keamanan: Upaya manipulasi nominal webhook pada order #{$order->order_code}. Nominal payload (Rp {$incomingAmount}) berbeda dengan tagihan (Rp {$payment->amount}).",
            ]);

            return [
                'success' => false,
                'status_code' => 422,
                'error' => 'Amount mismatch: payment nominal cannot be altered by client.',
            ];
        }

        // 4. Idempotency Check
        $targetPaymentStatus = match ($transactionStatus) {
            'settlement', 'capture', 'success', 'paid' => PaymentStatus::PAID->value,
            'deny', 'cancel', 'failed' => PaymentStatus::FAILED->value,
            'expire', 'expired' => PaymentStatus::EXPIRED->value,
            default => PaymentStatus::PENDING->value,
        };

        if ($payment->status === $targetPaymentStatus) {
            return [
                'success' => true,
                'status_code' => 200,
                'message' => 'Notification already processed (idempotent).',
                'is_idempotent' => true,
                'payment_code' => $payment->payment_code,
                'order_code' => $order->order_code,
                'payment_status' => $payment->status,
                'order_status' => $order->order_status,
            ];
        }

        // 5. Atomic State Transition inside Database Transaction
        DB::transaction(function () use ($payment, $order, $targetPaymentStatus, $eventId) {
            $oldPaymentStatus = $payment->status;
            $oldOrderStatus = $order->order_status;

            // Merge webhook payload metadata into payment payload
            $existingPayload = is_array($payment->payload) ? $payment->payload : [];
            $existingPayload['webhook_history'][] = [
                'event_id' => $eventId,
                'received_at' => now()->toIso8601String(),
                'status' => $targetPaymentStatus,
            ];

            $updateData = [
                'status' => $targetPaymentStatus,
                'payload' => $existingPayload,
            ];

            if ($targetPaymentStatus === PaymentStatus::PAID->value) {
                $updateData['paid_at'] = now();
            }

            $payment->update($updateData);

            // Update order status accordingly: Auto-complete for Top Up, assignment queue for Joki
            if ($targetPaymentStatus === PaymentStatus::PAID->value) {
                $isTopUp = $order->isTopUp();

                $newOrderStatus = $isTopUp
                    ? OrderStatus::COMPLETED->value
                    : ($order->joki_profile_id ? OrderStatus::PROCESSING->value : OrderStatus::WAITING_ASSIGNMENT->value);

                $orderUpdate = ['order_status' => $newOrderStatus];
                if ($isTopUp) {
                    $orderUpdate['completed_at'] = now();
                }

                $order->update($orderUpdate);

                if ($order->user_id) {
                    Notification::create([
                        'user_id' => $order->user_id,
                        'title' => $isTopUp ? 'Top Up Diamond Berhasil Masuk!' : 'Pembayaran Berhasil Dikonfirmasi!',
                        'message' => $isTopUp
                            ? "Pembayaran order #{$order->order_code} telah lunas dan diamond otomatis sukses terkirim ke akun {$order->account_id}."
                            : "Pembayaran untuk order #{$order->order_code} senilai {$order->formatted_total} telah berhasil diverifikasi via payment gateway.",
                        'type' => 'order',
                        'link' => route('orders.show', $order->order_code),
                    ]);
                }
            } elseif (in_array($targetPaymentStatus, [PaymentStatus::FAILED->value, PaymentStatus::EXPIRED->value])) {
                $order->update(['order_status' => OrderStatus::CANCELLED->value]);
            }

            AuditLog::create([
                'user_id' => $order->user_id,
                'action' => 'payment_webhook_processed',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'PaymentGatewayWebhook',
                'old_values' => [
                    'payment_status' => $oldPaymentStatus,
                    'order_status' => $oldOrderStatus,
                ],
                'new_values' => [
                    'payment_status' => $targetPaymentStatus,
                    'order_status' => $order->order_status,
                    'event_id' => $eventId,
                ],
                'details' => "Webhook payment diproses untuk #{$payment->payment_code} (Order #{$order->order_code}). Status: {$targetPaymentStatus}.",
            ]);
        });

        $payment->refresh();
        $order->refresh();

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'Payment webhook processed successfully.',
            'is_idempotent' => false,
            'payment_code' => $payment->payment_code,
            'order_code' => $order->order_code,
            'payment_status' => $payment->status,
            'order_status' => $order->order_status,
        ];
    }

    /**
     * Simulate payment success transition.
     */
    public function simulatePaymentSuccess(Payment $payment): Payment
    {
        $payment->update([
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        $order = $payment->order;
        if ($order) {
            $isTopUp = $order->isTopUp();

            $newStatus = $isTopUp
                ? OrderStatus::COMPLETED->value
                : ($order->joki_profile_id ? OrderStatus::PROCESSING->value : OrderStatus::WAITING_ASSIGNMENT->value);

            $orderUpdate = ['order_status' => $newStatus];
            if ($isTopUp) {
                $orderUpdate['completed_at'] = now();
            }

            $order->update($orderUpdate);

            AuditLog::create([
                'user_id' => auth()->id() ?? $order->user_id,
                'action' => 'payment_paid',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'MockPaymentService',
                'new_values' => [
                    'payment_status' => PaymentStatus::PAID->value,
                    'order_status' => $order->order_status,
                ],
                'details' => $isTopUp
                    ? "Pembayaran #{$payment->payment_code} untuk order top up #{$order->order_code} lunas. Pesanan otomatis diselesaikan (auto-completed) & diamond berhasil terkirim."
                    : "Pembayaran #{$payment->payment_code} untuk order #{$order->order_code} berhasil diselesaikan.",
            ]);

            // Dispatch Telegram notification for successful payment
            app(TelegramService::class)->notifyPaymentSuccess($order);
        }

        return $payment->fresh();
    }

    /**
     * Simulate payment failure transition.
     */
    public function simulatePaymentFailure(Payment $payment): Payment
    {
        $payment->update([
            'status' => PaymentStatus::FAILED->value,
        ]);

        $order = $payment->order;
        if ($order) {
            $order->update([
                'order_status' => OrderStatus::CANCELLED->value,
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? $order->user_id,
                'action' => 'payment_failed',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'MockPaymentService',
                'new_values' => ['payment_status' => PaymentStatus::FAILED->value],
                'details' => "Pembayaran #{$payment->payment_code} gagal diproses oleh gateway.",
            ]);
        }

        return $payment->fresh();
    }

    /**
     * Simulate payment expiration transition.
     */
    public function simulatePaymentExpired(Payment $payment): Payment
    {
        $payment->update([
            'status' => PaymentStatus::EXPIRED->value,
        ]);

        $order = $payment->order;
        if ($order) {
            $order->update([
                'order_status' => OrderStatus::CANCELLED->value,
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? $order->user_id,
                'action' => 'payment_expired',
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'MockPaymentService',
                'new_values' => ['payment_status' => PaymentStatus::EXPIRED->value],
                'details' => "Pembayaran #{$payment->payment_code} telah melewati batas waktu dan kedaluwarsa.",
            ]);
        }

        return $payment->fresh();
    }

    /**
     * Helper to get instructions text for mock payload.
     */
    protected function getInstructions(string $method, ?string $vaNumber): array
    {
        return match ($method) {
            'qris' => [
                'Buka aplikasi e-wallet (GoPay, OVO, Dana, ShopeePay) atau mobile banking pilihan Anda.',
                'Pilih menu Scan / Bayar QRIS.',
                'Arahkan kamera ke kode QR yang tertera di layar.',
                'Periksa detail nama merchant: Aethereal Joki.',
                'Masukkan PIN Anda dan pembayaran akan diverifikasi secara otomatis dalam 1 detik.',
            ],
            'bca' => [
                'Buka aplikasi m-BCA (BCA Mobile) atau KlikBCA.',
                'Pilih menu m-Transfer > BCA Virtual Account.',
                'Masukkan nomor Virtual Account: '.($vaNumber ?? '88012XXXXXXXX'),
                'Pastikan nominal transfer sesuai dengan total tagihan.',
                'Masukkan PIN m-BCA Anda. Transaksi selesai otomatis.',
            ],
            'mandiri' => [
                'Buka aplikasi Livin by Mandiri.',
                'Pilih menu Bayar > Buat Pembayaran Baru > Multipayment.',
                'Pilih penyedia jasa Aethereal Joki atau masukkan kode VA: '.($vaNumber ?? '89012XXXXXXXX'),
                'Periksa nama dan jumlah nominal yang tertera.',
                'Konfirmasi dengan MPIN Livin Anda.',
            ],
            'briva' => [
                'Buka aplikasi BRImo atau ATM BRI.',
                'Pilih menu Pembayaran > BRIVA (BRI Virtual Account).',
                'Masukkan nomor BRIVA: '.($vaNumber ?? '12801XXXXXXXX'),
                'Periksa kesesuaian nama tagihan dan total pembayaran.',
                'Masukkan PIN BRImo Anda. Transaksi selesai otomatis.',
            ],
            'bni' => [
                'Buka aplikasi BNI Mobile Banking atau ATM BNI.',
                'Pilih menu Transfer > Virtual Account Billing.',
                'Masukkan nomor Virtual Account: '.($vaNumber ?? '8277XXXXXXXX'),
                'Periksa informasi tagihan dan konfirmasi dengan Password Transaksi.',
            ],
            'dana', 'gopay', 'ovo', 'shopeepay' => [
                'Buka aplikasi dompet digital Anda.',
                'Konfirmasi pembayaran untuk tagihan pesanan Aethereal Joki.',
                'Pastikan saldo mencukupi dan masukkan PIN keamanan.',
            ],
            'alfamart', 'indomaret' => [
                'Kunjungi gerai '.ucfirst($method).' terdekat.',
                'Beritahu kasir ingin melakukan pembayaran merchant Aethereal Joki.',
                'Tunjukkan kode pembayaran kasir: '.($vaNumber ?? 'KODE-BAYAR-XXXXXXXX'),
                'Lakukan pembayaran sesuai tagihan dan simpan struk kasir.',
            ],
            default => [
                'Lakukan transfer sesuai instruksi ke rekening yang tertera.',
                'Simpan bukti transfer dan unggah pada halaman pelacakan pesanan.',
            ],
        };
    }
}
