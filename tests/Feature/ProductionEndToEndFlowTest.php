<?php

namespace Tests\Feature;

use App\Contracts\PaymentServiceInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiProfile;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionEndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Flow 1:
     * Register -> Login -> Browse Game -> Top Up -> Checkout -> Mock Payment -> Order -> Customer Dashboard
     */
    public function test_complete_flow_1_customer_topup_and_dashboard(): void
    {
        // 1. Setup Active Game & Product
        $game = Game::factory()->create([
            'name' => 'Mobile Legends: Bang Bang',
            'slug' => 'mobile-legends',
            'is_active' => true,
        ]);

        $product = GameProduct::factory()->create([
            'game_id' => $game->id,
            'name' => '86 Diamonds',
            'price' => 20000,
            'is_active' => true,
        ]);

        // 2. Register
        $registerResponse = $this->post(route('register'), [
            'name' => 'Gamer Sultan',
            'email' => 'sultan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $registerResponse->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $customer = User::where('email', 'sultan@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('customer', $customer->role);

        // 3. Browse Game
        $browseGames = $this->get(route('games'));
        $browseGames->assertStatus(200);
        $browseGames->assertSee('Mobile Legends: Bang Bang');

        $browseGameDetail = $this->get(route('games.show', $game->slug));
        $browseGameDetail->assertStatus(200);
        $browseGameDetail->assertSee('86 Diamonds');

        // 4. View Top Up Page
        $topupPage = $this->get(route('topup.game', $game->slug));
        $topupPage->assertStatus(200);
        $topupPage->assertSee('86 Diamonds');

        // 5. Checkout
        $checkoutResponse = $this->post(route('checkout.store'), [
            'product_id' => $product->id,
            'account_id' => '12345678',
            'account_server' => '2109',
            'quantity' => 1,
            'customer_name' => 'Gamer Sultan',
            'customer_email' => 'sultan@example.com',
            'customer_whatsapp' => '081234567890',
            'payment_method' => 'qris',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'order_status' => OrderStatus::PENDING->value,
            'subtotal' => 20000,
        ]);

        $order = Order::with('payment')->where('user_id', $customer->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->payment);
        $this->assertEquals(PaymentStatus::PENDING->value, $order->payment->status);

        $checkoutResponse->assertRedirect(route('orders.show', $order->order_code));

        // 6. View Order Tracking / Payment Page
        $orderPage = $this->get(route('orders.show', $order->order_code));
        $orderPage->assertStatus(200);
        $orderPage->assertSee($order->order_code);
        $orderPage->assertSee('QRIS');

        // 7. Process Mock Payment Webhook
        $paymentService = app(PaymentServiceInterface::class);
        $webhookPayload = [
            'order_code' => $order->order_code,
            'payment_code' => $order->payment->payment_code,
            'amount' => $order->total_amount,
            'transaction_status' => 'paid',
            'event_id' => 'evt_flow1_test',
        ];
        $signature = $paymentService->generateWebhookSignature($webhookPayload);

        $webhookResponse = $this->postJson(route('webhooks.payment'), $webhookPayload, [
            'X-Payment-Signature' => $signature,
        ]);
        $webhookResponse->assertStatus(200);

        // Verify status after payment: top-up is auto-completed instantly
        $order->refresh();
        $this->assertEquals(PaymentStatus::PAID->value, $order->payment->fresh()->status);
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->order_status);
        $this->assertNotNull($order->completed_at);

        // 8. Access Customer Dashboard
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($order->order_code);

        $dashboardOrderDetail = $this->get(route('dashboard.orders.show', $order->id));
        $dashboardOrderDetail->assertStatus(200);
        $dashboardOrderDetail->assertSee($order->order_code);
        $dashboardOrderDetail->assertSee('86 Diamonds');
    }

    /**
     * Flow 2:
     * Browse Joki -> Joki Checkout -> Order -> Admin Assignment -> Joki Dashboard -> Processing -> Completed -> Review
     */
    public function test_complete_flow_2_joki_marketplace_and_lifecycle(): void
    {
        // 1. Setup Admin, Booster/Joki, and Customer
        $admin = User::factory()->create([
            'email' => 'admin_test@example.com',
            'role' => 'admin',
        ]);

        $boosterUser = User::factory()->create([
            'name' => 'Pro Booster Alpha',
            'email' => 'booster_alpha@example.com',
            'role' => 'joki',
        ]);

        $jokiProfile = JokiProfile::create([
            'user_id' => $boosterUser->id,
            'nickname' => 'AlphaBooster',
            'bio' => 'Top Global Booster 100% Winrate',
            'specialized_games' => ['Valorant', 'Mobile Legends'],
            'rating' => 4.9,
            'completed_orders_count' => 150,
            'status' => 'available',
            'is_verified' => true,
        ]);

        $customer = User::factory()->create([
            'name' => 'Customer Ranker',
            'email' => 'ranker@example.com',
            'role' => 'customer',
        ]);

        $game = Game::factory()->create([
            'name' => 'Valorant',
            'slug' => 'valorant',
            'is_active' => true,
        ]);

        $jokiService = JokiService::factory()->create([
            'game_id' => $game->id,
            'name' => 'Joki Rank Radiant / Immortal',
            'slug' => 'joki-rank-radiant',
            'price' => 250000,
            'rank_from' => 'Ascendant 1',
            'rank_to' => 'Immortal 3',
            'estimated_duration' => '2 Hari',
            'is_active' => true,
        ]);

        // 2. Customer browses Joki Marketplace
        $this->actingAs($customer);

        $jokiCatalog = $this->get(route('joki'));
        $jokiCatalog->assertStatus(200);
        $jokiCatalog->assertSee('Joki Rank Radiant / Immortal');

        $jokiDetail = $this->get(route('joki.show', $jokiService->slug));
        $jokiDetail->assertStatus(200);
        $jokiDetail->assertSee('250.000');

        // 3. Joki Checkout
        $checkoutResponse = $this->post(route('joki.checkout.store'), [
            'service_id' => $jokiService->id,
            'account_id' => 'RiotRanker#ID1',
            'account_server' => 'Asia',
            'account_nickname' => 'RiotRanker',
            'login_via' => 'Riot Games Client',
            'account_notes' => 'Main pas malam hari saja ya gan',
            'customer_name' => 'Customer Ranker',
            'customer_email' => 'ranker@example.com',
            'customer_whatsapp' => '087712345678',
            'payment_method' => 'bca',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'order_status' => OrderStatus::PENDING->value,
            'subtotal' => 250000,
        ]);

        $order = Order::with('payment')->where('user_id', $customer->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->payment);

        $checkoutResponse->assertRedirect(route('orders.show', $order->order_code));

        // 4. Pay via Webhook (Simulate Payment Gateway)
        $paymentService = app(PaymentServiceInterface::class);
        $webhookPayload = [
            'order_code' => $order->order_code,
            'payment_code' => $order->payment->payment_code,
            'amount' => $order->total_amount,
            'transaction_status' => 'paid',
            'event_id' => 'evt_flow2_test',
        ];
        $signature = $paymentService->generateWebhookSignature($webhookPayload);

        $webhookResponse = $this->postJson(route('webhooks.payment'), $webhookPayload, [
            'X-Payment-Signature' => $signature,
        ]);
        $webhookResponse->assertStatus(200);

        // After payment, Joki order transitions to waiting_assignment
        $order->refresh();
        $this->assertEquals(OrderStatus::WAITING_ASSIGNMENT->value, $order->order_status);

        // 5. Admin Assignment
        $this->actingAs($admin);
        $assignResponse = $this->post(route('admin.orders.assign', $order->id), [
            'joki_profile_id' => $jokiProfile->id,
        ]);
        $assignResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals($jokiProfile->id, $order->joki_profile_id);
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->order_status);

        // 6. Joki Dashboard - View & Update Progress
        $this->actingAs($boosterUser);

        $jokiDashboard = $this->get(route('joki.dashboard'));
        $jokiDashboard->assertStatus(200);
        $jokiDashboard->assertSee($order->order_code);

        $jokiOrderDetail = $this->get(route('joki.dashboard.orders.show', $order->id));
        $jokiOrderDetail->assertStatus(200);
        $jokiOrderDetail->assertSee($order->order_code);

        // Joki sets status to PROCESSING
        $progressToProcessing = $this->patch(route('joki.dashboard.orders.progress', $order->id), [
            'order_status' => OrderStatus::PROCESSING->value,
            'progress_note' => 'Sedang dimainkan sesi push rank 1st',
        ]);
        $progressToProcessing->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->order_status);
        $this->assertEquals('Sedang dimainkan sesi push rank 1st', $order->progress_note);

        // Joki completes order -> COMPLETED
        $progressToCompleted = $this->patch(route('joki.dashboard.orders.progress', $order->id), [
            'order_status' => OrderStatus::COMPLETED->value,
            'progress_note' => 'Target Immortal 3 tercapai dengan winstreak!',
        ]);
        $progressToCompleted->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->order_status);

        // 7. Customer submits review
        $this->actingAs($customer);

        $reviewResponse = $this->post(route('orders.review', $order->order_code), [
            'rating' => 5,
            'comment' => 'Booster sangat ramah, pengerjaan super kilat, 100% winrate!',
        ]);
        $reviewResponse->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Booster sangat ramah, pengerjaan super kilat, 100% winrate!',
        ]);
    }

    /**
     * SEO: Sitemap XML generation test
     */
    public function test_sitemap_xml_returns_valid_xml_structure(): void
    {
        Game::factory()->create(['slug' => 'sitemap-game', 'is_active' => true]);
        JokiService::factory()->create(['slug' => 'sitemap-service', 'is_active' => true]);

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee(route('games'), false);
        $response->assertSee(route('topup'), false);
        $response->assertSee(route('joki'), false);
        $response->assertSee('sitemap-game', false);
        $response->assertSee('sitemap-service', false);
    }

    /**
     * SEO & UX: Custom 404 page test
     */
    public function test_custom_404_page_renders_with_aethereal_branding(): void
    {
        $response = $this->get('/non-existent-random-page-12345');
        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Aethereal Joki');
        $response->assertSee('Halaman Tidak Ditemukan');
    }
}
