<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminTelegramTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->customer = User::where('email', 'customer@example.com')->firstOrFail();
    }

    /**
     * Test guest cannot access telegram settings.
     */
    public function test_guest_is_redirected_from_telegram_settings(): void
    {
        $response = $this->get(route('admin.telegram'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test customer cannot access telegram settings.
     */
    public function test_customer_cannot_access_telegram_settings(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.telegram'));
        $response->assertStatus(403);
    }

    /**
     * Test admin can access telegram settings page.
     */
    public function test_admin_can_access_telegram_settings_page(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/getMe' => Http::response([
                'ok' => true,
                'result' => [
                    'id' => 123456789,
                    'is_bot' => true,
                    'first_name' => 'AetherelBot',
                    'username' => 'AetherelBot',
                    'can_join_groups' => true,
                ],
            ]),
            'https://api.telegram.org/bot*/getUpdates' => Http::response([
                'ok' => true,
                'result' => [],
            ]),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.telegram'));
        $response->assertStatus(200);
        $response->assertSeeText('Koneksi & Integrasi Telegram Bot');
        $response->assertSeeText('Pengaturan API & Target Notifikasi');
    }

    /**
     * Test admin can update telegram settings and it creates an audit log.
     */
    public function test_admin_can_update_telegram_settings(): void
    {
        $newBotToken = '987654321:XYZ-TEST-TOKEN-123456789';
        $newChatId = '555666777';

        $response = $this->actingAs($this->admin)->post(route('admin.telegram.update'), [
            'bot_token' => $newBotToken,
            'chat_id' => $newChatId,
            'purchase_chat_id' => '111222333',
            'joki_chat_id' => '444555666',
            'enabled' => '1',
        ]);

        $response->assertRedirect(route('admin.telegram'));
        $response->assertSessionHas('success');

        // Verify service settings
        $service = app(TelegramService::class);
        $settings = $service->getSettings();
        $this->assertEquals($newBotToken, $settings['bot_token']);
        $this->assertEquals($newChatId, $settings['chat_id']);
        $this->assertEquals('111222333', $settings['purchase_chat_id']);
        $this->assertEquals('444555666', $settings['joki_chat_id']);
        $this->assertTrue($settings['enabled']);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'update_telegram_api',
        ]);
    }

    /**
     * Test admin can test telegram bot connection via API.
     */
    public function test_admin_can_test_bot_connection(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/getMe' => Http::response([
                'ok' => true,
                'result' => [
                    'id' => 999888777,
                    'is_bot' => true,
                    'first_name' => 'NexusTestBot',
                    'username' => 'NexusTestBot',
                ],
            ]),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.telegram.test'), [
            'bot_token' => '123456:FAKE-BOT-TOKEN',
            'send_message' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'bot' => [
                'username' => 'NexusTestBot',
            ],
        ]);
    }

    /**
     * Test admin can send a test notification to a chat ID.
     */
    public function test_admin_can_send_test_message(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/sendMessage' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 101,
                ],
            ]),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.telegram.test'), [
            'bot_token' => '123456:FAKE-BOT-TOKEN',
            'chat_id' => '5170978169',
            'send_message' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test admin can detect recent chats from telegram updates.
     */
    public function test_admin_can_detect_recent_chats(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*/getUpdates' => Http::response([
                'ok' => true,
                'result' => [
                    [
                        'update_id' => 1,
                        'message' => [
                            'message_id' => 10,
                            'chat' => [
                                'id' => 5170978169,
                                'type' => 'private',
                                'first_name' => 'Budi',
                                'last_name' => 'Gamer',
                                'username' => 'budigamer',
                            ],
                            'text' => '/start',
                            'date' => 1700000000,
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.telegram.detect-chats'), [
            'bot_token' => '123456:FAKE-BOT-TOKEN',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 1,
            'chats' => [
                [
                    'id' => '5170978169',
                    'name' => 'Budi Gamer',
                    'username' => '@budigamer',
                ],
            ],
        ]);
    }
}
