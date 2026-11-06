<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for sending real-time order alerts to Telegram.
    | bot_token: Telegram Bot API Token from @BotFather
    | chat_id: Default recipient chat_id / group ID / channel ID
    | purchase_chat_id: Recipient for top up / diamond purchases (fallback to chat_id)
    | joki_chat_id: Recipient for jokian orders (fallback to chat_id)
    | enabled: Toggle sending Telegram notifications
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN', '8981326385:AAHPaXTRjbX1eFfnlC_MtototXJnjtWQNSs'),
    'chat_id' => env('TELEGRAM_CHAT_ID', ''),
    'purchase_chat_id' => env('TELEGRAM_PURCHASE_CHAT_ID', ''),
    'joki_chat_id' => env('TELEGRAM_JOKI_CHAT_ID', ''),
    'enabled' => (bool) env('TELEGRAM_NOTIFICATIONS_ENABLED', true),
];
