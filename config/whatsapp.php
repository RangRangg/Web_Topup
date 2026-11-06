<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Notification Recipients Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized phone numbers for WhatsApp notification redirects.
    | Admin: Notifikasi pembelian DM & Joki
    | Booster: Notifikasi khusus order joki masuk
    | Default Customer: Fallback nomor pembeli
    |
    */

    'admin_phone' => env('WHATSAPP_ADMIN_PHONE', '6281384849982'),
    'booster_phone' => env('WHATSAPP_BOOSTER_PHONE', '6282298561286'),
    'default_customer_phone' => env('WHATSAPP_DEFAULT_CUSTOMER_PHONE', '6285147486281'),
];
