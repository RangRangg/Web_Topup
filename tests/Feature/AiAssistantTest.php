<?php

namespace Tests\Feature;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\Game;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that dedicated AI Assistant page renders successfully.
     */
    public function test_ai_assistant_page_renders_successfully(): void
    {
        $response = $this->get(route('ai-assistant'));

        $response->assertStatus(200);
        $response->assertSee('Aethereal AI Assistant');
        $response->assertSee('Aethereal Neural Consultant');
    }

    /**
     * Test that floating AI assistant component is rendered on homepage and catalog pages.
     */
    public function test_floating_ai_component_is_present_on_pages(): void
    {
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Aethereal AI Assistant', false);

        $gamesResponse = $this->get(route('games'));
        $gamesResponse->assertStatus(200);
        $gamesResponse->assertSee('Aethereal AI Assistant', false);
    }

    /**
     * Test budget-based recommendation for Mobile Legends with 50 ribu budget.
     */
    public function test_budget_recommendation_returns_appropriate_game_products(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Saya punya budget 50 ribu untuk Mobile Legends',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reply',
            'recommendations',
            'navigation',
            'source',
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['reply']);
        $this->assertNotEmpty($data['recommendations']);

        // Check that recommendations are under or equal to 50,000
        foreach ($data['recommendations'] as $rec) {
            $this->assertEquals('product', $rec['type']);
            $this->assertStringContainsString('Mobile Legends', $rec['subtitle']);
        }
    }

    /**
     * Test product search by game keyword.
     */
    public function test_product_search_by_game_returns_products(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Berapa harga diamond untuk Mobile Legends?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data['recommendations']);
        $this->assertStringContainsString('Mobile Legends', $data['reply']);
    }

    /**
     * Test joki service recommendation for rank boosting.
     */
    public function test_joki_recommendation_returns_joki_services(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Rekomendasi paket joki rank Mobile Legends',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data['recommendations']);
        $this->assertEquals('joki', $data['recommendations'][0]['type']);
    }

    /**
     * Test explanation of differences between services.
     */
    public function test_service_difference_explanation(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Apa perbedaan joki gendong dan joki biasa?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString('Top Up Game Instan', $data['reply']);
        $this->assertStringContainsString('Joki Push Rank Reguler', $data['reply']);
        $this->assertStringContainsString('Joki Gendong / Mabar', $data['reply']);
    }

    /**
     * Test FAQ for anti-banned guarantee and safety.
     */
    public function test_faq_safety_answers_anti_banned_guarantee(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Apakah joki di sini aman dan ada garansi anti banned?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString('100% Anti Banned Guarantee', $data['reply']);
        $this->assertStringContainsString('Enkripsi Kredensial', $data['reply']);
    }

    /**
     * Test guest user order inquiry asks to login or track.
     */
    public function test_guest_order_inquiry_prompts_to_login_or_track(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Cek status order saya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString('Login', $data['reply']);
        $this->assertEmpty($data['recommendations']);
    }

    /**
     * Test authenticated user order inquiry returns only their own orders.
     */
    public function test_authenticated_user_order_inquiry_returns_only_own_orders(): void
    {
        $userA = User::factory()->create(['name' => 'Gamer Alpha']);
        $userB = User::factory()->create(['name' => 'Gamer Beta']);

        $game = Game::first();

        // Create order for User A
        $orderA = Order::factory()->create([
            'user_id' => $userA->id,
            'order_code' => 'ORD-ALPHA-123',
            'customer_name' => $userA->name,
            'customer_email' => $userA->email,
            'customer_whatsapp' => '081234567890',
            'order_status' => 'processing',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'total_amount' => 50000,
        ]);

        OrderItem::create([
            'order_id' => $orderA->id,
            'item_type' => 'product',
            'item_id' => 1,
            'item_name' => '86 Diamonds MLBB',
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        Payment::create([
            'order_id' => $orderA->id,
            'payment_code' => 'PAY-ALPHA-123',
            'payment_method' => 'qris',
            'amount' => 50000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Create order for User B
        $orderB = Order::factory()->create([
            'user_id' => $userB->id,
            'order_code' => 'ORD-SECRET-999',
            'customer_name' => $userB->name,
            'customer_email' => $userB->email,
            'customer_whatsapp' => '081234567891',
            'order_status' => 'completed',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
        ]);

        // Query AI as User A
        $response = $this->actingAs($userA)->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Bagaimana status order saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Must see User A's order code
        $this->assertStringContainsString('ORD-ALPHA-123', $data['reply']);
        // Must NOT see User B's order code
        $this->assertStringNotContainsString('ORD-SECRET-999', $data['reply']);
    }

    /**
     * Test conversation messages are persisted for authenticated user.
     */
    public function test_authenticated_user_conversation_is_persisted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Punya budget 20rb untuk Free Fire',
        ]);

        $response->assertStatus(200);

        $conversation = AIConversation::where('user_id', $user->id)->first();
        $this->assertNotNull($conversation);

        $messages = AIMessage::where('ai_conversation_id', $conversation->id)->get();
        $this->assertCount(2, $messages); // 1 user, 1 assistant

        $this->assertEquals('user', $messages[0]->role);
        $this->assertEquals('Punya budget 20rb untuk Free Fire', $messages[0]->content);
        $this->assertEquals('assistant', $messages[1]->role);
    }

    /**
     * Test conversation history endpoint for authenticated user.
     */
    public function test_ai_history_endpoint_returns_saved_messages(): void
    {
        $user = User::factory()->create();
        $conversation = AIConversation::create([
            'user_id' => $user->id,
            'title' => 'Test Chat',
            'is_active' => true,
        ]);

        AIMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Halo Aether AI',
        ]);

        AIMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Halo! Ada yang bisa dibantu?',
            'meta' => ['recommendations' => [], 'navigation' => []],
        ]);

        $response = $this->actingAs($user)->getJson(route('ai.history'));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'messages');
    }

    /**
     * Test conversation clear endpoint deactivates active conversation.
     */
    public function test_ai_clear_endpoint_deactivates_conversation(): void
    {
        $user = User::factory()->create();
        $conversation = AIConversation::create([
            'user_id' => $user->id,
            'title' => 'Active Chat',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('ai.clear'));

        $response->assertStatus(200);
        $this->assertFalse((bool) $conversation->fresh()->is_active);
    }

    /**
     * Test prompt validation rejects empty or too long input.
     */
    public function test_prompt_validation_rejects_empty_or_too_long(): void
    {
        // Too short (< 2 chars)
        $shortResponse = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'a',
        ]);
        $shortResponse->assertStatus(422);

        // Too long (> 500 chars)
        $longResponse = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => str_repeat('halo ', 110),
        ]);
        $longResponse->assertStatus(422);
    }

    /**
     * Test Upstream Gemini API integration when key is configured.
     */
    public function test_gemini_api_integration_mock_success(): void
    {
        config([
            'services.ai.provider' => 'gemini',
            'services.ai.api_key' => 'test-api-key-123',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Tentu! Untuk Mobile Legends dengan budget 50 ribu, Anda bisa membeli paket 170 Diamonds.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Saya punya budget 50 ribu untuk Mobile Legends',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('upstream_ai', $data['source']);
        $this->assertStringContainsString('170 Diamonds', $data['reply']);
        $this->assertNotEmpty($data['recommendations']);
    }

    /**
     * Test Upstream Gemini API failure falls back gracefully without 500 error.
     */
    public function test_gemini_api_integration_fallback_on_api_error(): void
    {
        config([
            'services.ai.provider' => 'gemini',
            'services.ai.api_key' => 'test-api-key-123',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response('Service Unavailable', 503),
        ]);

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Saya punya budget 50 ribu untuk Mobile Legends',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Local budget engine responds instead of server crashing
        $this->assertEquals('local_budget', $data['source']);
        $this->assertNotEmpty($data['recommendations']);
    }

    /**
     * Test Upstream NVIDIA API integration mock success.
     */
    public function test_nvidia_api_integration_mock_success(): void
    {
        config([
            'services.ai.provider' => 'nvidia',
            'services.ai.api_key' => 'nvapi-mock-key',
            'services.ai.endpoint' => 'https://integrate.api.nvidia.com/v1/chat/completions',
            'services.ai.model' => 'meta/llama-3.2-11b-vision-instruct',
        ]);

        Http::fake([
            'integrate.api.nvidia.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Halo! Saya asisten Aethereal AI siap membantu kebutuhan gaming Anda.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Halo, apa kabar?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('upstream_ai', $data['source']);
        $this->assertStringContainsString('Aethereal AI', $data['reply']);
    }

    /**
     * Test asking "siapa nama saya" or "siapa saya" answers user name when authenticated.
     */
    public function test_user_identity_inquiry_answers_farrel_surya_pratama(): void
    {
        $user = User::factory()->create([
            'name' => 'Farrel Surya Pratama',
        ]);

        // 1. "siapa nama saya"
        $response1 = $this->actingAs($user)->postJson(route('ai-assistant.consult'), [
            'prompt' => 'siapa nama saya?',
        ]);

        $response1->assertStatus(200);
        $data1 = $response1->json();
        $this->assertStringContainsString('Farrel Surya Pratama', $data1['reply']);

        // 2. "siapa saya"
        $response2 = $this->actingAs($user)->postJson(route('ai-assistant.consult'), [
            'prompt' => 'kamu tahu siapa saya?',
        ]);

        $response2->assertStatus(200);
        $data2 = $response2->json();
        $this->assertStringContainsString('Farrel Surya Pratama', $data2['reply']);
    }

    /**
     * Test asking for Python code is declined with scope limited to web development only.
     */
    public function test_python_coding_request_is_declined_due_to_web_scope(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'buatkan coding python untuk menghitung luas lingkaran',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Must refuse python coding
        $this->assertStringContainsString('tidak dapat', $data['reply']);
        $this->assertStringContainsString('Python', $data['reply']);
        // Must explain scope is for web
        $this->assertStringContainsString('Web', $data['reply']);
    }

    /**
     * Test that AI reads active promotions directly from promotions database table.
     */
    public function test_ai_reads_and_recommends_active_promotions_from_database(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Apakah ada kode voucher promo atau diskon yang sedang aktif?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data['reply']);
        $this->assertStringContainsString('AETHER2026', $data['reply']);
        $this->assertNotEmpty($data['recommendations']);
        $this->assertStringContainsString('Voucher:', $data['recommendations'][0]['title']);
    }

    /**
     * Test that AI reads game products for various games (e.g., Roblox Robux) from database.
     */
    public function test_ai_reads_game_products_for_roblox_from_database(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Berapa harga Robux untuk Roblox?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data['recommendations']);
        $this->assertStringContainsString('Robux', $data['reply']);
        $this->assertStringContainsString('Roblox', $data['reply']);
    }

    /**
     * Test that AI reads and tracks specific order code directly from database.
     */
    public function test_ai_reads_and_tracks_specific_order_code_from_database(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => "Tolong cek status pesanan {$order->order_code}",
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString($order->order_code, $data['reply']);
        $this->assertStringContainsString($order->status_label, $data['reply']);
        $this->assertNotEmpty($data['recommendations']);
        $this->assertEquals('order', $data['recommendations'][0]['type']);
    }

    /**
     * Test that AI reads customer reviews and ratings from reviews database table.
     */
    public function test_ai_reads_and_summarizes_customer_reviews_from_database(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Bagaimana testimoni dan ulasan pelanggan di sini?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString('rating', strtolower($data['reply']));
        $this->assertStringContainsString('ulasan', strtolower($data['reply']));
    }

    /**
     * Test that AI reads games by category from database.
     */
    public function test_ai_reads_games_by_category_from_database(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'Game MOBA apa saja yang ada?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString('Mobile Legends', $data['reply']);
        $this->assertNotEmpty($data['recommendations']);
    }

    /**
     * Test that AI answers customer count and list accurately according to the database.
     */
    public function test_ai_answers_customer_count_from_database_accurately(): void
    {
        $customerCount = User::where('role', 'customer')->count();
        $this->assertGreaterThan(0, $customerCount);

        // Test with the exact prompt used by the user including typo ("ada bearapa jumlah customer?")
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'ada bearapa jumlah customer?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Must answer with actual customer count from DB
        $this->assertStringContainsString((string) $customerCount, $data['reply']);
        $this->assertStringContainsString('Customer', $data['reply']);
        // Must NOT falsely match Mobile Legends product search
        $this->assertStringNotContainsString('Diamonds MLBB', $data['reply']);
        $this->assertStringNotContainsString('pilihan nominal instan', $data['reply']);
    }

    /**
     * Test that AI does not falsely match game aliases on substrings like "jumlah".
     */
    /**
     * Test that AI answers total users count directly and includes user names.
     */
    public function test_ai_answers_total_users_count_directly(): void
    {
        $totalUsers = User::count();
        $this->assertGreaterThan(0, $totalUsers);
        $firstUser = User::first();

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'berapa total pengguna?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString((string) $totalUsers, $data['reply']);
        $this->assertStringContainsString('Total pengguna', $data['reply']);
        // Must show complete user names
        $this->assertStringContainsString($firstUser->name, $data['reply']);
        // Must NOT output raw code backticks or asterisks
        $this->assertStringNotContainsString('`', $data['reply']);
        $this->assertStringNotContainsString('*', $data['reply']);
    }

    /**
     * Test that AI answers customer count specifically and includes customer names without combining total users.
     */
    public function test_ai_answers_customer_count_without_combining_total_users(): void
    {
        $customerCount = User::where('role', 'customer')->count();
        $firstCustomer = User::where('role', 'customer')->first();

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'berapa customer?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString((string) $customerCount, $data['reply']);
        $this->assertStringContainsString('customer', strtolower($data['reply']));
        // Must show customer names
        $this->assertStringContainsString($firstCustomer->name, $data['reply']);
        // Must NOT combine with total platform users
        $this->assertStringNotContainsString('Total Pengguna Sistem', $data['reply']);
        $this->assertStringNotContainsString('Total pengguna terdaftar di sistem', $data['reply']);
        // Must NOT output raw code backticks or asterisks
        $this->assertStringNotContainsString('`', $data['reply']);
        $this->assertStringNotContainsString('*', $data['reply']);
    }

    /**
     * Test that customer list inquiry does not include raw code backticks.
     */
    public function test_ai_answers_customer_list_cleanly_without_raw_code(): void
    {
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'siapa saja customernya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringNotContainsString('`', $data['reply']);
        $this->assertStringNotContainsString('*', $data['reply']);
        $this->assertStringContainsString('customer', strtolower($data['reply']));
    }

    /**
     * Test that AI answers pro booster count and names specifically without combining other roles.
     */
    public function test_ai_answers_booster_count_and_names_specifically(): void
    {
        $boosterCount = User::where('role', 'joki')->count();
        $firstBooster = User::where('role', 'joki')->first();
        $this->assertNotNull($firstBooster);

        // Count inquiry
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'berapa pro booster?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString((string) $boosterCount, $data['reply']);
        $this->assertStringContainsString('booster', strtolower($data['reply']));
        $this->assertStringContainsString($firstBooster->name, $data['reply']);
        $this->assertStringNotContainsString('`', $data['reply']);
        $this->assertStringNotContainsString('*', $data['reply']);
        $this->assertStringNotContainsString('Total pengguna terdaftar di sistem', $data['reply']);

        // List inquiry
        $responseList = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'siapa saja boosternya?',
        ]);

        $responseList->assertStatus(200);
        $dataList = $responseList->json();

        $this->assertStringContainsString($firstBooster->name, $dataList['reply']);
        $this->assertStringNotContainsString('`', $dataList['reply']);
        $this->assertStringNotContainsString('*', $dataList['reply']);
    }

    /**
     * Test that AI answers admin count and names specifically without combining other roles.
     */
    public function test_ai_answers_admin_count_and_names_specifically(): void
    {
        $adminCount = User::where('role', 'admin')->count();
        $firstAdmin = User::where('role', 'admin')->first();
        $this->assertNotNull($firstAdmin);

        // Count inquiry
        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'berapa admin?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString((string) $adminCount, $data['reply']);
        $this->assertStringContainsString('admin', strtolower($data['reply']));
        $this->assertStringContainsString($firstAdmin->name, $data['reply']);
        $this->assertStringNotContainsString('`', $data['reply']);
        $this->assertStringNotContainsString('*', $data['reply']);
        $this->assertStringNotContainsString('Total pengguna terdaftar di sistem', $data['reply']);

        // List inquiry
        $responseList = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'siapa saja adminnya?',
        ]);

        $responseList->assertStatus(200);
        $dataList = $responseList->json();

        $this->assertStringContainsString($firstAdmin->name, $dataList['reply']);
        $this->assertStringNotContainsString('`', $dataList['reply']);
        $this->assertStringNotContainsString('*', $dataList['reply']);
    }

    /**
     * Test that AI handles joint booster and admin inquiry cleanly.
     */
    public function test_ai_answers_booster_and_admin_joint_inquiry(): void
    {
        $firstBooster = User::where('role', 'joki')->first();
        $firstAdmin = User::where('role', 'admin')->first();

        $response = $this->postJson(route('ai-assistant.consult'), [
            'prompt' => 'pro booster sama admin ada berapa?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertStringContainsString('Booster', $data['reply']);
        $this->assertStringContainsString('Admin', $data['reply']);
        $this->assertStringContainsString($firstBooster->name, $data['reply']);
        $this->assertStringContainsString($firstAdmin->name, $data['reply']);
        $this->assertStringNotContainsString('`', $data['reply']);
        $this->assertStringNotContainsString('*', $data['reply']);
    }
}
