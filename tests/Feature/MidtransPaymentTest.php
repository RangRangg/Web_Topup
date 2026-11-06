<?php

namespace Tests\Feature;

use App\Contracts\PaymentServiceInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Game;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\MockPaymentService;
use App\Services\Payment\MidtransPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test payment service driver resolution from configuration.
     */
    public function test_payment_service_resolves_configured_driver(): void
    {
        config(['services.payment.driver' => 'mock']);
        $service = app(PaymentServiceInterface::class);
        $this->assertInstanceOf(MockPaymentService::class, $service);

        config(['services.payment.driver' => 'midtrans']);
        $service = app(PaymentServiceInterface::class);
        $this->assertInstanceOf(MidtransPaymentService::class, $service);
    }

    /**
     * Test Midtrans fee calculations.
     */
    public function test_midtrans_fee_calculation(): void
    {
        $service = new MidtransPaymentService;
        $this->assertEquals(750.0, $service->calculateFee('qris', 50000));
        $this->assertEquals(2500.0, $service->calculateFee('bca', 50000));
        $this->assertEquals(1000.0, $service->calculateFee('dana', 50000));
    }

    /**
     * Test QRIS payment creation with mocked Midtrans Core API response.
     */
    public function test_midtrans_creates_qris_payment_via_core_api(): void
    {
        config([
            'services.payment.driver' => 'midtrans',
            'services.midtrans.server_key' => 'SB-Mid-server-TEST1234567890',
            'services.midtrans.is_production' => false,
        ]);

        $order = Order::firstOrFail();

        // Fake Midtrans Core API response for QRIS
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'QRIS transaction is generated',
                'transaction_id' => 'midtrans-trx-88992211',
                'order_id' => $order->order_code.'-1700000000',
                'gross_amount' => (string) round($order->total_amount),
                'currency' => 'IDR',
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => 'https://api.sandbox.midtrans.com/v2/qris/midtrans-trx-88992211/qr-code',
                    ],
                ],
                'qr_string' => '00020101021226590014ID.LINKAJA.WWW011893600911002000262102150001083905802115204549953033605405500005802ID5911Aethereal6007Jakarta6304C13F',
            ], 201),
        ]);

        $service = app(PaymentServiceInterface::class);
        $payment = $service->createPayment($order, 'qris');

        $this->assertEquals(PaymentStatus::PENDING->value, $payment->status);
        $this->assertEquals('qris', $payment->payment_method);
        $this->assertNotNull($payment->payload['qr_string']);
        $this->assertEquals('https://api.sandbox.midtrans.com/v2/qris/midtrans-trx-88992211/qr-code', $payment->payload['qr_url']);
    }

    /**
     * Test incoming Midtrans webhook updates order and payment to PAID when valid.
     */
    public function test_midtrans_webhook_successfully_settles_payment(): void
    {
        $serverKey = 'SB-Mid-server-TEST1234567890';
        config([
            'services.payment.driver' => 'midtrans',
            'services.midtrans.server_key' => $serverKey,
            'services.midtrans.is_production' => false,
        ]);

        $order = Order::create([
            'order_code' => 'AJ-TEST99',
            'idempotency_key' => 'midtrans-test-idempotency',
            'customer_name' => 'Budi Tester',
            'customer_email' => 'budi@test.com',
            'customer_whatsapp' => '081234567890',
            'account_id' => '12345678',
            'subtotal' => 50000,
            'total_amount' => 50750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        $game = Game::firstOrFail();
        $product = $game->gameProducts()->firstOrFail();

        OrderItem::create([
            'order_id' => $order->id,
            'game_product_id' => $product->id,
            'item_type' => 'product',
            'item_name' => 'Top Up Diamonds',
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-MIDTRANS99',
            'payment_method' => 'qris',
            'amount' => 50750,
            'status' => PaymentStatus::PENDING->value,
            'payload' => ['midtrans_order_id' => 'AJ-TEST99-1700000000'],
        ]);

        $orderId = 'AJ-TEST99-1700000000';
        $statusCode = '200';
        $grossAmount = '50750.00';
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-midtrans-unique-1234',
            'payment_type' => 'qris',
        ];

        $response = $this->postJson(route('webhooks.payment'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $payment->refresh();
        $order->refresh();

        $this->assertEquals(PaymentStatus::PAID->value, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->order_status);
        $this->assertNotNull($order->completed_at);
    }

    /**
     * Test incoming Midtrans webhook rejects invalid signature.
     */
    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        config([
            'services.payment.driver' => 'midtrans',
            'services.midtrans.server_key' => 'SB-Mid-server-REALKEY',
        ]);

        $payload = [
            'order_id' => 'AJ-INVALID-1700000000',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => 'invalidsignaturehash1234567890abcdef',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('webhooks.payment'), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'error' => 'Invalid Midtrans webhook signature.',
        ]);
    }
}
