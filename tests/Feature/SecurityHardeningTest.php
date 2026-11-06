<?php

namespace Tests\Feature;

use App\Contracts\PaymentServiceInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\GameProduct;
use App\Models\JokiProfile;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /*
    |--------------------------------------------------------------------------
    | 1. AUTHENTICATION & REGISTRATION TESTS
    |--------------------------------------------------------------------------
    */

    public function test_user_registration_hashes_password_and_assigns_default_customer_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'budi.santoso@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertNotEquals('SecurePass123!', $user->password);
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user_registered',
        ]);
    }

    public function test_joki_registration_creates_joki_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pro Booster Ace',
            'email' => 'booster.ace@example.com',
            'phone' => '081234567899',
            'password' => 'BoosterPass123!',
            'password_confirmation' => 'BoosterPass123!',
            'role' => 'joki',
        ]);

        $response->assertRedirect(route('joki.dashboard'));

        $user = User::where('email', 'booster.ace@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('joki', $user->role);

        $this->assertDatabaseHas('joki_profiles', [
            'user_id' => $user->id,
            'nickname' => 'Pro Booster Ace',
        ]);
    }

    public function test_registration_validation_enforces_required_fields_and_min_password(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_successful_login_regenerates_session_and_records_audit_log(): void
    {
        $user = User::factory()->create([
            'email' => 'gamer.login@example.com',
            'password' => Hash::make('MySecretPassword123!'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'gamer.login@example.com',
            'password' => 'MySecretPassword123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user_login',
        ]);
    }

    public function test_admin_login_records_specific_admin_login_audit_log(): void
    {
        $admin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'password' => Hash::make('AdminSecret123!'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'superadmin@example.com',
            'password' => 'AdminSecret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin_login',
        ]);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'banned@example.com',
            'password' => Hash::make('Secret123!'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'banned@example.com',
            'password' => 'Secret123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_logout_invalidates_session_and_clears_auth(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'user_logout',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. ROLE AUTHORIZATION TESTS
    |--------------------------------------------------------------------------
    */

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_joki_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/joki/dashboard');
        $response->assertStatus(403);
    }

    public function test_joki_cannot_access_admin_dashboard(): void
    {
        $joki = User::factory()->create(['role' => 'joki']);

        $response = $this->actingAs($joki)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_guest_redirected_to_login_on_protected_routes(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));

        $responseAdmin = $this->get('/admin');
        $responseAdmin->assertRedirect(route('login'));

        $responseJoki = $this->get('/joki/dashboard');
        $responseJoki->assertRedirect(route('login'));
    }

    /*
    |--------------------------------------------------------------------------
    | 3. ORDER SECURITY & TOP UP CHECKOUT TESTS
    |--------------------------------------------------------------------------
    */

    public function test_top_up_checkout_calculates_price_server_side_and_creates_order_payment(): void
    {
        $product = GameProduct::where('is_active', true)->first();
        $this->assertNotNull($product);

        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'account_id' => '12345678',
            'account_server' => '9999',
            'account_nickname' => 'ProPlayer',
            'payment_method' => 'qris',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => '081234567890',
        ]);

        $order = Order::where('customer_email', $customer->email)->latest()->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('orders.show', $order->order_code));

        // Subtotal calculated strictly on server
        $expectedSubtotal = (float) $product->price * 2;
        $this->assertEquals($expectedSubtotal, (float) $order->subtotal);
        $this->assertEquals(OrderStatus::PENDING->value, $order->order_status);

        // Payment record created
        $this->assertNotNull($order->payment);
        $this->assertEquals(PaymentStatus::PENDING->value, $order->payment->status);
        $this->assertEquals('qris', $order->payment->payment_method);
    }

    public function test_top_up_checkout_idempotency_prevents_duplicate_orders(): void
    {
        $product = GameProduct::where('is_active', true)->first();
        $idempotencyKey = 'idemp-'.Str::uuid();

        $payload = [
            'product_id' => $product->id,
            'quantity' => 1,
            'account_id' => '999888',
            'payment_method' => 'qris',
            'customer_name' => 'Idempotent User',
            'customer_email' => 'idempotent@example.com',
            'customer_whatsapp' => '081299998888',
            'idempotency_key' => $idempotencyKey,
        ];

        // 1st submission
        $res1 = $this->post(route('checkout.store'), $payload);
        $ordersCount1 = Order::count();

        // 2nd submission with identical idempotency key
        $res2 = $this->post(route('checkout.store'), $payload);
        $ordersCount2 = Order::count();

        // No second order created
        $this->assertEquals($ordersCount1, $ordersCount2);

        $order = Order::where('idempotency_key', $idempotencyKey)->first();
        $this->assertNotNull($order);
        $res2->assertRedirect(route('orders.show', $order->order_code));
    }

    /*
    |--------------------------------------------------------------------------
    | 4. JOKI CHECKOUT & IDEMPOTENCY TESTS
    |--------------------------------------------------------------------------
    */

    public function test_joki_checkout_calculates_price_server_side_and_creates_order_payment(): void
    {
        $service = JokiService::where('is_active', true)->first();
        $this->assertNotNull($service);

        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('joki.checkout.store'), [
            'service_id' => $service->id,
            'account_id' => 'joki_acc_101',
            'login_via' => 'Moonton',
            'account_notes' => 'Tolong main hero tank',
            'payment_method' => 'bca',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => '081234567891',
        ]);

        $order = Order::where('customer_email', $customer->email)->latest()->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('orders.show', $order->order_code));

        $this->assertEquals((float) $service->price, (float) $order->subtotal);
        $this->assertEquals(OrderStatus::PENDING->value, $order->order_status);
        $this->assertNotNull($order->payment);
    }

    public function test_joki_checkout_idempotency_prevents_duplicate_orders(): void
    {
        $service = JokiService::where('is_active', true)->first();
        $idempotencyKey = 'joki-idemp-'.Str::uuid();

        $payload = [
            'service_id' => $service->id,
            'account_id' => 'joki_dup_123',
            'login_via' => 'Riot Games',
            'payment_method' => 'qris',
            'customer_name' => 'Joki Tester',
            'customer_email' => 'joki.tester@example.com',
            'customer_whatsapp' => '081255554444',
            'idempotency_key' => $idempotencyKey,
        ];

        $res1 = $this->post(route('joki.checkout.store'), $payload);
        $count1 = Order::count();

        $res2 = $this->post(route('joki.checkout.store'), $payload);
        $count2 = Order::count();

        $this->assertEquals($count1, $count2);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. CUSTOMER & JOKI ORDER ACCESS (POLICY AUTHORIZATION)
    |--------------------------------------------------------------------------
    */

    public function test_customer_can_view_their_own_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'order_code' => 'ORD-MY-ORDER-1',
        ]);

        $response = $this->actingAs($customer)->get(route('dashboard.orders.show', $order->order_code));
        $response->assertStatus(200);
        $response->assertSee('ORD-MY-ORDER-1');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $customerA = User::factory()->create(['role' => 'customer']);
        $customerB = User::factory()->create(['role' => 'customer']);

        $orderA = Order::factory()->create([
            'user_id' => $customerA->id,
            'order_code' => 'ORD-CUSTOMER-A',
        ]);

        // Customer B tries to view Customer A's order in dashboard
        $response = $this->actingAs($customerB)->get(route('dashboard.orders.show', $orderA->order_code));
        $response->assertStatus(403);
    }

    public function test_joki_can_view_orders_assigned_to_them(): void
    {
        $jokiUser = User::factory()->create(['role' => 'joki']);
        $jokiProfile = JokiProfile::create([
            'user_id' => $jokiUser->id,
            'nickname' => 'Booster Top',
            'status' => 'active',
            'rating' => 5.0,
        ]);

        $order = Order::factory()->create([
            'joki_profile_id' => $jokiProfile->id,
            'order_status' => OrderStatus::PROCESSING->value,
            'order_code' => 'ORD-JOKI-ASSIGNED',
        ]);

        $response = $this->actingAs($jokiUser)->get(route('joki.dashboard.orders.show', $order->order_code));
        $response->assertStatus(200);
        $response->assertSee('ORD-JOKI-ASSIGNED');
    }

    public function test_joki_cannot_view_orders_assigned_to_another_booster(): void
    {
        $boosterA = User::factory()->create(['role' => 'joki']);
        $profileA = JokiProfile::create([
            'user_id' => $boosterA->id,
            'nickname' => 'Booster Alpha',
            'status' => 'active',
            'rating' => 5.0,
        ]);

        $boosterB = User::factory()->create(['role' => 'joki']);
        $profileB = JokiProfile::create([
            'user_id' => $boosterB->id,
            'nickname' => 'Booster Beta',
            'status' => 'active',
            'rating' => 5.0,
        ]);

        $orderOfA = Order::factory()->create([
            'joki_profile_id' => $profileA->id,
            'order_status' => OrderStatus::PROCESSING->value,
            'order_code' => 'ORD-BOOSTER-A',
        ]);

        // Booster B tries to view Booster A's order
        $response = $this->actingAs($boosterB)->get(route('joki.dashboard.orders.show', $orderOfA->order_code));
        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. ADMIN ACCESS TESTS
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_access_all_dashboard_views(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.users'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.orders'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.games'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.products'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.joki-services'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.payments'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.audit-logs'))->assertStatus(200);
    }

    public function test_admin_can_assign_booster_to_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $boosterUser = User::factory()->create(['role' => 'joki']);
        $booster = JokiProfile::create([
            'user_id' => $boosterUser->id,
            'nickname' => 'Speedy Booster',
            'status' => 'active',
            'rating' => 5.0,
        ]);

        $order = Order::factory()->create([
            'joki_profile_id' => null,
            'order_status' => OrderStatus::WAITING_ASSIGNMENT->value,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.orders.assign', $order->order_code), [
            'joki_profile_id' => $booster->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals($booster->id, $order->fresh()->joki_profile_id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin_assigned_booster',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. PAYMENT GATEWAY WEBHOOK & SECURITY TESTS
    |--------------------------------------------------------------------------
    */

    public function test_webhook_rejects_missing_or_invalid_signature(): void
    {
        // 1. Missing signature
        $responseNoSig = $this->postJson(route('webhooks.payment'), [
            'order_code' => 'AJ-123456',
            'amount' => 50000,
            'status' => 'paid',
        ]);

        $responseNoSig->assertStatus(401);

        // 2. Invalid signature
        $responseBadSig = $this->withHeaders([
            'X-Payment-Signature' => 'invalid-fake-signature',
        ])->postJson(route('webhooks.payment'), [
            'order_code' => 'AJ-123456',
            'amount' => 50000,
            'status' => 'paid',
        ]);

        $responseBadSig->assertStatus(403);
    }

    public function test_webhook_rejects_tampered_nominal(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 50000,
            'total_amount' => 50000,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-TEST-TAMPER',
            'payment_method' => 'qris',
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentServiceInterface::class);

        // Attacker attempts to pay 1000 instead of 50000
        $tamperedPayload = [
            'order_code' => $order->order_code,
            'payment_code' => $payment->payment_code,
            'amount' => 1000,
            'transaction_status' => 'paid',
            'event_id' => 'evt_tamper_1',
        ];

        $signature = $paymentService->generateWebhookSignature($tamperedPayload);

        $response = $this->postJson(route('webhooks.payment'), $tamperedPayload, [
            'X-Payment-Signature' => $signature,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(PaymentStatus::PENDING->value, $payment->fresh()->status);
    }

    public function test_webhook_processes_successful_payment_and_updates_order(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 75000,
            'total_amount' => 75000,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'product',
            'item_name' => '100 Diamond MLBB',
            'price' => 75000,
            'quantity' => 1,
            'subtotal' => 75000,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-VALID-WEBHOOK',
            'payment_method' => 'qris',
            'amount' => 75000,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentServiceInterface::class);

        $payload = [
            'order_code' => $order->order_code,
            'payment_code' => $payment->payment_code,
            'amount' => 75000,
            'transaction_status' => 'paid',
            'event_id' => 'evt_success_100',
        ];

        $signature = $paymentService->generateWebhookSignature($payload);

        $response = $this->postJson(route('webhooks.payment'), $payload, [
            'X-Payment-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        // Payment marked paid and top-up order auto-completed
        $this->assertEquals(PaymentStatus::PAID->value, $payment->fresh()->status);
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->fresh()->order_status);

        // Audit log created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_webhook_processed',
        ]);
    }

    public function test_webhook_is_idempotent_on_duplicate_call(): void
    {
        $order = Order::factory()->create([
            'total_amount' => 25000,
            'order_status' => OrderStatus::PENDING->value,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'PAY-IDEMP-WEBHOOK',
            'payment_method' => 'qris',
            'amount' => 25000,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentServiceInterface::class);

        $payload = [
            'order_code' => $order->order_code,
            'payment_code' => $payment->payment_code,
            'amount' => 25000,
            'transaction_status' => 'paid',
            'event_id' => 'evt_repeat_1',
        ];

        $signature = $paymentService->generateWebhookSignature($payload);

        // 1st webhook call
        $res1 = $this->postJson(route('webhooks.payment'), $payload, [
            'X-Payment-Signature' => $signature,
        ]);
        $res1->assertStatus(200);
        $this->assertFalse($res1->json('is_idempotent'));

        // 2nd webhook call (duplicate)
        $res2 = $this->postJson(route('webhooks.payment'), $payload, [
            'X-Payment-Signature' => $signature,
        ]);
        $res2->assertStatus(200);
        $this->assertTrue($res2->json('is_idempotent'));
    }

    /*
    |--------------------------------------------------------------------------
    | 8. ORDER STATUS & PROGRESS UPDATES
    |--------------------------------------------------------------------------
    */

    public function test_booster_progress_update_transitions_order_status(): void
    {
        $jokiUser = User::factory()->create(['role' => 'joki']);
        $jokiProfile = JokiProfile::create([
            'user_id' => $jokiUser->id,
            'nickname' => 'Rank Hunter',
            'status' => 'active',
            'rating' => 5.0,
            'completed_orders_count' => 0,
        ]);

        $order = Order::factory()->create([
            'joki_profile_id' => $jokiProfile->id,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        $response = $this->actingAs($jokiUser)->patch(route('joki.dashboard.orders.progress', $order->order_code), [
            'order_status' => 'completed',
            'progress_note' => 'Target Mythic berhasil dicapai dalam 3 jam!',
        ]);

        $response->assertRedirect();
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->fresh()->order_status);
        $this->assertNotNull($order->fresh()->completed_at);
        $this->assertEquals(1, $jokiProfile->fresh()->completed_orders_count);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $jokiUser->id,
            'action' => 'booster_order_status_updated',
        ]);
    }

    public function test_unauthorized_user_cannot_update_order_progress(): void
    {
        $otherUser = User::factory()->create(['role' => 'joki']);
        JokiProfile::create([
            'user_id' => $otherUser->id,
            'nickname' => 'Other Booster',
            'status' => 'active',
        ]);

        $assignedUser = User::factory()->create(['role' => 'joki']);
        $assignedProfile = JokiProfile::create([
            'user_id' => $assignedUser->id,
            'nickname' => 'Assigned Booster',
            'status' => 'active',
        ]);

        $order = Order::factory()->create([
            'joki_profile_id' => $assignedProfile->id,
            'order_status' => OrderStatus::PROCESSING->value,
        ]);

        // otherUser tries to update order assigned to assignedProfile
        $response = $this->actingAs($otherUser)->patch(route('joki.dashboard.orders.progress', $order->order_code), [
            'order_status' => 'completed',
        ]);

        $response->assertStatus(403);
    }
}
