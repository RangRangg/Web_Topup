<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\JokiService;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected User $joki;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::where('email', 'customer@example.com')->firstOrFail();
        $this->joki = User::where('email', 'joki@example.com')->firstOrFail();
        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->otherCustomer = User::factory()->create([
            'name' => 'Stranger Customer',
            'email' => 'stranger@example.com',
            'role' => 'customer',
        ]);
    }

    /**
     * Test guests are redirected to login for protected dashboard routes.
     */
    public function test_guest_is_redirected_to_login_when_accessing_dashboard_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/dashboard/orders')->assertRedirect(route('login'));
        $this->get('/dashboard/profile')->assertRedirect(route('login'));
        $this->get('/dashboard/notifications')->assertRedirect(route('login'));
    }

    /**
     * Test customer can view dashboard overview with metrics and recent orders.
     */
    public function test_customer_can_view_dashboard_overview_with_metrics_and_recent_orders(): void
    {
        // Create an order for customer
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'order_status' => OrderStatus::COMPLETED->value,
            'total_amount' => 150000,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 150000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $service = JokiService::first();
        OrderItem::create([
            'order_id' => $order->id,
            'joki_service_id' => $service?->id,
            'item_type' => 'joki_service',
            'item_name' => 'Paket Joki Mythic',
            'price' => 150000,
            'quantity' => 1,
            'subtotal' => 150000,
        ]);

        $response = $this->actingAs($this->customer)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Orders');
        $response->assertSee('Active Orders');
        $response->assertSee('Completed Orders');
        $response->assertSee('Total Spending');
        $response->assertSee('Recent Orders');
        $response->assertSee($order->order_code);
        $response->assertSee('Paket Joki Mythic');
    }

    /**
     * Test customer can view orders list and use search and status filters.
     */
    public function test_customer_can_view_and_filter_orders_list(): void
    {
        $order1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'order_code' => 'ORD-TEST-001',
            'order_status' => 'completed',
            'account_id' => '12345678',
            'total_amount' => 100000,
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'order_code' => 'ORD-TEST-002',
            'order_status' => 'pending',
            'account_id' => '87654321',
            'total_amount' => 50000,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'item_type' => 'joki_service',
            'item_name' => 'Joki Bintang Mythic',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'item_type' => 'game_product',
            'item_name' => '86 Diamonds MLBB',
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        // Access all orders
        $response = $this->actingAs($this->customer)->get(route('dashboard.orders'));
        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-001');
        $response->assertSee('ORD-TEST-002');

        // Filter by completed status
        $completedResponse = $this->actingAs($this->customer)->get(route('dashboard.orders', ['status' => 'completed']));
        $completedResponse->assertStatus(200);
        $completedResponse->assertSee('ORD-TEST-001');
        $completedResponse->assertDontSee('ORD-TEST-002');

        // Search by order code
        $searchResponse = $this->actingAs($this->customer)->get(route('dashboard.orders', ['search' => 'TEST-002']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertDontSee('ORD-TEST-001');
        $searchResponse->assertSee('ORD-TEST-002');
    }

    /**
     * Test customer can view their own order detail with complete info.
     */
    public function test_customer_can_view_own_order_detail(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'order_code' => 'ORD-MY-DETAIL',
            'order_status' => 'processing',
            'account_id' => '99887766',
            'account_server' => '2001',
            'account_nickname' => 'ProPlayer99',
            'subtotal' => 200000,
            'total_amount' => 200000,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 200000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $service = JokiService::first();
        OrderItem::create([
            'order_id' => $order->id,
            'joki_service_id' => $service?->id,
            'item_type' => 'joki_service',
            'item_name' => 'Paket Joki Mythic Honor',
            'price' => 200000,
            'quantity' => 1,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($this->customer)->get(route('dashboard.orders.show', $order->order_code));

        $response->assertStatus(200);
        $response->assertSee('ORD-MY-DETAIL');
        $response->assertSee('99887766');
        $response->assertSee('2001');
        $response->assertSee('ProPlayer99');
        $response->assertSee('Paket Joki Mythic Honor');
        $response->assertSee('Timeline Progres Pesanan');
        $response->assertSee('INVOICE RESMI');
    }

    /**
     * Test customer CANNOT view another customer's order (enforced via Laravel Policy).
     */
    public function test_customer_cannot_view_another_customers_order_enforced_by_policy(): void
    {
        $strangerOrder = Order::factory()->create([
            'user_id' => $this->otherCustomer->id,
            'order_code' => 'ORD-STRANGER-01',
            'order_status' => 'processing',
            'total_amount' => 300000,
        ]);

        // Customer attempts to view stranger's order
        $response = $this->actingAs($this->customer)->get(route('dashboard.orders.show', $strangerOrder->order_code));

        // Must return 403 Forbidden via OrderPolicy@view
        $response->assertStatus(403);
    }

    /**
     * Test customer can update their profile information.
     */
    public function test_customer_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->customer)->put(route('dashboard.profile.update'), [
            'name' => 'Nama Baru Pelanggan',
            'phone' => '081299887766',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->customer->refresh();
        $this->assertEquals('Nama Baru Pelanggan', $this->customer->name);
        $this->assertEquals('081299887766', $this->customer->phone);
    }

    /**
     * Test customer can update password with valid current password.
     */
    public function test_customer_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->customer)->put(route('dashboard.profile.password'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->customer->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->customer->password));
    }

    /**
     * Test customer fails to update password when current password is invalid.
     */
    public function test_customer_fails_to_update_password_with_invalid_current_password(): void
    {
        $response = $this->actingAs($this->customer)->put(route('dashboard.profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    /**
     * Test customer can view and manage notifications.
     */
    public function test_customer_can_view_and_manage_notifications(): void
    {
        $notification = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Pesanan Diproses',
            'message' => 'Booster telah mulai mengerjakan akun Anda.',
            'type' => 'order',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)->get(route('dashboard.notifications'));
        $response->assertStatus(200);
        $response->assertSee('Pesanan Diproses');
        $response->assertSee('Booster telah mulai mengerjakan akun Anda.');

        // Mark single notification read
        $readResponse = $this->actingAs($this->customer)->patch(route('dashboard.notifications.read', $notification->id));
        $readResponse->assertRedirect();

        $notification->refresh();
        $this->assertTrue($notification->is_read);

        // Create another unread notification and mark all as read
        $notif2 = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Pembayaran Berhasil',
            'message' => 'Pembayaran Anda telah diverifikasi oleh sistem.',
            'type' => 'payment',
            'is_read' => false,
        ]);

        $markAllResponse = $this->actingAs($this->customer)->post(route('dashboard.notifications.mark-all-read'));
        $markAllResponse->assertRedirect();

        $notif2->refresh();
        $this->assertTrue($notif2->is_read);
    }

    /**
     * Test customer cannot mark another user's notification as read.
     */
    public function test_customer_cannot_mark_another_users_notification_as_read(): void
    {
        $strangerNotif = Notification::create([
            'user_id' => $this->otherCustomer->id,
            'title' => 'Notifikasi Rahasia',
            'message' => 'Hanya untuk stranger.',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->customer)->patch(route('dashboard.notifications.read', $strangerNotif->id));
        $response->assertStatus(403);
    }
}
