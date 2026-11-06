<?php

namespace Database\Seeders;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameProduct;
use App\Models\JokiProfile;
use App\Models\JokiService;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with authentic top-up and joki data.
     */
    public function run(): void
    {
        // -------------------------------------------------------------
        // 1. PRIMARY OPERATIONAL ACCOUNTS
        // -------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Aethereal Operations Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
            ]
        );

        $jokiUser = User::firstOrCreate(
            ['email' => 'joki@example.com'],
            [
                'name' => 'Pro Booster Kael',
                'password' => Hash::make('password'),
                'role' => 'joki',
                'phone' => '081298765432',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Reza Pratama',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '085712345678',
            ]
        );

        // -------------------------------------------------------------
        // 2. VERIFIED JOKI BOOSTER PROFILES
        // -------------------------------------------------------------
        $jokiProfile = JokiProfile::firstOrCreate(
            ['user_id' => $jokiUser->id],
            [
                'nickname' => 'Kael the Ascendant',
                'phone' => $jokiUser->phone,
                'bio' => 'Booster profesional bersertifikasi 6+ tahun. Top Global Mobile Legends & Radiant Valorant. Winrate garansi 90%+, manual hand murni tanpa script.',
                'specialized_games' => ['Mobile Legends', 'Valorant', 'Honor of Kings', 'PUBG Mobile', 'Free Fire'],
                'highest_rank' => 'Mythical Immortal 142 Stars / Radiant #38',
                'rating' => 4.98,
                'completed_orders_count' => 412,
                'status' => 'available',
                'is_verified' => true,
            ]
        );

        // -------------------------------------------------------------
        // 3. OFFICIAL GAME TITLES (12 Standard Topup Games)
        // -------------------------------------------------------------
        $gamesData = [
            [
                'name' => 'Mobile Legends',
                'slug' => 'mobile-legends',
                'category' => 'moba',
                'publisher' => 'Moonton Games',
                'icon_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Top Up Diamond resmi MLBB dan Jasa Joki Rank Mobile Legends: Bang Bang mulai dari Epic, Legend, Mythic Honor, hingga Immortal 100+ Star.',
                'badge' => 'HOT',
                'order_priority' => 1,
            ],
            [
                'name' => 'Free Fire',
                'slug' => 'free-fire',
                'category' => 'battle_royale',
                'publisher' => 'Garena',
                'icon_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Isi ulang Diamond Free Fire kilat & jasa push rank Free Fire MAX Battle Royale dan Clash Squad tier Master & Grandmaster aman 100%.',
                'badge' => 'BESTSELLER',
                'order_priority' => 2,
            ],
            [
                'name' => 'PUBG Mobile',
                'slug' => 'pubg-mobile',
                'category' => 'battle_royale',
                'publisher' => 'Krafton / Level Infinite',
                'icon_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Top up Unknown Cash (UC) legal dan jasa push rank PUBG Mobile tier Crown, Ace, Ace Master, hingga Conqueror bergaransi.',
                'badge' => 'POPULAR',
                'order_priority' => 3,
            ],
            [
                'name' => 'Valorant',
                'slug' => 'valorant',
                'category' => 'fps',
                'publisher' => 'Riot Games',
                'icon_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Top Up Valorant Points (VP) resmi Riot Games server SEA/Indonesia dan joki ranked oleh booster tier Radiant/Immortal.',
                'badge' => 'POPULAR',
                'order_priority' => 4,
            ],
            [
                'name' => 'Genshin Impact',
                'slug' => 'genshin-impact',
                'category' => 'rpg',
                'publisher' => 'HoYoverse',
                'icon_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1563089145-599997674d42?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Beli Genesis Crystals dan Blessing of the Welkin Moon via UID resmi HoYoverse. Layanan Spiral Abyss 36 Star & perawatan akun.',
                'badge' => 'FAVORITE',
                'order_priority' => 5,
            ],
            [
                'name' => 'Honor of Kings',
                'slug' => 'honor-of-kings',
                'category' => 'moba',
                'publisher' => 'Level Infinite',
                'icon_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Top up Token resmi Honor of Kings global dan jasa joki rank Diamond, Master, Grandmaster, serta Epic King winrate tinggi.',
                'badge' => 'NEW',
                'order_priority' => 6,
            ],
            [
                'name' => 'Honkai: Star Rail',
                'slug' => 'honkai-star-rail',
                'category' => 'rpg',
                'publisher' => 'HoYoverse',
                'icon_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Top up Oneiric Shards & Express Supply Pass via UID resmi HoYoverse. Layanan Memory of Chaos & Apocalyptic Shadow.',
                'badge' => 'POPULAR',
                'order_priority' => 7,
            ],
            [
                'name' => 'Roblox',
                'slug' => 'roblox',
                'category' => 'rpg',
                'publisher' => 'Roblox Corporation',
                'icon_url' => 'https://images.unsplash.com/photo-1579373903781-fd5c0c30c4cd?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1579373903781-fd5c0c30c4cd?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Beli Robux resmi instan untuk kustomisasi avatar dan pembelian game pass favorit di platform Roblox.',
                'badge' => 'BESTSELLER',
                'order_priority' => 8,
            ],
            [
                'name' => 'Call of Duty: Mobile',
                'slug' => 'cod-mobile',
                'category' => 'fps',
                'publisher' => 'Garena',
                'icon_url' => 'https://images.unsplash.com/photo-1542751110-97427bbecf20?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1542751110-97427bbecf20?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Isi CP (COD Points) resmi Garena untuk gacha draw senjata Legendary/Mythic dan Battle Pass CODM.',
                'badge' => 'POPULAR',
                'order_priority' => 9,
            ],
            [
                'name' => 'Point Blank',
                'slug' => 'point-blank',
                'category' => 'fps',
                'publisher' => 'Zepetto',
                'icon_url' => 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1560253023-3ec5d502959f?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Isi PB Cash resmi Zepetto Indonesia instan untuk beli senjata seri terbaru dan item battle pass.',
                'badge' => 'POPULAR',
                'order_priority' => 10,
            ],
            [
                'name' => 'Steam Wallet Code IDR',
                'slug' => 'steam-wallet-idr',
                'category' => 'fps',
                'publisher' => 'Valve',
                'icon_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Voucher resmi Steam Wallet IDR untuk beli game di Steam Store, item pasar komunitas Dota 2, CS2, dll.',
                'badge' => 'FAVORITE',
                'order_priority' => 11,
            ],
            [
                'name' => 'League of Legends: Wild Rift',
                'slug' => 'wild-rift',
                'category' => 'moba',
                'publisher' => 'Riot Games',
                'icon_url' => 'https://images.unsplash.com/photo-1563206767-5b18f218e8de?auto=format&fit=crop&w=400&q=80',
                'banner_url' => 'https://images.unsplash.com/photo-1563206767-5b18f218e8de?auto=format&fit=crop&w=1200&q=80',
                'description' => 'Isi Wild Cores resmi Riot Games untuk beli skin juara dan Wild Pass League of Legends: Wild Rift.',
                'badge' => 'POPULAR',
                'order_priority' => 12,
            ],
        ];

        $games = [];
        foreach ($gamesData as $gd) {
            $games[$gd['slug']] = Game::updateOrCreate(['slug' => $gd['slug']], $gd);
        }

        // -------------------------------------------------------------
        // 4. GAME PRODUCTS (Standard Market Denominations & Pass Pricing)
        // -------------------------------------------------------------
        $productsData = [
            // --- MOBILE LEGENDS ---
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '86 Diamonds MLBB',
                'slug' => '86-diamonds-mlbb',
                'code' => 'ML-86',
                'category' => 'diamond',
                'price' => 21500,
                'original_price' => 24000,
                'stock' => 9999,
                'description' => 'Top up diamond legal via User ID & Zone ID. Proses kilat 1-3 detik.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '257 Diamonds MLBB',
                'slug' => '257-diamonds-mlbb',
                'code' => 'ML-257',
                'category' => 'diamond',
                'price' => 63500,
                'original_price' => 70000,
                'stock' => 9999,
                'description' => 'Cukup untuk beli Starlight Pass atau skin diskon.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => 'Weekly Diamond Pass MLBB',
                'slug' => 'weekly-diamond-pass-mlbb',
                'code' => 'ML-WDP',
                'category' => 'pass',
                'price' => 28500,
                'original_price' => 32000,
                'stock' => 9999,
                'description' => 'Dapatkan total 210 Diamonds + 70 Starlight Points selama 7 hari.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => 'Twilight Pass MLBB',
                'slug' => 'twilight-pass-mlbb',
                'code' => 'ML-TP',
                'category' => 'pass',
                'price' => 145000,
                'original_price' => 160000,
                'stock' => 9999,
                'description' => 'Buka reward eksklusif skin hero dan ribuan ticket.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '5 Diamonds MLBB',
                'slug' => '5-diamonds-mlbb',
                'code' => 'ML-5',
                'category' => 'diamond',
                'price' => 1600,
                'original_price' => 2000,
                'stock' => 9999,
                'description' => 'Nominal hemat untuk klaim bonus top up season baru.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '172 Diamonds MLBB',
                'slug' => '172-diamonds-mlbb',
                'code' => 'ML-172',
                'category' => 'diamond',
                'price' => 42500,
                'original_price' => 48000,
                'stock' => 9999,
                'description' => 'Top up diamond instan resmi Moonton via User ID & Zone ID.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '344 Diamonds MLBB',
                'slug' => '344-diamonds-mlbb',
                'code' => 'ML-344',
                'category' => 'diamond',
                'price' => 84500,
                'original_price' => 95000,
                'stock' => 9999,
                'description' => 'Pilihan populer event draw skin Kolaborasi dan Collector.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '706 Diamonds MLBB',
                'slug' => '706-diamonds-mlbb',
                'code' => 'ML-706',
                'category' => 'diamond',
                'price' => 173000,
                'original_price' => 192000,
                'stock' => 9999,
                'description' => 'Diamond masuk seketika untuk gacha event Epic Showcase.',
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => '2195 Diamonds MLBB',
                'slug' => '2195-diamonds-mlbb',
                'code' => 'ML-2195',
                'category' => 'diamond',
                'price' => 535000,
                'original_price' => 590000,
                'stock' => 9999,
                'description' => 'Paket sultan diamond untuk koleksi semua skin terbaru.',
            ],

            // --- FREE FIRE ---
            [
                'game_id' => $games['free-fire']->id,
                'name' => '140 Diamonds Free Fire',
                'slug' => '140-diamonds-free-fire',
                'code' => 'FF-140',
                'category' => 'diamond',
                'price' => 19500,
                'original_price' => 22000,
                'stock' => 9999,
                'description' => 'Top up diamond Free Fire instan via Player ID.',
            ],
            [
                'game_id' => $games['free-fire']->id,
                'name' => '355 Diamonds Free Fire',
                'slug' => '355-diamonds-free-fire',
                'code' => 'FF-355',
                'category' => 'diamond',
                'price' => 48500,
                'original_price' => 54000,
                'stock' => 9999,
                'description' => 'Bisa untuk spin bundle dan upgrade senjata eksklusif.',
            ],
            [
                'game_id' => $games['free-fire']->id,
                'name' => '720 Diamonds Free Fire',
                'slug' => '720-diamonds-free-fire',
                'code' => 'FF-720',
                'category' => 'diamond',
                'price' => 98000,
                'original_price' => 110000,
                'stock' => 9999,
                'description' => 'Paket diamond favorit untuk event Booyah Pass & Mystery Shop.',
            ],
            [
                'game_id' => $games['free-fire']->id,
                'name' => 'Membership Mingguan Free Fire',
                'slug' => 'membership-mingguan-free-fire',
                'code' => 'FF-WM',
                'category' => 'pass',
                'price' => 30000,
                'original_price' => 35000,
                'stock' => 9999,
                'description' => 'Dapatkan diamond harian selama 7 hari plus badge khusus member.',
            ],
            [
                'game_id' => $games['free-fire']->id,
                'name' => 'Membership Bulanan Free Fire',
                'slug' => 'membership-bulanan-free-fire',
                'code' => 'FF-MM',
                'category' => 'pass',
                'price' => 120000,
                'original_price' => 140000,
                'stock' => 9999,
                'description' => 'Paket langganan bulanan Free Fire paling hemat untuk kolektor.',
            ],

            // --- PUBG MOBILE ---
            [
                'game_id' => $games['pubg-mobile']->id,
                'name' => '325 UC PUBG Mobile',
                'slug' => '325-uc-pubg-mobile',
                'code' => 'PUBG-325',
                'category' => 'uc',
                'price' => 72000,
                'original_price' => 80000,
                'stock' => 9999,
                'description' => 'Unknown Cash (UC) legal untuk server Indonesia / Global.',
            ],
            [
                'game_id' => $games['pubg-mobile']->id,
                'name' => '660 UC (Royale Pass) PUBG',
                'slug' => '660-uc-pubg-mobile',
                'code' => 'PUBG-660',
                'category' => 'uc',
                'price' => 145000,
                'original_price' => 160000,
                'stock' => 9999,
                'description' => 'Cukup untuk membuka Elite Royale Pass musim terbaru.',
            ],
            [
                'game_id' => $games['pubg-mobile']->id,
                'name' => '60 + 3 UC PUBG Mobile',
                'slug' => '60-uc-pubg-mobile',
                'code' => 'PUBG-63',
                'category' => 'uc',
                'price' => 14500,
                'original_price' => 16000,
                'stock' => 9999,
                'description' => 'Isi UC instan via Player ID resmi Level Infinite.',
            ],
            [
                'game_id' => $games['pubg-mobile']->id,
                'name' => '1500 + 300 UC PUBG Mobile',
                'slug' => '1800-uc-pubg-mobile',
                'code' => 'PUBG-1800',
                'category' => 'uc',
                'price' => 360000,
                'original_price' => 400000,
                'stock' => 9999,
                'description' => 'Paket UC bonus besar untuk spin Lucky Crate senjata upgrade.',
            ],

            // --- VALORANT ---
            [
                'game_id' => $games['valorant']->id,
                'name' => '1000 Valorant Points (VP)',
                'slug' => '1000-valorant-points',
                'code' => 'VALO-1000',
                'category' => 'points',
                'price' => 108000,
                'original_price' => 120000,
                'stock' => 9999,
                'description' => 'Cukup untuk membeli Battlepass Valorant atau skin knife hemat.',
            ],
            [
                'game_id' => $games['valorant']->id,
                'name' => '475 Valorant Points (VP)',
                'slug' => '475-valorant-points',
                'code' => 'VALO-475',
                'category' => 'points',
                'price' => 53000,
                'original_price' => 60000,
                'stock' => 9999,
                'description' => 'Redeem Points resmi Riot Games untuk server SEA / Indonesia.',
            ],
            [
                'game_id' => $games['valorant']->id,
                'name' => '2050 Valorant Points (VP)',
                'slug' => '2050-valorant-points',
                'code' => 'VALO-2050',
                'category' => 'points',
                'price' => 215000,
                'original_price' => 240000,
                'stock' => 9999,
                'description' => 'Cukup untuk membeli 1 skin senjata Premium (Prime, Reaver, dll).',
            ],
            [
                'game_id' => $games['valorant']->id,
                'name' => '5350 Valorant Points (VP)',
                'slug' => '5350-valorant-points',
                'code' => 'VALO-5350',
                'category' => 'points',
                'price' => 535000,
                'original_price' => 590000,
                'stock' => 9999,
                'description' => 'Paket bundling skin eksklusif senjata Ultra Edition.',
            ],

            // --- GENSHIN IMPACT ---
            [
                'game_id' => $games['genshin-impact']->id,
                'name' => 'Blessing of the Welkin Moon',
                'slug' => 'welkin-moon-genshin',
                'code' => 'GI-WELKIN',
                'category' => 'pass',
                'price' => 79000,
                'original_price' => 85000,
                'stock' => 9999,
                'description' => 'Dapatkan 300 Genesis Crystals instan + 90 Primogems harian selama 30 hari.',
            ],
            [
                'game_id' => $games['genshin-impact']->id,
                'name' => '300 + 30 Genesis Crystals',
                'slug' => '300-genesis-crystals',
                'code' => 'GI-330',
                'category' => 'points',
                'price' => 79000,
                'original_price' => 85000,
                'stock' => 9999,
                'description' => 'Genesis Crystals resmi HoYoverse via UID & Server.',
            ],
            [
                'game_id' => $games['genshin-impact']->id,
                'name' => '980 + 110 Genesis Crystals',
                'slug' => '980-genesis-crystals',
                'code' => 'GI-1090',
                'category' => 'points',
                'price' => 249000,
                'original_price' => 275000,
                'stock' => 9999,
                'description' => 'Genesis Crystals resmi untuk gacha karakter banner bintang 5.',
            ],
            [
                'game_id' => $games['genshin-impact']->id,
                'name' => '1980 + 260 Genesis Crystals',
                'slug' => '1980-genesis-crystals',
                'code' => 'GI-2240',
                'category' => 'points',
                'price' => 479000,
                'original_price' => 530000,
                'stock' => 9999,
                'description' => 'Bonus dobel kristal pada topup perdana setiap reset tahunan.',
            ],

            // --- HONOR OF KINGS ---
            [
                'game_id' => $games['honor-of-kings']->id,
                'name' => '250 Tokens Honor of Kings',
                'slug' => '250-tokens-hok',
                'code' => 'HOK-250',
                'category' => 'points',
                'price' => 42000,
                'original_price' => 48000,
                'stock' => 9999,
                'description' => 'Token in-game legal Honor of Kings untuk pembelian skin dan pass.',
            ],
            [
                'game_id' => $games['honor-of-kings']->id,
                'name' => 'Weekly Card Honor of Kings',
                'slug' => 'weekly-card-hok',
                'code' => 'HOK-WC',
                'category' => 'pass',
                'price' => 14500,
                'original_price' => 16000,
                'stock' => 9999,
                'description' => 'Dapatkan kupon diskon dan token harian setiap login.',
            ],
            [
                'game_id' => $games['honor-of-kings']->id,
                'name' => '800 + 68 Tokens Honor of Kings',
                'slug' => '800-tokens-hok',
                'code' => 'HOK-868',
                'category' => 'points',
                'price' => 145000,
                'original_price' => 160000,
                'stock' => 9999,
                'description' => 'Bisa untuk beli skin Epic tier eksklusif di shop.',
            ],

            // --- HONKAI: STAR RAIL ---
            [
                'game_id' => $games['honkai-star-rail']->id,
                'name' => 'Express Supply Pass',
                'slug' => 'express-supply-pass-hsr',
                'code' => 'HSR-PASS',
                'category' => 'pass',
                'price' => 79000,
                'original_price' => 85000,
                'stock' => 9999,
                'description' => '300 Oneiric Shards instan + 90 Stellar Jade harian selama 30 hari.',
            ],
            [
                'game_id' => $games['honkai-star-rail']->id,
                'name' => '980 + 110 Oneiric Shards',
                'slug' => '980-oneiric-shards',
                'code' => 'HSR-1090',
                'category' => 'points',
                'price' => 249000,
                'original_price' => 275000,
                'stock' => 9999,
                'description' => 'Shards resmi HoYoverse untuk warp banner karakter bintang 5.',
            ],

            // --- ROBLOX ---
            [
                'game_id' => $games['roblox']->id,
                'name' => '400 Robux',
                'slug' => '400-robux',
                'code' => 'RBX-400',
                'category' => 'points',
                'price' => 75000,
                'original_price' => 85000,
                'stock' => 9999,
                'description' => 'Robux resmi masuk langsung ke akun Roblox Anda.',
            ],
            [
                'game_id' => $games['roblox']->id,
                'name' => '800 Robux',
                'slug' => '800-robux',
                'code' => 'RBX-800',
                'category' => 'points',
                'price' => 150000,
                'original_price' => 170000,
                'stock' => 9999,
                'description' => 'Paling laris untuk beli gamepass Blox Fruits & avatar eksklusif.',
            ],

            // --- CALL OF DUTY MOBILE ---
            [
                'game_id' => $games['cod-mobile']->id,
                'name' => '321 + 32 CP CODM',
                'slug' => '321-cp-codm',
                'code' => 'CODM-353',
                'category' => 'points',
                'price' => 50000,
                'original_price' => 56000,
                'stock' => 9999,
                'description' => 'Cukup untuk membuka Battle Pass musim terbaru Call of Duty: Mobile.',
            ],
            [
                'game_id' => $games['cod-mobile']->id,
                'name' => '645 + 65 CP CODM',
                'slug' => '645-cp-codm',
                'code' => 'CODM-710',
                'category' => 'points',
                'price' => 100000,
                'original_price' => 112000,
                'stock' => 9999,
                'description' => 'Isi CP instan via Player ID akun Garena resmi.',
            ],

            // --- POINT BLANK ---
            [
                'game_id' => $games['point-blank']->id,
                'name' => '6.000 PB Cash',
                'slug' => '6000-pb-cash',
                'code' => 'PB-6000',
                'category' => 'points',
                'price' => 50000,
                'original_price' => 55000,
                'stock' => 9999,
                'description' => 'PB Cash resmi Zepetto untuk beli item dan senjata favorit.',
            ],
            [
                'game_id' => $games['point-blank']->id,
                'name' => '12.000 PB Cash',
                'slug' => '12000-pb-cash',
                'code' => 'PB-12000',
                'category' => 'points',
                'price' => 100000,
                'original_price' => 110000,
                'stock' => 9999,
                'description' => 'Proses otomatis instan ke User ID Point Blank Anda.',
            ],

            // --- STEAM WALLET IDR ---
            [
                'game_id' => $games['steam-wallet-idr']->id,
                'name' => 'Steam Wallet Code IDR 60.000',
                'slug' => 'steam-wallet-60k',
                'code' => 'STEAM-60K',
                'category' => 'voucher',
                'price' => 68000,
                'original_price' => 74000,
                'stock' => 9999,
                'description' => 'Kode digital resmi Valve untuk saldo Steam Wallet IDR.',
            ],
            [
                'game_id' => $games['steam-wallet-idr']->id,
                'name' => 'Steam Wallet Code IDR 120.000',
                'slug' => 'steam-wallet-120k',
                'code' => 'STEAM-120K',
                'category' => 'voucher',
                'price' => 135000,
                'original_price' => 145000,
                'stock' => 9999,
                'description' => 'Beli game original favorit di Steam Store tanpa kartu kredit.',
            ],

            // --- WILD RIFT ---
            [
                'game_id' => $games['wild-rift']->id,
                'name' => '1000 Wild Cores',
                'slug' => '1000-wild-cores',
                'code' => 'WR-1000',
                'category' => 'points',
                'price' => 120000,
                'original_price' => 135000,
                'stock' => 9999,
                'description' => 'Beli Wild Pass dan skin Champion League of Legends: Wild Rift.',
            ],
        ];

        foreach ($productsData as $prod) {
            GameProduct::updateOrCreate(['slug' => $prod['slug']], $prod);
        }

        // -------------------------------------------------------------
        // 5. JOKI SERVICES (Comprehensive Tier Boosting & Packages)
        // -------------------------------------------------------------
        $servicesData = [
            // Mobile Legends
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => 'MLBB - Epic ke Legend',
                'slug' => 'mlbb-epic-ke-legend',
                'service_type' => 'rank',
                'rank_from' => 'Epic V',
                'rank_to' => 'Legend V',
                'price' => 35000,
                'original_price' => 50000,
                'unit' => 'Paket',
                'estimated_duration' => '2 - 4 Jam',
                'is_popular' => false,
                'description' => 'Push rank cepat dan bersih dari rank Epic ke Legend. Bebas request hero favorit.',
                'features' => ['Garansi Winrate 85%+', 'Pro Booster Tier Glory', 'Bonus Screenshot Win Streak', 'Bisa Request Hero/Role'],
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => 'MLBB - Legend ke Mythic',
                'slug' => 'mlbb-legend-ke-mythic',
                'service_type' => 'rank',
                'rank_from' => 'Legend V',
                'rank_to' => 'Mythic Placement',
                'price' => 65000,
                'original_price' => 85000,
                'unit' => 'Paket',
                'estimated_duration' => '4 - 6 Jam',
                'is_popular' => true,
                'description' => 'Paket paling diminati! Tembus gerbang Mythic tanpa drama rekan tim troll.',
                'features' => ['Garansi Winrate 90%+', 'Bebas Pilih Role Utama', 'Free 10 Match Placement', 'Update Real-time via WA'],
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => 'MLBB - Mythic Honor ke Glory (25-50 Bintang)',
                'slug' => 'mlbb-honor-ke-glory',
                'service_type' => 'rank',
                'rank_from' => 'Mythic 25 Bintang',
                'rank_to' => 'Mythic Glory 50 Bintang',
                'price' => 135000,
                'original_price' => 170000,
                'unit' => 'Paket',
                'estimated_duration' => '8 - 12 Jam',
                'is_popular' => true,
                'description' => 'Dikerjakan langsung oleh Top Global Booster. Gameplay kelas turnamen.',
                'features' => ['Top Global Semi-Pro Booster', 'Anti Minus & Garansi Poin', 'Jadwal Main Sesuai Permintaan', 'Privasi Akun Terenkripsi'],
            ],
            [
                'game_id' => $games['mobile-legends']->id,
                'name' => 'MLBB - Joki Gendong Mabar (5 Match)',
                'slug' => 'mlbb-joki-gendong-5-match',
                'service_type' => 'mabar',
                'rank_from' => 'Epic / Legend / Mythic',
                'rank_to' => 'Tergantung Match',
                'price' => 55000,
                'original_price' => 70000,
                'unit' => '5 Match',
                'estimated_duration' => '1 - 2 Jam',
                'is_popular' => false,
                'description' => 'Main bareng pro player dalam 1 party. Tanpa perlu serahkan login akun!',
                'features' => ['Akun Tetap Di Tangan Kamu', 'Voice Discord / In-game Bersama', 'Coaching Singkat Makro/Mikro', 'Garansi Menang Minimal 4/5'],
            ],
            // Valorant
            [
                'game_id' => $games['valorant']->id,
                'name' => 'Valorant - Iron ke Silver',
                'slug' => 'valo-iron-ke-silver',
                'service_type' => 'rank',
                'rank_from' => 'Iron 1',
                'rank_to' => 'Silver 1',
                'price' => 55000,
                'original_price' => 75000,
                'unit' => 'Paket',
                'estimated_duration' => '3 - 5 Jam',
                'is_popular' => false,
                'description' => 'Keluar dari low rank elo hell dengan mulus. Dikerjakan booster Radiant/Ascendant.',
                'features' => ['No Cheat / 100% Manual Hand', 'VPN Server Indonesia', 'Pilihan Agent Fleksibel', 'KDA 1.8+ Rata-rata'],
            ],
            [
                'game_id' => $games['valorant']->id,
                'name' => 'Valorant - Silver ke Gold',
                'slug' => 'valo-silver-ke-gold',
                'service_type' => 'rank',
                'rank_from' => 'Silver 1',
                'rank_to' => 'Gold 1',
                'price' => 79000,
                'original_price' => 99000,
                'unit' => 'Paket',
                'estimated_duration' => '4 - 6 Jam',
                'is_popular' => true,
                'description' => 'Tembus Gold dan raih gun buddy eksklusif act baru.',
                'features' => ['Garansi Winrate Tinggi', 'Live Discord Stream (Optional)', 'Offline Mode Aktif', 'Pro Crosshair Placement'],
            ],
            // Free Fire
            [
                'game_id' => $games['free-fire']->id,
                'name' => 'Free Fire - Diamond ke Master',
                'slug' => 'ff-diamond-ke-master',
                'service_type' => 'rank',
                'rank_from' => 'Diamond 1',
                'rank_to' => 'Master',
                'price' => 45000,
                'original_price' => 60000,
                'unit' => 'Paket',
                'estimated_duration' => '3 - 5 Jam',
                'is_popular' => true,
                'description' => 'Push rank Battle Royale cepat dengan posisi Top 1 Booyah beruntun.',
                'features' => ['Banyak Booyah', 'Headshot Murni No Script', 'Laporan Match Riwayat', 'Cepat & Aman'],
            ],
            // PUBG Mobile
            [
                'game_id' => $games['pubg-mobile']->id,
                'name' => 'PUBG Mobile - Crown ke Ace',
                'slug' => 'pubg-crown-ke-ace',
                'service_type' => 'rank',
                'rank_from' => 'Crown V',
                'rank_to' => 'Ace Tier',
                'price' => 60000,
                'original_price' => 80000,
                'unit' => 'Paket',
                'estimated_duration' => '4 - 8 Jam',
                'is_popular' => true,
                'description' => 'Raih gelar Ace dan badge parasut eksklusif musim ini.',
                'features' => ['KD Tinggi Terjaga', 'Rotasi Safe Zone Optimal', 'Manual 100%', 'Screenshot Statistik'],
            ],
            // Genshin Impact
            [
                'game_id' => $games['genshin-impact']->id,
                'name' => 'Genshin - Spiral Abyss 36 Star (Floor 9-12)',
                'slug' => 'genshin-spiral-abyss-36-star',
                'service_type' => 'quest',
                'rank_from' => 'AR 45+',
                'rank_to' => '36 Star Clear',
                'price' => 45000,
                'original_price' => 60000,
                'unit' => 'Floor 9-12',
                'estimated_duration' => '1 - 2 Jam',
                'is_popular' => true,
                'description' => 'Sapu bersih 600 Primogems dari Abyss tanpa stres rotasi combo.',
                'features' => ['Full 36 Star Guarantee', 'Artifact & Team Tuning', 'Bisa Semua Roster', 'Screenshot Bukti Selesai'],
            ],
            // Honor of Kings
            [
                'game_id' => $games['honor-of-kings']->id,
                'name' => 'Honor of Kings - Master ke Grandmaster',
                'slug' => 'hok-master-ke-grandmaster',
                'service_type' => 'rank',
                'rank_from' => 'Master V',
                'rank_to' => 'Grandmaster',
                'price' => 85000,
                'original_price' => 110000,
                'unit' => 'Paket',
                'estimated_duration' => '5 - 8 Jam',
                'is_popular' => true,
                'description' => 'Capai rank bergengsi Grandmaster di Honor of Kings server global.',
                'features' => ['Winrate 90%+', 'Hero Pool Luas', 'Laporan Progres Harian', 'Garansi Akun Aman'],
            ],
        ];

        $services = [];
        foreach ($servicesData as $srv) {
            $services[$srv['slug']] = JokiService::updateOrCreate(['slug' => $srv['slug']], $srv);
        }

        // -------------------------------------------------------------
        // 6. PROMOTIONS
        // -------------------------------------------------------------
        $promo1 = Promotion::firstOrCreate(
            ['code' => 'AETHER2026'],
            [
                'name' => 'Diskon Spesial Musim 2026',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_spend' => 50000,
                'max_discount' => 30000,
                'valid_from' => now()->subDays(10),
                'valid_until' => now()->addDays(60),
                'usage_limit' => 500,
                'used_count' => 45,
                'is_active' => true,
            ]
        );

        $promo2 = Promotion::firstOrCreate(
            ['code' => 'WELCOME30'],
            [
                'name' => 'Diskon Pelanggan Baru 30%',
                'discount_type' => 'percentage',
                'discount_value' => 30,
                'min_spend' => 30000,
                'max_discount' => 25000,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addDays(90),
                'usage_limit' => 200,
                'used_count' => 18,
                'is_active' => true,
            ]
        );

        // -------------------------------------------------------------
        // 7. REALISTIC CUSTOMER TRANSACTIONS & AUDIT TRAILS
        // -------------------------------------------------------------
        // Order 1: Processing joki order, assigned to booster
        $srvMlbb = $services['mlbb-legend-ke-mythic'];
        $order1 = Order::firstOrCreate(
            ['order_code' => 'AJ-8821'],
            [
                'user_id' => $customer->id,
                'joki_profile_id' => $jokiProfile->id,
                'promotion_id' => $promo1->id,
                'customer_name' => 'Reza Pratama',
                'customer_email' => 'customer@example.com',
                'customer_whatsapp' => '085712345678',
                'account_id' => '12847291',
                'account_server' => '2194',
                'account_nickname' => 'ShadowBlade',
                'login_via' => 'Moonton ID',
                'account_notes' => 'Tolong pakai hero Assassin / Gusion ya kak.',
                'subtotal' => $srvMlbb->price,
                'discount_amount' => $srvMlbb->price * 0.20,
                'total_amount' => $srvMlbb->price * 0.80,
                'order_status' => 'processing',
                'completed_at' => null,
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order1->id],
            [
                'joki_service_id' => $srvMlbb->id,
                'item_type' => 'joki_service',
                'item_name' => $srvMlbb->name,
                'price' => $srvMlbb->price,
                'quantity' => 1,
                'subtotal' => $srvMlbb->price,
                'meta' => ['rank_from' => 'Legend V', 'rank_to' => 'Mythic'],
            ]
        );

        Payment::firstOrCreate(
            ['order_id' => $order1->id],
            [
                'payment_code' => 'PAY-AJ8821',
                'payment_method' => 'qris',
                'amount' => $order1->total_amount,
                'status' => 'paid',
                'paid_at' => now()->subHours(2),
                'payment_proof' => 'proofs/sample-qris.png',
            ]
        );

        // Order 2: Completed Valorant order
        $srvValo = $services['valo-silver-ke-gold'];
        $order2 = Order::firstOrCreate(
            ['order_code' => 'AJ-9923'],
            [
                'user_id' => $customer->id,
                'joki_profile_id' => $jokiProfile->id,
                'promotion_id' => null,
                'customer_name' => 'Reza Pratama',
                'customer_email' => 'customer@example.com',
                'customer_whatsapp' => '085712345678',
                'account_id' => 'RiotTag#9999',
                'account_server' => 'APAC',
                'account_nickname' => 'ViperMain',
                'login_via' => 'Riot Games',
                'account_notes' => 'Mohon offline mode saat pengerjaan.',
                'subtotal' => $srvValo->price,
                'discount_amount' => 0,
                'total_amount' => $srvValo->price,
                'order_status' => 'completed',
                'completed_at' => now()->subHours(8),
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order2->id],
            [
                'joki_service_id' => $srvValo->id,
                'item_type' => 'joki_service',
                'item_name' => $srvValo->name,
                'price' => $srvValo->price,
                'quantity' => 1,
                'subtotal' => $srvValo->price,
                'meta' => ['agent_request' => 'Viper / Omen'],
            ]
        );

        Payment::firstOrCreate(
            ['order_id' => $order2->id],
            [
                'payment_code' => 'PAY-AJ9923',
                'payment_method' => 'bca',
                'amount' => $order2->total_amount,
                'status' => 'paid',
                'paid_at' => now()->subHours(12),
            ]
        );

        // Order 3: Pending Genshin order
        $srvGenshin = $services['genshin-spiral-abyss-36-star'];
        $order3 = Order::firstOrCreate(
            ['order_code' => 'AJ-7410'],
            [
                'user_id' => $customer->id,
                'joki_profile_id' => null,
                'promotion_id' => null,
                'customer_name' => 'Reza Pratama',
                'customer_email' => 'customer@example.com',
                'customer_whatsapp' => '085712345678',
                'account_id' => '801239845',
                'account_server' => 'Asia',
                'account_nickname' => 'TravelerAether',
                'login_via' => 'HoYoverse',
                'account_notes' => 'Rotasi tim Neuvillette dan Raiden ya kak.',
                'subtotal' => $srvGenshin->price,
                'discount_amount' => 0,
                'total_amount' => $srvGenshin->price,
                'order_status' => 'pending',
                'completed_at' => null,
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order3->id],
            [
                'joki_service_id' => $srvGenshin->id,
                'item_type' => 'joki_service',
                'item_name' => $srvGenshin->name,
                'price' => $srvGenshin->price,
                'quantity' => 1,
                'subtotal' => $srvGenshin->price,
                'meta' => ['floor' => 'Floor 9-12'],
            ]
        );

        Payment::firstOrCreate(
            ['order_id' => $order3->id],
            [
                'payment_code' => 'PAY-AJ7410',
                'payment_method' => 'qris',
                'amount' => $order3->total_amount,
                'status' => 'pending',
            ]
        );

        // -------------------------------------------------------------
        // 8. NOTIFICATIONS
        // -------------------------------------------------------------
        Notification::firstOrCreate(
            ['title' => 'Pesanan #AJ-8821 Sedang Dikerjakan'],
            [
                'user_id' => $customer->id,
                'message' => 'Booster Kael telah memulai pengerjaan joki rank Mobile Legends Anda.',
                'type' => 'order',
                'link' => '/order/AJ-8821',
                'is_read' => false,
            ]
        );

        Notification::firstOrCreate(
            ['title' => 'Pesanan Baru Ditugaskan'],
            [
                'user_id' => $jokiUser->id,
                'message' => 'Anda ditugaskan untuk mengerjakan order #AJ-8821 (MLBB - Legend ke Mythic).',
                'type' => 'order',
                'link' => '/order/AJ-8821',
                'is_read' => true,
                'read_at' => now()->subHour(),
            ]
        );

        // -------------------------------------------------------------
        // 9. AI CONVERSATIONS & CONSULTATIONS
        // -------------------------------------------------------------
        $conv = AIConversation::firstOrCreate(
            ['session_id' => 'session-kael-consult'],
            [
                'user_id' => $customer->id,
                'title' => 'Konsultasi Rekomendasi Joki MLBB',
                'is_active' => true,
            ]
        );

        AIMessage::firstOrCreate(
            ['ai_conversation_id' => $conv->id, 'role' => 'user'],
            [
                'content' => 'Halo! Saya sekarang berada di rank Legend V dan ingin push ke Mythic sebelum season berakhir, paket apa yang paling cocok?',
            ]
        );

        AIMessage::firstOrCreate(
            ['ai_conversation_id' => $conv->id, 'role' => 'assistant'],
            [
                'content' => 'Halo! Untuk akun di rank Legend V, paket paling pas adalah **MLBB - Legend ke Mythic** seharga Rp 65.000 dengan estimasi pengerjaan 4-6 jam dan garansi winrate 90%+. Paket ini dikerjakan oleh Booster Kael.',
                'meta' => ['recommended_service_id' => $srvMlbb->id],
            ]
        );

        // -------------------------------------------------------------
        // 10. REAL CUSTOMER REVIEWS
        // -------------------------------------------------------------
        $reviewsData = [
            [
                'user_id' => $customer->id,
                'order_id' => $order2->id,
                'joki_profile_id' => $jokiProfile->id,
                'customer_name' => 'Reza Pratama',
                'game_name' => 'Valorant',
                'service_name' => 'Silver ke Gold',
                'rating' => 5,
                'comment' => 'KDA boosternya mantap 2.4, headshot percentage tinggi dan aman karena manual tangan murni tanpa cheat. Recommended banget buat yang stuck di elo hell.',
                'is_verified' => true,
            ],
            [
                'user_id' => null,
                'order_id' => null,
                'joki_profile_id' => $jokiProfile->id,
                'customer_name' => 'Bintang Ramadhan',
                'game_name' => 'Mobile Legends',
                'service_name' => 'Legend ke Mythic',
                'rating' => 5,
                'comment' => 'Order jam 8 malam, jam 1 subuh sudah Mythic win streak tanpa lose satu kalipun. Boosternya ramah banget dan akun aman sentosa.',
                'is_verified' => true,
            ],
            [
                'user_id' => null,
                'order_id' => null,
                'joki_profile_id' => null,
                'customer_name' => 'Kevin Sanjaya',
                'game_name' => 'Genshin Impact',
                'service_name' => 'Spiral Abyss 36 Star',
                'rating' => 5,
                'comment' => 'Artifact saya padahal pas-pasan, tapi sama boosternya di-combo rotasi sampai 36 star rata. Layanan top markotop!',
                'is_verified' => true,
            ],
        ];

        foreach ($reviewsData as $rev) {
            Review::firstOrCreate(['comment' => $rev['comment']], $rev);
        }

        // -------------------------------------------------------------
        // 11. AUDIT LOGS
        // -------------------------------------------------------------
        AuditLog::firstOrCreate(
            ['action' => 'order_created', 'details' => 'Order #AJ-8821 berhasil dibuat oleh customer Reza Pratama.'],
            [
                'user_id' => $customer->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'new_values' => ['order_code' => 'AJ-8821', 'total_amount' => 52000],
            ]
        );

        AuditLog::firstOrCreate(
            ['action' => 'payment_confirmed', 'details' => 'Pembayaran order #AJ-8821 sebesar Rp 52.000 diverifikasi via QRIS.'],
            [
                'user_id' => $admin->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'new_values' => ['payment_status' => 'paid', 'order_status' => 'processing'],
            ]
        );

        AuditLog::firstOrCreate(
            ['action' => 'booster_assigned', 'details' => 'Order #AJ-8821 ditugaskan ke Booster Kael the Ascendant.'],
            [
                'user_id' => $admin->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'new_values' => ['joki_profile_id' => $jokiProfile->id],
            ]
        );
    }
}
