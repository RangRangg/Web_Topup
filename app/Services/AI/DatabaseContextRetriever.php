<?php

namespace App\Services\AI;

use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiService;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;

class DatabaseContextRetriever
{
    /**
     * Map of common game keywords/aliases to game slugs in database.
     *
     * @var array<string, string>
     */
    protected array $gameAliases = [
        'mobile legends' => 'mobile-legends',
        'mobile legend' => 'mobile-legends',
        'mlbb' => 'mobile-legends',
        'ml' => 'mobile-legends',
        'free fire' => 'free-fire',
        'freefire' => 'free-fire',
        'ff' => 'free-fire',
        'epep' => 'free-fire',
        'pubg mobile' => 'pubg-mobile',
        'pubgm' => 'pubg-mobile',
        'pubg' => 'pubg-mobile',
        'valorant' => 'valorant',
        'valo' => 'valorant',
        'genshin impact' => 'genshin-impact',
        'genshin' => 'genshin-impact',
        'gi' => 'genshin-impact',
        'honor of kings' => 'honor-of-kings',
        'hok' => 'honor-of-kings',
        'honkai: star rail' => 'honkai-star-rail',
        'honkai star rail' => 'honkai-star-rail',
        'star rail' => 'honkai-star-rail',
        'hsr' => 'honkai-star-rail',
        'roblox' => 'roblox',
        'robux' => 'roblox',
        'call of duty: mobile' => 'cod-mobile',
        'call of duty mobile' => 'cod-mobile',
        'cod mobile' => 'cod-mobile',
        'codm' => 'cod-mobile',
        'point blank' => 'point-blank',
        'pb cash' => 'point-blank',
        'pb' => 'point-blank',
        'steam wallet code idr' => 'steam-wallet-idr',
        'steam wallet' => 'steam-wallet-idr',
        'steam' => 'steam-wallet-idr',
        'league of legends: wild rift' => 'wild-rift',
        'league of legends wild rift' => 'wild-rift',
        'wild rift' => 'wild-rift',
        'wr' => 'wild-rift',
    ];

    /**
     * Retrieve structured database context grounded on user prompt.
     *
     * @return array{
     *     intent: string,
     *     grounding_text: string,
     *     recommendations: array<int, array{type: string, title: string, subtitle: string|null, price: string|null, badge: string|null, url: string}>,
     *     navigation: array<int, array{label: string, url: string}>,
     *     matched_game: Game|null,
     *     matched_order: Order|null,
     *     matched_products: Collection<int, GameProduct>,
     *     matched_joki: Collection<int, JokiService>,
     *     matched_promotions: Collection<int, Promotion>,
     *     budget: float|null,
     *     category: string|null
     * }
     */
    public function retrieve(string $prompt, ?User $user = null): array
    {
        $normalized = strtolower(trim($prompt));

        $orderCode = $this->extractOrderCode($normalized);
        $matchedGame = $this->findGame($normalized);
        $matchedCategory = $this->findCategory($normalized);
        $budget = $this->extractBudget($normalized);

        $matchedOrder = null;
        $matchedProducts = collect();
        $matchedJoki = collect();
        $matchedPromotions = collect();

        $intent = 'general';
        $groundingParts = [];

        // 1. SPECIFIC ORDER CODE LOOKUP
        if ($orderCode) {
            $intent = 'order_code_lookup';
            $matchedOrder = Order::with(['orderItems', 'payment', 'jokiProfile'])->where('order_code', $orderCode)->first();
            if ($matchedOrder) {
                $groundingParts[] = $this->formatOrderGrounding($matchedOrder, $user);
            } else {
                $groundingParts[] = "HASIL PENCARIAN ORDER: Kode pesanan '{$orderCode}' tidak ditemukan di database. Pastikan kode invoice sesuai.";
            }
        }

        // 2. USER RECENT ORDERS INQUIRY
        if ($this->isUserOrderInquiry($normalized) && ! $orderCode) {
            $intent = 'user_orders';
            if ($user) {
                $userOrders = $user->orders()->with(['orderItems', 'payment', 'jokiProfile'])->latest()->take(3)->get();
                if ($userOrders->isNotEmpty()) {
                    $groundingParts[] = "PESANAN TERAKHIR PENGGUNA ({$user->name}):\n";
                    foreach ($userOrders as $uo) {
                        $groundingParts[] = $this->formatOrderGrounding($uo, $user);
                    }
                } else {
                    $groundingParts[] = "DATA PESANAN PENGGUNA: Pengguna {$user->name} saat ini belum memiliki riwayat pesanan.";
                }
            } else {
                $groundingParts[] = 'DATA PESANAN PENGGUNA: Pengunjung saat ini adalah Guest (belum login). Arahkan untuk login atau menggunakan fitur Lacak Order dengan kode invoice.';
            }
        }

        // 3. PROMOTIONS / VOUCHERS INQUIRY
        if ($this->isPromotionInquiry($normalized)) {
            $intent = 'promotions';
            $matchedPromotions = Promotion::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })
                ->get();

            if ($matchedPromotions->isNotEmpty()) {
                $groundingParts[] = "PROMO & VOUCHER DISKON AKTIF DI DATABASE:\n";
                foreach ($matchedPromotions as $promo) {
                    $discountStr = $promo->discount_type === 'percentage'
                        ? number_format($promo->discount_value, 0).'% OFF'
                        : 'Potongan Rp '.number_format($promo->discount_value, 0, ',', '.');
                    $minSpendStr = $promo->min_spend ? ' (Min. Belanja Rp '.number_format($promo->min_spend, 0, ',', '.').')' : '';
                    $groundingParts[] = "- Kode: `{$promo->code}` | {$promo->name} | Diskon: {$discountStr}{$minSpendStr}";
                }
            } else {
                $groundingParts[] = 'PROMO & VOUCHER: Saat ini belum ada promo aktif khusus.';
            }
        }

        // 4. BUDGET SEARCH
        if ($budget !== null) {
            $intent = 'budget_recommendation';
            $query = GameProduct::with('game')->where('is_active', true);
            if ($matchedGame) {
                $query->where('game_id', $matchedGame->id);
            }
            $affordable = (clone $query)->where('price', '<=', $budget)->orderByDesc('price')->take(4)->get();
            if ($affordable->isEmpty()) {
                $cheapest = (clone $query)->orderBy('price')->first();
                if ($cheapest) {
                    $matchedProducts = collect([$cheapest]);
                }
            } else {
                $matchedProducts = $affordable;
            }

            $groundingParts[] = 'PENCARIAN PRODUK SESUAI BUDGET (Rp '.number_format($budget, 0, ',', '.');
            if ($matchedGame) {
                $groundingParts[count($groundingParts) - 1] .= " untuk {$matchedGame->name}";
            }
            $groundingParts[count($groundingParts) - 1] .= "):\n";

            foreach ($matchedProducts as $mp) {
                $groundingParts[] = "- {$mp->name} [{$mp->game?->name}] — Rp ".number_format($mp->price, 0, ',', '.');
            }
        }

        // 5. MATCHED GAME PRODUCTS & JOKI
        if ($matchedGame && $budget === null && ! in_array($intent, ['order_code_lookup', 'user_orders'])) {
            $matchedProducts = GameProduct::where('game_id', $matchedGame->id)
                ->where('is_active', true)
                ->orderBy('price')
                ->get();

            $matchedJoki = JokiService::where('game_id', $matchedGame->id)
                ->where('is_active', true)
                ->orderBy('price')
                ->get();

            if ($this->isJokiInquiry($normalized)) {
                $intent = 'joki_services';
            } else {
                $intent = 'game_products';
            }

            $groundingParts[] = "KATALOG LENGKAP GAME: {$matchedGame->name} (Kategori: {$matchedGame->category}):\n";
            $groundingParts[] = 'Daftar Produk Top Up Tersedia:';
            foreach ($matchedProducts as $gp) {
                $groundingParts[] = "- {$gp->name} (Kode: {$gp->code}) — Rp ".number_format($gp->price, 0, ',', '.');
            }

            if ($matchedJoki->isNotEmpty()) {
                $groundingParts[] = "\nDaftar Layanan Joki Tersedia:";
                foreach ($matchedJoki as $js) {
                    $groundingParts[] = "- {$js->name} (Target: {$js->target}, Durasi: {$js->estimated_duration}) — Rp ".number_format($js->price, 0, ',', '.');
                }
            }
        }

        // 6. GAME CATEGORY INQUIRY
        if ($matchedCategory && ! $matchedGame) {
            $intent = 'game_category';
            $categoryGames = Game::where('is_active', true)
                ->where('category', $matchedCategory)
                ->get();

            $groundingParts[] = "KATALOG GAME KATEGORI '".strtoupper($matchedCategory)."':\n";
            foreach ($categoryGames as $cg) {
                $groundingParts[] = "- {$cg->name} (Slug: {$cg->slug}, Publisher: {$cg->publisher})";
            }
        }

        // 7. CUSTOMER REVIEWS & REPUTATION
        if ($this->isReviewInquiry($normalized)) {
            $intent = 'reviews';
            $totalReviews = Review::count();
            $avgRating = Review::avg('rating') ?? 5.0;
            $recentReviews = Review::latest()->take(4)->get();

            $groundingParts[] = 'STATISTIK ULASAN & KEPUASAN PELANGGAN:';
            $groundingParts[] = '- Rata-rata Rating: '.number_format($avgRating, 1)."/5.0 dari total {$totalReviews} ulasan terverifikasi.";
            $groundingParts[] = 'Contoh Ulasan Pelanggan Terbaru:';
            foreach ($recentReviews as $rev) {
                $groundingParts[] = "- \"{$rev->comment}\" — {$rev->customer_name} ({$rev->rating}/5 bintang untuk {$rev->game_name})";
            }
        }

        // 8. TOTAL USERS, CUSTOMERS, BOOSTERS & ADMIN DATABASE STATS
        if ($this->isBoosterAndAdminInquiry($normalized)) {
            $intent = 'booster_admin_stats';
            $jokiCount = User::where('role', 'joki')->where('is_active', true)->count();
            $adminCount = User::where('role', 'admin')->count();

            $boosters = User::where('role', 'joki')->with('jokiProfile')->orderBy('id')->get();
            $boosterNames = $boosters->map(function ($b) {
                $nick = $b->jokiProfile?->nickname ? " (Nickname: {$b->jokiProfile->nickname})" : '';

                return "- {$b->name}{$nick}";
            })->implode("\n");

            $admins = User::where('role', 'admin')->orderBy('id')->get();
            $adminNames = $admins->map(fn ($a) => "- {$a->name}")->implode("\n");

            $groundingParts[] = "STATISTIK PRO BOOSTER & ADMINISTRATOR DI DATABASE:\n"
                ."- Jumlah Pro Booster / Joki Terdaftar: {$jokiCount} booster.\n"
                ."Daftar Nama Booster:\n{$boosterNames}\n\n"
                ."- Jumlah Administrator Terdaftar: {$adminCount} admin.\n"
                ."Daftar Nama Admin:\n{$adminNames}\n"
                ."Instruksi Jawaban: Jawab langsung jumlah booster ({$jokiCount} booster) dan admin ({$adminCount} admin) beserta daftar lengkap nama masing-masing. JANGAN menggabungkan dengan total pengguna sistem atau customer. Jangan berikan kode mentah, tanda backtick, atau tanda bintang.";
        } elseif ($this->isBoosterInquiry($normalized)) {
            $intent = 'booster_stats';
            $jokiCount = User::where('role', 'joki')->where('is_active', true)->count();
            $boosters = User::where('role', 'joki')->with('jokiProfile')->orderBy('id')->get();
            $boosterNames = $boosters->map(function ($b) {
                $nick = $b->jokiProfile?->nickname ? " (Nickname: {$b->jokiProfile->nickname})" : '';

                return "- {$b->name}{$nick}";
            })->implode("\n");

            $groundingParts[] = "STATISTIK PRO BOOSTER / JOKI DI DATABASE:\n"
                ."- Jumlah Pro Booster Terdaftar: {$jokiCount} booster.\n"
                ."Daftar Nama Pro Booster:\n{$boosterNames}\n"
                ."Instruksi Jawaban: Jawab langsung jumlah pro booster ({$jokiCount} booster) beserta daftar lengkap nama booster. JANGAN menggabungkan dengan total pengguna sistem atau customer. Jangan berikan kode mentah, tanda backtick, atau tanda bintang.";
        } elseif ($this->isAdminInquiry($normalized)) {
            $intent = 'admin_stats';
            $adminCount = User::where('role', 'admin')->count();
            $admins = User::where('role', 'admin')->orderBy('id')->get();
            $adminNames = $admins->map(fn ($a) => "- {$a->name}")->implode("\n");

            $groundingParts[] = "STATISTIK ADMINISTRATOR DI DATABASE:\n"
                ."- Jumlah Administrator Terdaftar: {$adminCount} admin.\n"
                ."Daftar Nama Administrator:\n{$adminNames}\n"
                ."Instruksi Jawaban: Jawab langsung jumlah administrator ({$adminCount} admin) beserta daftar lengkap nama admin. JANGAN menggabungkan dengan total pengguna sistem atau customer. Jangan berikan kode mentah, tanda backtick, atau tanda bintang.";
        } elseif ($this->isTotalUsersInquiry($normalized)) {
            $intent = 'user_stats';
            $totalUsers = User::count();
            $customersCount = User::where('role', 'customer')->count();
            $jokiCount = User::where('role', 'joki')->where('is_active', true)->count();
            $adminCount = User::where('role', 'admin')->count();

            $allUsers = User::select('name', 'role')->orderBy('id')->get();
            $userNames = $allUsers->map(fn ($u) => "- {$u->name} (".ucfirst($u->role).')')->implode("\n");

            $groundingParts[] = "STATISTIK TOTAL PENGGUNA DI DATABASE:\n"
                ."- Total Pengguna Terdaftar: {$totalUsers} akun ({$customersCount} customer, {$jokiCount} pro booster/joki, {$adminCount} admin).\n"
                ."Daftar Nama Pengguna:\n{$userNames}\n"
                ."Instruksi Jawaban: Jawab langsung total pengguna ({$totalUsers} akun) dan sebutkan daftar lengkap nama pengguna secara rapi. Jangan berikan kode mentah atau tanda backtick.";
        } elseif ($this->isCustomerInquiry($normalized)) {
            $intent = 'customer_stats';
            $customersCount = User::where('role', 'customer')->count();

            $customers = User::where('role', 'customer')
                ->select('id', 'name', 'email', 'is_active', 'created_at')
                ->orderBy('id')
                ->get();
            $customerNames = $customers->map(fn ($c) => "- {$c->name}")->implode("\n");

            $groundingParts[] = "STATISTIK CUSTOMER DI DATABASE:\n"
                ."- Jumlah Customer Terdaftar: {$customersCount} customer.\n"
                ."Daftar Nama Customer:\n{$customerNames}\n"
                ."Instruksi Jawaban: Jawab langsung jumlah customer ({$customersCount} customer) beserta daftar lengkap nama customer. JANGAN menggabungkan dengan total pengguna sistem atau total pesanan. Jangan berikan kode mentah atau tanda backtick.";
        }

        // Build UI Recommendations and Navigation
        $recommendations = $this->buildRecommendations($intent, $matchedGame, $matchedProducts, $matchedJoki, $matchedOrder, $matchedPromotions);
        $navigation = $this->buildNavigation($intent, $matchedGame, $matchedOrder);

        return [
            'intent' => $intent,
            'grounding_text' => implode("\n", $groundingParts),
            'recommendations' => $recommendations,
            'navigation' => $navigation,
            'matched_game' => $matchedGame,
            'matched_order' => $matchedOrder,
            'matched_products' => $matchedProducts,
            'matched_joki' => $matchedJoki,
            'matched_promotions' => $matchedPromotions,
            'budget' => $budget,
            'category' => $matchedCategory,
        ];
    }

    /**
     * Get a comprehensive schema and domain knowledge guide for the LLM.
     */
    public function getSchemaAndDomainKnowledge(): string
    {
        $gamesCount = Game::where('is_active', true)->count();
        $productsCount = GameProduct::where('is_active', true)->count();
        $jokiCount = JokiService::where('is_active', true)->count();
        $promosCount = Promotion::where('is_active', true)->count();
        $customersCount = User::where('role', 'customer')->count();
        $boostersCount = User::where('role', 'joki')->where('is_active', true)->count();
        $adminCount = User::where('role', 'admin')->count();
        $totalUsers = User::count();

        return "STRUKTUR & PEMAHAMAN DATABASE PLATFORM AETHEREAL TOPUP & JOKI:\n"
            ."- Anda terhubung langsung ke database produksi dengan status saat ini: {$customersCount} customer terdaftar, {$boostersCount} pro booster aktif, {$adminCount} admin, {$totalUsers} total pengguna, {$gamesCount} game aktif, {$productsCount} paket topup, {$jokiCount} layanan joki, dan {$promosCount} promo aktif.\n"
            ."- Tabel `users`: Menyimpan akun pengguna dengan kolom role ('customer', 'joki', 'admin'). Saat ini terdapat {$customersCount} customer terdaftar, {$boostersCount} booster aktif, dan {$adminCount} admin.\n"
            ."- Tabel `games`: Menyimpan judul game, slug URL, kategori (moba, fps, battle_royale, rpg), publisher, dan status aktif.\n"
            ."- Tabel `game_products`: Nominal top up instan resmi (Diamonds MLBB/FF, UC PUBG, VP Valorant, Genesis Crystals, Stellar Jade, Robux, CP, PB Cash, Steam Wallet, Wild Cores). Pembelian instan hanya butuh User ID & Server ID tanpa password akun.\n"
            ."- Tabel `joki_services`: Paket jasa joki bergaransi 100% Anti Banned. Terbagi menjadi tipe 'rank' (dikerjakan pro booster) dan tipe 'mabar/gendong' (party mabar bersama pro booster tanpa bagi password).\n"
            ."- Tabel `promotions`: Voucher diskon resmi dengan kode promo, persentase/potongan nominal, dan batas minimum belanja.\n"
            ."- Tabel `orders`: Transaksi pesanan dengan kode invoice (format AJ-XXXX atau ORD-XXXX). Status pesanan: pending, paid, waiting_assignment, processing, completed, cancelled.\n"
            ."- Tabel `payments`: Metode pembayaran digital (QRIS otomatis, Virtual Account BCA/Mandiri/BNI/BRI, E-Wallet DANA/GoPay).\n"
            ."- Tabel `reviews`: Testimoni dan rating 1-5 bintang dari pelanggan terverifikasi.\n";
    }

    /**
     * Check if prompt inquires about order status.
     */
    public function isUserOrderInquiry(string $prompt): bool
    {
        $keywords = [
            'status order', 'pesanan saya', 'order saya', 'cek order', 'lacak pesanan',
            'status pesanan', 'riwayat order', 'pesanan ku', 'orderan saya', 'progress joki saya',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($prompt, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt inquires about promotions or discounts.
     */
    public function isPromotionInquiry(string $prompt): bool
    {
        $keywords = [
            'promo', 'promosi', 'diskon', 'voucher', 'kupon', 'cashback', 'potongan', 'kode promo', 'kode diskon', 'hemat', 'event diskon',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($prompt, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt inquires about joki services.
     */
    public function isJokiInquiry(string $prompt): bool
    {
        $keywords = [
            'joki', 'push rank', 'rank', 'gendong', 'mabar', 'mythic', 'radiant', 'immortal', 'placement', 'winrate', 'paket joki',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($prompt, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt inquires about customer reviews or rating.
     */
    public function isReviewInquiry(string $prompt): bool
    {
        $keywords = [
            'review', 'ulasan', 'testimoni', 'rating', 'bintang', 'reputasi', 'kepuasan', 'terpercaya', 'apakah terpercaya', 'testi',
        ];

        foreach ($keywords as $kw) {
            if (str_contains($prompt, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt inquires specifically about customer/pelanggan count or list.
     */
    public function isCustomerInquiry(string $prompt): bool
    {
        $normalized = strtolower(trim($prompt));

        // Negative check: general customer service without stats
        if (preg_match('/\b(customer service|hubungi cs|kontak cs|nomor cs|bantuan cs)\b/iu', $normalized)
            && ! preg_match('/\b(berapa|bearapa|brapa|jumlah|total|hitung|daftar|list|siapa|ada)\b/iu', $normalized)) {
            return false;
        }

        // Must have customer-specific keywords (including -nya suffix or plural)
        $hasCustomerTerm = (bool) preg_match('/\b(customers?|customernya|pelanggan(?:nya)?|clients?|clientnya|klien(?:nya)?)\b/iu', $normalized);
        if (! $hasCustomerTerm) {
            return false;
        }

        // Direct matching patterns for count / quantity / list of customers
        if (preg_match('/(?:ada\s+)?(?:berapa|bearapa|brapa)\s+(?:banyak\s+)?(?:jumlah\s+)?(?:customers?|customernya|pelanggan(?:nya)?|client(?:nya)?|klien(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:jumlah|total|banyaknya|hitung|daftar|list|data|rincian)\s+(?:customers?|customernya|pelanggan(?:nya)?|client(?:nya)?|klien(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:customers?|customernya|pelanggan(?:nya)?|client(?:nya)?|klien(?:nya)?)\s+(?:ada\s+)?(?:berapa|bearapa|brapa|totalnya|jumlahnya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:siapa|siapakah)\s+(?:saja\s+)?(?:customers?|customernya|pelanggan(?:nya)?|client(?:nya)?|klien(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        $hasStatsTerm = (bool) preg_match('/\b(berapa|bearapa|brapa|jumlah|total|banyak|daftar|list|data|siapa|siapakah|hitung|ada|terdaftar)\b/iu', $normalized);

        return $hasCustomerTerm && $hasStatsTerm;
    }

    /**
     * Check if customer inquiry asks for a list/details rather than just count.
     */
    public function isCustomerListInquiry(string $prompt): bool
    {
        $normalized = strtolower(trim($prompt));

        return (bool) preg_match('/\b(siapa|siapakah|daftar|list|rincian|detail|data)\s+(?:saja\s+)?(?:customers?|customernya|pelanggan(?:nya)?|client(?:nya)?|klien(?:nya)?)\b/iu', $normalized)
            || (bool) preg_match('/(?:customers?|customernya|pelanggan(?:nya)?|client(?:nya)?|klien(?:nya)?)\s+(?:siapa\s+saja|daftarnya|listnya|rinciannya)\b/iu', $normalized);
    }

    /**
     * Check if prompt inquires about both pro booster and admin together.
     */
    public function isBoosterAndAdminInquiry(string $prompt): bool
    {
        $normalized = strtolower(trim($prompt));

        $hasBooster = (bool) preg_match('/\b(pro\s+booster|boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\b/iu', $normalized);
        $hasAdmin = (bool) preg_match('/\b(admins?|adminnya|administrator(?:nya)?)\b/iu', $normalized);

        return $hasBooster && $hasAdmin;
    }

    /**
     * Check if prompt inquires specifically about pro booster / joki personnel count or list.
     */
    public function isBoosterInquiry(string $prompt): bool
    {
        $normalized = strtolower(trim($prompt));

        if ($this->isBoosterAndAdminInquiry($normalized)) {
            return false;
        }

        // Negative check: inquiring about joki service/pricing/order rather than personnel
        if (preg_match('/\b(paket|harga|tarif|biaya|cara|pesan|order|beli|katalog|aturan|format)\b/iu', $normalized)
            && ! preg_match('/\b(berapa\s+(?:pro\s+)?(?:booster|joki)|ada\s+berapa\s+(?:pro\s+)?(?:booster|joki)|siapa\s+(?:saja\s+)?(?:pro\s+)?(?:booster|joki)|daftar\s+(?:pro\s+)?(?:booster|joki)|list\s+(?:pro\s+)?(?:booster|joki))\b/iu', $normalized)) {
            return false;
        }

        $hasBoosterTerm = (bool) preg_match('/\b(pro\s+boosters?|pro\s+boosternya|boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\b/iu', $normalized);
        if (! $hasBoosterTerm) {
            return false;
        }

        // Direct matching patterns for count / quantity / list of boosters
        if (preg_match('/(?:ada\s+)?(?:berapa|bearapa|brapa)\s+(?:banyak\s+)?(?:jumlah\s+)?(?:pro\s+)?(?:boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:jumlah|total|banyaknya|hitung|daftar|list|data|rincian)\s+(?:pro\s+)?(?:boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:pro\s+)?(?:boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\s+(?:ada\s+)?(?:berapa|bearapa|brapa|totalnya|jumlahnya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:siapa|siapakah)\s+(?:saja\s+)?(?:pro\s+)?(?:boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:pro\s+)?(?:boosters?|boosternya|joki(?:nya)?|penjoki(?:nya)?)\s+(?:siapa\s+saja|daftarnya|listnya|rinciannya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/\b(pro\s+booster|booster)\b/iu', $normalized) && preg_match('/\b(berapa|siapa|ada|daftar|list|jumlah|total|nama|akun|orang)\b/iu', $normalized)) {
            return true;
        }

        return false;
    }

    /**
     * Check if prompt inquires specifically about administrator count or list.
     */
    public function isAdminInquiry(string $prompt): bool
    {
        $normalized = strtolower(trim($prompt));

        if ($this->isBoosterAndAdminInquiry($normalized)) {
            return false;
        }

        $hasAdminTerm = (bool) preg_match('/\b(admins?|adminnya|administrator(?:nya)?|pengelola(?:nya)?)\b/iu', $normalized);
        if (! $hasAdminTerm) {
            return false;
        }

        // Direct matching patterns for count / quantity / list of admins
        if (preg_match('/(?:ada\s+)?(?:berapa|bearapa|brapa)\s+(?:banyak\s+)?(?:jumlah\s+)?(?:admins?|adminnya|administrator(?:nya)?|pengelola(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:jumlah|total|banyaknya|hitung|daftar|list|data|rincian)\s+(?:admins?|adminnya|administrator(?:nya)?|pengelola(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:admins?|adminnya|administrator(?:nya)?|pengelola(?:nya)?)\s+(?:ada\s+)?(?:berapa|bearapa|brapa|totalnya|jumlahnya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:siapa|siapakah)\s+(?:saja\s+)?(?:admins?|adminnya|administrator(?:nya)?|pengelola(?:nya)?)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:admins?|adminnya|administrator(?:nya)?|pengelola(?:nya)?)\s+(?:siapa\s+saja|daftarnya|listnya|rinciannya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/\b(admins?|adminnya|administrator(?:nya)?)\b/iu', $normalized) && preg_match('/\b(berapa|siapa|ada|daftar|list|jumlah|total|nama|akun|orang)\b/iu', $normalized)) {
            return true;
        }

        return false;
    }

    /**
     * Check if prompt inquires about total users / platform members / all accounts.
     */
    public function isTotalUsersInquiry(string $prompt): bool
    {
        $normalized = strtolower(trim($prompt));

        // If explicitly asking about customer, booster, or admin specifically, let them handle it
        if ($this->isCustomerInquiry($normalized) || $this->isBoosterInquiry($normalized) || $this->isAdminInquiry($normalized) || $this->isBoosterAndAdminInquiry($normalized)) {
            return false;
        }

        // If asking about joki / admin count specifically without asking all / total
        if (preg_match('/\b(joki|booster|admin)\b/iu', $normalized) && ! preg_match('/\b(semua|seluruh|total)\b/iu', $normalized)) {
            return false;
        }

        // Check for total/all user/pengguna/akun/member patterns (including -nya suffix or plural)
        if (preg_match('/(?:ada\s+)?(?:berapa|bearapa|brapa)\s+(?:banyak\s+)?(?:total\s+)?(?:jumlah\s+)?(?:users?|usernya|pengguna(?:nya)?|akun(?:nya)?|members?|membernya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:total|jumlah|banyaknya|hitung)\s+(?:seluruh\s+|semua\s+)?(?:users?|usernya|pengguna(?:nya)?|akun(?:nya)?|members?|membernya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/(?:users?|usernya|pengguna(?:nya)?|akun(?:nya)?|members?|membernya)\s+(?:ada\s+)?(?:berapa|bearapa|brapa|totalnya|jumlahnya)\b/iu', $normalized)) {
            return true;
        }

        if (preg_match('/\b(users?|usernya|pengguna(?:nya)?|akun(?:nya)?)\b/iu', $normalized) && preg_match('/\b(berapa|bearapa|brapa|jumlah|total|terdaftar|semua|seluruh)\b/iu', $normalized)) {
            return true;
        }

        return false;
    }

    /**
     * Extract specific order code from prompt (e.g., AJ-8821, ORD-ALPHA-123).
     */
    public function extractOrderCode(string $prompt): ?string
    {
        if (preg_match('/\b(AJ-\d+|ORD-[A-Z0-9_-]+|AETH-[A-Z0-9_-]+)\b/i', $prompt, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    /**
     * Match Game model from prompt.
     */
    public function findGame(string $prompt): ?Game
    {
        $games = Game::where('is_active', true)->get();

        foreach ($this->gameAliases as $alias => $slug) {
            if (preg_match('/(?:\b|^)'.preg_quote($alias, '/').'(?:\b|$)/iu', $prompt)) {
                $found = $games->firstWhere('slug', $slug);
                if ($found) {
                    return $found;
                }
            }
        }

        foreach ($games as $game) {
            $gameName = strtolower($game->name);
            if (preg_match('/(?:\b|^)'.preg_quote($gameName, '/').'(?:\b|$)/iu', $prompt) ||
                preg_match('/(?:\b|^)'.preg_quote($game->slug, '/').'(?:\b|$)/iu', $prompt)) {
                return $game;
            }
        }

        return null;
    }

    /**
     * Match game category from prompt.
     */
    public function findCategory(string $prompt): ?string
    {
        $categories = [
            'moba' => ['moba'],
            'fps' => ['fps', 'first person shooter', 'tembak', 'shooter'],
            'battle_royale' => ['battle royale', 'battleroyale', 'br', 'survival'],
            'rpg' => ['rpg', 'role playing game', 'gacha', 'anime'],
        ];

        foreach ($categories as $cat => $keywords) {
            foreach ($keywords as $kw) {
                if (preg_match('/\b'.preg_quote($kw, '/').'\b/i', $prompt)) {
                    return $cat;
                }
            }
        }

        return null;
    }

    /**
     * Extract budget numeric value from user prompt.
     */
    public function extractBudget(string $prompt): ?float
    {
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:ribu|rb|k)\b/i', $prompt, $matches)) {
            $val = (float) str_replace(',', '.', $matches[1]);

            return $val * 1000;
        }

        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(?:juta|jt)\b/i', $prompt, $matches)) {
            $val = (float) str_replace(',', '.', $matches[1]);

            return $val * 1000000;
        }

        if (preg_match('/(?:budget|dana|uang|modal|rp\.?|punya)\s*(\d{1,3}(?:\.\d{3})+|\d{4,})/i', $prompt, $matches)) {
            $clean = str_replace('.', '', $matches[1]);

            return (float) $clean;
        }

        return null;
    }

    /**
     * Format an order's information for system grounding.
     */
    protected function formatOrderGrounding(Order $order, ?User $currentUser): string
    {
        $item = $order->orderItems->first();
        $itemName = $item ? $item->item_name : 'Layanan Game';
        $paymentStatus = strtoupper($order->payment?->status ?? 'UNPAID');
        $booster = $order->jokiProfile ? $order->jokiProfile->nickname : 'Menunggu Penugasan Booster';
        $progress = $order->progress_note ?: 'Dalam antrean pengerjaan';

        return "DETAIL PESANAN #{$order->order_code}:\n"
            ."- Layanan: {$itemName}\n"
            ."- Status Pesanan: {$order->status_label} (".strtoupper($order->order_status).")\n"
            ."- Status Pembayaran: {$paymentStatus}\n"
            .'- Total Pembayaran: Rp '.number_format($order->total_amount, 0, ',', '.')."\n"
            ."- Booster Ditugaskan: {$booster}\n"
            ."- Catatan Progress: {$progress}\n"
            ."- Dibuat Pada: {$order->created_at->format('d M Y H:i')}";
    }

    /**
     * Build structured card recommendations from retrieved models.
     *
     * @param  Collection<int, GameProduct>  $products
     * @param  Collection<int, JokiService>  $joki
     * @param  Collection<int, Promotion>  $promotions
     * @return array<int, array{type: string, title: string, subtitle: string|null, price: string|null, badge: string|null, url: string}>
     */
    protected function buildRecommendations(
        string $intent,
        ?Game $game,
        Collection $products,
        Collection $joki,
        ?Order $order,
        Collection $promotions
    ): array {
        $recommendations = [];

        // 1. Order Card
        if ($order) {
            $item = $order->orderItems->first();
            $itemName = $item ? $item->item_name : 'Layanan Game';

            $recommendations[] = [
                'type' => 'order',
                'title' => "#{$order->order_code} ({$itemName})",
                'subtitle' => "Status: {$order->status_label} • ".strtoupper($order->payment?->status ?? 'UNPAID'),
                'price' => $order->formatted_total,
                'badge' => strtoupper($order->order_status),
                'url' => route('orders.show', $order->order_code),
            ];

            return $recommendations;
        }

        // 2. Promotions Cards
        if ($intent === 'promotions' && $promotions->isNotEmpty()) {
            foreach ($promotions->take(3) as $promo) {
                $discountStr = $promo->discount_type === 'percentage'
                    ? number_format($promo->discount_value, 0).'% OFF'
                    : 'Potongan Rp '.number_format($promo->discount_value, 0, ',', '.');

                $recommendations[] = [
                    'type' => 'product',
                    'title' => "Voucher: {$promo->code}",
                    'subtitle' => $promo->name,
                    'price' => $discountStr,
                    'badge' => 'PROMO AKTIF',
                    'url' => route('topup'),
                ];
            }

            return $recommendations;
        }

        // 3. Joki Cards
        if (($intent === 'joki_services' || $products->isEmpty()) && $joki->isNotEmpty()) {
            foreach ($joki->take(3) as $s) {
                $recommendations[] = [
                    'type' => 'joki',
                    'title' => $s->name,
                    'subtitle' => "Target: {$s->target} • Durasi: {$s->estimated_duration}",
                    'price' => $s->formatted_price,
                    'badge' => $s->is_popular ? 'POPULER' : 'BERGARANSI',
                    'url' => route('joki.show', $s->slug),
                ];
            }

            return $recommendations;
        }

        // 4. Products Cards
        if ($products->isNotEmpty()) {
            foreach ($products->take(4) as $p) {
                $recommendations[] = [
                    'type' => 'product',
                    'title' => $p->name,
                    'subtitle' => $p->game?->name ?? ($game?->name ?? 'Aethereal Topup'),
                    'price' => $p->formatted_price,
                    'badge' => 'Instant Top Up',
                    'url' => $p->game ? route('topup.game', $p->game->slug) : route('topup'),
                ];
            }

            return $recommendations;
        }

        // Fallback default popular products
        if ($game) {
            $defaultProducts = GameProduct::where('game_id', $game->id)->where('is_active', true)->take(2)->get();
            foreach ($defaultProducts as $p) {
                $recommendations[] = [
                    'type' => 'product',
                    'title' => $p->name,
                    'subtitle' => $game->name,
                    'price' => $p->formatted_price,
                    'badge' => 'Pilihan Utama',
                    'url' => route('topup.game', $game->slug),
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Build navigation shortcuts.
     *
     * @return array<int, array{label: string, url: string}>
     */
    protected function buildNavigation(string $intent, ?Game $game, ?Order $order): array
    {
        $navigation = [];

        if ($order) {
            $navigation[] = ['label' => "Lihat Pesanan #{$order->order_code}", 'url' => route('orders.show', $order->order_code)];
            $navigation[] = ['label' => 'Halaman Lacak Order', 'url' => route('tracking')];

            return $navigation;
        }

        if ($game) {
            $navigation[] = ['label' => "Top Up {$game->name}", 'url' => route('topup.game', $game->slug)];
            $navigation[] = ['label' => 'Cek Jasa Joki', 'url' => route('joki')];

            return $navigation;
        }

        if ($intent === 'promotions') {
            $navigation[] = ['label' => 'Gunakan di Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Pilih Layanan Joki', 'url' => route('joki')];

            return $navigation;
        }

        if (in_array($intent, ['customer_stats', 'user_stats', 'booster_stats', 'admin_stats', 'booster_admin_stats'], true)) {
            $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
            $navigation[] = ['label' => 'Layanan Joki', 'url' => route('joki')];
            $navigation[] = ['label' => 'Lacak Pesanan', 'url' => route('tracking')];

            return $navigation;
        }

        $navigation[] = ['label' => 'Katalog Top Up', 'url' => route('topup')];
        $navigation[] = ['label' => 'Layanan Joki Rank', 'url' => route('joki')];
        $navigation[] = ['label' => 'Lacak Pesanan', 'url' => route('tracking')];

        return $navigation;
    }
}
