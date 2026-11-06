<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'nvidia'),
        'api_key' => env('AI_API_KEY'),
        'endpoint' => env('AI_ENDPOINT', 'https://integrate.api.nvidia.com/v1/chat/completions'),
        'model' => env('AI_MODEL', 'meta/llama-3.2-11b-vision-instruct'),
        'timeout' => (int) env('AI_TIMEOUT', 30),
    ],

    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'mock'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET', 'aethereal-secure-webhook-secret-key-2026'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    ],

];
