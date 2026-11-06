<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TelegramService
{
    protected string $botToken;

    protected ?string $defaultChatId;

    protected ?string $purchaseChatId;

    protected ?string $jokiChatId;

    protected bool $enabled;

    public function __construct()
    {
        $settings = $this->loadSettings();

        $this->botToken = (string) ($settings['bot_token'] ?? config('telegram.bot_token', '8981326385:AAHPaXTRjbX1eFfnlC_MtototXJnjtWQNSs'));
        $this->defaultChatId = ! empty($settings['chat_id']) ? (string) $settings['chat_id'] : (config('telegram.chat_id') ?: null);
        $this->purchaseChatId = ! empty($settings['purchase_chat_id']) ? (string) $settings['purchase_chat_id'] : (! empty(config('telegram.purchase_chat_id')) ? (string) config('telegram.purchase_chat_id') : $this->defaultChatId);
        $this->jokiChatId = ! empty($settings['joki_chat_id']) ? (string) $settings['joki_chat_id'] : (! empty(config('telegram.joki_chat_id')) ? (string) config('telegram.joki_chat_id') : $this->defaultChatId);
        $this->enabled = isset($settings['enabled']) ? (bool) $settings['enabled'] : (bool) config('telegram.enabled', true);
    }

    /**
     * Get settings directory path.
     */
    protected function getSettingsFilePath(): string
    {
        return storage_path('app/settings/telegram.json');
    }

    /**
     * Load settings from storage if exists.
     *
     * @return array<string, mixed>
     */
    public function loadSettings(): array
    {
        if (app()->environment('testing')) {
            return [];
        }

        $path = $this->getSettingsFilePath();
        if (file_exists($path)) {
            try {
                $content = file_get_contents($path);
                $decoded = json_decode((string) $content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (Throwable $e) {
                Log::warning('TelegramService failed reading settings file: '.$e->getMessage());
            }
        }

        return [];
    }

    /**
     * Get current settings array for UI representation.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return [
            'bot_token' => $this->botToken,
            'masked_token' => $this->maskToken($this->botToken),
            'chat_id' => $this->defaultChatId ?? '',
            'purchase_chat_id' => $this->purchaseChatId ?? '',
            'joki_chat_id' => $this->jokiChatId ?? '',
            'enabled' => $this->enabled,
            'is_configured' => ! empty($this->botToken) && ! empty($this->defaultChatId),
        ];
    }

    /**
     * Update settings in storage, memory, and sync to .env if writable.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateSettings(array $data): array
    {
        $botToken = isset($data['bot_token']) ? trim((string) $data['bot_token']) : $this->botToken;
        $chatId = isset($data['chat_id']) ? trim((string) $data['chat_id']) : ($this->defaultChatId ?? '');
        $purchaseChatId = isset($data['purchase_chat_id']) ? trim((string) $data['purchase_chat_id']) : ($this->purchaseChatId ?? '');
        $jokiChatId = isset($data['joki_chat_id']) ? trim((string) $data['joki_chat_id']) : ($this->jokiChatId ?? '');
        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : $this->enabled;

        $payload = [
            'bot_token' => $botToken,
            'chat_id' => $chatId,
            'purchase_chat_id' => $purchaseChatId,
            'joki_chat_id' => $jokiChatId,
            'enabled' => $enabled,
            'updated_at' => now()->toIso8601String(),
        ];

        // Ensure storage directory exists
        $path = $this->getSettingsFilePath();
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (! app()->environment('testing')) {
            file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Update in-memory service properties
        $this->botToken = $botToken;
        $this->defaultChatId = $chatId ?: null;
        $this->purchaseChatId = $purchaseChatId ?: $this->defaultChatId;
        $this->jokiChatId = $jokiChatId ?: $this->defaultChatId;
        $this->enabled = $enabled;

        // Sync with Laravel in-memory config
        config([
            'telegram.bot_token' => $this->botToken,
            'telegram.chat_id' => $this->defaultChatId,
            'telegram.purchase_chat_id' => $this->purchaseChatId,
            'telegram.joki_chat_id' => $this->jokiChatId,
            'telegram.enabled' => $this->enabled,
        ]);

        // Sync with .env file (skip in testing to prevent corrupting local environment)
        if (! app()->environment('testing')) {
            $this->syncToEnv([
                'TELEGRAM_BOT_TOKEN' => $botToken,
                'TELEGRAM_CHAT_ID' => $chatId,
                'TELEGRAM_PURCHASE_CHAT_ID' => $purchaseChatId,
                'TELEGRAM_JOKI_CHAT_ID' => $jokiChatId,
                'TELEGRAM_NOTIFICATIONS_ENABLED' => $enabled ? 'true' : 'false',
            ]);
        }

        return $this->getSettings();
    }

    /**
     * Safely synchronize settings to .env file.
     *
     * @param  array<string, string>  $envMap
     */
    protected function syncToEnv(array $envMap): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        $envPath = base_path('.env');
        if (! file_exists($envPath) || ! is_writable($envPath)) {
            return false;
        }

        try {
            $envContent = (string) file_get_contents($envPath);

            foreach ($envMap as $key => $value) {
                // If key already exists in .env, replace it
                $pattern = "/^{$key}=.*$/m";
                $replacement = "{$key}={$value}";

                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    // Append key to the end of .env
                    $envContent = rtrim($envContent)."\n{$replacement}\n";
                }
            }

            file_put_contents($envPath, $envContent);

            return true;
        } catch (Throwable $e) {
            Log::warning('TelegramService syncToEnv failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Mask token for sensitive display.
     */
    public function maskToken(?string $token): string
    {
        if (empty($token)) {
            return '-';
        }

        $length = strlen($token);
        if ($length <= 8) {
            return '••••••••';
        }

        return substr($token, 0, 5).'••••••••'.substr($token, -4);
    }

    /**
     * Get configured bot token.
     */
    public function getBotToken(): string
    {
        return $this->botToken;
    }

    /**
     * Get default target chat ID.
     */
    public function getDefaultChatId(): ?string
    {
        return $this->defaultChatId;
    }

    /**
     * Get target chat ID for purchase orders.
     */
    public function getPurchaseChatId(): ?string
    {
        return $this->purchaseChatId;
    }

    /**
     * Get target chat ID for joki orders.
     */
    public function getJokiChatId(): ?string
    {
        return $this->jokiChatId;
    }

    /**
     * Check if Telegram notifications are enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->botToken);
    }

    /**
     * Send notification for a newly created order (Top Up / Pembelian or Joki).
     */
    public function notifyOrderCreated(Order $order): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $order->loadMissing(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'payment']);

        if ($order->isJoki()) {
            return $this->sendJokiOrderNotification($order);
        }

        return $this->sendPurchaseOrderNotification($order);
    }

    /**
     * Send notification for a successful payment (Top Up or Joki).
     */
    public function notifyPaymentSuccess(Order $order): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $order->loadMissing(['orderItems.jokiService.game', 'orderItems.gameProduct.game', 'payment']);

        if ($order->isJoki()) {
            return $this->sendJokiPaymentSuccessNotification($order);
        }

        return $this->sendPurchasePaymentSuccessNotification($order);
    }

    /**
     * Send purchase (Top Up / Game Product) order notification to Telegram.
     */
    public function sendPurchaseOrderNotification(Order $order): bool
    {
        $chatId = ! empty($this->purchaseChatId) ? $this->purchaseChatId : $this->defaultChatId;
        if (empty($chatId)) {
            Log::info('TelegramService: Target chat_id for purchase order is not configured.');

            return false;
        }

        $message = $this->buildPurchaseOrderMessage($order);
        $sent = $this->sendMessage($chatId, $message);

        // Fallback to default chat ID if custom chat failed and is different
        if (! $sent && ! empty($this->defaultChatId) && $chatId !== $this->defaultChatId) {
            Log::info("TelegramService: Fallback sending purchase order notification to default chat {$this->defaultChatId}");
            $sent = $this->sendMessage($this->defaultChatId, $message);
        }

        return $sent;
    }

    /**
     * Send Joki order notification to Telegram.
     */
    public function sendJokiOrderNotification(Order $order): bool
    {
        $chatId = ! empty($this->jokiChatId) ? $this->jokiChatId : $this->defaultChatId;
        if (empty($chatId)) {
            Log::info('TelegramService: Target chat_id for joki order is not configured.');

            return false;
        }

        $message = $this->buildJokiOrderMessage($order);
        $sent = $this->sendMessage($chatId, $message);

        // Fallback to default chat ID if custom chat failed and is different
        if (! $sent && ! empty($this->defaultChatId) && $chatId !== $this->defaultChatId) {
            Log::info("TelegramService: Fallback sending joki order notification to default chat {$this->defaultChatId}");
            $sent = $this->sendMessage($this->defaultChatId, $message);
        }

        return $sent;
    }

    /**
     * Send purchase payment success notification to Telegram.
     */
    public function sendPurchasePaymentSuccessNotification(Order $order): bool
    {
        $chatId = ! empty($this->purchaseChatId) ? $this->purchaseChatId : $this->defaultChatId;
        if (empty($chatId)) {
            return false;
        }

        $message = $this->buildPurchasePaymentSuccessMessage($order);
        $sent = $this->sendMessage($chatId, $message);

        // Fallback to default chat ID if custom chat failed and is different
        if (! $sent && ! empty($this->defaultChatId) && $chatId !== $this->defaultChatId) {
            Log::info("TelegramService: Fallback sending purchase payment success to default chat {$this->defaultChatId}");
            $sent = $this->sendMessage($this->defaultChatId, $message);
        }

        return $sent;
    }

    /**
     * Send Joki payment success notification to Telegram.
     */
    public function sendJokiPaymentSuccessNotification(Order $order): bool
    {
        $chatId = ! empty($this->jokiChatId) ? $this->jokiChatId : $this->defaultChatId;
        if (empty($chatId)) {
            return false;
        }

        $message = $this->buildJokiPaymentSuccessMessage($order);
        $sent = $this->sendMessage($chatId, $message);

        // Fallback to default chat ID if custom chat failed and is different
        if (! $sent && ! empty($this->defaultChatId) && $chatId !== $this->defaultChatId) {
            Log::info("TelegramService: Fallback sending joki payment success to default chat {$this->defaultChatId}");
            $sent = $this->sendMessage($this->defaultChatId, $message);
        }

        return $sent;
    }

    /**
     * Build HTML message for purchase order created.
     */
    public function buildPurchaseOrderMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $gameName = $firstItem?->gameProduct?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';
        $productName = $firstItem?->item_name ?? 'Produk Top Up';
        $paymentMethod = $order->payment?->payment_method ? strtoupper($order->payment->payment_method) : 'QRIS';
        $serverInfo = $order->account_server ? " ({$order->account_server})" : '';
        $trackingUrl = route('orders.show', $order->order_code);

        return implode("\n", [
            '🛒 <b>PESANAN BARU: PEMBELIAN / TOP UP GAME</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            "📦 <b>Kode Pesanan:</b> <code>#{$this->escape($order->order_code)}</code>",
            "🎮 <b>Game:</b> {$this->escape($gameName)}",
            "💎 <b>Produk:</b> {$this->escape($productName)}",
            "🆔 <b>User ID:</b> <code>{$this->escape($order->account_id)}{$this->escape($serverInfo)}</code>",
            '👤 <b>Nickname:</b> '.($order->account_nickname ? $this->escape($order->account_nickname) : '-'),
            "💰 <b>Total Tagihan:</b> <b>{$this->escape($order->formatted_total)}</b>",
            "💳 <b>Metode Bayar:</b> {$this->escape($paymentMethod)}",
            "📊 <b>Status:</b> 🟡 <i>{$this->escape($order->status_label)}</i>",
            "📱 <b>WhatsApp Pembeli:</b> <code>{$this->escape($order->customer_whatsapp)}</code> ({$this->escape($order->customer_name)})",
            '━━━━━━━━━━━━━━━━━━━━',
            "🔗 <a href=\"{$trackingUrl}\">Lihat Detail Pesanan</a>",
        ]);
    }

    /**
     * Build HTML message for joki order created.
     */
    public function buildJokiOrderMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $gameName = $firstItem?->jokiService?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';
        $serviceName = $firstItem?->item_name ?? 'Layanan Joki';
        $paymentMethod = $order->payment?->payment_method ? strtoupper($order->payment->payment_method) : 'QRIS';
        $serverInfo = $order->account_server ? " ({$order->account_server})" : '';
        $boosterCut = (float) $order->total_amount * 0.80;
        $boosterCutFormatted = 'Rp '.number_format($boosterCut, 0, ',', '.');
        $trackingUrl = route('orders.show', $order->order_code);
        $workstationUrl = route('joki.dashboard.orders.show', $order->order_code);

        $lines = [
            '🎮 <b>PESANAN BARU: JASA JOKI GAME</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            "📦 <b>Kode Pesanan:</b> <code>#{$this->escape($order->order_code)}</code>",
            "🎯 <b>Game:</b> {$this->escape($gameName)}",
            "🛡️ <b>Paket Layanan:</b> {$this->escape($serviceName)}",
            "🆔 <b>Akun Game:</b> <code>{$this->escape($order->account_id)}{$this->escape($serverInfo)}</code>",
            '👤 <b>Nickname:</b> '.($order->account_nickname ? $this->escape($order->account_nickname) : '-'),
            '🔑 <b>Login Via:</b> '.($order->login_via ? $this->escape($order->login_via) : 'Moonton / Akun Game'),
        ];

        if (! empty($order->account_notes)) {
            $lines[] = "📝 <b>Catatan:</b> <i>{$this->escape($order->account_notes)}</i>";
        }

        $lines[] = "💰 <b>Total Tagihan:</b> <b>{$this->escape($order->formatted_total)}</b>";
        $lines[] = "💵 <b>Komisi Booster (80%):</b> <b>{$this->escape($boosterCutFormatted)}</b>";
        $lines[] = "💳 <b>Metode Bayar:</b> {$this->escape($paymentMethod)}";
        $lines[] = "📊 <b>Status:</b> 🟡 <i>{$this->escape($order->status_label)}</i>";
        $lines[] = "📱 <b>Data Customer:</b> <code>{$this->escape($order->customer_whatsapp)}</code> ({$this->escape($order->customer_name)})";
        $lines[] = '━━━━━━━━━━━━━━━━━━━━';
        $lines[] = "⚡ <a href=\"{$workstationUrl}\">Buka Workstation Joki</a> | <a href=\"{$trackingUrl}\">Detail Order</a>";

        return implode("\n", $lines);
    }

    /**
     * Build HTML message for purchase payment success.
     */
    public function buildPurchasePaymentSuccessMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $gameName = $firstItem?->gameProduct?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';
        $productName = $firstItem?->item_name ?? 'Produk Top Up';
        $paymentMethod = $order->payment?->payment_method ? strtoupper($order->payment->payment_method) : 'QRIS';
        $serverInfo = $order->account_server ? " ({$order->account_server})" : '';
        $trackingUrl = route('orders.show', $order->order_code);

        return implode("\n", [
            '✅ <b>PEMBAYARAN DITERIMA: TOP UP SELESAI</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            "📦 <b>Kode Pesanan:</b> <code>#{$this->escape($order->order_code)}</code>",
            "🎮 <b>Game:</b> {$this->escape($gameName)}",
            "💎 <b>Produk:</b> {$this->escape($productName)}",
            "🆔 <b>Akun:</b> <code>{$this->escape($order->account_id)}{$this->escape($serverInfo)}</code>".($order->account_nickname ? " ({$this->escape($order->account_nickname)})" : ''),
            "💰 <b>Total Lunas:</b> <b>{$this->escape($order->formatted_total)}</b>",
            "💳 <b>Metode:</b> {$this->escape($paymentMethod)}",
            '🎉 <b>Status:</b> 🟢 <b>SUKSES (Auto-Completed)</b>',
            "📱 <b>WhatsApp:</b> <code>{$this->escape($order->customer_whatsapp)}</code>",
            '━━━━━━━━━━━━━━━━━━━━',
            "🔗 <a href=\"{$trackingUrl}\">Lihat Transaksi</a>",
        ]);
    }

    /**
     * Build HTML message for joki payment success.
     */
    public function buildJokiPaymentSuccessMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $gameName = $firstItem?->jokiService?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';
        $serviceName = $firstItem?->item_name ?? 'Layanan Joki';
        $paymentMethod = $order->payment?->payment_method ? strtoupper($order->payment->payment_method) : 'QRIS';
        $boosterCut = (float) $order->total_amount * 0.80;
        $boosterCutFormatted = 'Rp '.number_format($boosterCut, 0, ',', '.');
        $workstationUrl = route('joki.dashboard.orders.show', $order->order_code);

        return implode("\n", [
            '⚡ <b>PEMBAYARAN JOKI LUNAS: SIAP DIKERJAKAN</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            "📦 <b>Kode Pesanan:</b> <code>#{$this->escape($order->order_code)}</code>",
            "🎯 <b>Game:</b> {$this->escape($gameName)}",
            "🛡️ <b>Paket:</b> {$this->escape($serviceName)}",
            "🆔 <b>Akun:</b> <code>{$this->escape($order->account_id)}</code>".($order->account_nickname ? " ({$this->escape($order->account_nickname)})" : ''),
            '🔑 <b>Login:</b> '.($order->login_via ? $this->escape($order->login_via) : 'Moonton / Akun Game'),
            "💰 <b>Total Lunas:</b> <b>{$this->escape($order->formatted_total)}</b>",
            "💵 <b>Komisi Booster:</b> <b>{$this->escape($boosterCutFormatted)}</b>",
            '🚀 <b>Status:</b> 🔵 <b>SIAP DIAMBIL PENJOKI</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            "⚡ <a href=\"{$workstationUrl}\">Ambil Pesanan di Workstation</a>",
        ]);
    }

    /**
     * Send HTTP POST request to Telegram sendMessage API.
     */
    public function sendMessage(string|int $chatId, string $message, string $parseMode = 'HTML'): bool
    {
        if (empty($this->botToken) || empty($chatId)) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $response = Http::timeout(8)
                ->acceptJson()
                ->post($url, [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('TelegramService send failed: '.$response->body());

            return false;
        } catch (Throwable $e) {
            Log::warning('TelegramService exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get updates from Telegram Bot to inspect recent chat IDs.
     */
    public function getUpdates(): array
    {
        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
            $response = Http::timeout(10)->acceptJson()->get($url);

            return $response->json('result', []);
        } catch (Throwable $e) {
            Log::warning('TelegramService getUpdates exception: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Test bot connection and token validity.
     */
    public function getMe(?string $token = null): ?array
    {
        $botToken = $token ?: $this->botToken;
        if (empty($botToken)) {
            return null;
        }

        try {
            $url = "https://api.telegram.org/bot{$botToken}/getMe";
            $response = Http::timeout(10)->acceptJson()->get($url);

            return $response->json('result');
        } catch (Throwable $e) {
            Log::warning('TelegramService getMe exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Test connection with Telegram API with diagnostic details.
     *
     * @return array{success: bool, message: string, bot: array<string, mixed>|null}
     */
    public function testConnection(?string $token = null): array
    {
        $botToken = $token ? trim($token) : $this->botToken;
        if (empty($botToken)) {
            return [
                'success' => false,
                'message' => 'API Bot Token belum dimasukkan.',
                'bot' => null,
            ];
        }

        try {
            $url = "https://api.telegram.org/bot{$botToken}/getMe";
            $response = Http::timeout(8)->acceptJson()->get($url);

            if ($response->successful() && $response->json('ok') === true) {
                /** @var array<string, mixed> $result */
                $result = $response->json('result') ?? [];
                $username = isset($result['username']) ? '@'.$result['username'] : ($result['first_name'] ?? 'Bot');

                return [
                    'success' => true,
                    'message' => "Koneksi ke bot Telegram ({$username}) berhasil terhubung!",
                    'bot' => $result,
                ];
            }

            $description = $response->json('description') ?? 'Unauthorized: Bot Token salah atau bot tidak ditemukan.';

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Telegram API: '.$description,
                'bot' => null,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: '.$e->getMessage(),
                'bot' => null,
            ];
        }
    }

    /**
     * Send test notification to verify end-to-end receipt.
     *
     * @return array{success: bool, message: string}
     */
    public function sendTestNotification(string|int $chatId, ?string $token = null): array
    {
        $botToken = $token ? trim($token) : $this->botToken;
        $chatId = trim((string) $chatId);

        if (empty($botToken)) {
            return [
                'success' => false,
                'message' => 'Bot Token tidak boleh kosong.',
            ];
        }

        if (empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Target Chat ID tidak boleh kosong.',
            ];
        }

        $timestamp = now()->timezone('Asia/Jakarta')->format('d M Y, H:i:s').' WIB';
        $message = implode("\n", [
            '🚀 <b>TES INTEGRASI TELEGRAM BOT BERHASIL</b>',
            '━━━━━━━━━━━━━━━━━━━━',
            '✅ Pesan ini dikirim dari <b>Aetherel Nexus Admin</b>.',
            "⏱️ <b>Waktu:</b> {$timestamp}",
            "🎯 <b>Target Chat ID:</b> <code>{$this->escape($chatId)}</code>",
            '🤖 <b>Status API:</b> Terhubung & Siap Mengirim Notifikasi Real-time!',
            '━━━━━━━━━━━━━━━━━━━━',
            '<i>Jika Anda menerima pesan ini, konfigurasi API Telegram Anda sudah aktif dan berfungsi normal.</i>',
        ]);

        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $response = Http::timeout(8)->acceptJson()->post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful() && $response->json('ok') === true) {
                return [
                    'success' => true,
                    'message' => "Pesan uji coba berhasil terkirim ke Chat ID: {$chatId}!",
                ];
            }

            $desc = $response->json('description') ?? 'Gagal mengirim pesan ke Telegram.';

            return [
                'success' => false,
                'message' => 'Telegram menolak pengiriman: '.$desc,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan uji coba: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Detect recent chat IDs interacting with the bot via getUpdates.
     *
     * @return array<int, array<string, mixed>>
     */
    public function detectRecentChats(?string $token = null): array
    {
        $botToken = $token ? trim($token) : $this->botToken;
        if (empty($botToken)) {
            return [];
        }

        try {
            $url = "https://api.telegram.org/bot{$botToken}/getUpdates";
            $response = Http::timeout(10)->acceptJson()->get($url);

            if (! $response->successful()) {
                return [];
            }

            $updates = $response->json('result', []);
            $chats = [];

            foreach ($updates as $update) {
                $msg = $update['message'] ?? $update['channel_post'] ?? $update['edited_message'] ?? null;
                if (! $msg || ! isset($msg['chat']['id'])) {
                    continue;
                }

                $chat = $msg['chat'];
                $chatId = (string) $chat['id'];

                $name = trim(($chat['first_name'] ?? '').' '.($chat['last_name'] ?? ''));
                if (empty($name)) {
                    $name = $chat['title'] ?? 'Chat #'.$chatId;
                }

                $chats[$chatId] = [
                    'id' => $chatId,
                    'type' => $chat['type'] ?? 'private',
                    'name' => $name,
                    'username' => isset($chat['username']) ? '@'.$chat['username'] : null,
                    'last_message' => isset($msg['text']) ? Str::limit((string) $msg['text'], 45) : '(media/action)',
                    'date' => isset($msg['date']) ? date('d M Y H:i', (int) $msg['date']) : null,
                ];
            }

            return array_values($chats);
        } catch (Throwable $e) {
            Log::warning('TelegramService detectRecentChats exception: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Escape HTML special characters for Telegram HTML parse mode.
     */
    protected function escape(?string $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
