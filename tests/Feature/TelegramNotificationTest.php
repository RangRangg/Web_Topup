<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\GameProduct;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\MockPaymentService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Config::set('telegram.bot_token', '8981326385:AAHPaXTRjbX1eFfnlC_MtototXJnjtWQNSs');
        Config::set('telegram.chat_id', '123456789');
        Config::set('telegram.purchase_chat_id', '123456789');
        Config::set('telegram.joki_chat_id', '987654321');
        Config::set('telegram.enabled', true);
    }

    /**
     * Test purchase notification message content and structure.
     */
    public function test_purchase_order_message_content(): void
    {
        $service = app(TelegramService::class);
        $product = GameProduct::with('game')->firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-TOPUP01',
            'customer_name' => 'Budi Gamer',
            'customer_email' => 'budi@example.com',
            'customer_whatsapp' => '081234567890',
            'account_id' => '12345678',
            'account_server' => '2024',
            'account_nickname' => 'BudiGz',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'total_amount' => 50750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'game_product_id' => $product->id,
            'item_type' => 'product',
            'item_name' => $product->name,
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
            'meta' => ['game_name' => $product->game->name],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-TP01',
            'payment_method' => 'qris',
            'amount' => 50750,
            'status' => 'pending',
            'payload' => [],
        ]);

        $message = $service->buildPurchaseOrderMessage($order);

        $this->assertStringContainsString('PESANAN BARU: PEMBELIAN / TOP UP GAME', $message);
        $this->assertStringContainsString('AJ-TOPUP01', $message);
        $this->assertStringContainsString($product->name, $message);
        $this->assertStringContainsString('12345678', $message);
        $this->assertStringContainsString('BudiGz', $message);
        $this->assertStringContainsString('50.750', $message);
        $this->assertStringContainsString('081234567890', $message);
    }

    /**
     * Test joki notification message content and structure.
     */
    public function test_joki_order_message_content(): void
    {
        $service = app(TelegramService::class);
        $jokiService = JokiService::with('game')->firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-JOKI01',
            'customer_name' => 'Siti Pro',
            'customer_email' => 'siti@example.com',
            'customer_whatsapp' => '082233445566',
            'account_id' => '87654321',
            'account_server' => '2025',
            'account_nickname' => 'SitiQueen',
            'login_via' => 'Moonton ID',
            'account_notes' => 'Fokus hero Mage',
            'subtotal' => 150000,
            'discount_amount' => 0,
            'total_amount' => 151000,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'joki_service_id' => $jokiService->id,
            'item_type' => 'joki_service',
            'item_name' => $jokiService->name,
            'price' => 150000,
            'quantity' => 1,
            'subtotal' => 150000,
            'meta' => ['game_name' => $jokiService->game?->name],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-JK01',
            'payment_method' => 'bca',
            'amount' => 151000,
            'status' => 'pending',
            'payload' => [],
        ]);

        $message = $service->buildJokiOrderMessage($order);

        $this->assertStringContainsString('PESANAN BARU: JASA JOKI GAME', $message);
        $this->assertStringContainsString('AJ-JOKI01', $message);
        $this->assertStringContainsString($jokiService->name, $message);
        $this->assertStringContainsString('87654321', $message);
        $this->assertStringContainsString('Moonton ID', $message);
        $this->assertStringContainsString('Fokus hero Mage', $message);
        $this->assertStringContainsString('Komisi Booster', $message);
        $this->assertStringContainsString('082233445566', $message);
    }

    /**
     * Test payment success messages for both purchase and joki.
     */
    public function test_payment_success_messages(): void
    {
        $service = app(TelegramService::class);
        $product = GameProduct::with('game')->firstOrFail();

        $topupOrder = Order::create([
            'order_code' => 'AJ-PAID01',
            'customer_name' => 'Andi Lunas',
            'customer_email' => 'andi@example.com',
            'customer_whatsapp' => '081122334455',
            'account_id' => '99887766',
            'subtotal' => 30000,
            'discount_amount' => 0,
            'total_amount' => 30750,
            'order_status' => OrderStatus::COMPLETED->value,
        ]);

        OrderItem::create([
            'order_id' => $topupOrder->id,
            'game_product_id' => $product->id,
            'item_type' => 'product',
            'item_name' => $product->name,
            'price' => 30000,
            'quantity' => 1,
            'subtotal' => 30000,
            'meta' => ['game_name' => $product->game->name],
        ]);

        Payment::create([
            'order_id' => $topupOrder->id,
            'payment_code' => 'PAY-PAID01',
            'payment_method' => 'qris',
            'amount' => 30750,
            'status' => 'paid',
            'paid_at' => now(),
            'payload' => [],
        ]);

        $topupMsg = $service->buildPurchasePaymentSuccessMessage($topupOrder);
        $this->assertStringContainsString('PEMBAYARAN DITERIMA: TOP UP SELESAI', $topupMsg);
        $this->assertStringContainsString('AJ-PAID01', $topupMsg);
        $this->assertStringContainsString('SUKSES (Auto-Completed)', $topupMsg);
    }

    /**
     * Test sending Telegram HTTP request when order is placed.
     */
    public function test_send_message_dispatches_http_request(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $service = app(TelegramService::class);
        $result = $service->sendMessage('123456789', '<b>Hello World</b>');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage')
                && $request['chat_id'] === '123456789'
                && $request['parse_mode'] === 'HTML'
                && $request['text'] === '<b>Hello World</b>';
        });
    }

    /**
     * Test checkout flow sends Telegram notification for top-up order.
     */
    public function test_checkout_topup_sends_telegram_alert(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $product = GameProduct::where('is_active', true)->firstOrFail();

        $response = $this->post(route('checkout.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'payment_method' => 'qris',
            'account_id' => '100200300',
            'account_server' => '1234',
            'account_nickname' => 'ProGamerX',
            'customer_name' => 'Buyer One',
            'customer_email' => 'buyer1@example.com',
            'customer_whatsapp' => '081299998888',
        ]);

        $response->assertStatus(302);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage')
                && $request['chat_id'] === '123456789'
                && str_contains($request['text'], 'PEMBELIAN / TOP UP GAME')
                && str_contains($request['text'], '100200300');
        });
    }

    /**
     * Test joki checkout flow sends Telegram alert to joki chat ID.
     */
    public function test_checkout_joki_sends_telegram_alert(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $service = JokiService::where('is_active', true)->firstOrFail();

        $response = $this->post(route('joki.checkout.store'), [
            'service_id' => $service->id,
            'payment_method' => 'qris',
            'account_id' => '777888999',
            'account_server' => 'Asia',
            'account_nickname' => 'JokiCustomer',
            'login_via' => 'Moonton',
            'account_notes' => 'Mohon jangan main siang hari',
            'customer_name' => 'Buyer Joki',
            'customer_email' => 'buyerjoki@example.com',
            'customer_whatsapp' => '081388887777',
        ]);

        $response->assertStatus(302);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage')
                && $request['chat_id'] === '987654321'
                && str_contains($request['text'], 'JASA JOKI GAME')
                && str_contains($request['text'], '777888999');
        });
    }

    /**
     * Test payment simulation sends Telegram alert on payment success.
     */
    public function test_payment_success_triggers_telegram_alert(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $product = GameProduct::where('is_active', true)->firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-TESTPAY',
            'customer_name' => 'Pay User',
            'customer_email' => 'pay@example.com',
            'customer_whatsapp' => '081234567890',
            'account_id' => '555666',
            'subtotal' => 20000,
            'discount_amount' => 0,
            'total_amount' => 20750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'game_product_id' => $product->id,
            'item_type' => 'product',
            'item_name' => $product->name,
            'price' => 20000,
            'quantity' => 1,
            'subtotal' => 20000,
            'meta' => ['game_name' => $product->game->name],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-SIM01',
            'payment_method' => 'qris',
            'amount' => 20750,
            'status' => 'pending',
            'payload' => [],
        ]);

        $paymentService = app(MockPaymentService::class);
        $paymentService->simulatePaymentSuccess($payment);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sendMessage')
                && str_contains($request['text'], 'PEMBAYARAN DITERIMA: TOP UP SELESAI')
                && str_contains($request['text'], 'AJ-TESTPAY');
        });
    }
}
