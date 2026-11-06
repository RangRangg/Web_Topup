<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Game;
use App\Models\JokiProfile;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JokiMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test joki catalog displays services with title, game, target, price, and rating.
     */
    public function test_joki_catalog_displays_services_and_supports_game_filter_and_search(): void
    {
        $response = $this->get(route('joki'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Jasa Joki');
        $response->assertSee('MLBB - Legend ke Mythic');
        $response->assertSee('Valorant - Silver ke Gold');

        // Test filter by game
        $mlResponse = $this->get(route('joki', ['game' => 'mobile-legends']));
        $mlResponse->assertStatus(200);
        $mlResponse->assertSee('MLBB - Legend ke Mythic');
        $mlResponse->assertDontSee('Valorant - Silver ke Gold');

        // Test keyword search
        $searchResponse = $this->get(route('joki', ['search' => 'Mythic']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('MLBB - Legend ke Mythic');
        $searchResponse->assertDontSee('Valorant - Iron ke Silver');
    }

    /**
     * Test joki service detail page displays target, estimated time, and reviews.
     */
    public function test_joki_detail_page_displays_service_details_and_reviews(): void
    {
        $service = JokiService::where('slug', 'mlbb-legend-ke-mythic')->firstOrFail();

        $response = $this->get(route('joki.show', $service->slug));

        $response->assertStatus(200);
        $response->assertSee($service->name);
        $response->assertSee($service->target);
        $response->assertSee($service->estimated_duration);
        $response->assertSee($service->formatted_starting_price);
        $response->assertSee('Garansi Keamanan Akun');
    }

    /**
     * Test joki checkout page renders form with selected service.
     */
    public function test_joki_checkout_page_renders_form_with_selected_service(): void
    {
        $service = JokiService::where('slug', 'mlbb-legend-ke-mythic')->firstOrFail();

        $response = $this->get(route('joki.checkout', ['service' => $service->slug]));

        $response->assertStatus(200);
        $response->assertSee('Checkout Jasa Joki');
        $response->assertSee($service->name);
        $response->assertSee('User ID Game');
        $response->assertSee('Metode Login Akun');
        $response->assertSee('QRIS Instan');
    }

    /**
     * Test joki checkout submission creates pending order and strictly prevents price tampering.
     */
    public function test_joki_checkout_submission_creates_pending_order_and_prevents_price_tampering(): void
    {
        $service = JokiService::where('slug', 'mlbb-legend-ke-mythic')->firstOrFail();
        $realPrice = (float) $service->price;
        $fee = 750.0; // QRIS flat fee
        $expectedTotal = $realPrice + $fee;

        $hackedPayload = [
            'service_id' => $service->id,
            'account_id' => '12847291',
            'account_server' => '2194',
            'account_nickname' => 'ShadowBlade',
            'login_via' => 'Moonton ID',
            'account_notes' => 'Request hero Gusion',
            'payment_method' => 'qris',
            'customer_name' => 'Aditya Pratama',
            'customer_email' => 'aditya@example.com',
            'customer_whatsapp' => '081234567899',
            // Tampered client price injection attempt:
            'price' => 500,
            'subtotal' => 500,
            'total_amount' => 500,
        ];

        $response = $this->post(route('joki.checkout.store'), $hackedPayload);

        $order = Order::where('customer_email', 'aditya@example.com')->latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order->order_code));

        // Order assertions
        $this->assertEquals(OrderStatus::PENDING->value, $order->order_status);
        $this->assertEquals('12847291', $order->account_id);
        $this->assertEquals('Moonton ID', $order->login_via);
        $this->assertEquals($realPrice, (float) $order->subtotal);
        $this->assertEquals($expectedTotal, (float) $order->total_amount);
        $this->assertNotEquals(500.0, (float) $order->total_amount);

        // OrderItem assertions
        $item = $order->orderItems->first();
        $this->assertEquals('joki_service', $item->item_type);
        $this->assertEquals($service->id, $item->joki_service_id);
        $this->assertEquals($realPrice, (float) $item->price);

        // Payment assertions
        $payment = $order->payment;
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentStatus::PENDING->value, $payment->status);
        $this->assertEquals($expectedTotal, (float) $payment->amount);
    }

    /**
     * Test payment simulation transitions unassigned joki order to WAITING_ASSIGNMENT.
     */
    public function test_payment_simulation_transitions_unassigned_joki_order_to_waiting_assignment(): void
    {
        $service = JokiService::firstOrFail();
        $order = Order::create([
            'order_code' => 'AJ-TEST-JOKI',
            'user_id' => null,
            'joki_profile_id' => null,
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_whatsapp' => '081299990000',
            'subtotal' => (float) $service->price,
            'total_amount' => (float) $service->price + 750,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        $order->orderItems()->create([
            'joki_service_id' => $service->id,
            'item_type' => 'joki_service',
            'item_name' => $service->name,
            'price' => (float) $service->price,
            'quantity' => 1,
            'subtotal' => (float) $service->price,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-TEST-JOKI',
            'payment_method' => 'qris',
            'amount' => $order->total_amount,
            'status' => PaymentStatus::PENDING->value,
        ]);

        // Simulate payment success
        $this->post(route('orders.simulate-pay', $order->order_code));

        $order->refresh();
        $payment->refresh();

        $this->assertEquals(PaymentStatus::PAID->value, $payment->status);
        // Progress tracker requirement: unassigned joki order transitions to WAITING_ASSIGNMENT
        $this->assertEquals(OrderStatus::WAITING_ASSIGNMENT->value, $order->order_status);
    }

    /**
     * Test Admin can assign booster to order and status transitions to PROCESSING.
     */
    public function test_admin_can_assign_booster_to_order(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booster = JokiProfile::firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-ASSIGN-01',
            'user_id' => null,
            'joki_profile_id' => null,
            'customer_name' => 'Need Booster',
            'customer_email' => 'booster@example.com',
            'customer_whatsapp' => '081299990000',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'order_status' => OrderStatus::WAITING_ASSIGNMENT->value,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.assign', $order->id), [
            'joki_profile_id' => $booster->id,
        ]);

        $response->assertSessionHas('success');
        $order->refresh();

        $this->assertEquals($booster->id, $order->joki_profile_id);
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->order_status);
    }

    /**
     * Test Joki booster can self-claim an available order in WAITING_ASSIGNMENT.
     */
    public function test_joki_can_self_claim_available_order(): void
    {
        $jokiUser = User::where('role', 'joki')->firstOrFail();
        $boosterProfile = $jokiUser->jokiProfile;

        $order = Order::create([
            'order_code' => 'AJ-CLAIM-01',
            'user_id' => null,
            'joki_profile_id' => null,
            'customer_name' => 'Pool Customer',
            'customer_email' => 'pool@example.com',
            'customer_whatsapp' => '081299990000',
            'subtotal' => 65000,
            'total_amount' => 65000,
            'order_status' => OrderStatus::WAITING_ASSIGNMENT->value,
        ]);

        $response = $this->actingAs($jokiUser)->post(route('orders.claim', $order->order_code));

        $response->assertSessionHas('success');
        $order->refresh();

        $this->assertEquals($boosterProfile->id, $order->joki_profile_id);
        $this->assertEquals(OrderStatus::PROCESSING->value, $order->order_status);
    }

    /**
     * Test assigned booster can update order progress to COMPLETED.
     */
    public function test_booster_can_update_order_progress_to_completed(): void
    {
        $jokiUser = User::where('role', 'joki')->firstOrFail();
        $boosterProfile = $jokiUser->jokiProfile;
        $initialCompletedCount = $boosterProfile->completed_orders_count;

        $order = Order::create([
            'order_code' => 'AJ-PROGRESS-01',
            'user_id' => null,
            'joki_profile_id' => $boosterProfile->id,
            'customer_name' => 'Progress User',
            'customer_email' => 'progress@example.com',
            'customer_whatsapp' => '081299990000',
            'subtotal' => 65000,
            'total_amount' => 65000,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        $response = $this->actingAs($jokiUser)->patch(route('orders.progress', $order->order_code), [
            'status' => 'completed',
        ]);

        $response->assertSessionHas('success');
        $order->refresh();
        $boosterProfile->refresh();

        $this->assertEquals(OrderStatus::COMPLETED->value, $order->order_status);
        $this->assertNotNull($order->completed_at);
        $this->assertEquals($initialCompletedCount + 1, $boosterProfile->completed_orders_count);
    }

    /**
     * Test server-side policy authorization: Customer can ONLY view their own orders.
     */
    public function test_customer_cannot_view_another_customers_order(): void
    {
        $customerA = User::where('role', 'customer')->firstOrFail();
        $customerB = User::create([
            'name' => 'Customer B',
            'email' => 'customer_b@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        // Order owned by customer A
        $orderA = Order::create([
            'order_code' => 'AJ-PRIVATE-A',
            'user_id' => $customerA->id,
            'customer_name' => $customerA->name,
            'customer_email' => $customerA->email,
            'customer_whatsapp' => '085711112222',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        // Customer A can view their own order
        $allowedResponse = $this->actingAs($customerA)->get(route('orders.show', $orderA->order_code));
        $allowedResponse->assertStatus(200);

        // Customer B attempting to view Customer A's order is refused with HTTP 403 Forbidden!
        $forbiddenResponse = $this->actingAs($customerB)->get(route('orders.show', $orderA->order_code));
        $forbiddenResponse->assertStatus(403);
    }

    /**
     * Test server-side policy authorization: Joki booster can view assigned or available orders, but not orders assigned to another booster.
     */
    public function test_joki_can_only_view_assigned_or_available_orders(): void
    {
        $jokiUser = User::where('role', 'joki')->firstOrFail();
        $boosterProfile = $jokiUser->jokiProfile;

        // Create another booster
        $otherUser = User::create([
            'name' => 'Other Booster',
            'email' => 'other_booster@example.com',
            'password' => bcrypt('password'),
            'role' => 'joki',
        ]);
        $otherProfile = JokiProfile::create([
            'user_id' => $otherUser->id,
            'nickname' => 'Other Pro',
            'phone' => '08999999999',
            'bio' => 'Other booster',
            'status' => 'available',
        ]);

        // 1. Order assigned to Joki Kael -> Allowed
        $myOrder = Order::create([
            'order_code' => 'AJ-MY-BOOST',
            'user_id' => null,
            'joki_profile_id' => $boosterProfile->id,
            'customer_name' => 'Client One',
            'customer_email' => 'c1@example.com',
            'customer_whatsapp' => '08111',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);
        $this->actingAs($jokiUser)->get(route('orders.show', $myOrder->order_code))->assertStatus(200);

        // 2. Order unassigned in waiting_assignment pool -> Allowed
        $poolOrder = Order::create([
            'order_code' => 'AJ-POOL-BOOST',
            'user_id' => null,
            'joki_profile_id' => null,
            'customer_name' => 'Client Pool',
            'customer_email' => 'cp@example.com',
            'customer_whatsapp' => '08222',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'order_status' => OrderStatus::WAITING_ASSIGNMENT->value,
        ]);
        $this->actingAs($jokiUser)->get(route('orders.show', $poolOrder->order_code))->assertStatus(200);

        // 3. Order assigned to OTHER booster in processing -> Forbidden (403)
        $otherOrder = Order::create([
            'order_code' => 'AJ-OTHER-BOOST',
            'user_id' => null,
            'joki_profile_id' => $otherProfile->id,
            'customer_name' => 'Client Other',
            'customer_email' => 'co@example.com',
            'customer_whatsapp' => '08333',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);
        $this->actingAs($jokiUser)->get(route('orders.show', $otherOrder->order_code))->assertStatus(403);
    }

    /**
     * Test Customer can submit review after order is COMPLETED.
     */
    public function test_customer_can_submit_review_after_order_completed(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $booster = JokiProfile::firstOrFail();
        $service = JokiService::firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-REV-01',
            'user_id' => $customer->id,
            'joki_profile_id' => $booster->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => '08123456789',
            'subtotal' => 65000,
            'total_amount' => 65000,
            'order_status' => OrderStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);

        $order->orderItems()->create([
            'joki_service_id' => $service->id,
            'item_type' => 'joki_service',
            'item_name' => $service->name,
            'price' => 65000,
            'quantity' => 1,
            'subtotal' => 65000,
        ]);

        $response = $this->actingAs($customer)->post(route('orders.review', $order->order_code), [
            'rating' => 5,
            'comment' => 'Booster sangat cepat, win streak tanpa lose! Terima kasih Aethereal Joki.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'joki_profile_id' => $booster->id,
            'rating' => 5,
        ]);
    }

    /**
     * Test review submission is rejected if order is NOT yet completed.
     */
    public function test_review_rejected_if_order_not_completed(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();

        $order = Order::create([
            'order_code' => 'AJ-NOT-DONE',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => '08123456789',
            'subtotal' => 65000,
            'total_amount' => 65000,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        $response = $this->actingAs($customer)->post(route('orders.review', $order->order_code), [
            'rating' => 5,
            'comment' => 'Testing review prematurely.',
        ]);

        // Denied by OrderPolicy@review -> 403 Forbidden
        $response->assertStatus(403);
    }
}
