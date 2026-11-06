<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\AuditLog;
use App\Models\JokiProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JokiDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $joki;

    protected JokiProfile $jokiProfile;

    protected User $otherJoki;

    protected JokiProfile $otherJokiProfile;

    protected User $customer;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->joki = User::where('email', 'joki@example.com')->firstOrFail();
        $this->jokiProfile = $this->joki->jokiProfile;

        $this->customer = User::where('email', 'customer@example.com')->firstOrFail();
        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();

        // Create a secondary booster for authorization isolation tests
        $this->otherJoki = User::factory()->create([
            'name' => 'Other Booster',
            'email' => 'otherjoki@example.com',
            'role' => 'joki',
        ]);

        $this->otherJokiProfile = JokiProfile::create([
            'user_id' => $this->otherJoki->id,
            'nickname' => 'OtherBoosterPro',
            'status' => 'active',
            'rating' => 4.9,
            'completed_orders_count' => 5,
            'is_verified' => true,
        ]);
    }

    /**
     * Test guest is redirected to login when accessing joki dashboard routes.
     */
    public function test_guest_is_redirected_to_login_when_accessing_joki_dashboard_routes(): void
    {
        $this->get('/joki/dashboard')->assertRedirect(route('login'));
        $this->get('/joki/dashboard/orders')->assertRedirect(route('login'));
        $this->get('/joki/dashboard/profile')->assertRedirect(route('login'));
        $this->get('/joki/dashboard/earnings')->assertRedirect(route('login'));
    }

    /**
     * Test customer cannot access joki dashboard routes (enforced via role:joki middleware).
     */
    public function test_customer_cannot_access_joki_dashboard_routes_enforced_by_role_middleware(): void
    {
        $response = $this->actingAs($this->customer)->get('/joki/dashboard');
        $response->assertStatus(403);

        $ordersResponse = $this->actingAs($this->customer)->get('/joki/dashboard/orders');
        $ordersResponse->assertStatus(403);
    }

    /**
     * Test joki cannot access admin routes (enforced via role:admin middleware).
     */
    public function test_joki_cannot_access_admin_routes_enforced_by_role_middleware(): void
    {
        $response = $this->actingAs($this->joki)->get('/admin/orders');
        $response->assertStatus(403);
    }

    /**
     * Test joki can view dashboard overview with metrics, pool orders, and active orders.
     */
    public function test_joki_can_view_dashboard_overview_with_metrics_and_orders(): void
    {
        // 1. Create an available pool order
        $poolOrder = Order::factory()->create([
            'joki_profile_id' => null,
            'order_code' => 'ORD-POOL-001',
            'order_status' => OrderStatus::WAITING_ASSIGNMENT->value,
            'total_amount' => 100000,
        ]);
        Payment::factory()->create([
            'order_id' => $poolOrder->id,
            'status' => 'paid',
        ]);
        OrderItem::create([
            'order_id' => $poolOrder->id,
            'item_type' => 'joki_service',
            'item_name' => 'Joki MLBB Rank Mythic',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        // 2. Create an active assigned order
        $activeOrder = Order::factory()->create([
            'joki_profile_id' => $this->jokiProfile->id,
            'order_code' => 'ORD-ACTIVE-001',
            'order_status' => OrderStatus::PROCESSING->value,
            'total_amount' => 150000,
        ]);
        Payment::factory()->create([
            'order_id' => $activeOrder->id,
            'status' => 'paid',
        ]);
        OrderItem::create([
            'order_id' => $activeOrder->id,
            'item_type' => 'joki_service',
            'item_name' => 'Joki Valorant Ascendant',
            'price' => 150000,
            'quantity' => 1,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->joki)->get(route('joki.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Available Orders');
        $response->assertSee('Active Orders');
        $response->assertSee('Completed Orders');
        $response->assertSee('Total Earnings');
        $response->assertSee('ORD-POOL-001');
        $response->assertSee('ORD-ACTIVE-001');
        $response->assertSee('Joki MLBB Rank Mythic');
        $response->assertSee('Joki Valorant Ascendant');
    }

    /**
     * Test joki can view orders list with assigned tab and available pool tab.
     */
    public function test_joki_can_view_orders_list_with_tabs(): void
    {
        // Assigned order
        $assignedOrder = Order::factory()->create([
            'joki_profile_id' => $this->jokiProfile->id,
            'order_code' => 'ORD-MY-ASSIGNED',
            'order_status' => 'processing',
            'total_amount' => 80000,
        ]);
        OrderItem::create([
            'order_id' => $assignedOrder->id,
            'item_type' => 'joki_service',
            'item_name' => 'Paket Joki Mythic Honor',
            'price' => 80000,
            'quantity' => 1,
            'subtotal' => 80000,
        ]);

        // Available order
        $availableOrder = Order::factory()->create([
            'joki_profile_id' => null,
            'order_code' => 'ORD-IN-POOL',
            'order_status' => 'waiting_assignment',
            'total_amount' => 120000,
        ]);
        OrderItem::create([
            'order_id' => $availableOrder->id,
            'item_type' => 'joki_service',
            'item_name' => 'Paket Joki Valorant Radiant',
            'price' => 120000,
            'quantity' => 1,
            'subtotal' => 120000,
        ]);

        // View assigned tab
        $assignedResponse = $this->actingAs($this->joki)->get(route('joki.dashboard.orders', ['tab' => 'assigned']));
        $assignedResponse->assertStatus(200);
        $assignedResponse->assertSee('ORD-MY-ASSIGNED');
        $assignedResponse->assertDontSee('ORD-IN-POOL');

        // View available tab
        $availableResponse = $this->actingAs($this->joki)->get(route('joki.dashboard.orders', ['tab' => 'available']));
        $availableResponse->assertStatus(200);
        $availableResponse->assertSee('ORD-IN-POOL');
        $availableResponse->assertDontSee('ORD-MY-ASSIGNED');
    }

    /**
     * Test joki can view detail of their own assigned order.
     */
    public function test_joki_can_view_assigned_order_detail(): void
    {
        $order = Order::factory()->create([
            'joki_profile_id' => $this->jokiProfile->id,
            'order_code' => 'ORD-DETAIL-ASSIGNED',
            'order_status' => 'processing',
            'account_id' => '12345678',
            'account_server' => '9901',
            'account_nickname' => 'LordSaitama',
            'login_via' => 'moonton',
            'total_amount' => 200000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'joki_service',
            'item_name' => 'Joki MLBB Mythic Immortal',
            'price' => 200000,
            'quantity' => 1,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->joki)->get(route('joki.dashboard.orders.show', $order->order_code));

        $response->assertStatus(200);
        $response->assertSee('ORD-DETAIL-ASSIGNED');
        $response->assertSee('12345678');
        $response->assertSee('9901');
        $response->assertSee('LordSaitama');
        $response->assertSee('Update Progres & Status Pengerjaan', false);
    }

    /**
     * Test joki can view detail of available pool order.
     */
    public function test_joki_can_view_available_order_detail_in_pool(): void
    {
        $poolOrder = Order::factory()->create([
            'joki_profile_id' => null,
            'order_code' => 'ORD-POOL-DETAIL',
            'order_status' => 'waiting_assignment',
            'total_amount' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $poolOrder->id,
            'item_type' => 'joki_service',
            'item_name' => 'Joki Free Fire Master',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($this->joki)->get(route('joki.dashboard.orders.show', $poolOrder->order_code));

        $response->assertStatus(200);
        $response->assertSee('ORD-POOL-DETAIL');
        $response->assertSee('Ambil Tugas Ini Sekarang');
    }

    /**
     * Test joki CANNOT view order assigned to another booster (enforced via Laravel Policy).
     */
    public function test_joki_cannot_view_order_assigned_to_another_booster(): void
    {
        $otherOrder = Order::factory()->create([
            'joki_profile_id' => $this->otherJokiProfile->id,
            'order_code' => 'ORD-OTHER-BOOSTER',
            'order_status' => 'processing',
            'total_amount' => 150000,
        ]);

        $response = $this->actingAs($this->joki)->get(route('joki.dashboard.orders.show', $otherOrder->order_code));

        // Enforced by OrderPolicy@view: must return 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test joki can claim available order from pool and records AuditLog.
     */
    public function test_joki_can_claim_available_order_from_pool_and_records_audit_log(): void
    {
        $poolOrder = Order::factory()->create([
            'joki_profile_id' => null,
            'order_code' => 'ORD-CLAIM-ME',
            'order_status' => 'waiting_assignment',
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->joki)->post(route('joki.dashboard.orders.claim', $poolOrder->order_code));

        $response->assertRedirect(route('joki.dashboard.orders.show', $poolOrder->order_code));
        $response->assertSessionHas('success');

        $poolOrder->refresh();
        $this->assertEquals($this->jokiProfile->id, $poolOrder->joki_profile_id);
        $this->assertEquals(OrderStatus::PROCESSING->value, $poolOrder->order_status);

        // Verify AuditLog was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->joki->id,
            'action' => 'booster_claimed_order',
        ]);
    }

    /**
     * Test joki cannot claim order that is already assigned to another booster.
     */
    public function test_joki_cannot_claim_order_already_assigned_to_someone_else(): void
    {
        $assignedOrder = Order::factory()->create([
            'joki_profile_id' => $this->otherJokiProfile->id,
            'order_code' => 'ORD-ALREADY-ASSIGNED',
            'order_status' => 'processing',
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->joki)->post(route('joki.dashboard.orders.claim', $assignedOrder->order_code));

        // Enforced via OrderPolicy@assign: must return 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test joki can update order progress and add progress note, with AuditLog recorded.
     */
    public function test_joki_can_update_order_progress_and_progress_note(): void
    {
        $order = Order::factory()->create([
            'joki_profile_id' => $this->jokiProfile->id,
            'order_code' => 'ORD-UPDATE-PROGRESS',
            'order_status' => 'processing',
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->joki)->patch(route('joki.dashboard.orders.progress', $order->order_code), [
            'order_status' => 'processing',
            'progress_note' => 'Saat ini sudah mencapai Mythic bintang 12. Sedang push lagi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('Saat ini sudah mencapai Mythic bintang 12. Sedang push lagi.', $order->progress_note);

        // Verify AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->joki->id,
            'action' => 'booster_order_status_updated',
        ]);
    }

    /**
     * Test joki can mark order as completed, incrementing completed count and setting completed_at.
     */
    public function test_joki_can_mark_order_as_completed(): void
    {
        $initialCompletedCount = $this->jokiProfile->completed_orders_count ?? 0;

        $order = Order::factory()->create([
            'joki_profile_id' => $this->jokiProfile->id,
            'order_code' => 'ORD-FINISH-NOW',
            'order_status' => 'processing',
            'total_amount' => 200000,
        ]);

        $response = $this->actingAs($this->joki)->patch(route('joki.dashboard.orders.progress', $order->order_code), [
            'order_status' => 'completed',
            'progress_note' => 'Target Mythic Glory telah tercapai 100%. Silakan cek akun Anda.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(OrderStatus::COMPLETED->value, $order->order_status);
        $this->assertNotNull($order->completed_at);

        $this->jokiProfile->refresh();
        $this->assertEquals($initialCompletedCount + 1, $this->jokiProfile->completed_orders_count);

        // Verify AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->joki->id,
            'action' => 'booster_order_status_updated',
        ]);
    }

    /**
     * Test joki cannot update progress of another booster's order.
     */
    public function test_joki_cannot_update_progress_of_another_boosters_order(): void
    {
        $otherOrder = Order::factory()->create([
            'joki_profile_id' => $this->otherJokiProfile->id,
            'order_code' => 'ORD-UPDATE-FORBIDDEN',
            'order_status' => 'processing',
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->joki)->patch(route('joki.dashboard.orders.progress', $otherOrder->order_code), [
            'order_status' => 'completed',
            'progress_note' => 'Trying to hijack other booster order.',
        ]);

        // Enforced via OrderPolicy@update: must return 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test joki can update profile and availability status.
     */
    public function test_joki_can_update_profile_and_status(): void
    {
        $response = $this->actingAs($this->joki)->put(route('joki.dashboard.profile.update'), [
            'nickname' => 'ApexLegendPro',
            'status' => 'busy',
            'phone' => '081288776655',
            'bio' => 'Top global Fanny & Hayabusa MLBB.',
            'highest_rank' => 'Mythical Immortal 200 Stars',
            'specialized_games' => ['Mobile Legends', 'Valorant'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->jokiProfile->refresh();
        $this->assertEquals('ApexLegendPro', $this->jokiProfile->nickname);
        $this->assertEquals('busy', $this->jokiProfile->status);
        $this->assertEquals('081288776655', $this->jokiProfile->phone);
        $this->assertEquals('Mythical Immortal 200 Stars', $this->jokiProfile->highest_rank);
        $this->assertContains('Mobile Legends', $this->jokiProfile->specialized_games);
    }

    /**
     * Test joki can view earnings page with 80% net commission breakdown.
     */
    public function test_joki_can_view_earnings_page(): void
    {
        $order = Order::factory()->create([
            'joki_profile_id' => $this->jokiProfile->id,
            'order_code' => 'ORD-EARN-01',
            'order_status' => OrderStatus::COMPLETED->value,
            'total_amount' => 200000,
            'completed_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'joki_service',
            'item_name' => 'Joki Valorant Platinum ke Diamond',
            'price' => 200000,
            'quantity' => 1,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->joki)->get(route('joki.dashboard.earnings'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Pendapatan & Komisi', false);
        $response->assertSee('Total Komisi Bersih (80%)', false);
        $response->assertSee('ORD-EARN-01');
        // 80% of 200.000 = 160.000
        $response->assertSee('160.000');
    }
}
