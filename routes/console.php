<?php

use App\Services\TelegramService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:test {chat_id? : Target chat ID to receive the test message}', function (TelegramService $telegram) {
    $chatId = $this->argument('chat_id') ?: $telegram->getDefaultChatId();

    if (empty($chatId)) {
        $this->error('Target chat ID is missing! Provide one as argument (e.g. php artisan telegram:test 123456789) or set TELEGRAM_CHAT_ID in .env.');

        return 1;
    }

    $this->info("Sending test notification to Telegram chat: {$chatId}...");

    $testMessage = implode("\n", [
        '🚀 <b>TEST NOTIFIKASI TELEGRAM BERHASIL</b>',
        '━━━━━━━━━━━━━━━━━━━━',
        '🤖 <b>Bot:</b> @Aetherel_bot',
        '🌐 <b>Aplikasi:</b> Aethereal Joki & Top Up',
        '⏰ <b>Waktu:</b> '.now()->format('d M Y H:i:s').' WIB',
        '✅ <b>Keterangan:</b> Integrasi bot Telegram untuk notifikasi pesanan dan jokian siap digunakan!',
        '━━━━━━━━━━━━━━━━━━━━',
    ]);

    $result = $telegram->sendMessage($chatId, $testMessage);

    if ($result) {
        $this->info('Test message successfully sent to Telegram!');

        return 0;
    }

    $this->error('Failed to send test message. Check your bot token, chat ID, and network connection.');

    return 1;
})->purpose('Send a test notification message to Telegram');

Artisan::command('telegram:updates', function (TelegramService $telegram) {
    $this->info('Fetching recent bot updates from Telegram...');

    $updates = $telegram->getUpdates();

    if (empty($updates)) {
        $this->warn('No recent messages found on bot.');
        $this->line('Tip: Open Telegram, search @Aetherel_bot, click "Start" or send a message, then run this command again.');

        return 0;
    }

    $this->info('Found '.count($updates).' update(s):');
    $rows = [];

    foreach ($updates as $update) {
        $message = $update['message'] ?? $update['channel_post'] ?? [];
        $chat = $message['chat'] ?? [];
        $from = $message['from'] ?? [];

        $rows[] = [
            'Update ID' => $update['update_id'] ?? '-',
            'Chat ID' => $chat['id'] ?? '-',
            'Type' => $chat['type'] ?? '-',
            'Title/Name' => $chat['title'] ?? ($from['first_name'] ?? '-'),
            'Username' => isset($from['username']) ? '@'.$from['username'] : '-',
            'Text' => $message['text'] ?? '-',
        ];
    }

    $this->table(['Update ID', 'Chat ID', 'Type', 'Title/Name', 'Username', 'Text'], $rows);

    return 0;
})->purpose('Fetch recent Telegram updates to view Chat IDs');
