<?php

namespace App\Services\AI;

use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AetherealAIService implements AIServiceInterface
{
    protected string $provider;

    protected ?string $apiKey;

    protected string $endpoint;

    protected string $model;

    protected int $timeout;

    protected DatabaseContextRetriever $retriever;

    public function __construct(?DatabaseContextRetriever $retriever = null)
    {
        $this->provider = config('services.ai.provider', 'nvidia');
        $this->apiKey = config('services.ai.api_key');
        $this->endpoint = config('services.ai.endpoint', 'https://integrate.api.nvidia.com/v1/chat/completions');
        $this->model = config('services.ai.model', 'meta/llama-3.2-11b-vision-instruct');
        $this->timeout = (int) config('services.ai.timeout', 15);
        $this->retriever = $retriever ?? new DatabaseContextRetriever;
    }

    /**
     * Answer user prompt with contextual grounding from database.
     */
    public function ask(string $prompt, ?User $user = null, array $history = []): array
    {
        // 0. Hard guardrail: Python coding requests are strictly declined as per scope
        if ($this->isPythonCodingRequest($prompt)) {
            return $this->handlePythonCodingDeclined();
        }

        // 0.1 Fast-path & guardrail: User identity inquiry ("siapa saya", "siapa nama saya")
        if ($this->isUserIdentityInquiry($prompt)) {
            return $this->handleUserIdentity($user);
        }

        // 0.2 Fast-path: Total Users statistics ("berapa total pengguna", "total user")
        if ($this->retriever->isTotalUsersInquiry($prompt)) {
            return $this->handleTotalUsersInquiry($user);
        }

        // 0.3 Fast-path: Customer statistics & inquiry ("ada berapa jumlah customer", "data customer")
        if ($this->retriever->isCustomerInquiry($prompt)) {
            return $this->handleCustomerInquiry($user, $prompt);
        }

        // 1. If API key is present, attempt Upstream AI Provider with fallback
        if (! empty($this->apiKey)) {
            try {
                $response = $this->callUpstreamAi($prompt, $user, $history);
                if ($response !== null) {
                    return $response;
                }
            } catch (\Throwable $e) {
                Log::warning('Upstream AI Service failed or timed out. Falling back to local intelligence engine: '.$e->getMessage());
            }
        }

        // 2. Intelligent, database-grounded fallback engine
        return $this->fallbackResponse($prompt, $user);
    }

    /**
     * Dispatch to appropriate Upstream Generative AI provider (NVIDIA or Gemini)
     */
    protected function callUpstreamAi(string $prompt, ?User $user = null, array $history = []): ?array
    {
        if ($this->provider === 'gemini') {
            return $this->callGeminiAi($prompt, $user, $history);
        }

        return $this->callNvidiaAi($prompt, $user, $history);
    }

    /**
     * Call NVIDIA NIM AI Chat Completions API with database-grounded context
     */
    protected function callNvidiaAi(string $prompt, ?User $user = null, array $history = []): ?array
    {
        $retrieval = $this->retriever->retrieve($prompt, $user);
        $systemContext = $this->buildSystemContext($user, $retrieval['grounding_text']);

        $messages = [];
        $messages[] = [
            'role' => 'system',
            'content' => $systemContext,
        ];

        // Append conversation history
        foreach (array_slice($history, -6) as $item) {
            $role = ($item['role'] ?? 'user') === 'user' ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => (string) ($item['content'] ?? ''),
            ];
        }

        // Current prompt
        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.4,
            'max_tokens' => 800,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->post($this->endpoint, $payload);

        if (! $response->successful()) {
            Log::warning('NVIDIA AI API returned error: '.$response->status().' - '.$response->body());

            return null;
        }

        $json = $response->json();
        $reply = $json['choices'][0]['message']['content'] ?? null;

        if (empty($reply)) {
            return null;
        }

        // Guardrail: if response generated python code, sanitize
        if (preg_match('/```python/i', $reply)) {
            $reply = 'Maaf, saya tidak dapat memberikan coding dalam bahasa Python karena ruang lingkup saya hanya untuk pengembangan Web. Jika Anda memerlukan bantuan kode untuk web (HTML, CSS, JavaScript, PHP, Laravel), saya siap membantu!';
        }

        // Guardrail: Ensure Farrel is never mentioned in personal identity
        if (! $this->retriever->isCustomerInquiry($prompt) && ! $this->retriever->isTotalUsersInquiry($prompt) && stripos($reply, 'Farrel') !== false) {
            $replacementName = $user ? $user->name : 'Pengunjung Tamu';
            $reply = preg_replace('/Farrel\s+Surya\s+Pratama/i', $replacementName, $reply);
            $reply = preg_replace('/Farrel/i', $replacementName, $reply);
        }

        // Determine structured recommendations matching the prompt
        $fallbackData = $this->fallbackResponse($prompt, $user);

        return [
            'reply' => trim($reply),
            'recommendations' => ! empty($retrieval['recommendations']) ? $retrieval['recommendations'] : $fallbackData['recommendations'],
            'navigation' => ! empty($retrieval['navigation']) ? $retrieval['navigation'] : $fallbackData['navigation'],
            'source' => 'upstream_ai',
        ];
    }

    /**
     * Call Google Gemini API with database-grounded context
     */
    protected function callGeminiAi(string $prompt, ?User $user = null, array $history = []): ?array
    {
        $retrieval = $this->retriever->retrieve($prompt, $user);
        $systemContext = $this->buildSystemContext($user, $retrieval['grounding_text']);

        // Gemini API endpoint
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $contents = [];
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => "System Instructions:\n".$systemContext],
            ],
        ];

        // Append conversation history
        foreach (array_slice($history, -4) as $item) {
            $contents[] = [
                'role' => ($item['role'] ?? 'user') === 'user' ? 'user' : 'model',
                'parts' => [
                    ['text' => $item['content'] ?? ''],
                ],
            ];
        }

        // Current prompt
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $prompt],
            ],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 800,
            ],
        ];

        $response = Http::timeout($this->timeout)->post($endpoint, $payload);

        if (! $response->successful()) {
            Log::warning('Gemini API returned error: '.$response->status().' - '.$response->body());

            return null;
        }

        $json = $response->json();
        $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($reply)) {
            return null;
        }

        // Guardrail: if response generated python code, sanitize
        if (preg_match('/```python/i', $reply)) {
            $reply = 'Maaf, saya tidak dapat memberikan coding dalam bahasa Python karena ruang lingkup saya hanya untuk pengembangan Web. Jika Anda memerlukan bantuan kode untuk web (HTML, CSS, JavaScript, PHP, Laravel), saya siap membantu!';
        }

        // Guardrail: Ensure Farrel is never mentioned in personal identity
        if (! $this->retriever->isCustomerInquiry($prompt) && ! $this->retriever->isTotalUsersInquiry($prompt) && stripos($reply, 'Farrel') !== false) {
            $replacementName = $user ? $user->name : 'Pengunjung Tamu';
            $reply = preg_replace('/Farrel\s+Surya\s+Pratama/i', $replacementName, $reply);
            $reply = preg_replace('/Farrel/i', $replacementName, $reply);
        }

        // Determine structured recommendations matching the prompt
        $fallbackData = $this->fallbackResponse($prompt, $user);

        return [
            'reply' => trim($reply),
            'recommendations' => ! empty($retrieval['recommendations']) ? $retrieval['recommendations'] : $fallbackData['recommendations'],
            'navigation' => ! empty($retrieval['navigation']) ? $retrieval['navigation'] : $fallbackData['navigation'],
            'source' => 'upstream_ai',
        ];
    }

    /**
     * Build rich, bounded context from database for AI prompt grounding.
     */
    protected function buildSystemContext(?User $user = null, ?string $dynamicGrounding = null): string
    {
        $context = "Anda adalah 'Aethereal AI', asisten cerdas resmi platform marketplace gaming Aethereal Joki & Top Up.\n";
        $context .= "BATASAN KETAT KEAMANAN:\n";
        $context .= "- Anda TIDAK BISA dan DILARANG melakukan pembayaran, membuat order otomatis, atau mengubah database.\n";
        $context .= "- Anda HANYA boleh mengakses data pesanan milik pengguna yang saat ini sedang login atau kode invoice publik yang dicari.\n";
        $context .= "- Berikan jawaban ramah, jelas, profesional dalam Bahasa Indonesia bertema gaming modern.\n\n";

        // Domain & Schema Knowledge
        $context .= $this->retriever->getSchemaAndDomainKnowledge()."\n";

        // Dynamic Real-time Grounding from Database specifically for this prompt
        if (! empty($dynamicGrounding)) {
            $context .= "DATA REAL-TIME DIAMBIL DARI DATABASE SESUAI PERTANYAAN:\n";
            $context .= $dynamicGrounding."\n\n";
            $context .= "INSTRUKSI PENGGUNAAN DATA DATABASE:\n";
            $context .= "- Gunakan data real-time di atas untuk menjawab pertanyaan pengguna dengan data faktual 100% akurat (sebutkan nama produk, harga resmi, persentase diskon promo, atau status pesanan jika ada).\n";
            $context .= "- Tunjukkan pemahaman yang baik mengenai struktur katalog game, paket diamond/UC/koin, dan alur pemesanan.\n";
        }

        if ($user) {
            $context .= "\nDATA PENGGUNA SAAT INI (LOGIN):\n";
            $context .= "Nama: {$user->name}, Email: {$user->email}\n";
            $recentOrders = $user->orders()->with('payment')->latest()->limit(3)->get();
            if ($recentOrders->count() > 0) {
                $context .= "Pesanan Terakhir Milik Pengguna Ini:\n";
                foreach ($recentOrders as $ro) {
                    $context .= "- Order #{$ro->order_code} | Status: {$ro->order_status} | Bayar: ".($ro->payment?->status ?? 'pending').' | Total: Rp '.number_format($ro->total_amount, 0, ',', '.')."\n";
                }
            } else {
                $context .= "Pengguna ini belum memiliki riwayat pesanan.\n";
            }
        } else {
            $context .= "\nPENGGUNA SAAT INI: Pengunjung Tamu (Guest / Belum Login).\n";
        }

        $context .= "\nATURAN KHUSUS IDENTITAS PENGGUNA:\n";
        if ($user) {
            $context .= "- Ketika pengguna bertanya tentang siapa nama mereka atau siapa mereka (contoh: 'siapa nama saya', 'siapa saya', 'namaku siapa', 'kamu kenal aku?'), Anda WAJIB menjawab secara langsung dan ramah bahwa nama pengguna sesuai nama akun yang sedang login saat ini, yaitu '{$user->name}' (Role: {$user->role}, Email: {$user->email}). JANGAN PERNAH menyebut nama 'Farrel Surya Pratama'.\n";
        } else {
            $context .= "- Ketika pengguna bertanya tentang siapa nama mereka atau siapa mereka (contoh: 'siapa nama saya', 'siapa saya', 'namaku siapa', 'kamu kenal aku?'), jelaskan secara ramah bahwa mereka saat ini mengakses sebagai Pengunjung Tamu (Guest) dan belum login ke akun tertentu. JANGAN PERNAH menyebut nama 'Farrel Surya Pratama'.\n";
        }

        $context .= "\nBATASAN KETAT CODING / PEMROGRAMAN:\n";
        $context .= "- Ketika pengguna bertanya atau meminta dibuatkan coding / script / program dalam bahasa Python (misalnya 'buatkan coding python', 'bikin script python', 'tulis kode python', dsb.), Anda DILARANG KERAS memberikan codingan Python tersebut!\n";
        $context .= "- Tolak dengan sopan dan jelaskan bahwa ruang lingkup bantuan di platform ini HANYA untuk pengembangan Web saja (HTML, CSS, JavaScript, PHP, Laravel, database web), bukan untuk bahasa Python.\n";
        $context .= "- Untuk pertanyaan coding web (HTML, CSS, JS, PHP, Laravel), Anda dipersilakan membantu dengan baik.\n";
        $context .= "- DILARANG memberikan kode mentah (seperti tanda backtick ` atau blok kode) untuk data umum, email, atau role pengguna. Tuliskan teks biasa yang rapi dan nyaman dibaca.\n";
        $context .= "- Jika pengguna bertanya tentang TOTAL PENGGUNA, jawab langsung jumlah total pengguna sistem secara ringkas dan rapi. Jangan mencampuradukkan rincian data customer jika tidak diminta.\n";
        $context .= "- Jika pengguna bertanya tentang BERAPA CUSTOMER, jawab spesifik jumlah customer saja, JANGAN digabungkan dengan total pengguna sistem atau pesanan.\n";
        $context .= "- Jika pengguna bertanya tentang BERAPA PRO BOOSTER / JOKI, jawab spesifik jumlah pro booster saja beserta daftar lengkap nama booster, JANGAN digabungkan dengan total pengguna atau customer.\n";
        $context .= "- Jika pengguna bertanya tentang BERAPA ADMIN, jawab spesifik jumlah admin saja beserta daftar lengkap nama admin, JANGAN digabungkan dengan total pengguna atau customer.\n";
        $context .= "- DILARANG menggunakan tanda bintang (*) dalam jawaban Anda untuk menjaga tampilan obrolan tetap rapi dan bersih.\n";

        return $context;
    }

    /**
     * Local Intelligent Rule-Based & Semantic Engine
     * Covers budget parsing, game matching, service explanation, FAQ, order lookup, promos, and navigation.
     */
    public function fallbackResponse(string $prompt, ?User $user = null): array
    {
        $normalized = strtolower(trim($prompt));

        // 0. USER IDENTITY INQUIRY ("siapa nama saya" / "siapa saya")
        if ($this->isUserIdentityInquiry($normalized)) {
            return $this->handleUserIdentity($user);
        }

        // 0.1 PYTHON CODING REQUEST GUARDRAIL
        if ($this->isPythonCodingRequest($normalized)) {
            return $this->handlePythonCodingDeclined();
        }

        // 1. SPECIFIC ORDER CODE LOOKUP (e.g. AJ-8821, ORD-ALPHA-123)
        $explicitOrderCode = $this->retriever->extractOrderCode($normalized);
        if ($explicitOrderCode) {
            return $this->handleSpecificOrderLookup($explicitOrderCode, $user);
        }

        // 1.1 USER ORDER STATUS LOOKUP (GENERAL)
        if ($this->isOrderInquiry($normalized)) {
            return $this->handleOrderInquiry($normalized, $user);
        }

        // 1.2 PROMOTIONS & VOUCHER CODES INQUIRY
        if ($this->retriever->isPromotionInquiry($normalized)) {
            return $this->handlePromotionInquiry();
        }

        // 1.3 PRO BOOSTER & ADMIN JOINT INQUIRY ("pro booster sama admin", "berapa booster dan admin")
        if ($this->retriever->isBoosterAndAdminInquiry($normalized)) {
            return $this->handleBoosterAndAdminInquiry($user);
        }

        // 1.4 PRO BOOSTER / JOKI INQUIRY ("berapa booster", "siapa saja booster", "ada berapa pro booster")
        if ($this->retriever->isBoosterInquiry($normalized)) {
            return $this->handleBoosterInquiry($user, $normalized);
        }

        // 1.5 ADMIN INQUIRY ("berapa admin", "siapa saja adminnya", "ada berapa admin")
        if ($this->retriever->isAdminInquiry($normalized)) {
            return $this->handleAdminInquiry($user, $normalized);
        }

        // 1.6 TOTAL USERS DATABASE INQUIRY ("berapa total pengguna", "total user")
        if ($this->retriever->isTotalUsersInquiry($normalized)) {
            return $this->handleTotalUsersInquiry($user);
        }

        // 1.7 CUSTOMER DATABASE INQUIRY ("ada berapa jumlah customer", "data customer")
        if ($this->retriever->isCustomerInquiry($normalized)) {
            return $this->handleCustomerInquiry($user, $normalized);
        }

        // 2. BUDGET-BASED TOP UP RECOMMENDATION
        $budget = $this->extractBudget($normalized);
        $matchedGame = $this->findGameInPrompt($normalized);

        if ($budget !== null) {
            return $this->handleBudgetRecommendation($budget, $matchedGame);
        }

        // 3. SERVICE DIFFERENCE EXPLANATION
        if ($this->containsWord($normalized, ['beda', 'perbedaan', 'apa itu', 'versus', 'vs', 'macam layanan', 'jenis layanan', 'gendong vs biasa'])) {
            return $this->handleServiceDifference();
        }

        // 4. FAQ & SAFETY INQUIRIES
        if ($this->containsWord($normalized, ['aman', 'cheat', 'banned', 'legal', 'garansi', 'anti banned', 'keamanan', 'terpercaya'])) {
            return $this->handleFaqSafety();
        }

        if ($this->containsWord($normalized, ['bayar', 'pembayaran', 'metode', 'qris', 'transfer', 'bca', 'dana', 'gopay'])) {
            return $this->handleFaqPayment();
        }

        if ($this->containsWord($normalized, ['lama', 'durasi', 'estimasi', 'berapa jam', 'waktu'])) {
            return $this->handleFaqDuration();
        }

        if ($this->containsWord($normalized, ['batal', 'cancel', 'refund', 'kembali uang'])) {
            return $this->handleFaqRefund();
        }

        // 4.1 CUSTOMER REVIEWS & REPUTATION
        if ($this->retriever->isReviewInquiry($normalized)) {
            return $this->handleReviewInquiry();
        }

        // 4.2 GAME CATEGORY INQUIRY (MOBA, FPS, Battle Royale, RPG)
        $matchedCategory = $this->retriever->findCategory($normalized);
        if ($matchedCategory && ! $matchedGame) {
            return $this->handleCategoryInquiry($matchedCategory);
        }

        // 5. PRODUCT SEARCH BY GAME
        if ($matchedGame && ($this->containsWord($normalized, [
            'topup', 'top up', 'diamond', 'dm', 'point', 'vp', 'uc', 'produk', 'beli', 'harga',
            'nominal', 'biaya', 'katalog', 'daftar', 'crystal', 'robux', 'cash', 'coin', 'shards', 'pass', 'welkin',
        ]))) {
            return $this->handleGameProductSearch($matchedGame);
        }

        // 6. JOKI SERVICES RECOMMENDATION
        if ($this->containsWord($normalized, ['joki', 'push', 'rank', 'gendong', 'mabar', 'mythic', 'radiant', 'immortal', 'abyss', 'bintang', 'paket joki'])) {
            return $this->handleJokiRecommendation($normalized, $matchedGame);
        }

        // 6.1 MATCHED GAME ALONE (Show both products & joki overview)
        if ($matchedGame) {
            return $this->handleGameProductSearch($matchedGame);
        }

        // 7. WEBSITE NAVIGATION HELPER
        if ($this->containsWord($normalized, ['cara', 'menu', 'halaman', 'navigasi', 'buka', 'ke mana', 'web'])) {
            return $this->handleNavigation();
        }

        // 8. GENERAL GREETING / DEFAULT ASSISTANT INTRO
        return $this->handleDefaultIntro($matchedGame);
    }

    /**
     * Handle explicit order code lookup from database.
     */
    protected function handleSpecificOrderLookup(string $orderCode, ?User $user): array
    {
        $order = Order::with(['orderItems', 'payment', 'jokiProfile'])->where('order_code', $orderCode)->first();

        if (! $order) {
            return [
                'reply' => "Maaf, pesanan dengan kode invoice #{$orderCode} tidak ditemukan di database kami. Mohon pastikan kembali kode invoice yang Anda masukkan sudah benar.",
                'recommendations' => [],
                'navigation' => [
                    ['label' => 'Halaman Lacak Order', 'url' => route('tracking')],
                    ['label' => 'Hubungi Dukungan CS', 'url' => route('home').'#faq'],
                ],
                'source' => 'local_orders',
            ];
        }

        $itemTitle = $order->orderItems->first()?->item_name ?? 'Layanan Game';
        $paymentStatus = strtoupper($order->payment?->status ?? 'UNPAID');
        $boosterInfo = $order->jokiProfile ? "Booster: {$order->jokiProfile->nickname}" : 'Menunggu Penugasan Booster';
        $progressNote = $order->progress_note ? "Catatan: \"{$order->progress_note}\"" : 'Sedang dalam antrean pengerjaan pro booster';

        $reply = "Informasi pesanan dari database untuk #{$order->order_code}:\n\n"
            ."• Item/Layanan: {$itemTitle}\n"
            ."• Status Pesanan: {$order->status_label} (".strtoupper($order->order_status).")\n"
            ."• Status Pembayaran: {$paymentStatus} (Total: {$order->formatted_total})\n"
            ."• Penugasan: {$boosterInfo}\n"
            ."• Progress: {$progressNote}\n\n"
            .'Klik kartu pesanan di bawah untuk melihat timeline lengkap pengerjaan dan invoice resmi.';

        return [
            'reply' => $reply,
            'recommendations' => [[
                'type' => 'order',
                'title' => "#{$order->order_code} ({$itemTitle})",
                'subtitle' => "Status: {$order->status_label} • {$paymentStatus}",
                'price' => $order->formatted_total,
                'badge' => strtoupper($order->order_status),
                'url' => route('orders.show', $order->order_code),
            ]],
            'navigation' => [
                ['label' => "Buka Detail Pesanan #{$order->order_code}", 'url' => route('orders.show', $order->order_code)],
                ['label' => 'Lacak Pesanan Lain', 'url' => route('tracking')],
            ],
            'source' => 'local_orders',
        ];
    }

    /**
     * Handle active promotions inquiry from database.
     */
    protected function handlePromotionInquiry(): array
    {
        $promotions = Promotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->get();

        if ($promotions->isEmpty()) {
            return [
                'reply' => 'Saat ini belum ada kode promo khusus yang sedang aktif di database. Namun seluruh harga produk di katalog kami sudah mendapatkan potongan diskon langsung hingga 20%!',
                'recommendations' => [],
                'navigation' => [
                    ['label' => 'Katalog Top Up', 'url' => route('topup')],
                    ['label' => 'Layanan Joki', 'url' => route('joki')],
                ],
                'source' => 'local_promotions',
            ];
        }

        $reply = "Berikut adalah Promo & Voucher Diskon Aktif yang tercatat di database Aethereal Topup:\n\n";
        $recommendations = [];

        foreach ($promotions as $promo) {
            $discountStr = $promo->discount_type === 'percentage'
                ? number_format($promo->discount_value, 0).'% OFF'
                : 'Potongan Rp '.number_format($promo->discount_value, 0, ',', '.');
            $minSpend = $promo->min_spend ? ' (Min. Belanja Rp '.number_format($promo->min_spend, 0, ',', '.').')' : '';

            $reply .= "• Kode: {$promo->code} - {$promo->name}\n";
            $reply .= "  Diskon: {$discountStr}{$minSpend}\n\n";

            $recommendations[] = [
                'type' => 'product',
                'title' => "Voucher: {$promo->code}",
                'subtitle' => $promo->name,
                'price' => $discountStr,
                'badge' => 'PROMO AKTIF',
                'url' => route('topup'),
            ];
        }

        $reply .= 'Gunakan kode voucher di atas pada kolom pembayaran saat checkout untuk mendapatkan potongan harga langsung!';

        return [
            'reply' => $reply,
            'recommendations' => $recommendations,
            'navigation' => [
                ['label' => 'Gunakan di Katalog Top Up', 'url' => route('topup')],
                ['label' => 'Pilih Layanan Joki', 'url' => route('joki')],
            ],
            'source' => 'local_promotions',
        ];
    }

    /**
     * Handle total users / all system accounts inquiry directly.
     */
    public function handleTotalUsersInquiry(?User $user = null): array
    {
        $totalUsers = User::count();
        $customersCount = User::where('role', 'customer')->count();
        $activeJokis = User::where('role', 'joki')->where('is_active', true)->count();
        $adminCount = User::where('role', 'admin')->count();

        $allUsers = User::select('id', 'name', 'role', 'is_active')->orderBy('id')->get();

        $reply = "Total pengguna terdaftar di sistem Aethereal saat ini adalah {$totalUsers} akun ({$customersCount} customer, {$activeJokis} pro booster/joki, dan {$adminCount} admin).\n\n";

        if ($allUsers->isNotEmpty()) {
            $reply .= "Berikut daftar lengkap nama pengguna:\n";
            foreach ($allUsers as $index => $u) {
                $num = $index + 1;
                $roleLabel = match ($u->role) {
                    'admin' => 'Admin',
                    'joki' => 'Pro Booster / Joki',
                    default => 'Customer',
                };
                $reply .= "{$num}. {$u->name} ({$roleLabel})\n";
            }
            $reply .= "\n";
        }

        $reply .= 'Ada informasi lain yang ingin Anda ketahui seputar platform Aethereal?';

        $navigation = [];
        if ($user?->isAdmin()) {
            $navigation[] = ['label' => 'Kelola Pengguna di Admin', 'url' => route('admin.users')];
            $navigation[] = ['label' => 'Buka Dashboard Admin', 'url' => route('admin.dashboard')];
        } else {
            $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Layanan Joki', 'url' => route('joki')];
        }

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => $navigation,
            'source' => 'database_user_stats',
        ];
    }

    /**
     * Handle customer statistics and database directory inquiry.
     */
    public function handleCustomerInquiry(?User $user = null, string $prompt = ''): array
    {
        $customersCount = User::where('role', 'customer')->count();
        $customers = User::where('role', 'customer')
            ->select('id', 'name', 'email', 'is_active', 'created_at')
            ->orderBy('id')
            ->get();

        $reply = "Jumlah Customer terdaftar di platform Aethereal saat ini adalah {$customersCount} customer.\n\n";

        if ($customers->isNotEmpty()) {
            $reply .= "Berikut daftar lengkap nama customer:\n";
            foreach ($customers as $index => $c) {
                $num = $index + 1;
                $status = $c->is_active ? 'Aktif' : 'Nonaktif';
                $reply .= "{$num}. {$c->name} (Status: {$status})\n";
            }
            $reply .= "\n";
        }

        $reply .= 'Ada informasi lain yang ingin Anda ketahui seputar layanan, produk, atau pesanan?';

        $navigation = [];
        if ($user?->isAdmin()) {
            $navigation[] = ['label' => 'Kelola Pengguna di Admin', 'url' => route('admin.users')];
            $navigation[] = ['label' => 'Buka Dashboard Admin', 'url' => route('admin.dashboard')];
        } else {
            $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Layanan Joki', 'url' => route('joki')];
            $navigation[] = ['label' => 'Lacak Pesanan', 'url' => route('tracking')];
        }

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => $navigation,
            'source' => 'database_customer_stats',
        ];
    }

    /**
     * Handle Pro Booster / Joki inquiry specifically.
     */
    public function handleBoosterInquiry(?User $user = null, string $prompt = ''): array
    {
        $boosters = User::where('role', 'joki')
            ->with('jokiProfile')
            ->orderBy('id')
            ->get();
        $boosterCount = $boosters->count();

        $reply = "Jumlah Pro Booster / Joki terdaftar di platform Aethereal saat ini adalah {$boosterCount} booster.\n\n";

        if ($boosters->isNotEmpty()) {
            $reply .= "Berikut daftar lengkap nama pro booster:\n";
            foreach ($boosters as $index => $b) {
                $num = $index + 1;
                $details = [];
                if ($b->jokiProfile?->nickname) {
                    $details[] = "Nickname: {$b->jokiProfile->nickname}";
                }
                $details[] = $b->is_active ? 'Status: Aktif' : 'Status: Nonaktif';
                $detailStr = implode(', ', $details);

                $reply .= "{$num}. {$b->name} ({$detailStr})\n";
            }
            $reply .= "\n";
        }

        $reply .= 'Ada informasi lain yang ingin Anda ketahui seputar layanan joki rank atau profil booster?';

        $navigation = [];
        if ($user?->isAdmin()) {
            $navigation[] = ['label' => 'Kelola Joki di Admin', 'url' => route('admin.joki')];
            $navigation[] = ['label' => 'Buka Dashboard Admin', 'url' => route('admin.dashboard')];
        } else {
            $navigation[] = ['label' => 'Layanan Joki', 'url' => route('joki')];
            $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Lacak Pesanan', 'url' => route('tracking')];
        }

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => $navigation,
            'source' => 'database_booster_stats',
        ];
    }

    /**
     * Handle Administrator inquiry specifically.
     */
    public function handleAdminInquiry(?User $user = null, string $prompt = ''): array
    {
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email', 'is_active', 'created_at')
            ->orderBy('id')
            ->get();
        $adminCount = $admins->count();

        $reply = "Jumlah Administrator di sistem Aethereal saat ini adalah {$adminCount} admin.\n\n";

        if ($admins->isNotEmpty()) {
            $reply .= "Berikut daftar lengkap nama admin:\n";
            foreach ($admins as $index => $a) {
                $num = $index + 1;
                $status = $a->is_active ? 'Aktif' : 'Nonaktif';
                $reply .= "{$num}. {$a->name} (Role: Administrator, Status: {$status})\n";
            }
            $reply .= "\n";
        }

        $reply .= 'Ada informasi lain yang ingin Anda ketahui seputar sistem atau platform Aethereal?';

        $navigation = [];
        if ($user?->isAdmin()) {
            $navigation[] = ['label' => 'Kelola Pengguna di Admin', 'url' => route('admin.users')];
            $navigation[] = ['label' => 'Buka Dashboard Admin', 'url' => route('admin.dashboard')];
        } else {
            $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Layanan Joki', 'url' => route('joki')];
            $navigation[] = ['label' => 'Lacak Pesanan', 'url' => route('tracking')];
        }

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => $navigation,
            'source' => 'database_admin_stats',
        ];
    }

    /**
     * Handle joint inquiry about both booster and admin.
     */
    public function handleBoosterAndAdminInquiry(?User $user = null): array
    {
        $boosters = User::where('role', 'joki')
            ->with('jokiProfile')
            ->orderBy('id')
            ->get();
        $boosterCount = $boosters->count();

        $admins = User::where('role', 'admin')
            ->orderBy('id')
            ->get();
        $adminCount = $admins->count();

        $reply = "Berikut rincian akun Pro Booster dan Administrator di platform Aethereal:\n\n";

        $reply .= "1. Pro Booster / Joki ({$boosterCount} booster):\n";
        if ($boosters->isNotEmpty()) {
            foreach ($boosters as $index => $b) {
                $num = $index + 1;
                $nick = $b->jokiProfile?->nickname ? "Nickname: {$b->jokiProfile->nickname}, " : '';
                $status = $b->is_active ? 'Status: Aktif' : 'Status: Nonaktif';
                $reply .= "   {$num}. {$b->name} ({$nick}{$status})\n";
            }
        }
        $reply .= "\n";

        $reply .= "2. Administrator ({$adminCount} admin):\n";
        if ($admins->isNotEmpty()) {
            foreach ($admins as $index => $a) {
                $num = $index + 1;
                $status = $a->is_active ? 'Status: Aktif' : 'Status: Nonaktif';
                $reply .= "   {$num}. {$a->name} (Role: Administrator, Status: {$status})\n";
            }
        }
        $reply .= "\n";

        $reply .= 'Ada informasi lain yang ingin Anda ketahui seputar platform Aethereal?';

        $navigation = [];
        if ($user?->isAdmin()) {
            $navigation[] = ['label' => 'Kelola Pengguna di Admin', 'url' => route('admin.users')];
            $navigation[] = ['label' => 'Buka Dashboard Admin', 'url' => route('admin.dashboard')];
        } else {
            $navigation[] = ['label' => 'Layanan Joki', 'url' => route('joki')];
            $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Lacak Pesanan', 'url' => route('tracking')];
        }

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => $navigation,
            'source' => 'database_booster_admin_stats',
        ];
    }

    /**
     * Handle customer reviews & reputation inquiry from database.
     */
    protected function handleReviewInquiry(): array
    {
        $totalReviews = Review::count();
        $avgRating = Review::avg('rating') ?? 5.0;
        $reviews = Review::latest()->take(3)->get();

        $reply = 'Platform Aethereal Topup & Joki memiliki reputasi terpercaya dengan rating rata-rata '.number_format($avgRating, 1)."/5.0 Bintang dari {$totalReviews} ulasan pelanggan terverifikasi!\n\n";
        $reply .= "Berikut beberapa testimoni kepuasan pelanggan:\n";

        foreach ($reviews as $rev) {
            $stars = str_repeat('⭐', (int) $rev->rating);
            $reply .= "• {$rev->customer_name} ({$stars} untuk {$rev->game_name}):\n  \"{$rev->comment}\"\n\n";
        }

        $reply .= 'Seluruh proses top up dan joki dikerjakan secara profesional dengan jaminan keamanan akun 100%.';

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Katalog Top Up Instan', 'url' => route('topup')],
                ['label' => 'Lihat Layanan Joki Terpercaya', 'url' => route('joki')],
            ],
            'source' => 'local_reviews',
        ];
    }

    /**
     * Handle game category inquiry from database.
     */
    protected function handleCategoryInquiry(string $category): array
    {
        $games = Game::where('is_active', true)
            ->where('category', $category)
            ->get();

        $catLabel = match ($category) {
            'moba' => 'MOBA',
            'fps' => 'FPS (First Person Shooter)',
            'battle_royale' => 'Battle Royale',
            'rpg' => 'RPG & Gacha',
            default => strtoupper($category),
        };

        $reply = "Berikut adalah game bertema **{$catLabel}** yang tersedia di database Aethereal Topup:\n\n";
        $recommendations = [];

        foreach ($games as $g) {
            $reply .= "• **{$g->name}** (Publisher: {$g->publisher}) — [Buka Top Up](".route('topup.game', $g->slug).")\n";
            $topProduct = GameProduct::where('game_id', $g->id)->where('is_active', true)->orderBy('price')->first();
            if ($topProduct) {
                $recommendations[] = [
                    'type' => 'product',
                    'title' => $topProduct->name,
                    'subtitle' => $g->name,
                    'price' => $topProduct->formatted_price,
                    'badge' => $g->badge ?? 'Tersedia',
                    'url' => route('topup.game', $g->slug),
                ];
            }
        }

        return [
            'reply' => $reply,
            'recommendations' => array_slice($recommendations, 0, 4),
            'navigation' => [
                ['label' => 'Semua Katalog Game', 'url' => route('games')],
                ['label' => 'Katalog Top Up', 'url' => route('topup')],
            ],
            'source' => 'local_category',
        ];
    }

    /**
     * Check if prompt is inquiring about user orders.
     */
    protected function isOrderInquiry(string $prompt): bool
    {
        return $this->containsWord($prompt, [
            'status order', 'pesanan saya', 'order saya', 'cek order', 'lacak pesanan',
            'status pesanan', 'riwayat order', 'pesanan ku', 'orderan saya', 'progress joki saya',
        ]);
    }

    /**
     * Handle user order status inquiry.
     */
    protected function handleOrderInquiry(string $prompt, ?User $user): array
    {
        if (! $user) {
            return [
                'reply' => 'Halo! Untuk mengecek status pesanan secara otomatis, silakan **Login** ke akun Anda terlebih dahulu. Jika Anda memesan sebagai tamu, Anda dapat melacak status pesanan secara mandiri menggunakan kode invoice di halaman **Lacak Order**.',
                'recommendations' => [],
                'navigation' => [
                    ['label' => 'Masuk / Login', 'url' => route('login')],
                    ['label' => 'Halaman Lacak Order', 'url' => route('tracking')],
                ],
                'source' => 'local_orders',
            ];
        }

        $orders = $user->orders()->with(['orderItems', 'payment', 'jokiProfile'])->latest()->take(3)->get();

        if ($orders->isEmpty()) {
            return [
                'reply' => "Hai {$user->name}, kami telah memeriksa akun Anda dan saat ini **belum ada pesanan aktif** atau riwayat transaksi. Mau mulai push rank atau top up diamond sekarang?",
                'recommendations' => [],
                'navigation' => [
                    ['label' => 'Katalog Top Up', 'url' => route('topup')],
                    ['label' => 'Marketplace Joki', 'url' => route('joki')],
                ],
                'source' => 'local_orders',
            ];
        }

        $reply = "Halo **{$user->name}**, berikut adalah ringkasan status pesanan terbaru Anda:\n\n";
        $recommendations = [];

        foreach ($orders as $order) {
            $itemTitle = $order->orderItems->first()?->item_name ?? 'Layanan Game';
            $paymentStatus = strtoupper($order->payment?->status ?? 'UNPAID');
            $boosterInfo = $order->jokiProfile ? "Booster: {$order->jokiProfile->nickname}" : 'Menunggu Penugasan Booster';

            $reply .= "• **#{$order->order_code}** - {$itemTitle}\n";
            $reply .= "  Status: **{$order->status_label}** | Bayar: **{$paymentStatus}** | {$boosterInfo}\n\n";

            $recommendations[] = [
                'type' => 'order',
                'title' => "#{$order->order_code} ({$itemTitle})",
                'subtitle' => "Status: {$order->status_label} • {$paymentStatus}",
                'price' => $order->formatted_total,
                'badge' => strtoupper($order->order_status),
                'url' => route('orders.show', $order->order_code),
            ];
        }

        return [
            'reply' => $reply.'Klik kartu pesanan di bawah untuk melihat timeline lengkap pengerjaan dan invoice.',
            'recommendations' => $recommendations,
            'navigation' => [
                ['label' => 'Lihat Semua Pesanan Saya', 'url' => route('dashboard.orders')],
            ],
            'source' => 'local_orders',
        ];
    }

    /**
     * Extract budget numeric value from user prompt.
     */
    protected function extractBudget(string $prompt): ?float
    {
        return $this->retriever->extractBudget($prompt);
    }

    /**
     * Match Game from prompt.
     */
    protected function findGameInPrompt(string $prompt): ?Game
    {
        return $this->retriever->findGame($prompt);
    }

    /**
     * Handle budget-based recommendation.
     */
    protected function handleBudgetRecommendation(float $budget, ?Game $game): array
    {
        $budgetFormatted = 'Rp '.number_format($budget, 0, ',', '.');
        $query = GameProduct::with('game')->where('is_active', true);

        if ($game) {
            $query->where('game_id', $game->id);
        }

        // Match products at or under budget, sorted by highest value first
        $affordableProducts = (clone $query)->where('price', '<=', $budget)
            ->orderByDesc('price')
            ->take(3)
            ->get();

        // If none found, find the lowest priced product
        if ($affordableProducts->isEmpty()) {
            $cheapest = (clone $query)->orderBy('price')->first();
            $gameName = $game ? $game->name : 'game pilihan Anda';

            if ($cheapest) {
                return [
                    'reply' => "Dengan budget **{$budgetFormatted}**, nominal paket untuk {$gameName} saat ini dimulai dari **{$cheapest->name}** seharga **{$cheapest->formatted_price}**. Anda hanya butuh menambah sedikit dana untuk mendapatkannya!",
                    'recommendations' => [[
                        'type' => 'product',
                        'title' => $cheapest->name,
                        'subtitle' => $cheapest->game?->name,
                        'price' => $cheapest->formatted_price,
                        'badge' => 'Pilihan Termurah',
                        'url' => route('topup.game', $cheapest->game->slug),
                    ]],
                    'navigation' => [
                        ['label' => "Lihat Semua {$gameName}", 'url' => route('topup.game', $cheapest->game->slug)],
                    ],
                    'source' => 'local_budget',
                ];
            }
        }

        $gameName = $game ? $game->name : 'katalog game kami';
        $bestPick = $affordableProducts->first();
        $reply = "Dengan budget **{$budgetFormatted}** untuk {$gameName}, rekomendasi nominal paling maksimal yang bisa Anda beli adalah **{$bestPick->name}** ({$bestPick->formatted_price}).";

        $recommendations = [];
        foreach ($affordableProducts as $p) {
            $recommendations[] = [
                'type' => 'product',
                'title' => $p->name,
                'subtitle' => $p->game?->name,
                'price' => $p->formatted_price,
                'badge' => $p->id === $bestPick->id ? 'Rekomendasi Utama' : 'Pilihan Alternatif',
                'url' => route('topup.game', $p->game->slug),
            ];
        }

        $nav = [];
        if ($game) {
            $nav[] = ['label' => "Buka Halaman Top Up {$game->name}", 'url' => route('topup.game', $game->slug)];
        } else {
            $nav[] = ['label' => 'Katalog Top Up Lengkap', 'url' => route('topup')];
        }

        return [
            'reply' => $reply."\n\nBerikut pilihan paket yang pas untuk budget Anda. Klik untuk langsung menuju pengisian User ID dan Server ID:",
            'recommendations' => $recommendations,
            'navigation' => $nav,
            'source' => 'local_budget',
        ];
    }

    /**
     * Handle product search by game.
     */
    protected function handleGameProductSearch(Game $game): array
    {
        $products = GameProduct::where('game_id', $game->id)
            ->where('is_active', true)
            ->orderBy('price')
            ->take(6)
            ->get();

        $recommendations = [];
        $productListStr = '';
        foreach ($products as $p) {
            $productListStr .= "\n• **{$p->name}** — {$p->formatted_price}";
            $recommendations[] = [
                'type' => 'product',
                'title' => $p->name,
                'subtitle' => $game->name,
                'price' => $p->formatted_price,
                'badge' => 'Instant Top Up',
                'url' => route('topup.game', $game->slug),
            ];
        }

        $reply = "Untuk **{$game->name}**, Aethereal Joki menyediakan berbagai pilihan nominal instan mulai dari Rp ".number_format($products->first()?->price ?? 0, 0, ',', '.')." dengan proses kilat 1-5 menit langsung masuk ke User ID Anda:\n"
            .$productListStr."\n\nKlik kartu paket di bawah untuk langsung melakukan top up:";

        return [
            'reply' => $reply,
            'recommendations' => array_slice($recommendations, 0, 4),
            'navigation' => [
                ['label' => "Top Up {$game->name} Sekarang", 'url' => route('topup.game', $game->slug)],
                ['label' => 'Cek Jasa Joki', 'url' => route('joki')],
            ],
            'source' => 'local_game_search',
        ];
    }

    /**
     * Handle joki recommendation.
     */
    protected function handleJokiRecommendation(string $prompt, ?Game $game): array
    {
        $query = JokiService::with('game')->where('is_active', true);

        if ($game) {
            $query->where('game_id', $game->id);
        }

        if (str_contains($prompt, 'mabar') || str_contains($prompt, 'gendong')) {
            $query->where('service_type', 'mabar');
        } elseif (str_contains($prompt, 'placement')) {
            $query->where('service_type', 'placement');
        } elseif (str_contains($prompt, 'winrate') || str_contains($prompt, 'wr')) {
            $query->where('service_type', 'winrate');
        }

        $services = $query->orderByDesc('is_popular')->orderBy('price')->take(3)->get();

        if ($services->isEmpty()) {
            $services = JokiService::with('game')->where('is_active', true)->where('is_popular', true)->take(3)->get();
        }

        $recommendations = [];
        foreach ($services as $s) {
            $recommendations[] = [
                'type' => 'joki',
                'title' => $s->name,
                'subtitle' => "Target: {$s->target} • Durasi: {$s->estimated_duration}",
                'price' => $s->formatted_price,
                'badge' => $s->is_popular ? 'POPULER' : 'BERGARANSI',
                'url' => route('joki.show', $s->slug),
            ];
        }

        $targetGame = $game ? $game->name : 'Mobile Legends & Valorant';

        return [
            'reply' => "Untuk layanan joki di **{$targetGame}**, seluruh pengerjaan dilakukan oleh **Pro Booster Terverifikasi** dengan jaminan **100% Anti Banned** dan opsi screenshot progress berkala. Berikut paket yang paling kami rekomendasikan:",
            'recommendations' => $recommendations,
            'navigation' => [
                ['label' => 'Eksplor Semua Paket Joki', 'url' => route('joki')],
            ],
            'source' => 'local_joki',
        ];
    }

    /**
     * Handle explanation of differences between services.
     */
    protected function handleServiceDifference(): array
    {
        $reply = "Berikut adalah perbedaan utama layanan di **Aethereal Joki**:\n\n"
            ."1. **Top Up Game Instan**: Pengisian resmi diamond, VP, atau UC langsung ke User ID & Server ID Anda dalam 1-3 menit tanpa butuh login/password akun.\n\n"
            ."2. **Joki Push Rank Reguler**: Akun game Anda dikerjakan langsung oleh Pro Booster terverifikasi hingga mencapai target tier/rank dengan garansi 100% Anti Banned.\n\n"
            ."3. **Joki Gendong / Mabar**: Anda bermain bersama (Party) Pro Booster via Discord/Voice in-game. **Data akun dan password tidak pernah diserahkan**, 100% aman dan Anda tetap bermain sendiri!\n\n"
            .'4. **Joki Tugas RPL / Web**: Layanan pengerjaan coding tugas web Laravel, basis data SQL, dan konsultasi software engineering.';

        return [
            'reply' => $reply,
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Top Up Diamonds', 'url' => route('topup')],
                ['label' => 'Pilih Paket Joki', 'url' => route('joki')],
            ],
            'source' => 'local_explanation',
        ];
    }

    /**
     * Handle FAQ: Safety & Anti Banned.
     */
    protected function handleFaqSafety(): array
    {
        return [
            'reply' => "Keamanan akun Anda adalah prioritas nomor satu di **Aethereal Joki**:\n\n"
                ."• **100% Anti Banned Guarantee**: Seluruh booster kami bermain murni mekanik (Pure Skill) tanpa cheat, script, atau aplikasi pihak ketiga ilegal.\n"
                ."• **Enkripsi Kredensial**: Data login akun game hanya dapat diakses oleh Booster yang ditugaskan resmi dan dihapus setelah pesanan selesai.\n"
                .'• **Opsi Joki Mabar**: Jika Anda ragu membagikan akun, Anda bisa memilih paket **Joki Gendong Mabar** di mana Anda bermain bersama booster tanpa membagikan password.',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Lihat Paket Joki Gendong', 'url' => route('joki')],
            ],
            'source' => 'local_faq_safety',
        ];
    }

    /**
     * Handle FAQ: Payment Methods.
     */
    protected function handleFaqPayment(): array
    {
        return [
            'reply' => "Aethereal Joki mendukung metode pembayaran digital instan dan aman:\n\n"
                ."• **QRIS Real-Time**: Mendukung BCA, Mandiri, BRI, BNI, DANA, GoPay, OVO, ShopeePay, dan seluruh aplikasi m-banking berstandar QRIS.\n"
                ."• **Virtual Account Bank**: BCA, Mandiri, BNI, BRI.\n"
                ."• **E-Wallet Direct**: DANA, GoPay.\n\n"
                .'Setelah melakukan transfer, sistem akan memverifikasi pembayaran secara otomatis dalam hitungan detik.',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Lacak Status Pembayaran', 'url' => route('tracking')],
            ],
            'source' => 'local_faq_payment',
        ];
    }

    /**
     * Handle FAQ: Duration & Speed.
     */
    protected function handleFaqDuration(): array
    {
        return [
            'reply' => "Estimasi durasi pengerjaan di Aethereal Joki:\n\n"
                ."• Top Up Game: Instant (1 - 5 Menit otomatis masuk ke User ID).\n"
                ."• Joki Push Rank Reguler: Rata-rata 2 - 6 Jam tergantung jumlah bintang/tier.\n"
                ."• Joki Tier Tinggi (Mythic/Radiant): 12 - 24 Jam dengan update berkala dari Booster.\n\n"
                .'Setiap paket memiliki estimasi waktu pengerjaan resmi yang tertera di detail layanan.',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Pilih Paket Joki', 'url' => route('joki')],
            ],
            'source' => 'local_faq_duration',
        ];
    }

    /**
     * Handle FAQ: Refund Policy.
     */
    protected function handleFaqRefund(): array
    {
        return [
            'reply' => "Kebijakan Garansi & Pengembalian Dana (Refund) Aethereal Joki:\n\n"
                ."• Anda berhak mendapatkan pengembalian dana 100% penuh apabila booster kami gagal menyelesaikan order sesuai target atau melebihi batas toleransi waktu pengerjaan.\n"
                .'• Pembatalan dapat diajukan kepada Administrator sebelum pengerjaan order dimulai oleh booster.',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Pusat Bantuan & Syarat', 'url' => route('home').'#faq'],
            ],
            'source' => 'local_faq_refund',
        ];
    }

    /**
     * Handle General Navigation Assistance.
     */
    protected function handleNavigation(): array
    {
        return [
            'reply' => 'Berikut jalan pintas cepat untuk bernavigasi di website Aethereal Joki:',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Halaman Utama', 'url' => route('home')],
                ['label' => 'Top Up Diamonds', 'url' => route('topup')],
                ['label' => 'Layanan Joki', 'url' => route('joki')],
                ['label' => 'Katalog Games', 'url' => route('games')],
                ['label' => 'Lacak Order', 'url' => route('tracking')],
                ['label' => 'Dashboard Saya', 'url' => route('dashboard')],
            ],
            'source' => 'local_nav',
        ];
    }

    /**
     * Handle Default Introduction & Suggestions.
     */
    protected function handleDefaultIntro(?Game $game = null): array
    {
        $popularProduct = GameProduct::with('game')->where('is_active', true)->first();
        $popularJoki = JokiService::with('game')->where('is_active', true)->where('is_popular', true)->first();

        $recommendations = [];
        if ($popularProduct) {
            $recommendations[] = [
                'type' => 'product',
                'title' => $popularProduct->name,
                'subtitle' => $popularProduct->game?->name,
                'price' => $popularProduct->formatted_price,
                'badge' => 'TOP UP POPULER',
                'url' => route('topup.game', $popularProduct->game->slug),
            ];
        }

        if ($popularJoki) {
            $recommendations[] = [
                'type' => 'joki',
                'title' => $popularJoki->name,
                'subtitle' => $popularJoki->game?->name." • {$popularJoki->target}",
                'price' => $popularJoki->formatted_price,
                'badge' => 'JOKI TERLARIS',
                'url' => route('joki.show', $popularJoki->slug),
            ];
        }

        return [
            'reply' => "Halo Champion! Saya Aethereal AI, asisten cerdas resmi platform Aethereal Joki. Saya dapat membantu Anda:\n\n"
                ."• Mengetahui harga diamond/item terkini dari database ('Berapa harga diamond MLBB')\n"
                ."• Cek kode promo voucher diskon yang sedang aktif ('Ada promo apa saat ini')\n"
                ."• Merekomendasikan paket top up sesuai budget ('Punya budget 50 ribu untuk MLBB')\n"
                ."• Melacak status pesanan secara real-time ('Status pesanan AJ-8821' atau 'Cek order saya')\n"
                ."• Mencari paket joki rank yang pas ('Paket push rank Mythic')\n\n"
                .'Ada yang bisa saya bantu hari ini?',
            'recommendations' => $recommendations,
            'navigation' => [
                ['label' => 'Top Up Diamonds', 'url' => route('topup')],
                ['label' => 'Jasa Joki Rank', 'url' => route('joki')],
                ['label' => 'Lacak Pesanan', 'url' => route('tracking')],
            ],
            'source' => 'local_intro',
        ];
    }

    /**
     * Helper to test if prompt contains any of the provided keywords.
     *
     * @param  array<int, string>  $words
     */
    protected function containsWord(string $haystack, array $words): bool
    {
        foreach ($words as $word) {
            if (str_contains($haystack, strtolower($word))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt is asking for user identity.
     */
    protected function isUserIdentityInquiry(string $prompt): bool
    {
        return (bool) preg_match('/(?:siapa\s+(?:nama\s+)?(?:saya|aku|gue|gw|ku)|namaku\s+siapa|nama\s+saya\s+siapa|nama\s+aku\s+siapa|siapakah\s+(?:nama\s+)?(?:saya|aku)|kenal\s+(?:sama\s+)?(?:saya|aku|gue|gw)|tahu\s+(?:nama\s+)?(?:saya|ku|gue|gw)|siapa\s+saya|siapa\s+aku|identitas\s+(?:saya|aku)|nama\s+akun\s+(?:saya|ku))/i', $prompt);
    }

    /**
     * Handle user identity response.
     */
    protected function handleUserIdentity(?User $user = null): array
    {
        if ($user) {
            $roleLabel = match ($user->role) {
                'admin' => 'Aethereal Admin',
                'joki' => 'Pro Booster (Joki)',
                default => 'Customer',
            };

            return [
                'reply' => "Halo {$roleLabel}! Nama akun Anda adalah {$user->name} (Email: {$user->email}). Selamat datang di platform Aethereal Joki & Top Up! Apakah ada yang bisa saya bantu hari ini?",
                'recommendations' => [],
                'navigation' => [
                    ['label' => 'Dashboard Saya', 'url' => route('dashboard')],
                    ['label' => 'Pengaturan Profil', 'url' => route('dashboard.profile')],
                ],
                'source' => 'identity_lookup',
            ];
        }

        return [
            'reply' => 'Halo! Saat ini Anda sedang mengakses sebagai Pengunjung Tamu (Guest) dan belum login ke akun mana pun. Silakan login atau daftar akun untuk mengakses dashboard dan profil akun Anda.',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Masuk / Login', 'url' => route('login')],
                ['label' => 'Daftar Akun', 'url' => route('register')],
            ],
            'source' => 'identity_lookup',
        ];
    }

    /**
     * Check if prompt is asking for Python coding.
     */
    protected function isPythonCodingRequest(string $prompt): bool
    {
        if (! str_contains($prompt, 'python')) {
            return false;
        }

        return $this->containsWord($prompt, [
            'coding', 'koding', 'kode', 'code', 'script', 'skrip', 'program',
            'buatkan', 'bikin', 'tuliskan', 'tulis', 'contoh kode', 'source code',
            'fungsi', 'function', 'aplikasi', 'kodingan', 'algoritma',
        ]);
    }

    /**
     * Handle Python coding refusal per web scope guidelines.
     */
    protected function handlePythonCodingDeclined(): array
    {
        return [
            'reply' => 'Maaf, saya tidak dapat membuatkan atau memberikan coding dalam bahasa Python. Ruang lingkup keahlian dan bantuan koding di sini hanya dikhususkan untuk pengembangan Web saja (seperti HTML, CSS, JavaScript, PHP, Laravel, dan database web). Jika Anda membutuhkan bantuan pembuatan website atau fitur web, silakan tanyakan!',
            'recommendations' => [],
            'navigation' => [
                ['label' => 'Katalog Top Up', 'url' => route('topup')],
                ['label' => 'Layanan Joki', 'url' => route('joki')],
            ],
            'source' => 'policy_guardrail',
        ];
    }
}
