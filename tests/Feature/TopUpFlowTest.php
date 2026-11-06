<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Game;
use App\Models\GameProduct;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopUpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test games listing page displays active games, category filter, and search.
     */
    public function test_games_listing_page_displays_active_games_and_supports_filters(): void
    {
        $response = $this->get(route('games'));

        $response->assertStatus(200);
        $response->assertSee('KATALOG GAME RESMI');
        $response->assertSee('Semua Game & Layanan Tersedia', false);
        $response->assertSee('Mobile Legends');
        $response->assertSee('Valorant');

        // Test search filter (Mobile Legends publisher is Moonton, Valorant publisher is Riot Games)
        $searchResponse = $this->get(route('games', ['search' => 'Mobile Legends']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Moonton Games');
        $searchResponse->assertDontSee('Riot Games');

        // Test category filter (FPS category includes Riot Games, excludes Moonton Games)
        $categoryResponse = $this->get(route('games', ['type' => 'fps']));
        $categoryResponse->assertStatus(200);
        $categoryResponse->assertSee('Riot Games');
        $categoryResponse->assertDontSee('Moonton Games');
    }

    /**
     * Test game detail page displays game info and active top-up products.
     */
    public function test_game_detail_page_displays_game_info_and_products(): void
    {
        $game = Game::where('slug', 'mobile-legends')->firstOrFail();
        $product = $game->gameProducts()->where('is_active', true)->firstOrFail();

        $response = $this->get(route('games.show', $game->slug));

        $response->assertStatus(200);
        $response->assertSee($game->name);
        $response->assertSee($game->publisher);
        $response->assertSee($product->name);
        $response->assertSee('Rp '.number_format($product->price, 0, ',', '.'));
    }

    /**
     * Test top-up hub redirect and game top-up wizard form.
     */
    public function test_topup_wizard_page_renders_form_and_product_selection(): void
    {
        // Top-up index redirects to first active game
        $indexResponse = $this->get(route('topup'));
        $indexResponse->assertRedirect();

        $game = Game::where('slug', 'mobile-legends')->firstOrFail();
        $product = $game->gameProducts()->where('is_active', true)->firstOrFail();

        $response = $this->get(route('topup.game', $game->slug));

        $response->assertStatus(200);
        $response->assertSee('Top Up '.$game->name);
        $response->assertSee('Masukkan Data Akun');
        $response->assertSee('Pilih Nominal Top Up');
        $response->assertSee($product->name);
        $response->assertSee('QRIS Instan');
        $response->assertSee('BCA Virtual Account');
    }

    /**
     * Test checkout summary page calculates price, fee, and total strictly on server.
     */
    public function test_checkout_summary_page_calculates_server_prices_accurately(): void
    {
        $product = GameProduct::where('is_active', true)->firstOrFail();
        $quantity = 2;
        $expectedSubtotal = (float) $product->price * $quantity;
        $expectedFee = 750.0; // Flat QRIS fee
        $expectedTotal = $expectedSubtotal + $expectedFee;

        $response = $this->get(route('checkout', [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'payment_method' => 'qris',
            'account_id' => '12345678',
            'account_server' => '9999',
            'account_nickname' => 'ProPlayer',
        ]));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('Rp '.number_format($expectedSubtotal, 0, ',', '.'));
        $response->assertSee('Rp '.number_format($expectedFee, 0, ',', '.'));
        $response->assertSee('Rp '.number_format($expectedTotal, 0, ',', '.'));
    }

    /**
     * Test checkout submission creates order, order items, and payment with PENDING status.
     */
    public function test_checkout_submission_creates_pending_order_and_payment(): void
    {
        $product = GameProduct::where('slug', '86-diamonds-mlbb')->firstOrFail();
        $quantity = 2;
        $unitPrice = (float) $product->price;
        $subtotal = $unitPrice * $quantity;
        $fee = 750.0; // QRIS fee
        $total = $subtotal + $fee;

        $payload = [
            'product_id' => $product->id,
            'account_id' => '123456789',
            'account_server' => '2026',
            'account_nickname' => 'AetherKing',
            'quantity' => $quantity,
            'payment_method' => 'qris',
            'customer_name' => 'Budi Gamers',
            'customer_email' => 'budi@example.com',
            'customer_whatsapp' => '081234567899',
        ];

        $response = $this->post(route('checkout.store'), $payload);

        // Should redirect to order tracking page
        $order = Order::where('customer_email', 'budi@example.com')->latest('id')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('orders.show', $order->order_code));

        // 1. Order assertions
        $this->assertEquals(OrderStatus::PENDING->value, $order->order_status);
        $this->assertEquals('123456789', $order->account_id);
        $this->assertEquals('2026', $order->account_server);
        $this->assertEquals($subtotal, (float) $order->subtotal);
        $this->assertEquals(0, (float) $order->discount_amount);
        $this->assertEquals($total, (float) $order->total_amount);

        // 2. OrderItem assertions
        $this->assertCount(1, $order->orderItems);
        $item = $order->orderItems->first();
        $this->assertEquals('product', $item->item_type);
        $this->assertEquals($product->id, $item->game_product_id);
        $this->assertEquals($quantity, $item->quantity);
        $this->assertEquals($unitPrice, (float) $item->price);
        $this->assertEquals($subtotal, (float) $item->subtotal);

        // 3. Payment assertions
        $this->assertNotNull($order->payment);
        $payment = $order->payment;
        $this->assertEquals(PaymentStatus::PENDING->value, $payment->status);
        $this->assertEquals('qris', $payment->payment_method);
        $this->assertEquals($total, (float) $payment->amount);
        $this->assertNotNull($payment->payload['qr_string']);

        // 4. Audit Log assertion
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'topup_order_created',
        ]);
    }

    /**
     * Test price manipulation prevention: server NEVER trusts client-manipulated prices.
     */
    public function test_price_manipulation_is_completely_prevented(): void
    {
        $product = GameProduct::where('slug', '86-diamonds-mlbb')->firstOrFail();
        $realUnitPrice = (float) $product->price;
        $realFee = 750.0;
        $expectedSubtotal = $realUnitPrice * 1;
        $expectedTotal = $expectedSubtotal + $realFee;

        // Malicious client attempts to inject manipulated price parameters
        $hackedPayload = [
            'product_id' => $product->id,
            'account_id' => '123456789',
            'account_server' => '2026',
            'quantity' => 1,
            'payment_method' => 'qris',
            'customer_name' => 'Cheater User',
            'customer_email' => 'cheater@example.com',
            'customer_whatsapp' => '081299998888',
            // Manipulated fields that an attacker might try to send:
            'price' => 100,
            'subtotal' => 100,
            'fee' => 0,
            'total_amount' => 100,
            'discount_amount' => 50000,
        ];

        $response = $this->post(route('checkout.store'), $hackedPayload);

        $order = Order::where('customer_email', 'cheater@example.com')->latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order->order_code));

        // The order MUST contain the genuine DB price, NOT the injected 100 rupiah
        $this->assertEquals($expectedSubtotal, (float) $order->subtotal);
        $this->assertEquals($expectedTotal, (float) $order->total_amount);
        $this->assertNotEquals(100.0, (float) $order->total_amount);

        $payment = $order->payment;
        $this->assertEquals($expectedTotal, (float) $payment->amount);
        $this->assertNotEquals(100.0, (float) $payment->amount);
    }

    /**
     * Test checkout validation fails when required fields are missing.
     */
    public function test_checkout_fails_validation_with_missing_fields(): void
    {
        $response = $this->post(route('checkout.store'), []);

        $response->assertSessionHasErrors([
            'product_id',
            'account_id',
            'quantity',
            'payment_method',
            'customer_name',
            'customer_email',
            'customer_whatsapp',
        ]);
    }

    /**
     * Test checkout fails when product is inactive.
     */
    public function test_checkout_fails_when_product_is_inactive(): void
    {
        $product = GameProduct::where('is_active', true)->firstOrFail();
        $product->update(['is_active' => false]);

        $payload = [
            'product_id' => $product->id,
            'account_id' => '123456789',
            'quantity' => 1,
            'payment_method' => 'qris',
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_whatsapp' => '081234567899',
        ];

        $response = $this->post(route('checkout.store'), $payload);

        // Because product is inactive, firstOrFail() throws ModelNotFoundException which triggers catch block with redirect back and error message
        $response->assertSessionHas('error');
    }

    /**
     * Test promo code discount calculation during checkout.
     */
    public function test_checkout_applies_valid_promo_code_discount(): void
    {
        $promo = Promotion::where('code', 'AETHER2026')->firstOrFail();
        $product = GameProduct::where('price', '>=', $promo->min_spend)->first();

        if (! $product) {
            $product = GameProduct::firstOrFail();
            $product->update(['price' => 60000]);
        }

        $quantity = 1;
        $subtotal = (float) $product->price;
        $fee = 750.0;
        $discount = ($subtotal * (float) $promo->discount_value) / 100;
        if ($promo->max_discount && $discount > (float) $promo->max_discount) {
            $discount = (float) $promo->max_discount;
        }
        $expectedTotal = max(0, $subtotal + $fee - $discount);

        $payload = [
            'product_id' => $product->id,
            'account_id' => '99887766',
            'quantity' => $quantity,
            'payment_method' => 'qris',
            'customer_name' => 'Promo Hunter',
            'customer_email' => 'promo@example.com',
            'customer_whatsapp' => '081998877665',
            'promo_code' => 'AETHER2026',
        ];

        $response = $this->post(route('checkout.store'), $payload);

        $order = Order::where('customer_email', 'promo@example.com')->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals($promo->id, $order->promotion_id);
        $this->assertEquals($discount, (float) $order->discount_amount);
        $this->assertEquals($expectedTotal, (float) $order->total_amount);
    }

    /**
     * Test payment simulation transitions payment to PAID and order to PROCESSING.
     */
    public function test_payment_simulation_transitions_payment_and_order_status(): void
    {
        $product = GameProduct::firstOrFail();
        $order = Order::create([
            'order_code' => 'AJ-TEST01',
            'user_id' => null,
            'customer_name' => 'Simulate User',
            'customer_email' => 'simulate@example.com',
            'customer_whatsapp' => '081299990000',
            'account_id' => '11223344',
            'subtotal' => (float) $product->price,
            'discount_amount' => 0,
            'total_amount' => (float) $product->price + 750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        $order->orderItems()->create([
            'game_product_id' => $product->id,
            'item_type' => 'product',
            'item_name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 1,
            'subtotal' => (float) $product->price,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-TEST01',
            'payment_method' => 'qris',
            'amount' => $order->total_amount,
            'status' => PaymentStatus::PENDING->value,
            'payload' => ['qr_string' => 'MOCK_QR'],
        ]);

        $response = $this->post(route('orders.simulate-pay', $order->order_code));

        $response->assertRedirect(route('orders.show', $order->order_code));

        $payment->refresh();
        $order->refresh();

        $this->assertEquals(PaymentStatus::PAID->value, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->order_status);
        $this->assertNotNull($order->completed_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_paid',
        ]);
    }

    /**
     * Test logged-in customer order creates notification for user.
     */
    public function test_logged_in_customer_order_creates_notification(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $product = GameProduct::where('is_active', true)->firstOrFail();

        $payload = [
            'product_id' => $product->id,
            'account_id' => '99881122',
            'quantity' => 1,
            'payment_method' => 'bca',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => $customer->phone ?? '085712345678',
        ];

        $response = $this->actingAs($customer)->post(route('checkout.store'), $payload);

        $order = Order::where('customer_email', $customer->email)->latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order->order_code));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'order',
        ]);
    }
}
