<?php

namespace App\Services;

use App\Models\Order;

class WhatsAppService
{
    /**
     * Get configured Admin WhatsApp phone number.
     */
    public function getAdminPhone(): string
    {
        return $this->formatPhoneNumber(config('whatsapp.admin_phone', '6281384849982'));
    }

    /**
     * Get configured Booster / Penjoki WhatsApp phone number.
     */
    public function getBoosterPhone(): string
    {
        return $this->formatPhoneNumber(config('whatsapp.booster_phone', '6282298561286'));
    }

    /**
     * Get customer phone number from order or fallback.
     */
    public function getCustomerPhone(Order $order): string
    {
        $rawNumber = $order->customer_whatsapp ?: config('whatsapp.default_customer_phone', '6285147486281');

        return $this->formatPhoneNumber($rawNumber);
    }

    /**
     * Format raw phone number into standard international format without symbols (e.g. 6281384849982).
     */
    public function formatPhoneNumber(?string $number): string
    {
        if (empty($number)) {
            return '6281384849982';
        }

        // Remove non-digit characters (spaces, dashes, plus, parentheses)
        $digits = preg_replace('/\D+/', '', $number);

        if (str_starts_with($digits, '08')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Check if an order contains a joki service item.
     */
    public function isJokiOrder(Order $order): bool
    {
        return $order->orderItems()
            ->where(function ($q) {
                $q->where('item_type', 'joki_service')
                    ->orWhereNotNull('joki_service_id');
            })
            ->exists();
    }

    /**
     * Build WhatsApp URL with encoded message payload.
     */
    public function generateUrl(string $phone, string $message): string
    {
        $cleanedPhone = $this->formatPhoneNumber($phone);
        $encodedText = rawurlencode($message);

        return "https://wa.me/{$cleanedPhone}?text={$encodedText}";
    }

    /**
     * Build WhatsApp notification message for Admin (Pembelian DM Top Up atau Joki).
     */
    public function buildAdminNotificationMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $isJoki = $this->isJokiOrder($order);
        $orderType = $isJoki ? 'Jasa Joki Game' : 'Top Up Game / Diamond';

        $gameName = $firstItem?->jokiService?->game?->name
            ?? $firstItem?->gameProduct?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';

        $itemName = $firstItem?->item_name ?? 'Item Game';
        $statusLabel = strtoupper($order->order_status);
        $trackingUrl = route('orders.show', $order->order_code);

        $lines = [
            'Halo Admin Aethereal Joki,',
            '',
            'Terdapat pesanan baru masuk di sistem:',
            "* Kode Pesanan: #{$order->order_code}",
            "* Kategori: {$orderType}",
            "* Game: {$gameName}",
            "* Paket / Produk: {$itemName}",
            "* Akun Tujuan: ID {$order->account_id}".($order->account_server ? " ({$order->account_server})" : ''),
            '* Nickname: '.($order->account_nickname ?: '-'),
        ];

        if ($isJoki && $order->login_via) {
            $lines[] = "* Login Via: {$order->login_via}";
        }

        if (! empty($order->account_notes)) {
            $lines[] = "* Catatan: {$order->account_notes}";
        }

        $paymentMethod = $order->payment?->payment_method ? strtoupper($order->payment->payment_method) : 'QRIS';

        $lines[] = '* Total Pembayaran: Rp '.number_format($order->total_amount, 0, ',', '.');
        $lines[] = "* Metode Bayar: {$paymentMethod}";
        $lines[] = "* Status Pesanan: {$statusLabel}";
        $lines[] = "* Data Pembeli: {$order->customer_name} ({$order->customer_whatsapp})";
        $lines[] = '';
        $lines[] = 'Detail & Kelola Pesanan:';
        $lines[] = $trackingUrl;

        return implode("\n", $lines);
    }

    /**
     * Build WhatsApp notification message for Penjoki / Booster (Khusus Jokian Masuk).
     */
    public function buildBoosterNotificationMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $gameName = $firstItem?->jokiService?->game?->name
            ?? $firstItem?->meta['game_name']
            ?? 'Game';

        $serviceName = $firstItem?->item_name ?? 'Layanan Joki';
        $boosterCut = (float) $order->total_amount * 0.80;
        $workstationUrl = route('joki.dashboard.orders.show', $order->order_code);
        $statusLabel = strtoupper($order->order_status);

        $lines = [
            'Halo Booster Aethereal Joki,',
            '',
            'Terdapat pesanan joki baru masuk dan tersedia untuk Anda:',
            "* Kode Pesanan: #{$order->order_code}",
            "* Game: {$gameName}",
            "* Paket Layanan: {$serviceName}",
            "* Akun Game: ID {$order->account_id}".($order->account_server ? " ({$order->account_server})" : ''),
            '* Nickname: '.($order->account_nickname ?: '-'),
            '* Login Via: '.($order->login_via ?: 'Moonton / Game Account'),
        ];

        if (! empty($order->account_notes)) {
            $lines[] = "* Catatan Pembeli: {$order->account_notes}";
        }

        $lines[] = '* Estimasi Komisi Booster: Rp '.number_format($boosterCut, 0, ',', '.');
        $lines[] = "* Status Pesanan: {$statusLabel}";
        $lines[] = '';
        $lines[] = 'Silakan buka Workstation Joki untuk mengambil atau memantau tugas:';
        $lines[] = $workstationUrl;

        return implode("\n", $lines);
    }

    /**
     * Build WhatsApp notification message for Pembeli (Status PENDING - Menunggu Pembayaran).
     */
    public function buildCustomerPendingMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $isJoki = $this->isJokiOrder($order);
        $category = $isJoki ? 'Joki Game' : 'Top Up Game';
        $itemName = $firstItem?->item_name ?? 'Produk Game';
        $paymentMethod = $order->payment?->payment_method ? strtoupper($order->payment->payment_method) : 'QRIS';
        $trackingUrl = route('orders.show', $order->order_code);

        $lines = [
            "Halo {$order->customer_name},",
            '',
            'Terima kasih telah melakukan pemesanan di Aethereal Joki.',
            'Berikut adalah rincian tagihan pesanan Anda:',
            "* Nomor Pesanan: #{$order->order_code}",
            "* Kategori: {$category}",
            "* Produk / Paket: {$itemName}",
            "* Akun Game: ID {$order->account_id}".($order->account_server ? " ({$order->account_server})" : ''),
            '* Total Tagihan: Rp '.number_format($order->total_amount, 0, ',', '.'),
            "* Metode Pembayaran: {$paymentMethod}",
            '* Status: Menunggu Pembayaran (Pending)',
            '',
            'Silakan selesaikan pembayaran sesuai petunjuk yang tersedia.',
            'Lacak progres dan instruksi pembayaran di tautan berikut:',
            $trackingUrl,
            '',
            'Jika Anda butuh bantuan, silakan balas pesan ini.',
        ];

        return implode("\n", $lines);
    }

    /**
     * Build WhatsApp notification message for Pembeli (Status SUKSES / LUNAS).
     */
    public function buildCustomerSuccessMessage(Order $order): string
    {
        $firstItem = $order->orderItems->first();
        $isJoki = $this->isJokiOrder($order);
        $category = $isJoki ? 'Joki Game' : 'Top Up Game';
        $itemName = $firstItem?->item_name ?? 'Produk Game';
        $trackingUrl = route('orders.show', $order->order_code);

        $isCompleted = $order->order_status === 'completed';
        $statusText = $isCompleted ? 'Sukses & Selesai' : 'Pembayaran Berhasil (Diproses)';

        if (! $isJoki) {
            $processNote = $isCompleted
                ? 'Top up Diamond / Item telah berhasil dikirim dan sukses masuk ke akun game Anda.'
                : 'Top up Diamond / Item sedang diproses langsung ke akun game Anda.';
        } else {
            $processNote = $isCompleted
                ? 'Pesanan joki Anda telah selesai dikerjakan dengan hasil maksimal.'
                : 'Pesanan joki Anda telah masuk ke antrean booster dan segera dikerjakan.';
        }

        $lines = [
            "Halo {$order->customer_name},",
            '',
            'Kabar baik! Pembayaran untuk pesanan Anda telah berhasil kami terima dan terverifikasi.',
            "* Nomor Pesanan: #{$order->order_code}",
            "* Kategori: {$category}",
            "* Produk / Paket: {$itemName}",
            "* Akun Game: ID {$order->account_id}".($order->account_server ? " ({$order->account_server})" : ''),
            "* Status: {$statusText}",
            '',
            $processNote,
            'Anda dapat memantau invoice dan rincian lengkap di:',
            $trackingUrl,
            '',
            'Terima kasih telah mempercayakan kebutuhan gaming Anda kepada Aethereal.',
        ];

        return implode("\n", $lines);
    }

    /**
     * Get WhatsApp URL for Admin notification.
     */
    public function getAdminRedirectUrl(Order $order): string
    {
        $message = $this->buildAdminNotificationMessage($order);

        return $this->generateUrl($this->getAdminPhone(), $message);
    }

    /**
     * Get WhatsApp URL for Booster / Penjoki notification.
     */
    public function getBoosterRedirectUrl(Order $order): string
    {
        $message = $this->buildBoosterNotificationMessage($order);

        return $this->generateUrl($this->getBoosterPhone(), $message);
    }

    /**
     * Get WhatsApp URL for Pembeli (Pending status).
     */
    public function getCustomerPendingRedirectUrl(Order $order): string
    {
        $message = $this->buildCustomerPendingMessage($order);

        return $this->generateUrl($this->getCustomerPhone($order), $message);
    }

    /**
     * Get WhatsApp URL for Pembeli (Success status).
     */
    public function getCustomerSuccessRedirectUrl(Order $order): string
    {
        $message = $this->buildCustomerSuccessMessage($order);

        return $this->generateUrl($this->getCustomerPhone($order), $message);
    }

    /**
     * Get automated customer redirect URL based on current payment/order status.
     */
    public function getCustomerAutoRedirectUrl(Order $order): string
    {
        $isPaid = ($order->payment && $order->payment->status === 'paid')
            || in_array($order->order_status, ['paid', 'processing', 'completed']);

        return $isPaid
            ? $this->getCustomerSuccessRedirectUrl($order)
            : $this->getCustomerPendingRedirectUrl($order);
    }
}
