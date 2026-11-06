<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\GameProduct;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test phone number formatting rules for WhatsApp API.
     */
    public function test_whatsapp_service_formats_phone_numbers_correctly(): void
    {
        $service = app(WhatsAppService::class);

        // Standard Indonesian phone numbers
        $this->assertEquals('6281384849982', $service->formatPhoneNumber('081384849982'));
        $this->assertEquals('6282298561286', $service->formatPhoneNumber('082298561286'));
        $this->assertEquals('6285147486281', $service->formatPhoneNumber('085147486281'));

        // Numbers with formatting symbols
        $this->assertEquals('6281384849982', $service->formatPhoneNumber('+62 813-8484-9982'));
        $this->assertEquals('6282298561286', $service->formatPhoneNumber('0822-9856-1286'));
        $this->assertEquals('6285147486281', $service->formatPhoneNumber('85147486281'));

        // Config getters
        $this->assertEquals('6281384849982', $service->getAdminPhone());
        $this->assertEquals('6282298561286', $service->getBoosterPhone());
    }

    /**
     * Test WhatsApp Admin notification for top-up / DM purchases.
     */
    public function test_whatsapp_admin_notification_for_topup_order(): void
    {
        $service = app(WhatsAppService::class);
        $product = GameProduct::with('game')->firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-DMTEST',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_whatsapp' => '085147486281',
            'account_id' => '12345678',
            'account_server' => '2024',
            'account_nickname' => 'ProPlayer',
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
            'payment_code' => 'PAY-TEST01',
            'payment_method' => 'qris',
            'amount' => 50750,
            'status' => 'pending',
            'payload' => [],
        ]);

        $message = $service->buildAdminNotificationMessage($order);
        $this->assertStringContainsString('Halo Admin Aethereal Joki', $message);
        $this->assertStringContainsString('AJ-DMTEST', $message);
        $this->assertStringContainsString('12345678', $message);
        $this->assertStringContainsString('50.750', $message);
        $this->assertStringContainsString('085147486281', $message);

        $url = $service->getAdminRedirectUrl($order);
        $this->assertStringStartsWith('https://wa.me/6281384849982?text=', $url);
    }

    /**
     * Test WhatsApp Booster notification for Joki service orders.
     */
    public function test_whatsapp_booster_notification_for_joki_order(): void
    {
        $service = app(WhatsAppService::class);
        $jokiService = JokiService::with('game')->firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-JOKITEST',
            'customer_name' => 'Dimas Maulana',
            'customer_email' => 'dimas@example.com',
            'customer_whatsapp' => '085147486281',
            'account_id' => '98765432',
            'account_server' => '2026',
            'account_nickname' => 'MythicSeeker',
            'login_via' => 'Moonton',
            'account_notes' => 'Tolong main malam hari',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'total_amount' => 101000,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'joki_service_id' => $jokiService->id,
            'item_type' => 'joki_service',
            'item_name' => $jokiService->name,
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
            'meta' => ['game_name' => $jokiService->game?->name],
        ]);

        $this->assertTrue($service->isJokiOrder($order));

        $boosterMessage = $service->buildBoosterNotificationMessage($order);
        $this->assertStringContainsString('Halo Booster Aethereal Joki', $boosterMessage);
        $this->assertStringContainsString('AJ-JOKITEST', $boosterMessage);
        $this->assertStringContainsString('Moonton', $boosterMessage);
        $this->assertStringContainsString('Tolong main malam hari', $boosterMessage);

        $boosterUrl = $service->getBoosterRedirectUrl($order);
        $this->assertStringStartsWith('https://wa.me/6282298561286?text=', $boosterUrl);
    }

    /**
     * Test WhatsApp Customer notification for both pending and success status.
     */
    public function test_whatsapp_customer_pending_and_success_notifications(): void
    {
        $service = app(WhatsAppService::class);

        $order = Order::create([
            'order_code' => 'AJ-CUSTTEST',
            'customer_name' => 'Rina Sasmita',
            'customer_email' => 'rina@example.com',
            'customer_whatsapp' => '085147486281',
            'account_id' => '55667788',
            'subtotal' => 75000,
            'discount_amount' => 0,
            'total_amount' => 75750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-CUST01',
            'payment_method' => 'qris',
            'amount' => 75750,
            'status' => 'pending',
            'payload' => [],
        ]);

        // Pending notification
        $pendingMessage = $service->buildCustomerPendingMessage($order);
        $this->assertStringContainsString('Halo Rina Sasmita', $pendingMessage);
        $this->assertStringContainsString('AJ-CUSTTEST', $pendingMessage);
        $this->assertStringContainsString('Menunggu Pembayaran (Pending)', $pendingMessage);

        $pendingUrl = $service->getCustomerPendingRedirectUrl($order);
        $this->assertStringStartsWith('https://wa.me/6285147486281?text=', $pendingUrl);

        // Success notification
        $order->update(['order_status' => OrderStatus::PROCESSING->value]);
        $order->payment->update(['status' => 'paid', 'paid_at' => now()]);

        $successMessage = $service->buildCustomerSuccessMessage($order);
        $this->assertStringContainsString('Halo Rina Sasmita', $successMessage);
        $this->assertStringContainsString('AJ-CUSTTEST', $successMessage);
        $this->assertStringContainsString('Pembayaran Berhasil (Diproses)', $successMessage);

        $successUrl = $service->getCustomerSuccessRedirectUrl($order);
        $this->assertStringStartsWith('https://wa.me/6285147486281?text=', $successUrl);
    }

    /**
     * Test HTTP redirect route to WhatsApp.
     */
    public function test_whatsapp_web_redirect_routes(): void
    {
        $order = Order::create([
            'order_code' => 'AJ-ROUTETEST',
            'customer_name' => 'Test User',
            'customer_email' => 'user@example.com',
            'customer_whatsapp' => '085147486281',
            'account_id' => '11223344',
            'subtotal' => 25000,
            'discount_amount' => 0,
            'total_amount' => 25750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        // 1. Redirect to Admin WA
        $resAdmin = $this->get(route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'admin']));
        $resAdmin->assertStatus(302);
        $this->assertStringStartsWith('https://wa.me/6281384849982?text=', $resAdmin->headers->get('Location'));

        // 2. Redirect to Booster WA
        $resBooster = $this->get(route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'booster']));
        $resBooster->assertStatus(302);
        $this->assertStringStartsWith('https://wa.me/6282298561286?text=', $resBooster->headers->get('Location'));

        // 3. Redirect to Customer Pending WA
        $resCustPending = $this->get(route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'customer-pending']));
        $resCustPending->assertStatus(302);
        $this->assertStringStartsWith('https://wa.me/6285147486281?text=', $resCustPending->headers->get('Location'));

        // 4. Redirect to Customer Success WA
        $resCustSuccess = $this->get(route('orders.wa-redirect', ['code' => $order->order_code, 'target' => 'customer-success']));
        $resCustSuccess->assertStatus(302);
        $this->assertStringStartsWith('https://wa.me/6285147486281?text=', $resCustSuccess->headers->get('Location'));
    }
}
