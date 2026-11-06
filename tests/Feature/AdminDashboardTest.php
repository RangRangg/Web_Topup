<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected User $joki;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->customer = User::where('email', 'customer@example.com')->firstOrFail();
        $this->joki = User::where('email', 'joki@example.com')->firstOrFail();
    }

    /**
     * Test guests are redirected to login from admin routes.
     */
    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.users'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.reports'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test customers cannot access admin routes and receive HTTP 403.
     */
    public function test_customer_cannot_access_admin_dashboard_receives_403(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->customer)->get(route('admin.users'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->customer)->get(route('admin.games'));
        $response->assertStatus(403);
    }

    /**
     * Test jokis cannot access admin routes and receive HTTP 403.
     */
    public function test_joki_cannot_access_admin_dashboard_receives_403(): void
    {
        $response = $this->actingAs($this->joki)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->joki)->get(route('admin.reports'));
        $response->assertStatus(403);
    }

    /**
     * Test admin can view the dashboard overview with 7 core statistics.
     */
    public function test_admin_can_view_dashboard_overview_with_statistics(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Command Center');
        $response->assertSee('Total Revenue');
        $response->assertSee('Total Orders');
        $response->assertSee('Pending Orders');
        $response->assertSee('Completed');
        $response->assertSee('Customers');
        $response->assertSee('Active Jokis');
        $response->assertSee('Pending Pay');
    }

    /**
     * Test admin can view, search, and filter users.
     */
    public function test_admin_can_view_and_search_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users'));
        $response->assertStatus(200);
        $response->assertSee($this->customer->email);
        $response->assertSee($this->joki->email);

        // Search by customer name
        $response = $this->actingAs($this->admin)->get(route('admin.users', ['search' => $this->customer->name]));
        $response->assertStatus(200);
        $response->assertSee($this->customer->email);

        // Filter by role
        $response = $this->actingAs($this->admin)->get(route('admin.users', ['role' => 'joki']));
        $response->assertStatus(200);
        $response->assertSee($this->joki->email);
    }

    /**
     * Test admin can toggle user active status and an AuditLog is recorded.
     */
    public function test_admin_can_toggle_user_status_and_audit_log_is_recorded(): void
    {
        $targetUser = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.status', $targetUser->id));
        $response->assertRedirect();

        $this->assertFalse($targetUser->fresh()->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'admin_updated_user_status',
        ]);
    }

    /**
     * Test admin cannot deactivate their own account.
     */
    public function test_admin_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.users.status', $this->admin->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->is_active);
    }

    /**
     * Test admin can update user role.
     */
    public function test_admin_can_update_user_role(): void
    {
        $targetUser = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.role', $targetUser->id), [
            'role' => 'joki',
        ]);

        $response->assertRedirect();
        $this->assertEquals('joki', $targetUser->fresh()->role);
        $this->assertNotNull($targetUser->fresh()->jokiProfile);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'admin_updated_user_role',
        ]);
    }

    /**
     * Test admin can update user name, role, and active status via unified edit.
     */
    public function test_admin_can_update_user_name_role_and_status(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Original Name',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Updated Name',
            'role' => 'joki',
            'is_active' => '0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fresh = $targetUser->fresh();
        $this->assertEquals('Updated Name', $fresh->name);
        $this->assertEquals('joki', $fresh->role);
        $this->assertFalse($fresh->is_active);
        $this->assertNotNull($fresh->jokiProfile);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'admin_updated_user',
        ]);
    }

    /**
     * Test admin cannot demote or deactivate own account via unified edit.
     */
    public function test_admin_cannot_demote_or_deactivate_own_account(): void
    {
        // 1. Try demoting role
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin->id), [
            'name' => 'Aethereal Admin',
            'role' => 'customer',
            'is_active' => '1',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('admin', $this->admin->fresh()->role);

        // 2. Try deactivating self
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin->id), [
            'name' => 'Aethereal Admin',
            'role' => 'admin',
            'is_active' => '0',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    /**
     * Test admin can create, update, toggle, and delete games.
     */
    public function test_admin_can_crud_games(): void
    {
        // 1. View games
        $response = $this->actingAs($this->admin)->get(route('admin.games'));
        $response->assertStatus(200);

        // 2. Create game
        $response = $this->actingAs($this->admin)->post(route('admin.games.store'), [
            'name' => 'Apex Legends Mobile',
            'slug' => 'apex-legends-mobile',
            'category' => 'battle_royale',
            'publisher' => 'EA Games',
            'badge' => 'NEW',
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('games', ['slug' => 'apex-legends-mobile']);

        $game = Game::where('slug', 'apex-legends-mobile')->firstOrFail();

        // 3. Update game
        $response = $this->actingAs($this->admin)->put(route('admin.games.update', $game->id), [
            'name' => 'Apex Legends Mobile Updated',
            'slug' => 'apex-legends-mobile',
            'category' => 'battle_royale',
            'publisher' => 'Respawn',
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals('Apex Legends Mobile Updated', $game->fresh()->name);

        // 4. Toggle status
        $response = $this->actingAs($this->admin)->patch(route('admin.games.status', $game->id));
        $response->assertRedirect();
        $this->assertFalse($game->fresh()->is_active);

        // 5. Delete game
        $response = $this->actingAs($this->admin)->delete(route('admin.games.destroy', $game->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('games', ['id' => $game->id]);
    }

    /**
     * Test admin can create, update, toggle, and delete game products.
     */
    public function test_admin_can_crud_game_products(): void
    {
        $game = Game::firstOrFail();

        // 1. View products
        $response = $this->actingAs($this->admin)->get(route('admin.products'));
        $response->assertStatus(200);

        // 2. Create product
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'game_id' => $game->id,
            'name' => '1500 Diamond Pack',
            'code' => 'TEST-1500',
            'category' => 'diamond',
            'price' => 350000,
            'original_price' => 390000,
            'stock' => 50,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('game_products', ['code' => 'TEST-1500']);

        $product = GameProduct::where('code', 'TEST-1500')->firstOrFail();

        // 3. Update product
        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product->id), [
            'game_id' => $game->id,
            'name' => '1500 Diamond Pack Promo',
            'code' => 'TEST-1500',
            'category' => 'diamond',
            'price' => 320000,
            'stock' => 80,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals(320000, (float) $product->fresh()->price);
        $this->assertEquals(80, $product->fresh()->stock);

        // 4. Toggle status
        $response = $this->actingAs($this->admin)->patch(route('admin.products.status', $product->id));
        $response->assertRedirect();
        $this->assertFalse($product->fresh()->is_active);

        // 5. Delete product
        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('game_products', ['id' => $product->id]);
    }

    /**
     * Test admin can create, update, toggle, and delete joki services.
     */
    public function test_admin_can_crud_joki_services(): void
    {
        $game = Game::firstOrFail();

        // 1. View joki services
        $response = $this->actingAs($this->admin)->get(route('admin.joki-services'));
        $response->assertStatus(200);

        // 2. Create joki service
        $response = $this->actingAs($this->admin)->post(route('admin.joki-services.store'), [
            'game_id' => $game->id,
            'name' => 'Paket Push Mythic Fast Track',
            'service_type' => 'rank',
            'rank_from' => 'Legend I',
            'rank_to' => 'Mythic 25 Stars',
            'price' => 250000,
            'original_price' => 290000,
            'estimated_duration' => '6-12 Jam',
            'is_popular' => 1,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('joki_services', ['name' => 'Paket Push Mythic Fast Track']);

        $service = JokiService::where('name', 'Paket Push Mythic Fast Track')->firstOrFail();

        // 3. Update joki service
        $response = $this->actingAs($this->admin)->put(route('admin.joki-services.update', $service->id), [
            'game_id' => $game->id,
            'name' => 'Paket Push Mythic Fast Track Updated',
            'service_type' => 'rank',
            'price' => 220000,
            'estimated_duration' => '4-8 Jam',
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals(220000, (float) $service->fresh()->price);

        // 4. Toggle status
        $response = $this->actingAs($this->admin)->patch(route('admin.joki-services.status', $service->id));
        $response->assertRedirect();
        $this->assertFalse($service->fresh()->is_active);

        // 5. Delete service
        $response = $this->actingAs($this->admin)->delete(route('admin.joki-services.destroy', $service->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('joki_services', ['id' => $service->id]);
    }

    /**
     * Test admin can view orders and order detail.
     */
    public function test_admin_can_view_orders_and_order_detail(): void
    {
        $order = Order::firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('admin.orders'));
        $response->assertStatus(200);
        $response->assertSee($order->order_code);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order->order_code));
        $response->assertStatus(200);
        $response->assertSee($order->order_code);
        $response->assertSee($order->customer_name);
    }

    /**
     * Test admin can update order status and payment status.
     */
    public function test_admin_can_update_order_status_and_payment_status(): void
    {
        $order = Order::firstOrFail();

        $response = $this->actingAs($this->admin)->patch(route('admin.orders.status', $order->order_code), [
            'order_status' => 'processing',
            'payment_status' => 'paid',
            'progress_note' => 'Admin menugaskan booster untuk mulai bermain.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->order_status);
        $this->assertEquals('paid', $order->fresh()->payment?->status);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'admin_updated_order',
        ]);
    }

    /**
     * Test admin can view and update payments oversight.
     */
    public function test_admin_can_view_and_update_payments(): void
    {
        $payment = Payment::firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('admin.payments'));
        $response->assertStatus(200);
        $response->assertSee($payment->payment_code);

        $response = $this->actingAs($this->admin)->patch(route('admin.payments.status', $payment->id), [
            'status' => 'paid',
        ]);

        $response->assertRedirect();
        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'admin_updated_payment',
        ]);
    }

    /**
     * Test admin can CRUD promotions.
     */
    public function test_admin_can_crud_promotions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.promotions'));
        $response->assertStatus(200);

        // 1. Create promo
        $response = $this->actingAs($this->admin)->post(route('admin.promotions.store'), [
            'code' => 'DISC25',
            'name' => 'Diskon 25 Persen',
            'discount_type' => 'percentage',
            'discount_value' => 25,
            'min_spend' => 50000,
            'max_discount' => 20000,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['code' => 'DISC25']);

        $promo = Promotion::where('code', 'DISC25')->firstOrFail();

        // 2. Update promo
        $response = $this->actingAs($this->admin)->put(route('admin.promotions.update', $promo->id), [
            'code' => 'DISC30',
            'name' => 'Diskon 30 Persen Spesial',
            'discount_type' => 'percentage',
            'discount_value' => 30,
            'min_spend' => 40000,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals('DISC30', $promo->fresh()->code);

        // 3. Toggle status
        $response = $this->actingAs($this->admin)->patch(route('admin.promotions.status', $promo->id));
        $response->assertRedirect();
        $this->assertFalse($promo->fresh()->is_active);

        // 4. Delete promo
        $response = $this->actingAs($this->admin)->delete(route('admin.promotions.destroy', $promo->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    /**
     * Test admin can view reports and analytics.
     */
    public function test_admin_can_view_reports_and_analytics(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports'));
        $response->assertStatus(200);
        $response->assertSee('Pendapatan Harian');
        $response->assertSee('Pendapatan Bulanan');
        $response->assertSee('Distribusi Tipe Pesanan');
        $response->assertSee('Completion Rate');
    }

    /**
     * Test admin can view audit logs.
     */
    public function test_admin_can_view_audit_logs(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'admin_test_event',
            'ip_address' => '127.0.0.1',
            'details' => 'Testing immutable audit log entry.',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs'));
        $response->assertStatus(200);
        $response->assertSee('admin_test_event');
        $response->assertSee('Testing immutable audit log entry.');
    }
}
